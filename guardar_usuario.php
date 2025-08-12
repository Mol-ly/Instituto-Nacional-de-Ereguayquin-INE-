<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_usuario'])) {
    $conn = conectarDB();
    
    // Datos básicos (siempre presentes)
    $nie = $_POST['nie'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    
    // Datos condicionales (estudiante)
    $fecha_nacimiento = $rol === 'ESTUDIANTE' ? $_POST['fecha_nacimiento'] : null;
    $direccion = $rol === 'ESTUDIANTE' ? $_POST['direccion'] : null;
    $telefono = $rol === 'ESTUDIANTE' ? $_POST['telefono'] : null;
    $especialidad = $rol === 'ESTUDIANTE' ? $_POST['especialidad'] : null;
    $ano_academico = $rol === 'ESTUDIANTE' ? $_POST['ano_academico'] : null;
    $edad = $rol === 'ESTUDIANTE' ? $_POST['edad'] : null;
    $turno = $rol === 'ESTUDIANTE' ? $_POST['turno'] : null;
    $estado = $rol === 'ESTUDIANTE' ? $_POST['estado'] : null;
    $seccion = $rol === 'ESTUDIANTE' ? $_POST['seccion'] : null;
    
    try {
        $stmt = $conn->prepare("INSERT INTO personal_registrado (
            NIE, NOMBRE, APELLIDO, CORREO, CONTRASEÑA, ROL, 
            FECHA_DE_NACIMIENTO, DIRECCION, TELEFONO, ESPECIALIDAD, 
            ANO_ACADEMICO, EDAD, TURNO, ESTADO, SECCION
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param(
            "issssssssssiiss",
            $nie, $nombre, $apellido, $correo, $contrasena, $rol,
            $fecha_nacimiento, $direccion, $telefono, $especialidad,
            $ano_academico, $edad, $turno, $estado, $seccion
        );
        
        if ($stmt->execute()) {
            header("Location: admin_panel.php?success=1");
            exit();
        } else {
            header("Location: admin_panel.php?error=1");
            exit();
        }
    } catch (Exception $e) {
        header("Location: admin_panel.php?error=1");
        exit();
    }
} else {
    header("Location: admin_panel.php");
    exit();
}
?>