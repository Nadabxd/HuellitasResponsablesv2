<?php
/**
 * Sistema de Login
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!empty($correo) && !empty($password)) {
        try {
            $stmt = $conn->prepare("SELECT id_usuario, nombre, correo, password, rol, estado FROM usuarios WHERE correo = ? AND estado = 'activo'");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Crear sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_correo'] = $usuario['correo'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                // Redirigir al dashboard
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Credenciales incorrectas o usuario inactivo";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema. Por favor, intente más tarde.";
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HuellasResponsables</title>
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
        }
        .login-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #2C3E50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        .login-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .login-body {
            padding: 30px;
        }
        .btn-login {
            background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
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
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2><span class="material-icons" style="font-size: 2rem;">pets</span></h2>
            <h2>HuellasResponsables</h2>
            <p>Sistema de Gestión de Adopciones</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <span class="material-icons">error</span> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="correo" class="form-label">
                        <span class="material-icons">email</span> Correo Electrónico
                    </label>
                    <input type="email" class="form-control" id="correo" name="correo" required 
                           placeholder="usuario@ejemplo.com" value="<?php echo htmlspecialchars($correo ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <span class="material-icons">lock</span> Contraseña
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-login w-100">
                    <span class="material-icons">login</span> Iniciar Sesión
                </button>
            </form>
            
            <div class="mt-4 text-center text-muted small">
                <p>Credenciales de prueba:</p>
                <p class="mb-1"><strong>Admin:</strong> admin@huellas.com / admin123</p>
                <p class="mb-1"><strong>Refugio:</strong> refugio@huellas.com / admin123</p>
                <p><strong>Cliente:</strong> juan@example.com / admin123</p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
