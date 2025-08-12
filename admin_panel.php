<?php
// admin_panel.php

// 1. Iniciar sesión
session_start();

// 2. Incluir configuración
require_once 'config.php';

// 3. Verificar autenticación
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMINISTRADOR') {
    header("Location: login.php");
    exit();
}

// 4. Conectar a la base de datos
$conn = conectarDB();

// 5. Inicializar mensaje
$mensaje = '';

// 6. Procesar: Nuevo Usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_usuario'])) {
    $nie = trim($_POST['nie']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
    $contrasena = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $especialidad = trim($_POST['especialidad']);
    $ano_academico = trim($_POST['ano_academico']);
    $edad = (int)$_POST['edad'];
    $turno = trim($_POST['turno']); // ✅ Corregido: $_turno → $_POST['turno']
    $estado = trim($_POST['estado']);
    $seccion = trim($_POST['seccion']);

    if (!$nie || !$nombre || !$apellido || !$correo || !$rol) {
        $mensaje = '<div class="alert alert-danger">Todos los campos obligatorios deben llenarse.</div>';
    } else {
        $stmt_check = $conn->prepare("SELECT NIE FROM personal_registrado WHERE NIE = ?");
        $stmt_check->bind_param("i", $nie);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $mensaje = '<div class="alert alert-warning">El NIE ya está registrado.</div>';
        } else {
            $sql = "INSERT INTO personal_registrado 
                (NIE, NOMBRE, APELLIDO, CORREO, CONTRASEÑA, ROL, FECHA_DE_NACIMIENTO, DIRECCION, TELEFONO, 
                ESPECIALIDAD, ANO_ACADEMICO, EDAD, TURNO, ESTADO, SECCION)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "issssssisssissss",
                $nie, $nombre, $apellido, $correo, $contrasena, $rol, $fecha_nacimiento,
                $direccion, $telefono, $especialidad, $ano_academico, $edad, $turno, $estado, $seccion
            );

            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Usuario registrado exitosamente.</div>';
            } else {
                $mensaje = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

// 7. Procesar: Nueva Materia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_materia'])) {
    $materia = trim($_POST['materia']);
    $docente = trim($_POST['docente']);
    $logro = trim($_POST['logro']);
    $horario = $_POST['horario'];
    $estado = $_POST['estado'];
    $tipo = $_POST['tipo'];

    if (empty($materia) || empty($docente) || empty($horario) || empty($estado) || empty($tipo)) {
        $mensaje = '<div class="alert alert-danger">Los campos Materia, Docente, Horario, Estado y Tipo son obligatorios.</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO materia (MATERIA, DOCENTE, LOGRO, HORARIO, ESTADO, TIPO) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $materia, $docente, $logro, $horario, $estado, $tipo);

        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Materia agregada exitosamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al guardar: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// 8. Procesar: Editar Materia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_materia'])) {
    $id = (int)$_POST['id_materia'];
    $materia = trim($_POST['materia']);
    $docente = trim($_POST['docente']);
    $logro = trim($_POST['logro']);
    $horario = $_POST['horario'];
    $estado = $_POST['estado'];
    $tipo = $_POST['tipo'];

    if (empty($materia) || empty($docente) || empty($horario) || empty($estado) || empty($tipo)) {
        $mensaje = '<div class="alert alert-danger">Los campos obligatorios deben llenarse.</div>';
    } else {
        $stmt = $conn->prepare("UPDATE materia SET MATERIA = ?, DOCENTE = ?, LOGRO = ?, HORARIO = ?, ESTADO = ?, TIPO = ? WHERE ID_MATERIA = ?");
        $stmt->bind_param("ssssssi", $materia, $docente, $logro, $horario, $estado, $tipo, $id);

        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Materia actualizada correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al actualizar: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// 9. Procesar: Eliminar Materia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_materia'])) {
    $id = (int)$_POST['id_materia'];
    $stmt = $conn->prepare("DELETE FROM materia WHERE ID_MATERIA = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $mensaje = '<div class="alert alert-success">Materia eliminada correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-warning">No se encontró la materia.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-danger">Error al eliminar: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// 10. Procesar: Nuevo Módulo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_modulo'])) {
    $modulo = trim($_POST['modulo']);
    $nombre_modulo = trim($_POST['nombre_modulo']);
    $docente = trim($_POST['docente']);

    if (empty($modulo) || empty($nombre_modulo) || empty($docente)) {
        $mensaje = '<div class="alert alert-danger">Todos los campos del módulo son obligatorios.</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO modulo (MODULO, NOMBRE_MODULO, DOCENTE, TIPO) VALUES (?, ?, ?, 'MODULO')");
        $stmt->bind_param("sss", $modulo, $nombre_modulo, $docente);

        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Módulo agregado exitosamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al guardar: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// 11. Procesar: Editar Módulo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_modulo'])) {
    $id = (int)$_POST['id_modulo'];
    $modulo = trim($_POST['modulo']);
    $nombre_modulo = trim($_POST['nombre_modulo']);
    $docente = trim($_POST['docente']);

    if (empty($modulo) || empty($nombre_modulo) || empty($docente)) {
        $mensaje = '<div class="alert alert-danger">Todos los campos son obligatorios.</div>';
    } else {
        $stmt = $conn->prepare("UPDATE modulo SET MODULO = ?, NOMBRE_MODULO = ?, DOCENTE = ? WHERE ID_MODULO = ?");
        $stmt->bind_param("sssi", $modulo, $nombre_modulo, $docente, $id);

        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Módulo actualizado correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error al actualizar: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// 12. Procesar: Eliminar Módulo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_modulo'])) {
    $id = (int)$_POST['id_modulo'];
    $stmt = $conn->prepare("DELETE FROM modulo WHERE ID_MODULO = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $mensaje = '<div class="alert alert-success">Módulo eliminado correctamente.</div>';
        } else {
            $mensaje = '<div class="alert alert-warning">No se encontró el módulo.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-danger">Error al eliminar: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}

// 13. Cerrar conexión temporal
$conn->close();

// 14. Volver a conectar para estadísticas
$conn = conectarDB();

// 15. Estadísticas
$usuarios = $conn->query("SELECT COUNT(*) as total FROM personal_registrado")->fetch_assoc()['total'];
$estudiantes = $conn->query("SELECT COUNT(*) as total FROM personal_registrado WHERE ROL = 'ESTUDIANTE'")->fetch_assoc()['total'];
$docentes = $conn->query("SELECT COUNT(*) as total FROM personal_registrado WHERE ROL = 'DOCENTE'")->fetch_assoc()['total'];
$materias = $conn->query("SELECT COUNT(*) as total FROM materia")->fetch_assoc()['total'];
$modulos = $conn->query("SELECT COUNT(*) as total FROM modulo")->fetch_assoc()['total'];

// 16. Últimos registros
$actividad = $conn->query("
    SELECT CONCAT(NOMBRE, ' ', APELLIDO) as nombre, NOW() as fecha 
    FROM personal_registrado 
    WHERE ROL = 'ESTUDIANTE' 
    ORDER BY NIE DESC 
    LIMIT 4
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - INE </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #0056b3;
            --color-secondary: #1e293b;
            --color-accent: #0ea5e9;
            --color-danger: #dc3545;
            --color-success: #28a745;
            --color-warning: #ffc107;
            --color-text: #333333;
            --color-light: #f8f9fa;
            --color-border: #dee2e6;
            --sidebar-width: 280px;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            color: var(--color-text);
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .admin-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: .5px solid var(--color-accent);
            margin-bottom: 1rem;
        }
        .admin-name {
            font-weight: 500;
            font-size: 1.1rem;
        }
        .admin-role {
            font-size: 0.85rem;
            background-color: var(--color-danger);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            margin-top: 0.25rem;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .menu-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .menu-item.active {
            background-color: var(--color-secondary);
        }
        .menu-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            font-weight: 400;
        }
        .menu-icon {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 2rem;
            background-color: #f8fafc;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--color-border);
        }
        .page-title {
            font-size: 1.75rem;
            color: var(--color-secondary);
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--color-border);
            font-weight: 500;
            padding: 1.25rem 1.5rem;
            border-radius: 10px 10px 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 500;
            color: var(--color-secondary);
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .badge {
            font-weight: 500;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="text-center">
                <img src="imagenes/Logo_del_Gobierno_de_El_Salvador_(2019).svg.png" alt="Logo" class="admin-img">
                <div class="admin-name"><?php echo htmlspecialchars($_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido']); ?></div>
                <div class="admin-role">ADMINISTRADOR</div>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active" data-target="dashboard">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item" data-target="usuarios">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-people"></i>
                    <span>Control de Usuarios</span>
                </a>
            </li>
            <li class="menu-item" data-target="materias">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-journal-bookmark"></i>
                    <span>Gestión de Materias</span>
                </a>
            </li>
            <li class="menu-item" data-target="modulos">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-book"></i>
                    <span>Gestión de Módulos</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="logout.php" class="menu-link">
                    <i class="menu-icon bi bi-box-arrow-right"></i>
                    <span>Cerrar sesión</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="container-fluid mb-3">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- Dashboard -->
        <div id="dashboard-content" class="content-section">
            <div class="content-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="date-info"><?php echo date('d/m/Y'); ?></div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Usuarios</h6>
                                    <h3 class="mb-0"><?php echo $usuarios; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Estudiantes</h6>
                                    <h3 class="mb-0"><?php echo $estudiantes; ?></h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-video2 text-success" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Docentes</h6>
                                    <h3 class="mb-0"><?php echo $docentes; ?></h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-badge text-info" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Materias</h6>
                                    <h3 class="mb-0"><?php echo $materias; ?></h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-journal-bookmark text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Módulos</h6>
                                    <h3 class="mb-0"><?php echo $modulos; ?></h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-book text-primary" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Actividad reciente</div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php while ($act = $actividad->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-person-plus text-success"></i>
                                            <span class="ms-2">Nuevo estudiante: <?php echo htmlspecialchars($act['nombre']); ?></span>
                                        </div>
                                        <small class="text-muted">Hoy</small>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control de Usuarios -->
        <div id="usuarios-content" class="content-section" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <span>Control de Usuarios</span>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                        <i class="bi bi-plus"></i> Nuevo Usuario
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>NIE</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conn = conectarDB();
                            $usuarios_list = $conn->query("SELECT NIE, NOMBRE, APELLIDO, CORREO, ROL, ESTADO FROM personal_registrado");
                            while ($u = $usuarios_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $u['NIE']; ?></td>
                                <td><?php echo htmlspecialchars($u['NOMBRE'] . ' ' . $u['APELLIDO']); ?></td>
                                <td><?php echo htmlspecialchars($u['CORREO']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php echo $u['ROL'] === 'ADMINISTRADOR' ? 'bg-danger' : ($u['ROL'] === 'DOCENTE' ? 'bg-info text-dark' : 'bg-warning text-dark'); ?>">
                                        <?php echo $u['ROL']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $u['ESTADO'] === 'Activo' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $u['ESTADO']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php $conn->close(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Gestión de Materias -->
        <div id="materias-content" class="content-section" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <span>Gestión de Materias</span>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Materia</th>
                                <th>Docente</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conn = conectarDB();
                            $materias_list = $conn->query("SELECT * FROM materia ORDER BY MATERIA");
                            while ($m = $materias_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $m['ID_MATERIA']; ?></td>
                                <td><?php echo htmlspecialchars($m['MATERIA']); ?></td>
                                <td><?php echo htmlspecialchars($m['DOCENTE']); ?></td>
                                <td>
                                    <span class="badge <?php echo $m['ESTADO'] === 'ACTIVO' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $m['ESTADO']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($m['TIPO']); ?></td>
                                <td class="text-end">
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarMateria"
                                        data-id="<?php echo $m['ID_MATERIA']; ?>"
                                        data-materia="<?php echo htmlspecialchars($m['MATERIA']); ?>"
                                        data-docente="<?php echo htmlspecialchars($m['DOCENTE']); ?>"
                                        data-logro="<?php echo htmlspecialchars($m['LOGRO']); ?>"
                                        data-estado="<?php echo $m['ESTADO']; ?>"
                                        data-tipo="<?php echo $m['TIPO']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEliminarMateria"
                                        data-id="<?php echo $m['ID_MATERIA']; ?>"
                                        data-materia="<?php echo htmlspecialchars($m['MATERIA']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php $conn->close(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Gestión de Módulos -->
        <div id="modulos-content" class="content-section" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <span>Gestión de Módulos</span>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoModulo">
                        <i class="bi bi-plus"></i> Nuevo Módulo
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Módulo</th>
                                <th>Nombre Completo</th>
                                <th>Docente</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conn = conectarDB();
                            $modulos_list = $conn->query("SELECT * FROM modulo ORDER BY MODULO");
                            while ($mod = $modulos_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $mod['ID_MODULO']; ?></td>
                                <td><?php echo htmlspecialchars($mod['MODULO']); ?></td>
                                <td><?php echo htmlspecialchars($mod['NOMBRE_MODULO']); ?></td>
                                <td><?php echo htmlspecialchars($mod['DOCENTE']); ?></td>
                                <td class="text-end">
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarModulo"
                                        data-id="<?php echo $mod['ID_MODULO']; ?>"
                                        data-modulo="<?php echo htmlspecialchars($mod['MODULO']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($mod['NOMBRE_MODULO']); ?>"
                                        data-docente="<?php echo htmlspecialchars($mod['DOCENTE']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEliminarModulo"
                                        data-id="<?php echo $mod['ID_MODULO']; ?>"
                                        data-modulo="<?php echo htmlspecialchars($mod['MODULO']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php $conn->close(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nuevo Usuario -->
    <div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="guardar_usuario.php" id="formRegistroUsuario">
                <input type="hidden" name="guardar_usuario" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoUsuarioLabel">Registrar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Campos fundamentales (siempre visibles) -->
                    <div class="mb-3">
                        <label for="nie" class="form-label">NIE *</label>
                        <input type="number" class="form-control" name="nie" id="nie" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido" class="form-label">Apellido *</label>
                        <input type="text" class="form-control" name="apellido" id="apellido" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo *</label>
                        <input type="email" class="form-control" name="correo" id="correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="contrasena" id="contrasena" required>
                    </div>
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol *</label>
                        <select class="form-control" name="rol" id="rol" required>
                            <option value="">Seleccionar...</option>
                            <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                            <option value="DOCENTE">DOCENTE</option>
                            <option value="ESTUDIANTE">ESTUDIANTE</option>
                        </select>
                    </div>
                    
                    <!-- Campos condicionales para estudiantes -->
                    <div id="camposEstudiante" style="display: none;">
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento">
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="direccion">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" id="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="especialidad" class="form-label">Especialidad</label>
                            <input type="text" class="form-control" name="especialidad" id="especialidad">
                        </div>
                        <div class="mb-3">
                            <label for="ano_academico" class="form-label">Año Académico</label>
                            <select class="form-control" name="ano_academico" id="ano_academico">
                                <option value="">Seleccionar...</option>
                                <option value="1 AÑO">1° AÑO</option>
                                <option value="2 AÑO">2° AÑO</option>
                                <option value="3 AÑO">3° AÑO</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edad" class="form-label">Edad</label>
                            <input type="number" class="form-control" name="edad" id="edad" min="10" max="50">
                        </div>
                        <div class="mb-3">
                            <label for="turno" class="form-label">Turno</label>
                            <select class="form-control" name="turno" id="turno">
                                <option value="">Seleccionar...</option>
                                <option value="MATUTINO">MATUTINO</option>
                                <option value="VESPERTINO">VESPERTINO</option>
                                <option value="NOCTURNO">NOCTURNO</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-control" name="estado" id="estado">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="seccion" class="form-label">Sección</label>
                            <input type="text" class="form-control" name="seccion" id="seccion">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campos de estudiante según rol seleccionado
document.getElementById('rol').addEventListener('change', function() {
    const camposEstudiante = document.getElementById('camposEstudiante');
    if (this.value === 'ESTUDIANTE') {
        camposEstudiante.style.display = 'block';
        // Hacer requeridos los campos de estudiante
        document.querySelectorAll('#camposEstudiante input, #camposEstudiante select').forEach(campo => {
            campo.required = true;
        });
    } else {
        camposEstudiante.style.display = 'none';
        // Quitar el requerido de los campos
        document.querySelectorAll('#camposEstudiante input, #camposEstudiante select').forEach(campo => {
            campo.required = false;
        });
    }
});

// Manejar el envío del formulario
document.getElementById('formRegistroUsuario').addEventListener('submit', function(e) {
    const rol = document.getElementById('rol').value;
    
    if (rol !== 'ESTUDIANTE') {
        // Desactivar los campos de estudiante para que no se envíen
        document.querySelectorAll('#camposEstudiante input, #camposEstudiante select').forEach(campo => {
            campo.disabled = true;
        });
    }
    
    // Aquí podrías agregar validación adicional si es necesario
    // Si todo está bien, el formulario se enviará normalmente
});
</script>

    <!-- Modal: Editar Materia -->
    <div class="modal fade" id="modalEditarMateria" tabindex="-1" aria-labelledby="modalEditarMateriaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="editar_materia" value="1">
                    <input type="hidden" name="id_materia" id="edit_id_materia">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarMateriaLabel">Editar Materia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_materia" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="edit_materia" name="materia" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_docente" class="form-label">Docente *</label>
                            <input type="text" class="form-control" id="edit_docente" name="docente" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_logro" class="form-label">Logros</label>
                            <textarea class="form-control" id="edit_logro" name="logro" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_horario" class="form-label">Horario *</label>
                            <input type="datetime-local" class="form-control" id="edit_horario" name="horario" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_estado" class="form-label">Estado *</label>
                            <select class="form-control" id="edit_estado" name="estado" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tipo" class="form-label">Tipo *</label>
                            <select class="form-control" id="edit_tipo" name="tipo" required>
                                <option value="MATERIA">MATERIA</option>
                                <option value="SEMINARIO">SEMINARIO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Eliminar Materia -->
    <div class="modal fade" id="modalEliminarMateria" tabindex="-1" aria-labelledby="modalEliminarMateriaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="eliminar_materia" value="1">
                    <input type="hidden" name="id_materia" id="delete_id_materia">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEliminarMateriaLabel">Eliminar Materia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar la materia <strong id="delete_nombre_materia"></strong>?
                        <p class="text-danger mt-2"><small>Esta acción no se puede deshacer.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Nuevo Módulo -->
    <div class="modal fade" id="modalNuevoModulo" tabindex="-1" aria-labelledby="modalNuevoModuloLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="guardar_modulo" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNuevoModuloLabel">Agregar Nuevo Módulo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modulo" class="form-label">Código del Módulo *</label>
                            <input type="text" class="form-control" name="modulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre_modulo" class="form-label">Nombre del Módulo *</label>
                            <input type="text" class="form-control" name="nombre_modulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="docente_modulo" class="form-label">Docente *</label>
                            <input type="text" class="form-control" name="docente" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Módulo -->
    <div class="modal fade" id="modalEditarModulo" tabindex="-1" aria-labelledby="modalEditarModuloLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="editar_modulo" value="1">
                    <input type="hidden" name="id_modulo" id="edit_id_modulo">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarModuloLabel">Editar Módulo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_modulo" class="form-label">Código *</label>
                            <input type="text" class="form-control" id="edit_modulo" name="modulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nombre_modulo" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="edit_nombre_modulo" name="nombre_modulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_docente_modulo" class="form-label">Docente *</label>
                            <input type="text" class="form-control" id="edit_docente_modulo" name="docente" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Eliminar Módulo -->
    <div class="modal fade" id="modalEliminarModulo" tabindex="-1" aria-labelledby="modalEliminarModuloLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="eliminar_modulo" value="1">
                    <input type="hidden" name="id_modulo" id="delete_id_modulo">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEliminarModuloLabel">Eliminar Módulo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar el módulo <strong id="delete_nombre_modulo"></strong>?
                        <p class="text-danger mt-2"><small>Esta acción no se puede deshacer.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuItems = document.querySelectorAll('.menu-item');
            const sections = document.querySelectorAll('.content-section');

            menuItems.forEach(item => {
                item.addEventListener('click', function (e) {
                    const link = this.querySelector('a');
                    if (link && link.getAttribute('href') === 'logout.php') return;
                    e.preventDefault();
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    sections.forEach(s => s.style.display = 'none');
                    const target = this.getAttribute('data-target');
                    document.getElementById(`${target}-content`).style.display = 'block';
                    document.querySelector('.page-title').textContent = this.querySelector('span').textContent;
                });
            });

            // Cargar datos en modales
            document.querySelectorAll('[data-bs-target="#modalEditarMateria"]').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('edit_id_materia').value = this.getAttribute('data-id');
                    document.getElementById('edit_materia').value = this.getAttribute('data-materia');
                    document.getElementById('edit_docente').value = this.getAttribute('data-docente');
                    document.getElementById('edit_logro').value = this.getAttribute('data-logro');
                    document.getElementById('edit_horario').value = this.getAttribute('data-horario');
                    document.getElementById('edit_estado').value = this.getAttribute('data-estado');
                    document.getElementById('edit_tipo').value = this.getAttribute('data-tipo');
                });
            });

            document.querySelectorAll('[data-bs-target="#modalEliminarMateria"]').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('delete_id_materia').value = this.getAttribute('data-id');
                    document.getElementById('delete_nombre_materia').textContent = this.getAttribute('data-materia');
                });
            });

            document.querySelectorAll('[data-bs-target="#modalEditarModulo"]').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('edit_id_modulo').value = this.getAttribute('data-id');
                    document.getElementById('edit_modulo').value = this.getAttribute('data-modulo');
                    document.getElementById('edit_nombre_modulo').value = this.getAttribute('data-nombre');
                    document.getElementById('edit_docente_modulo').value = this.getAttribute('data-docente');
                });
            });

            document.querySelectorAll('[data-bs-target="#modalEliminarModulo"]').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('delete_id_modulo').value = this.getAttribute('data-id');
                    document.getElementById('delete_nombre_modulo').textContent = this.getAttribute('data-modulo');
                });
            });

            // Mostrar dashboard por defecto
            document.querySelector('.menu-item.active').click();
        });
    </script>
</body>
</html>