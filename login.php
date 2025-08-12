<?php
session_start();

// Si ya hay sesión, redirigir
if (isset($_SESSION['user_rol'])) {
    switch ($_SESSION['user_rol']) {
        case 'ADMINISTRADOR': header("Location: admin_panel.php"); break;
        case 'DOCENTE':       header("Location: docente_panel.php"); break;
        case 'ESTUDIANTE':    header("Location: estudiante_panel.php"); break;
        default:              header("Location: login.php"); break;
    }
    exit();
}
?>
<?php if (isset($_GET['mensaje'])): ?>
    <div class="alert alert-success">
        <?php echo $_GET['mensaje'] === 'sesion_cerrada' ? 'Sesión cerrada correctamente' : ''; ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Acceso - Gobierno de El Salvador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="imagenes/Logo_del_Gobierno_de_El_Salvador_(2019).png">
    <style>

        :root {
            --color-primary: #37363eff;
            --color-secondary: #1e293b;
            --color-accent: #383d59ff;
            --color-text: #ffffffff;
            --color-light: #f8f9fa;
            --color-border: #dee2e6;
        }
        body {
    font-family: 'Roboto', sans-serif;
    background: url('imagenes/fond (1).png') no-repeat center center fixed;
    background-size: cover;
    color: var(--color-text);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin: 0;
    padding: 0;
}
        
        .login-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            padding: 2rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #686868ff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        .card-header {
            background-color: var(--color-primary);
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: 4px solid rgba(255, 255, 255, 0.1);
        }
        .card-header h2 {
            font-weight: 500;
            margin: 0;
            font-size: 1.5rem;
        }
        .card-body {
            padding: 2rem;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-container img {
            height: 80px;
            width: auto;
            margin-bottom: 1rem;
        }
        .form-label {
            font-weight: 500;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }
        .form-control {
            height: 48px;
            border-radius: 6px;
            border: 1px solid var(--color-border);
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
        }
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 6px;
            background-color: var(--color-primary);
            border: none;
            transition: background-color 0.3s;
            margin-top: 0.5rem;
        }
        .btn-login:hover {
            background-color: #364a60ff;
        }
        .footer {
            text-align: center;
            padding: 1.5rem;
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 2rem;
        }
        .footer a {
            color: var(--color-primary);
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        .form-footer a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 576px) {
            .login-card {
                border-radius: 0;
                box-shadow: none;
            }
            .login-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <div class="card-header">
                <h2>Acceso al Sistema</h2>
            </div>
            <div class="card-body">
                <div class="logo-container">
                    <img src="imagenes/Logo_del_Gobierno_de_El_Salvador_(2019).png" 
                        alt="Logo Gobierno de El Salvador" 
                        class="img-fluid">
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger mb-4">
                        <?php
                        $errores = [
                            'campos_vacios' => 'Por favor complete todos los campos',
                            'credenciales_invalidas' => 'Usuario o contraseña incorrectos',
                            'rol_no_valido' => 'Rol de usuario no reconocido'
                        ];
                        echo htmlspecialchars($errores[$_GET['error']] ?? 'Error');
                        ?>
                    </div>
                <?php endif; ?>

                <form action="procesar_login.php" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Correo</label>
                        <input type="text" 
                            class="form-control" 
                            id="usuario" 
                            name="usuario" 
                            required
                            placeholder="Ingrese su correo">
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" 
                            class="form-control" 
                            id="contrasena" 
                            name="contrasena" 
                            required
                            placeholder="Ingrese su contraseña">
                    </div>
                    <button type="submit" class="btn btn-primary btn-login">
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>Sistema de Información © 2025 Gobierno de El Salvador. Todos los derechos reservados.</p>
        <p>Versión 2.1.0 | <a href="#">Términos de uso</a> | <a href="#">Política de privacidad</a></p>
    </footer>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario').value.trim();
            const contrasena = document.getElementById('contrasena').value.trim();
            if (!usuario || !contrasena) {
                e.preventDefault();
                alert('Por favor complete todos los campos.');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('usuario').focus();
        });
    </script>
</body>
</html>


