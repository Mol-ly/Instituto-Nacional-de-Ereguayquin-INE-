<?php
// estudiante_panel.php
require_once 'config.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ESTUDIANTE') {
    header("Location: login.php");
    exit();
}

$conn = conectarDB();
$nie = $_SESSION['user_nie'];

// Obtener datos del estudiante
$estudiante = [];
$stmt = $conn->prepare("SELECT * FROM personal_registrado WHERE NIE = ?");
$stmt->bind_param("i", $nie);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
    $estudiante = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php?error=usuario_no_encontrado");
    exit();
}
$stmt->close();

// Obtener todas las notas del estudiante
$notas_organizadas = [];

// Preparar consulta
$stmt = $conn->prepare("SELECT * FROM notas WHERE NIE = ? ORDER BY MATERIA, PERIODO");
if (!$stmt) {
    die("❌ Error al preparar la consulta: " . $conn->error);
}

// Vincular y ejecutar
$stmt->bind_param("i", $nie);
if (!$stmt->execute()) {
    die("❌ Error al ejecutar la consulta: " . $stmt->error);
}

// Obtener resultados
$result = $stmt->get_result();
if (!$result) {
    die("❌ Error al obtener resultados: " . $conn->error);
}

// Procesar cada fila
while ($row = $result->fetch_assoc()) {
    $materia = $row['MATERIA'];
    $periodo = $row['PERIODO'];
    
    // Inicializar si no existe
    if (!isset($notas_organizadas[$materia])) {
        $notas_organizadas[$materia] = [
            'PERIODO1' => null,
            'PERIODO2' => null,
            'PERIODO3' => null,
            'DOCENTE' => $row['DOCENTE'] ?? 'No asignado',
            'TIPO' => 'MATERIA',
            'ESTADO' => $row['ESTADO'] ?? 'En curso',
            'PROMEDIO_FINAL' => null 
        ];
    }

    // Asignar nota al periodo
    if ($periodo == 1) $notas_organizadas[$materia]['PERIODO1'] = $row['NOTA'];
    if ($periodo == 2) $notas_organizadas[$materia]['PERIODO2'] = $row['NOTA'];
    if ($periodo == 3) $notas_organizadas[$materia]['PERIODO3'] = $row['NOTA'];
}

$stmt->close();

// Clasificar materias por tipo: MATERIA, SEMINARIO, MODULO
$notas_por_tipo = [
    'materias'   => [],
    'seminarios' => [],
    'modulos'    => []
];

foreach ($notas_organizadas as $materia => $datos) {
    // Obtener tipo desde tabla `materia`
    $tipo_stmt = $conn->prepare("SELECT TIPO FROM materia WHERE MATERIA = ?");
    $tipo_stmt->bind_param("s", $materia);
    $tipo_stmt->execute();
    $tipo_result = $tipo_stmt->get_result();
    if ($tipo_result->num_rows > 0) {
        $tipo_data = $tipo_result->fetch_assoc();
        $datos['TIPO'] = $tipo_data['TIPO'];
    }
    $tipo_stmt->close();

    // Si es módulo, obtener nombre del módulo
    $modulo_stmt = $conn->prepare("SELECT NOMBRE_MODULO FROM modulo WHERE MODULO = ?");
    $modulo_stmt->bind_param("s", $materia);
    $modulo_stmt->execute();
    $modulo_result = $modulo_stmt->get_result();
    if ($modulo_result->num_rows > 0) {
        $modulo_data = $modulo_result->fetch_assoc();
        $datos['TIPO'] = 'MODULO';
        $datos['NOMBRE_MODULO'] = $modulo_data['NOMBRE_MODULO'];
    }
    $modulo_stmt->close();

    // Calcular promedio final
    $suma = 0;
    $contador = 0;
    foreach (['PERIODO1', 'PERIODO2', 'PERIODO3'] as $p) {
        if ($datos[$p] !== null) {
            $suma += $datos[$p];
            $contador++;
        }
    }
    $datos['PROMEDIO_FINAL'] = $contador > 0 ? round($suma / $contador, 2) : null;
    $datos['ESTADO'] = $datos['PROMEDIO_FINAL'] !== null 
        ? ($datos['PROMEDIO_FINAL'] >= 6 ? 'Aprobado' : 'Reprobado') 
        : 'En curso';

    // Clasificar
    if ($datos['TIPO'] === 'SEMINARIO') {
        $notas_por_tipo['seminarios'][$materia] = $datos;
    } elseif ($datos['TIPO'] === 'MODULO') {
        $notas_por_tipo['modulos'][$materia] = $datos;
    } else {
        $notas_por_tipo['materias'][$materia] = $datos;
    }
}

