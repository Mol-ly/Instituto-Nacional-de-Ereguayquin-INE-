<?php
// guardar_asistencia.php
session_start();
require_once 'config.php';

// Verificar autenticación
if (!isset($_SESSION['user_nie'])) {
    die("Acceso denegado.");
}

$conn = conectarDB();

// Recibir datos
$tipo = $_POST['tipo'] ?? '';
$materia_nombre = trim($_POST['materia'] ?? '');
$modulo = trim($_POST['modulo'] ?? '');
$fecha = $_POST['fecha'] ?? '';
$asistencia = $_POST['asistencia'] ?? [];
$docente_nombre = $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido'];

// Validar datos requeridos
if (empty($tipo) || empty($fecha) || empty($asistencia)) {
    die("Datos incompletos.");
}

if ($tipo === 'materia' && empty($materia_nombre)) {
    die("Debe seleccionar una materia.");
}
if ($tipo === 'modulo' && empty($modulo)) {
    die("Debe seleccionar un módulo.");
}

// Obtener ID_MATERIA si es por materia
$id_materia = null;
if ($tipo === 'materia') {
    $stmt_check = $conn->prepare("SELECT ID_MATERIA FROM materia WHERE MATERIA = ?");
    $stmt_check->bind_param("s", $materia_nombre);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        die("Error: La materia '$materia_nombre' no existe.");
    }
    $id_materia = $row['ID_MATERIA'];
    $stmt_check->close();
}

// Validar que el docente imparte esta materia/módulo
if ($tipo === 'materia') {
    $stmt_perm = $conn->prepare("SELECT ID_MATERIA FROM materia WHERE MATERIA = ? AND DOCENTE = ?");
    $stmt_perm->bind_param("ss", $materia_nombre, $docente_nombre);
    $stmt_perm->execute();
    if ($stmt_perm->get_result()->num_rows == 0) {
        die("No tiene permiso para registrar asistencia en esta materia.");
    }
    $stmt_perm->close();
} elseif ($tipo === 'modulo') {
    $stmt_perm = $conn->prepare("SELECT MODULO FROM modulo WHERE MODULO = ? AND DOCENTE = ?");
    $stmt_perm->bind_param("ss", $modulo, $docente_nombre);
    $stmt_perm->execute();
    if ($stmt_perm->get_result()->num_rows == 0) {
        die("No tiene permiso para registrar asistencia en este módulo.");
    }
    $stmt_perm->close();
}

// Insertar asistencias
foreach ($asistencia as $nie => $estado) {
    // Validar que el estudiante exista y sea estudiante
    $stmt_est = $conn->prepare("SELECT NIE FROM personal_registrado WHERE NIE = ? AND ROL = 'ESTUDIANTE'");
    $stmt_est->bind_param("i", $nie);
    $stmt_est->execute();
    if ($stmt_est->get_result()->num_rows == 0) {
        error_log("Estudiante con NIE $nie no encontrado o no es estudiante.");
        continue; // Saltar si no existe
    }
    $stmt_est->close();

    if ($tipo === 'materia') {
        // Usar ID_MATERIA (entero)
        $stmt = $conn->prepare("INSERT INTO asistencia_materias (ID_ESTUDIANTE, ID_MATERIA, FECHA, ASISTENCIA, DOCENTE) 
                                VALUES (?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE ASISTENCIA = VALUES(ASISTENCIA)");
        $stmt->bind_param("iisss", $nie, $id_materia, $fecha, $estado, $docente_nombre);
    } elseif ($tipo === 'modulo') {
        $stmt = $conn->prepare("INSERT INTO asistencia_modulos (ID_ESTUDIANTE, ID_MODULO, FECHA, ASISTENCIA, DOCENTE) 
                                VALUES (?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE ASISTENCIA = VALUES(ASISTENCIA)");
        $stmt->bind_param("issss", $nie, $modulo, $fecha, $estado, $docente_nombre);
    } else {
        continue;
    }

    if (!$stmt->execute()) {
        error_log("Error al guardar asistencia para NIE $nie: " . $stmt->error);
    }
    $stmt->close();
}

$conn->close();

// Redirigir con éxito
header("Location: docente_panel.php?mensaje=asistencia_guardada");
exit();
?>