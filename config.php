<?php
// config.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'b_instituto');

function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Error: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

function limpiarDatos($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function autenticarUsuario($correo, $contrasena) {
    $conn = conectarDB();

    $stmt = $conn->prepare("SELECT NIE, NOMBRE, APELLIDO, CORREO, CONTRASEÑA, ROL, FECHA_DE_NACIMIENTO, DIRECCION, TELEFONO, ESPECIALIDAD, ANO_ACADEMICO, EDAD, TURNO, ESTADO, SECCION 
                            FROM personal_registrado 
                            WHERE CORREO = ? AND ESTADO = 'ACTIVO'");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        
        // Comparar contraseña en texto plano
        if ($contrasena === $usuario['CONTRASEÑA']) {
            unset($usuario['CONTRASEÑA']); // No guardar en sesión
            $stmt->close();
            $conn->close();
            return $usuario;
        } else {
            error_log("Contraseña incorrecta para: $correo. Ingresada: $contrasena, Guardada: " . $usuario['CONTRASEÑA']);
        }
    } else {
        error_log("Usuario no encontrado o inactivo: $correo");
    }

    $stmt->close();
    $conn->close();
    return false;
}