// Calcular promedios generales
$promedios = [
    'general' => 0,
    'p1' => 0,
    'p2' => 0,
    'p3' => 0
];
$total_materias = 0;
$suma_p1 = $suma_p2 = $suma_p3 = 0;
$contador_p1 = $contador_p2 = $contador_p3 = 0;

foreach ($notas_organizadas as $datos) {
    if ($datos['PROMEDIO_FINAL'] !== null) {
        $promedios['general'] += $datos['PROMEDIO_FINAL'];
        $total_materias++;
    }
    if ($datos['PERIODO1'] !== null) { $suma_p1 += $datos['PERIODO1']; $contador_p1++; }
    if ($datos['PERIODO2'] !== null) { $suma_p2 += $datos['PERIODO2']; $contador_p2++; }
    if ($datos['PERIODO3'] !== null) { $suma_p3 += $datos['PERIODO3']; $contador_p3++; }
}

$promedios['general'] = $total_materias > 0 ? round($promedios['general'] / $total_materias, 2) : 0;
$promedios['p1'] = $contador_p1 > 0 ? round($suma_p1 / $contador_p1, 2) : 0;
$promedios['p2'] = $contador_p2 > 0 ? round($suma_p2 / $contador_p2, 2) : 0;
$promedios['p3'] = $contador_p3 > 0 ? round($suma_p3 / $contador_p3, 2) : 0;

