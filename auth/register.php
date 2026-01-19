<?php
/**
 * Sistema de Registro de Usuarios
 * HuellasResponsables
 */

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    // Validaciones
    if (empty($nombre) || empty($correo) || empty($password) || empty($password_confirm)) {
        $error = "Por favor complete todos los campos obligatorios";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor ingrese un correo electrónico válido";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden";
    } else {
        try {
            // Verificar si el correo ya existe
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            
            if ($stmt->fetch()) {
                $error = "Este correo electrónico ya está registrado";
            } else {
                // Registrar nuevo usuario como 'cliente'
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("
                    INSERT INTO usuarios (nombre, correo, password, rol, telefono, direccion, estado)
                    VALUES (?, ?, ?, 'cliente', ?, ?, 'activo')
                ");
                $stmt->execute([$nombre, $correo, $password_hash, $telefono, $direccion]);
                
                $success = "¡Registro exitoso! Ya puedes iniciar sesión.";
                
                // Limpiar campos
                $nombre = $correo = $telefono = $direccion = '';
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema. Por favor, intente más tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - HuellasResponsables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2C3E50 0%, #27AE60 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        .register-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: 20px auto;
        }
        .register-header {
            background: linear-gradient(135deg, #2C3E50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        .register-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .register-body {
            padding: 30px;
        }
        .btn-register {
            background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
            background: linear-gradient(135deg, #229954 0%, #27AE60 100%);
        }
        .form-control:focus {
            border-color: #27AE60;
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }
        .material-icons {
            vertical-align: middle;
            margin-right: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #27AE60;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <h2><span class="material-icons" style="font-size: 2rem;">pets</span></h2>
            <h2>HuellasResponsables</h2>
            <p>Crear Nueva Cuenta</p>
        </div>
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <span class="material-icons">error</span> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <span class="material-icons">check_circle</span> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nombre" class="form-label">
                        <span class="material-icons">person</span> Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                           placeholder="Juan Pérez" value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="correo" class="form-label">
                        <span class="material-icons">email</span> Correo Electrónico <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control" id="correo" name="correo" required 
                           placeholder="usuario@ejemplo.com" value="<?php echo htmlspecialchars($correo ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <span class="material-icons">lock</span> Contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="Mínimo 6 caracteres" minlength="6">
                    <small class="text-muted">Mínimo 6 caracteres</small>
                </div>
                
                <div class="mb-3">
                    <label for="password_confirm" class="form-label">
                        <span class="material-icons">lock</span> Confirmar Contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required
                           placeholder="Repite tu contraseña" minlength="6">
                </div>
                
                <div class="mb-3">
                    <label for="telefono" class="form-label">
                        <span class="material-icons">phone</span> Teléfono
                    </label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                           placeholder="555-1234" value="<?php echo htmlspecialchars($telefono ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="direccion" class="form-label">
                        <span class="material-icons">home</span> Dirección
                    </label>
                    <input type="text" class="form-control" id="direccion" name="direccion" 
                           placeholder="Calle Principal 123" value="<?php echo htmlspecialchars($direccion ?? ''); ?>">
                </div>
                
                <div class="alert alert-info small">
                    <span class="material-icons" style="font-size: 1rem;">info</span>
                    Te registrarás como <strong>Cliente/Adoptante</strong>. Podrás ver mascotas disponibles y gestionar tus adopciones.
                </div>
                
                <button type="submit" class="btn btn-register w-100">
                    <span class="material-icons">person_add</span> Registrarse
                </button>
            </form>
            
            <div class="login-link">
                <p class="text-muted mb-0">¿Ya tienes una cuenta?</p>
                <a href="login.php">
                    <span class="material-icons">login</span> Iniciar Sesión
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de formulario
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Validar que las contraseñas coincidan
        document.getElementById('password_confirm').addEventListener('input', function() {
            var password = document.getElementById('password').value;
            var confirm = this.value;
            
            if (password !== confirm) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
