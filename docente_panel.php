<?php
// docente_panel.php
session_start();
// Verificar autenticación
if (!isset($_SESSION['user_nie'])) {
    header("Location: login.php");
    exit();
}
require 'config.php';
$conn = conectarDB();
$docente_nie = $_SESSION['user_nie'];
$docente_nombre = $_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido'];
// Obtener información del docente
$docente_info = [];
$stmt = $conn->prepare("SELECT * FROM personal_registrado WHERE NIE = ?");
$stmt->bind_param("i", $docente_nie);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
    $docente_info = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php?error=usuario_no_encontrado");
    exit();
}
$stmt->close();
// Obtener materias que el docente imparte
$materias = [];
$stmt = $conn->prepare("SELECT MATERIA, TIPO FROM materia WHERE DOCENTE = ?");
$stmt->bind_param("s", $docente_nombre);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $materias[] = $row;
}
$stmt->close();
// Obtener módulos que el docente imparte
$modulos = [];
$stmt = $conn->prepare("SELECT MODULO, NOMBRE_MODULO FROM modulo WHERE DOCENTE = ?");
$stmt->bind_param("s", $docente_nombre);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $modulos[] = $row;
}
$stmt->close();
// === CORREGIDO: Nombre de tabla correcto ===
$estudiantes_por_materia = [];
foreach ($materias as $materia) {
    $nombre_materia = $materia['MATERIA'];
    $stmt = $conn->prepare("
        SELECT e.ID_ESTUDIANTE AS NIE, 
               p.NOMBRE, 
               p.APELLIDO, 
               e.ANO_ACADEMICO 
        FROM estudintes_materia e
        JOIN personal_registrado p ON e.ID_ESTUDIANTE = p.NIE
        WHERE e.MATERIA = ? AND p.ROL = 'ESTUDIANTE'
    ");
    $stmt->bind_param("s", $nombre_materia);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $estudiantes_por_materia[$nombre_materia][] = $row;
    }
    $stmt->close();
}
// Obtener estudiantes por módulo
$estudiantes_por_modulo = [];
foreach ($modulos as $modulo) {
    $nombre_modulo = $modulo['MODULO'];
    $stmt = $conn->prepare("
        SELECT e.NIE, 
               p.NOMBRE, 
               p.APELLIDO, 
               e.ANO_ACADEMICO 
        FROM estudiantes_modulo e
        JOIN personal_registrado p ON e.NIE = p.NIE
        WHERE e.MODULO = ? AND p.ROL = 'ESTUDIANTE'
    ");
    $stmt->bind_param("s", $nombre_modulo);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $estudiantes_por_modulo[$nombre_modulo][] = $row;
    }
    $stmt->close();
}
$meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];
$mes_seleccionado = isset($_GET['mes']) ? $_GET['mes'] : "Enero";
// Obtener notas existentes
$notas = [];
$all_materias = array_column($materias, 'MATERIA');
$all_modulos = array_column($modulos, 'MODULO');
$all_subjects = array_merge($all_materias, $all_modulos);
if (!empty($all_subjects)) {
    $placeholders = implode(',', array_fill(0, count($all_subjects), '?'));
    $types = str_repeat('s', count($all_subjects));
    $stmt = $conn->prepare("SELECT * FROM notas WHERE MATERIA IN ($placeholders) OR MODULO IN ($placeholders)");
    $stmt->bind_param($types . $types, ...$all_subjects, ...$all_subjects);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $key = $row['MATERIA'] ?: $row['MODULO'];
        $notas[$row['NIE']][$key] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Docente - INE - <?php echo htmlspecialchars($docente_info['ESPECIALIDAD'] ?? 'Docente'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        .teacher-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .teacher-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--color-accent);
            margin-bottom: 1rem;
        }
        .teacher-name {
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .teacher-position {
            font-size: 0.85rem;
            background-color: var(--color-secondary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            margin-bottom: 0.5rem;
        }
        .teacher-id {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .menu-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background-color 0.3s;
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
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .card-header select, .card-header input {
            max-width: 200px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 500;
            color: var(--color-secondary);
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .nota-input:invalid {
            border-color: var(--color-danger);
        }
        .invalid-feedback {
            display: none;
            color: var(--color-danger);
            font-size: 0.8rem;
        }
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        .card-header .form-select, .card-header .form-control {
            margin-left: 0.5rem;
            display: inline-block;
        }
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .card-header {
                flex-direction: column;
                align-items: stretch;
            }
            .card-header .form-select, .card-header .form-control {
                margin-left: 0;
                margin-top: 0.5rem;
                display: block;
                width: 100%;
                max-width: 100%;
            }
        }
        .table tbody tr:hover {
            background-color: rgba(0, 86, 179, 0.05);
        }
        .nota-input {
            transition: border-color 0.3s;
        }
        .nota-input:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'notas_guardadas'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 10px; right: 10px; z-index: 1050;">
        Notas guardadas correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'asistencia_guardada'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 10px; right: 10px; z-index: 1050;">
        Asistencia guardada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="teacher-profile">
                <img src="imagenes/Logo_del_Gobierno_de_El_Salvador_(2019).svg.png" alt="Foto del docente" class="teacher-img">
                <div class="teacher-name"><?php echo htmlspecialchars($_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido']); ?></div>
                <div class="teacher-position"><?php echo htmlspecialchars($docente_info['ROL']); ?></div>
                <div class="teacher-id">NIE: <?php echo htmlspecialchars($_SESSION['user_nie']); ?></div>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active" data-target="dashboard">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item" data-target="datos">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-person"></i>
                    <span>Datos personales</span>
                </a>
            </li>
            <li class="menu-item" data-target="asistencias">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-clipboard-check"></i>
                    <span>Asistencia</span>
                </a>
            </li>
            <li class="menu-item" data-target="notas">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-journal-text"></i>
                    <span>Notas</span>
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
        <!-- Dashboard -->
        <div id="dashboard-content" class="content-section">
            <div class="content-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="date-info"><?php echo date('d/m/Y'); ?></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Resumen</div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr><td>Materias</td><td><?php echo count($materias); ?></td></tr>
                                    <tr><td>Módulos</td><td><?php echo count($modulos); ?></td></tr>
                                    <tr><td>Estudiantes</td><td>
                                        <?php 
                                        $total = 0;
                                        foreach ($estudiantes_por_materia as $l) $total += count($l);
                                        foreach ($estudiantes_por_modulo as $l) $total += count($l);
                                        echo $total;
                                        ?>
                                    </td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Datos personales -->
        <div id="datos-content" class="content-section" style="display:none;">
            <div class="card">
                <div class="card-header">Información personal</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Nombre</th><td><?php echo htmlspecialchars($docente_info['NOMBRE'] . ' ' . $docente_info['APELLIDO']); ?></td></tr>
                            <tr><th>NIE</th><td><?php echo htmlspecialchars($docente_info['NIE']); ?></td></tr>
                            <tr><th>Correo</th><td><?php echo htmlspecialchars($docente_info['CORREO']); ?></td></tr>
                            <tr><th>Especialidad</th><td><?php echo htmlspecialchars($docente_info['ESPECIALIDAD'] ?? 'No asignada'); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Asistencias -->
        <div id="asistencias-content" class="content-section" style="display:none;">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Control de Asistencia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4 g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo</label>
                            <select class="form-select" id="select-tipo-asistencia">
                                <option value="">Seleccionar...</option>
                                <option value="materia">Por Materia</option>
                                <option value="modulo">Por Módulo</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="materia-container" style="display:none;">
                            <label class="form-label fw-bold">Materia</label>
                            <select class="form-select" id="select-materia-asistencia">
                                <option value="">Seleccionar materia...</option>
                                <?php foreach ($materias as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['MATERIA']); ?>">
                                    <?php echo htmlspecialchars($m['MATERIA']); ?> (<?php echo htmlspecialchars($m['TIPO']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4" id="modulo-container" style="display:none;">
                            <label class="form-label fw-bold">Módulo</label>
                            <select class="form-select" id="select-modulo-asistencia">
                                <option value="">Seleccionar módulo...</option>
                                <?php foreach ($modulos as $m): ?>
                                <option value="<?php echo htmlspecialchars($m['MODULO']); ?>">
                                    <?php echo htmlspecialchars($m['MODULO']); ?> (<?php echo htmlspecialchars($m['NOMBRE_MODULO']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" class="form-control" id="fecha-asistencia" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="alert alert-info mb-3" id="info-asignatura" style="display: none;">
                        <strong>Asignatura:</strong> <span id="nombre-asignatura"></span>
                    </div>
                    <div class="legend mb-3">
                        <span class="badge bg-success me-2"><i class="bi bi-check-circle"></i> Presente</span>
                        <span class="badge bg-danger me-2"><i class="bi bi-x-circle"></i> Ausente</span>
                        <span class="badge bg-warning text-dark me-2"><i class="bi bi-question-circle"></i> Justificado</span>
                        <span class="badge bg-info me-2"><i class="bi bi-calendar-event"></i> Día Festivo</span>
                    </div>
                    <form action="guardar_asistencia.php" method="POST" id="form-asistencia">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="10%">NIE</th>
                                        <th width="30%">Estudiante</th>
                                        <th width="15%">Año Académico</th>
                                        <th width="45%">Asistencia</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-estudiantes-asistencia">
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                                            Seleccione el tipo de asistencia y la materia/módulo correspondiente
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="tipo" id="input-tipo" value="">
                        <input type="hidden" name="materia" id="input-materia-asistencia" value="">
                        <input type="hidden" name="modulo" id="input-modulo-asistencia" value="">
                        <input type="hidden" name="fecha" id="input-fecha-asistencia" value="<?php echo date('Y-m-d'); ?>">
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-save me-2"></i> Guardar Asistencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="calendario-container">
                <h1>CALENDARIO ESCOLAR 2025</h1>
                <form class="mes-selector" method="get">
                    <label for="mes">Selecciona el mes:</label>
                    <select name="mes" id="mes" onchange="this.form.submit()">
                        <?php foreach($meses as $mes): ?>
                            <option value="<?= $mes ?>" <?= $mes_seleccionado==$mes?"selected":"" ?>><?= $mes ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php if ($mes_seleccionado == "Enero"): ?>
                    <div class="mes-titulo">ENERO</div>
                    <table class="mes-tabla">
                        <tr>
                            <th>Lunes</th><th>Martes</th><th>Miércoles</th><th>Jueves</th><th>Viernes</th><th>Sábado</th><th>Domingo</th>
                        </tr>
                        <tr>
                            <td></td><td></td><td>1</td><td>2</td>
                            <td>3<span class="evento evento-presentacion">Presentación del personal administrativo y técnico</span></td>
                            <td></td><td></td>
                        </tr>
                        <tr>
                            <td>6<span class="evento evento-presentacion">Presentación de docentes</span></td>
                            <td>7<span class="evento evento-capacitacion">Capacitación de docentes</span></td>
                            <td>8<span class="evento evento-matricula">Matrícula 2025</span></td>
                            <td>9<span class="evento evento-capacitacion">Capacitación de docentes</span></td>
                            <td>10<span class="evento evento-matricula">Matrícula 2025</span></td>
                            <td>11</td><td>12</td>
                        </tr>
                        <tr>
                            <td>13<span class="evento evento-presentacion">Presentación de docentes</span></td>
                            <td>14<span class="evento evento-matricula">Matrícula 2025</span></td>
                            <td>15<span class="evento evento-capacitacion">Capacitación de docentes</span></td>
                            <td>16<span class="evento evento-matricula">Matrícula 2025</span></td>
                            <td>17<span class="evento evento-matricula">Matrícula 2025</span></td>
                            <td>18</td><td>19</td>
                        </tr>
                        <tr>
                            <td>20<span class="evento evento-inicio">INICIO DEL AÑO ESCOLAR</span><span class="evento evento-otros">Reunión de Padres</span></td>
                            <td>21<span class="evento evento-otros">Primer Trimestre</span></td>
                            <td>22</td><td>23</td>
                            <td>24<span class="evento evento-ambientacion">Ambientación de aula</span></td>
                            <td>25</td><td>26</td>
                        </tr>
                        <tr>
                            <td>27<span class="evento evento-inicio">INICIO DEL AÑO ESCOLAR</span><span class="evento evento-otros">Reunión de Padres</span></td>
                            <td>28</td><td>29</td><td>30</td><td>31</td><td></td><td></td>
                        </tr>
                    </table>
                <?php elseif ($mes_seleccionado == "Febrero"): ?>
                    <div class="mes-titulo">FEBRERO</div>
                    <table class="mes-tabla">
                        <tr>
                            <th>Lunes</th><th>Martes</th><th>Miércoles</th><th>Jueves</th><th>Viernes</th><th>Sábado</th><th>Domingo</th>
                        </tr>
                        <tr>
                            <td></td><td></td><td></td><td></td><td></td><td>1</td><td>2</td>
                        </tr>
                        <tr>
                            <td>3<span class="evento evento-otros">Semana de la Amistad</span></td>
                            <td>4<span class="evento evento-matricula">Inicio de prueba Conociendo Mis Logros</span></td>
                            <td>5</td><td>6</td><td>7</td><td>8</td><td>9</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>11<span class="evento evento-capacitacion">Día Internacional de la Mujer y la Niña en la Ciencia</span></td>
                            <td>12</td><td>13</td><td>14<span class="evento evento-reunion">Día del Amor y la Amistad</span></td>
                            <td>15</td><td>16</td>
                        </tr>
                        <tr>
                            <td>17</td><td>18</td><td>19</td><td>20</td>
                            <td>21<span class="evento evento-matricula">Fin de prueba Mis Logros</span><span class="evento evento-feriado">Día Internacional de la Lengua Materna</span></td>
                            <td>22</td><td>23</td>
                        </tr>
                        <tr>
                            <td>24<span class="evento evento-capacitacion">Aprendamos a estudiar</span></td>
                            <td>25<span class="evento evento-capacitacion">Aprendamos a estudiar</span></td>
                            <td>26<span class="evento evento-capacitacion">Aprendamos a estudiar</span></td>
                            <td>27</td>
                            <td>28<span class="evento evento-ambientacion">Planificación mensual de los aprendizajes</span></td>
                            <td></td><td></td>
                        </tr>
                    </table>
                <?php endif; ?>
                <style>
                    body { font-family: Arial, sans-serif; background: #f8fafc; }
                    h1 { text-align: center; color: #7c3aed; margin-bottom: 24px; letter-spacing: 2px; }
                    .mes-selector { text-align: right; margin-bottom: 18px; }
                    select { padding: 6px 12px; border-radius: 6px; border: 1px solid #7c3aed; font-size: 1em; }
                    .mes-titulo { text-align: center; font-size: 2em; color: #7c3aed; margin-bottom: 10px; font-weight: bold; }
                    .mes-tabla { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                    .mes-tabla th, .mes-tabla td { border: 1px solid #e0e0e0; width: 14.28%; height: 80px; vertical-align: top; text-align: left; padding: 6px; font-size: 1em; }
                    .mes-tabla th { background: #e0e7ff; color: #3730a3; text-align: center; font-weight: bold; }
                    .evento { border-radius: 6px; padding: 2px 6px; margin-bottom: 4px; display: block; font-size: 0.95em; }
                    .evento-capacitacion { background: #fde68a; color: #92400e; }
                    .evento-matricula { background: #c7d2fe; color: #3730a3; }
                    .evento-inicio { background: #bbf7d0; color: #166534; }
                    .evento-ambientacion { background: #fca5a5; color: #991b1b; }
                    .evento-presentacion { background: #f3f4f6; color: #2563eb; }
                    .evento-reunion { background: #f0abfc; color: #7c3aed; }
                    .evento-otros { background: #e0e7ff; color: #3730a3; }
                    .evento-feriado { background: #ffe0e0; color: #c00; }
                    @media (max-width: 900px) {
                        .calendario-container { padding: 8px; }
                        .mes-tabla th, .mes-tabla td { font-size: 0.85em; height: 60px; }
                    }
                </style>
            </div>
        </div>
        <!-- Notas -->
        <div id="notas-content" class="content-section" style="display:none;">
            <div class="card">
                <div class="card-header">
                    <span>Registro de notas</span>
                    <div>
                        <select class="form-select form-select-sm" id="select-materia-notas">
                            <option value="">Seleccionar materia o módulo</option>
                            <?php foreach ($materias as $m): ?>
                            <option value="m_<?php echo htmlspecialchars($m['MATERIA']); ?>">
                                <?php echo htmlspecialchars($m['MATERIA']); ?> (<?php echo htmlspecialchars($m['TIPO']); ?>)
                            </option>
                            <?php endforeach; ?>
                            <?php foreach ($modulos as $m): ?>
                            <option value="mod_<?php echo htmlspecialchars($m['MODULO']); ?>">
                                Módulo: <?php echo htmlspecialchars($m['MODULO']); ?> (<?php echo htmlspecialchars($m['NOMBRE_MODULO']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-select form-select-sm" id="select-periodo">
                            <option value="1">Primer Periodo</option>
                            <option value="2">Segundo Periodo</option>
                            <option value="3">Tercer Periodo</option>
                        </select>
                        <button type="button" id="filtrar" class="btn btn-sm btn-success">
                            <i class="bi bi-search"></i> FILTRAR
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="guardar_notas.php" method="POST" id="form-notas">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NIE</th>
                                    <th>Estudiante</th>
                                    <th>Año</th>
                                    <th>Nota (0-10)</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-estudiantes-notas">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Selecciona materia o módulo.</td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="materia" id="input-materia-notas" value="">
                        <input type="hidden" name="modulo" id="input-modulo-notas" value="">
                        <input type="hidden" name="periodo" id="input-periodo-notas" value="1">
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Guardar notas</button>
                        </div>
                    </form>
                    <div id="mensaje" style="margin-top: 10px; font-weight: bold;"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Datos desde PHP
        const estudiantesPorMateriaData = <?php echo json_encode($estudiantes_por_materia); ?>;
        const estudiantesPorModuloData = <?php echo json_encode($estudiantes_por_modulo); ?>;
        const notasData = <?php echo json_encode($notas); ?>;

        // Cambiar sección
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item');
            const sections = document.querySelectorAll('.content-section');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (this.querySelector('a').getAttribute('href') === 'logout.php') return;
                    e.preventDefault();
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    sections.forEach(s => s.style.display = 'none');
                    const target = this.getAttribute('data-target');
                    document.getElementById(`${target}-content`).style.display = 'block';
                    document.querySelector('.page-title').textContent = this.querySelector('span').textContent;
                });
            });
            document.getElementById('dashboard-content').style.display = 'block'; // Mostrar dashboard
        });

        // === NOTAS ===
        document.getElementById('select-materia-notas').addEventListener('change', cargarEstudiantesNotas);
        document.getElementById('select-periodo').addEventListener('change', () => {
            document.getElementById('input-periodo-notas').value = document.getElementById('select-periodo').value;
            cargarEstudiantesNotas();
        });

        // ⚠️ Comentado: No existe el botón con id="exportar-excel"
        // document.getElementById('exportar-excel').addEventListener('click', function() { ... });

        function cargarEstudiantesNotas() {
            const select = document.getElementById('select-materia-notas');
            const value = select.value;
            const tbody = document.getElementById('tabla-estudiantes-notas');
            const periodo = document.getElementById('select-periodo').value;
            tbody.innerHTML = '';
            document.getElementById('input-periodo-notas').value = periodo;

            if (!value) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="4" class="text-center text-muted">Selecciona materia o módulo.</td>';
                tbody.appendChild(tr);
                return;
            }

            let estudiantes = [];
            let esModulo = false;
            let nombre = '';

            if (value.startsWith('m_')) {
                nombre = value.substring(2);
                estudiantes = estudiantesPorMateriaData[nombre] || [];
                document.getElementById('input-materia-notas').value = nombre;
                document.getElementById('input-modulo-notas').value = '';
            } else if (value.startsWith('mod_')) {
                nombre = value.substring(4);
                estudiantes = estudiantesPorModuloData[nombre] || [];
                document.getElementById('input-modulo-notas').value = nombre;
                document.getElementById('input-materia-notas').value = '';
                esModulo = true;
            }

            if (!estudiantes || estudiantes.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="4" class="text-center text-warning">No hay estudiantes matriculados en esta asignatura.</td>';
                tbody.appendChild(tr);
                return;
            }

            estudiantes.forEach(est => {
                const key = esModulo ? document.getElementById('input-modulo-notas').value : document.getElementById('input-materia-notas').value;
                const nota = (notasData[est.NIE] && notasData[est.NIE][key]) || {};
                const campo = `PERIODO_${periodo}`;
                const notaValue = nota[campo] || '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${est.NIE}</td>
                    <td>${est.NOMBRE} ${est.APELLIDO}</td>
                    <td>${est.ANO_ACADEMICO}</td>
                    <td>
                        <input type="number" min="0" max="10" step="0.01" 
                               name="notas[${est.NIE}][nota]" 
                               value="${notaValue}" 
                               class="form-control nota-input" 
                               style="width:80px"
                               onchange="validarNota(this)">
                        <div class="invalid-feedback">La nota debe estar entre 0 y 10</div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function validarNota(input) {
            const value = parseFloat(input.value);
            if (isNaN(value) || value < 0 || value > 10) {
                input.classList.add('is-invalid');
                return false;
            } else {
                input.classList.remove('is-invalid');
                return true;
            }
        }

        document.getElementById('form-notas').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('.nota-input');
            let isValid = true;
            inputs.forEach(input => {
                if (!validarNota(input)) {
                    isValid = false;
                }
            });
            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrija las notas inválidas (deben estar entre 0 y 10)');
            }
        });

        // === ASISTENCIA ===
        document.getElementById('select-tipo-asistencia').addEventListener('change', function() {
            const tipo = this.value;
            document.getElementById('input-tipo').value = tipo;
            document.getElementById('materia-container').style.display = tipo === 'materia' ? 'block' : 'none';
            document.getElementById('modulo-container').style.display = tipo === 'modulo' ? 'block' : 'none';
            document.getElementById('select-materia-asistencia').value = '';
            document.getElementById('select-modulo-asistencia').value = '';
            document.getElementById('info-asignatura').style.display = 'none';
            cargarEstudiantesAsistencia();
        });
        document.getElementById('select-materia-asistencia').addEventListener('change', cargarEstudiantesAsistencia);
        document.getElementById('select-modulo-asistencia').addEventListener('change', cargarEstudiantesAsistencia);
        document.getElementById('fecha-asistencia').addEventListener('change', () => {
            document.getElementById('input-fecha-asistencia').value = document.getElementById('fecha-asistencia').value;
            if (document.getElementById('select-tipo-asistencia').value) cargarEstudiantesAsistencia();
        });

        function cargarEstudiantesAsistencia() {
            const tipo = document.getElementById('select-tipo-asistencia').value;
            const materia = document.getElementById('select-materia-asistencia').value;
            const modulo = document.getElementById('select-modulo-asistencia').value;
            const fecha = document.getElementById('fecha-asistencia').value;
            document.getElementById('input-tipo').value = tipo;
            document.getElementById('input-fecha-asistencia').value = fecha;
            document.getElementById('input-materia-asistencia').value = materia;
            document.getElementById('input-modulo-asistencia').value = modulo;
            const tbody = document.getElementById('tabla-estudiantes-asistencia');
            const infoAsignatura = document.getElementById('info-asignatura');
            const nombreAsignatura = document.getElementById('nombre-asignatura');
            tbody.innerHTML = '';
            if (!tipo || (!materia && tipo === 'materia') || (!modulo && tipo === 'modulo')) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                            Seleccione el tipo de asistencia y la materia/módulo correspondiente
                        </td>
                    </tr>
                `;
                infoAsignatura.style.display = 'none';
                return;
            }
            let estudiantes = [];
            let nombre = '';
            if (tipo === 'materia') {
                estudiantes = estudiantesPorMateriaData[materia] || [];
                nombre = materia;
            } else {
                estudiantes = estudiantesPorModuloData[modulo] || [];
                nombre = modulo;
            }
            if (estudiantes.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-warning">
                            <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                            No hay estudiantes matriculados en esta asignatura
                        </td>
                    </tr>
                `;
                infoAsignatura.style.display = 'none';
                return;
            }
            nombreAsignatura.textContent = nombre;
            infoAsignatura.style.display = 'block';
            estudiantes.forEach(est => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${est.NIE}</td>
                    <td>${est.APELLIDO}, ${est.NOMBRE}</td>
                    <td>${est.ANO_ACADEMICO}</td>
                    <td>
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <input type="radio" class="btn-check" name="asistencia[${est.NIE}]" id="p_${est.NIE}" value="PRESENTE" checked>
                            <label class="btn btn-outline-success" for="p_${est.NIE}"><i class="bi bi-check-circle"></i></label>
                            <input type="radio" class="btn-check" name="asistencia[${est.NIE}]" id="a_${est.NIE}" value="AUSENTE">
                            <label class="btn btn-outline-danger" for="a_${est.NIE}"><i class="bi bi-x-circle"></i></label>
                            <input type="radio" class="btn-check" name="asistencia[${est.NIE}]" id="j_${est.NIE}" value="JUSTIFICADO">
                            <label class="btn btn-outline-warning" for="j_${est.NIE}"><i class="bi bi-question-circle"></i></label>
                            <input type="radio" class="btn-check" name="asistencia[${est.NIE}]" id="f_${est.NIE}" value="DIA FESTIVO">
                            <label class="btn btn-outline-info" for="f_${est.NIE}"><i class="bi bi-calendar-event"></i></label>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    </script>
</body>
</html>