// Alertas de bajas calificaciones
$bajas = [];
foreach ($notas_organizadas as $materia => $datos) {
    if ($datos['PERIODO1'] !== null && $datos['PERIODO1'] < 6) $bajas[] = "$materia (P1)";
    if ($datos['PERIODO2'] !== null && $datos['PERIODO2'] < 6) $bajas[] = "$materia (P2)";
    if ($datos['PERIODO3'] !== null && $datos['PERIODO3'] < 6) $bajas[] = "$materia (P3)";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aula Virtual - Estudiante - INE </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --color-primary: #0056b3;
            --color-secondary: #1e293b;
            --color-accent: #0ea5e9;
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
        .student-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: .5px solid var(--color-accent);
            margin-bottom: 1rem;
        }
        .student-name {
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .student-id {
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
        .menu-icon {
            margin-right: 10px;
            font-size: 1.1rem;
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
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 500;
            color: var(--color-secondary);
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        .btn-download {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        .content-section {
            display: none;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar-menu {
                display: flex;
                flex-wrap: wrap;
            }
            .menu-item {
                flex: 1 0 auto;
                border-bottom: none;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="student-profile">
                <img src="imagenes/Logo_del_Gobierno_de_El_Salvador_(2019).svg.png" alt="Foto del estudiante" class="student-img">
                <div class="student-name"><?php echo htmlspecialchars($_SESSION['user_nombre'] . ' ' . $_SESSION['user_apellido']); ?></div>
                <div class="student-id">NIE: <?php echo htmlspecialchars($_SESSION['user_nie']); ?></div>
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
            <li class="menu-item" data-target="materias">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-journal-bookmark"></i>
                    <span>Materias Regulares</span>
                </a>
            </li>
            <li class="menu-item" data-target="seminarios">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-journal-code"></i>
                    <span>Seminarios</span>
                </a>
            </li>
            <li class="menu-item" data-target="modulos">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-collection"></i>
                    <span>Módulos</span>
                </a>
            </li>
            <li class="menu-item" data-target="notas">
                <a href="#" class="menu-link">
                    <i class="menu-icon bi bi-journal-text"></i>
                    <span>Boletín de Notas</span>
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
        <div id="dashboard-content" class="content-section" style="display: block;">
            <div class="content-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="date-info"><?php echo date('d/m/Y'); ?></div>
            </div>

            <?php if (!empty($bajas)): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Alerta Académica:</strong>
                    Tienes calificaciones bajas en: <?php echo implode(', ', array_unique($bajas)); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Resumen académico</div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr><td>Total materias</td><td><?php echo count($notas_organizadas); ?></td></tr>
                                    <tr><td>Promedio general</td><td><?php echo $promedios['general']; ?></td></tr>
                                    <tr><td>Periodo 1</td><td><?php echo $promedios['p1']; ?></td></tr>
                                    <tr><td>Periodo 2</td><td><?php echo $promedios['p2']; ?></td></tr>
                                    <tr><td>Periodo 3</td><td><?php echo $promedios['p3']; ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Rendimiento por periodo</div>
                        <div class="card-body">
                            <canvas id="rendimientoChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos personales -->
        <div id="datos-content" class="content-section">
            <div class="card">
                <div class="card-header">Datos personales completos</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Nombre completo</th><td><?php echo htmlspecialchars($estudiante['NOMBRE'] . ' ' . $estudiante['APELLIDO']); ?></td></tr>
                            <tr><th>Fecha de nacimiento</th><td><?php echo htmlspecialchars($estudiante['FECHA_DE_NACIMIENTO'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Dirección</th><td><?php echo htmlspecialchars($estudiante['DIRECCION'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Teléfono</th><td><?php echo htmlspecialchars($estudiante['TELEFONO'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Correo electrónico</th><td><?php echo htmlspecialchars($estudiante['CORREO']); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Materias Regulares -->
        <div id="materias-content" class="content-section">
            <div class="card">
                <div class="card-header">Materias Regulares</div>
                <div class="card-body">
                    <?php if (!empty($notas_por_tipo['materias'])): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Materia</th>
                                    <th>Docente</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>Promedio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_por_tipo['materias'] as $materia => $datos): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia); ?></td>
                                    <td><?php echo htmlspecialchars($datos['DOCENTE']); ?></td>
                                    <td><?php echo $datos['PERIODO1'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO2'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO3'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PROMEDIO_FINAL'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($datos['ESTADO'] === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($datos['ESTADO'] === 'Reprobado'): ?>
                                            <span class="badge bg-warning text-dark">Reprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No tienes materias regulares asignadas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seminarios -->
        <div id="seminarios-content" class="content-section">
            <div class="card">
                <div class="card-header">Seminarios</div>
                <div class="card-body">
                    <?php if (!empty($notas_por_tipo['seminarios'])): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Seminario</th>
                                    <th>Docente</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>Promedio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_por_tipo['seminarios'] as $materia => $datos): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia); ?></td>
                                    <td><?php echo htmlspecialchars($datos['DOCENTE']); ?></td>
                                    <td><?php echo $datos['PERIODO1'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO2'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO3'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PROMEDIO_FINAL'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($datos['ESTADO'] === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($datos['ESTADO'] === 'Reprobado'): ?>
                                            <span class="badge bg-warning text-dark">Reprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No tienes seminarios asignados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Módulos -->
        <div id="modulos-content" class="content-section">
            <div class="card">
                <div class="card-header">Módulos</div>
                <div class="card-body">
                    <?php if (!empty($notas_por_tipo['modulos'])): ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Nombre</th>
                                    <th>Docente</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>Promedio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_por_tipo['modulos'] as $materia => $datos): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia); ?></td>
                                    <td><?php echo htmlspecialchars($datos['NOMBRE_MODULO'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($datos['DOCENTE']); ?></td>
                                    <td><?php echo $datos['PERIODO1'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO2'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO3'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PROMEDIO_FINAL'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($datos['ESTADO'] === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($datos['ESTADO'] === 'Reprobado'): ?>
                                            <span class="badge bg-warning text-dark">Reprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No tienes módulos asignados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Notas (Boletín completo) -->
        <div id="notas-content" class="content-section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Boletín Académico</span>
                    <button class="btn btn-sm btn-primary btn-download" onclick="generarPDF()">
                        <i class="bi bi-download"></i> Descargar PDF
                    </button>
                </div>
                <div class="card-body">

                    <?php if (!empty($notas_por_tipo['materias'])): ?>
                        <h5>Materias Regulares</h5>
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Materia</th>
                                    <th>Docente</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>Promedio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_por_tipo['materias'] as $materia => $datos): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia); ?></td>
                                    <td><?php echo htmlspecialchars($datos['DOCENTE']); ?></td>
                                    <td><?php echo $datos['PERIODO1'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO2'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO3'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PROMEDIO_FINAL'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($datos['ESTADO'] === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($datos['ESTADO'] === 'Reprobado'): ?>
                                            <span class="badge bg-warning text-dark">Reprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (!empty($notas_por_tipo['seminarios'])): ?>
                        <h5>Seminarios</h5>
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Seminario</th>
                                    <th>Docente</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>Promedio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_por_tipo['seminarios'] as $materia => $datos): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia); ?></td>
                                    <td><?php echo htmlspecialchars($datos['DOCENTE']); ?></td>
                                    <td><?php echo $datos['PERIODO1'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO2'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO3'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PROMEDIO_FINAL'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($datos['ESTADO'] === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($datos['ESTADO'] === 'Reprobado'): ?>
                                            <span class="badge bg-warning text-dark">Reprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (!empty($notas_por_tipo['modulos'])): ?>
                        <h5>Módulos</h5>
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Módulo</th>
                                    <th>Nombre</th>
                                    <th>Docente</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>Promedio</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_por_tipo['modulos'] as $materia => $datos): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia); ?></td>
                                    <td><?php echo htmlspecialchars($datos['NOMBRE_MODULO'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($datos['DOCENTE']); ?></td>
                                    <td><?php echo $datos['PERIODO1'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO2'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PERIODO3'] ?? '-'; ?></td>
                                    <td><?php echo $datos['PROMEDIO_FINAL'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($datos['ESTADO'] === 'Aprobado'): ?>
                                            <span class="badge bg-success">Aprobado</span>
                                        <?php elseif ($datos['ESTADO'] === 'Reprobado'): ?>
                                            <span class="badge bg-warning text-dark">Reprobado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">En curso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (empty($notas_organizadas)): ?>
                        <p class="text-muted">No tienes ninguna nota registrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
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

            // Mostrar dashboard por defecto
            document.querySelector('.menu-item.active').click();

            // Gráfico
            const ctx = document.getElementById('rendimientoChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Periodo 1', 'Periodo 2', 'Periodo 3'],
                    datasets: [{
                        label: 'Promedio',
                        data: [<?php echo $promedios['p1']; ?>, <?php echo $promedios['p2']; ?>, <?php echo $promedios['p3']; ?>],
                        backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)'],
                        borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, max: 10 }
                    }
                }
            });
        });

        function generarPDF() {
            const element = document.getElementById('notas-content');
            const opt = {
                margin: 1,
                filename: 'boletin_<?php echo $_SESSION['user_nie']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'cm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().from(element).set(opt).save();
        }
    </script>

    <?php $conn->close(); ?>
</body>
</html>