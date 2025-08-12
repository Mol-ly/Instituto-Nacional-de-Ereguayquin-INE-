<?php
// procesar_login.php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

if (empty($_POST['usuario']) || empty($_POST['contrasena'])) {
    header("Location: login.php?error=campos_vacios");
    exit();
}

$usuario = limpiarDatos($_POST['usuario']);
$contrasena = limpiarDatos($_POST['contrasena']);

$usuario_data = autenticarUsuario($usuario, $contrasena);

if ($usuario_data) {
    session_start();
    session_regenerate_id(true);

    $_SESSION['user_nie'] = $usuario_data['NIE'];
    $_SESSION['user_nombre'] = $usuario_data['NOMBRE'];
    $_SESSION['user_apellido'] = $usuario_data['APELLIDO'];
    $_SESSION['user_rol'] = $usuario_data['ROL'];
    $_SESSION['user_fecha_de_nacimiento'] = $usuario_data['FECHA_DE_NACIMIENTO'];
    $_SESSION['user_direccion'] = $usuario_data['DIRECCION'];
    $_SESSION['user_telefono'] = $usuario_data['TELEFONO'];
    $_SESSION['user_especialidad'] = $usuario_data['ESPECIALIDAD'];
    $_SESSION['user_ano_academico'] = $usuario_data['ANO_ACADEMICO'];
    $_SESSION['user_edad'] = $usuario_data['EDAD'];
    $_SESSION['user_turno'] = $usuario_data['TURNO'] ?? null;
    $_SESSION['user_estado'] = $usuario_data['ESTADO'] ?? null;
    $_SESSION['user_seccion'] = $usuario_data['SECCION'] ?? null;

    switch ($usuario_data['ROL']) {
        case 'ADMINISTRADOR':
            $destino = 'admin_panel.php';
            break;
        case 'DOCENTE':
            $destino = 'docente_panel.php';
            break;
        case 'ESTUDIANTE':
            $destino = 'estudiante_panel.php';
            break;
        default:
            header("Location: login.php?error=rol_no_valido");
            exit();
    }
    header("Location: $destino");
    exit();
} else {
    header("Location: login.php?error=credenciales_invalidas");
    exit();
}
?>