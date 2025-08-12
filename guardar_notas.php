<?php
session_start();
require_once 'config.php';

// No enviamos header JSON porque vamos a redirigir

if (!isset($_SESSION['user_nie'])) {
    // No autenticado: redirigir a login o error
    header("Location: login.php?error=acceso_denegado");
    exit();
}

$conn = conectarDB();

try {
    $conn->begin_transaction();

    $materia = $_POST['materia'] ?? null;
    $modulo = $_POST['modulo'] ?? null;
    $periodo = (int)($_POST['periodo'] ?? 1);
    $notas = $_POST['notas'] ?? [];
    $docente_nombre = $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido'];

    if ($periodo < 1 || $periodo > 3) {
        throw new Exception("Periodo inválido.");
    }

    if (empty($notas)) {
        throw new Exception("No se recibieron notas.");
    }

    foreach ($notas as $nie => $datos) {
        if (!is_numeric($nie)) {
            throw new Exception("NIE inválido: $nie");
        }
        $nie = (int)$nie;

        $nota = filter_var($datos['nota'], FILTER_VALIDATE_FLOAT);
        if ($nota === false || $nota < 0 || $nota > 10) {
            throw new Exception("Nota inválida para el estudiante $nie.");
        }

        $stmt_est = $conn->prepare("SELECT NIE FROM personal_registrado WHERE NIE = ? AND ROL = 'ESTUDIANTE'");
        $stmt_est->bind_param("i", $nie);
        $stmt_est->execute();
        if ($stmt_est->get_result()->num_rows == 0) {
            $stmt_est->close();
            throw new Exception("El estudiante con NIE $nie no existe o no es estudiante.");
        }
        $stmt_est->close();

        if ($materia) {
            $materia_trim = trim($materia);

            $stmt_check = $conn->prepare("SELECT MATERIA FROM materia WHERE MATERIA COLLATE utf8mb4_general_ci = ? LIMIT 1");
            $stmt_check->bind_param("s", $materia_trim);
            $stmt_check->execute();
            $res_materia = $stmt_check->get_result();

            if ($res_materia->num_rows == 0) {
                $stmt_check->close();
                throw new Exception("La materia '$materia_trim' no existe en la base de datos.");
            } else {
                $row_materia = $res_materia->fetch_assoc();
                $materia_val = $row_materia['MATERIA'];
            }
            $stmt_check->close();

            $stmt = $conn->prepare("
                INSERT INTO notas (NIE, MATERIA, PERIODO, NOTA, MODULO, NOTA_MODULO, DOCENTE) 
                VALUES (?, ?, ?, ?, NULL, NULL, ?)
                ON DUPLICATE KEY UPDATE 
                    NOTA = VALUES(NOTA), 
                    DOCENTE = VALUES(DOCENTE)
            ");
            $stmt->bind_param("isids", $nie, $materia_val, $periodo, $nota, $docente_nombre);

        } elseif ($modulo) {
            $modulo_trim = trim($modulo);

            $stmt_check = $conn->prepare("SELECT MODULO FROM modulo WHERE MODULO COLLATE utf8mb4_general_ci = ? LIMIT 1");
            $stmt_check->bind_param("s", $modulo_trim);
            $stmt_check->execute();
            $res_modulo = $stmt_check->get_result();

            if ($res_modulo->num_rows == 0) {
                $stmt_check->close();
                throw new Exception("El módulo '$modulo_trim' no existe en la base de datos.");
            } else {
                $row_modulo = $res_modulo->fetch_assoc();
                $modulo_val = $row_modulo['MODULO'];
            }
            $stmt_check->close();

            $stmt = $conn->prepare("
                INSERT INTO notas (NIE, MATERIA, PERIODO, NOTA, MODULO, NOTA_MODULO, DOCENTE) 
                VALUES (?, NULL, ?, NULL, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    NOTA_MODULO = VALUES(NOTA_MODULO), 
                    DOCENTE = VALUES(DOCENTE)
            ");
            $stmt->bind_param("iisds", $nie, $periodo, $modulo_val, $nota, $docente_nombre);

        } else {
            continue;
        }

        if (!$stmt->execute()) {
            error_log("Error al guardar nota para NIE $nie: " . $stmt->error);
            throw new Exception("Error al guardar nota para el estudiante $nie.");
        }
        $stmt->close();
    }

    $conn->commit();

    header("Location: docente_panel.php?mensaje=nota_guardada");
    exit();

} catch (Exception $e) {
    $conn->rollback();

    // Puedes redirigir a la página con mensaje de error, o mostrar mensaje
    $error_msg = urlencode($e->getMessage());
    header("Location: docente_panel.php?error=$error_msg");
    exit();
}

$conn->close();


?>

