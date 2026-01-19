<?php
/**
 * CRUD de Usuarios
 * Solo accesible para Administradores
 */

session_start();
require_once '../../config/conexion.php';

// Verificar que es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'administrador') {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Gestión de Usuarios';
$current_page = 'usuarios';
$base_url = '../../';

// Procesar acciones
$accion = $_GET['accion'] ?? '';
$id_usuario = $_GET['id'] ?? null;
$mensaje = '';
$tipo_mensaje = '';

// Crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    
    if ($nombre && $correo && $password && $rol) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, telefono, direccion) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $correo, $password_hash, $rol, $telefono, $direccion]);
            $mensaje = "Usuario creado exitosamente";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}

// Actualizar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'editar' && $id_usuario) {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    
    if ($nombre && $correo && $rol) {
        try {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, telefono = ?, direccion = ?, estado = ? WHERE id_usuario = ?");
            $stmt->execute([$nombre, $correo, $rol, $telefono, $direccion, $estado, $id_usuario]);
            $mensaje = "Usuario actualizado exitosamente";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}

// Eliminar usuario
if ($accion === 'eliminar' && $id_usuario) {
    try {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $mensaje = "Usuario eliminado exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: No se puede eliminar el usuario porque tiene registros asociados";
        $tipo_mensaje = "danger";
    }
}

// Obtener usuario para editar
$usuario_editar = null;
if ($accion === 'editar' && $id_usuario) {
    $stmt = $conn->prepare("SELECT id_usuario, nombre, correo, rol, telefono, direccion, estado FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario_editar = $stmt->fetch();
}

// Obtener todos los usuarios (sin password)
$stmt = $conn->query("SELECT id_usuario, nombre, correo, rol, telefono, estado, fecha_registro FROM usuarios ORDER BY fecha_registro DESC");
$usuarios = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="text-primary-custom">
                        <span class="material-icons" style="font-size: 2rem;">people</span>
                        Gestión de Usuarios
                    </h2>
                    <p class="text-muted">Administrar usuarios del sistema</p>
                </div>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Crear/Editar -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons"><?php echo $usuario_editar ? 'edit' : 'add'; ?></span>
                            <?php echo $usuario_editar ? 'Editar Usuario' : 'Nuevo Usuario'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?accion=<?php echo $usuario_editar ? 'editar&id=' . $usuario_editar['id_usuario'] : 'crear'; ?>" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombre" class="form-label">
                                            <span class="material-icons">person</span> Nombre Completo
                                        </label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo htmlspecialchars($usuario_editar['nombre'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="correo" class="form-label">
                                            <span class="material-icons">email</span> Correo Electrónico
                                        </label>
                                        <input type="email" class="form-control" id="correo" name="correo" 
                                               value="<?php echo htmlspecialchars($usuario_editar['correo'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <?php if (!$usuario_editar): ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            <span class="material-icons">lock</span> Contraseña
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="rol" class="form-label">
                                            <span class="material-icons">admin_panel_settings</span> Rol
                                        </label>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="administrador">Administrador</option>
                                            <option value="refugio">Refugio</option>
                                            <option value="cliente">Cliente</option>
                                        </select>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="rol" class="form-label">
                                            <span class="material-icons">admin_panel_settings</span> Rol
                                        </label>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <option value="administrador" <?php echo $usuario_editar['rol'] === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                            <option value="refugio" <?php echo $usuario_editar['rol'] === 'refugio' ? 'selected' : ''; ?>>Refugio</option>
                                            <option value="cliente" <?php echo $usuario_editar['rol'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="estado" class="form-label">
                                            <span class="material-icons">toggle_on</span> Estado
                                        </label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="activo" <?php echo $usuario_editar['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactivo" <?php echo $usuario_editar['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="telefono" class="form-label">
                                            <span class="material-icons">phone</span> Teléfono
                                        </label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" 
                                               value="<?php echo htmlspecialchars($usuario_editar['telefono'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="direccion" class="form-label">
                                            <span class="material-icons">home</span> Dirección
                                        </label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" 
                                               value="<?php echo htmlspecialchars($usuario_editar['direccion'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <?php if ($usuario_editar): ?>
                                        <a href="crud_usuarios.php" class="btn btn-secondary">
                                            <span class="material-icons">cancel</span> Cancelar
                                        </a>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-success">
                                        <span class="material-icons">save</span> Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Listado de Usuarios -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">list</span>
                            Lista de Usuarios
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Rol</th>
                                            <th>Teléfono</th>
                                            <th>Estado</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?php echo $usuario['id_usuario']; ?></td>
                                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo ucfirst($usuario['rol']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($usuario['telefono'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usuario['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($usuario['estado']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                                <td>
                                                    <a href="?accion=editar&id=<?php echo $usuario['id_usuario']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <span class="material-icons">edit</span>
                                                    </a>
                                                    <?php if ($usuario['id_usuario'] != $_SESSION['usuario_id']): ?>
                                                        <a href="?accion=eliminar&id=<?php echo $usuario['id_usuario']; ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirmarEliminacion('¿Eliminar este usuario?')" 
                                                           title="Eliminar">
                                                            <span class="material-icons">delete</span>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../../includes/footer.php'; ?>
