<?php
/**
 * Gestión de Mascotas
 * Accesible para Administradores y Refugios
 */

session_start();
require_once '../../config/conexion.php';

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Gestión de Mascotas';
$current_page = 'mascotas';
$base_url = '../../';

// Procesar acciones
$accion = $_GET['accion'] ?? '';
$id_mascota = $_GET['id'] ?? null;
$mensaje = '';
$tipo_mensaje = '';

// Crear mascota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {
    $nombre = $_POST['nombre'] ?? '';
    $especie = $_POST['especie'] ?? '';
    $raza = $_POST['raza'] ?? '';
    $edad = $_POST['edad'] ?? null;
    $sexo = $_POST['sexo'] ?? '';
    $tamanio = $_POST['tamanio'] ?? '';
    $color = $_POST['color'] ?? '';
    $estado = $_POST['estado'] ?? 'disponible';
    $descripcion = $_POST['descripcion'] ?? '';
    $vacunado = isset($_POST['vacunado']) ? 1 : 0;
    $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? date('Y-m-d');
    
    // Determinar ID del refugio
    $id_refugio = $_SESSION['usuario_rol'] === 'administrador' ? ($_POST['id_refugio'] ?? null) : $_SESSION['usuario_id'];
    
    // Procesar imagen
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = '../../assets/img/uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $new_filename;
            }
        }
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO mascotas (nombre, especie, raza, edad, sexo, tamanio, color, estado, descripcion, foto, vacunado, esterilizado, id_refugio, fecha_ingreso)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $especie, $raza, $edad, $sexo, $tamanio, $color, $estado, $descripcion, $foto, $vacunado, $esterilizado, $id_refugio, $fecha_ingreso]);
        $mensaje = "Mascota registrada exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Actualizar mascota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'editar' && $id_mascota) {
    $nombre = $_POST['nombre'] ?? '';
    $especie = $_POST['especie'] ?? '';
    $raza = $_POST['raza'] ?? '';
    $edad = $_POST['edad'] ?? null;
    $sexo = $_POST['sexo'] ?? '';
    $tamanio = $_POST['tamanio'] ?? '';
    $color = $_POST['color'] ?? '';
    $estado = $_POST['estado'] ?? 'disponible';
    $descripcion = $_POST['descripcion'] ?? '';
    $vacunado = isset($_POST['vacunado']) ? 1 : 0;
    $esterilizado = isset($_POST['esterilizado']) ? 1 : 0;
    
    // Obtener adoptante si el estado es adoptado
    $id_adoptante = null;
    $fecha_adopcion = null;
    if ($estado === 'adoptado' && isset($_POST['id_adoptante'])) {
        $id_adoptante = $_POST['id_adoptante'];
        $fecha_adopcion = $_POST['fecha_adopcion'] ?? date('Y-m-d');
    }
    
    try {
        $stmt = $conn->prepare("
            UPDATE mascotas 
            SET nombre = ?, especie = ?, raza = ?, edad = ?, sexo = ?, tamanio = ?, color = ?, 
                estado = ?, descripcion = ?, vacunado = ?, esterilizado = ?, id_adoptante = ?, fecha_adopcion = ?
            WHERE id_mascota = ?
        ");
        $stmt->execute([$nombre, $especie, $raza, $edad, $sexo, $tamanio, $color, $estado, $descripcion, $vacunado, $esterilizado, $id_adoptante, $fecha_adopcion, $id_mascota]);
        $mensaje = "Mascota actualizada exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Eliminar mascota
if ($accion === 'eliminar' && $id_mascota) {
    try {
        $stmt = $conn->prepare("DELETE FROM mascotas WHERE id_mascota = ?");
        $stmt->execute([$id_mascota]);
        $mensaje = "Mascota eliminada exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: No se puede eliminar la mascota";
        $tipo_mensaje = "danger";
    }
}

// Obtener mascota para editar
$mascota_editar = null;
if ($accion === 'editar' && $id_mascota) {
    $stmt = $conn->prepare("SELECT * FROM mascotas WHERE id_mascota = ?");
    $stmt->execute([$id_mascota]);
    $mascota_editar = $stmt->fetch();
}

// Obtener mascotas según el rol
if ($_SESSION['usuario_rol'] === 'administrador') {
    $stmt = $conn->query("
        SELECT m.*, u.nombre as refugio_nombre, a.nombre as adoptante_nombre
        FROM mascotas m
        LEFT JOIN usuarios u ON m.id_refugio = u.id_usuario
        LEFT JOIN usuarios a ON m.id_adoptante = a.id_usuario
        ORDER BY m.fecha_registro DESC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT m.*, u.nombre as refugio_nombre, a.nombre as adoptante_nombre
        FROM mascotas m
        LEFT JOIN usuarios u ON m.id_refugio = u.id_usuario
        LEFT JOIN usuarios a ON m.id_adoptante = a.id_usuario
        WHERE m.id_refugio = ?
        ORDER BY m.fecha_registro DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
}
$mascotas = $stmt->fetchAll();

// Obtener refugios para el formulario (solo admin)
$refugios = [];
$clientes = [];
if ($_SESSION['usuario_rol'] === 'administrador') {
    $stmt = $conn->query("SELECT id_usuario, nombre FROM usuarios WHERE rol = 'refugio' AND estado = 'activo'");
    $refugios = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT id_usuario, nombre FROM usuarios WHERE rol = 'cliente' AND estado = 'activo'");
    $clientes = $stmt->fetchAll();
}

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
                        <span class="material-icons" style="font-size: 2rem;">pets</span>
                        Gestión de Mascotas
                    </h2>
                    <p class="text-muted">Administrar mascotas del refugio</p>
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
                            <span class="material-icons"><?php echo $mascota_editar ? 'edit' : 'add'; ?></span>
                            <?php echo $mascota_editar ? 'Editar Mascota' : 'Nueva Mascota'; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?accion=<?php echo $mascota_editar ? 'editar&id=' . $mascota_editar['id_mascota'] : 'crear'; ?>" 
                                  enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nombre" class="form-label">
                                            <span class="material-icons">pets</span> Nombre
                                        </label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo htmlspecialchars($mascota_editar['nombre'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="especie" class="form-label">
                                            <span class="material-icons">category</span> Especie
                                        </label>
                                        <select class="form-select" id="especie" name="especie" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="Perro" <?php echo ($mascota_editar['especie'] ?? '') === 'Perro' ? 'selected' : ''; ?>>Perro</option>
                                            <option value="Gato" <?php echo ($mascota_editar['especie'] ?? '') === 'Gato' ? 'selected' : ''; ?>>Gato</option>
                                            <option value="Ave" <?php echo ($mascota_editar['especie'] ?? '') === 'Ave' ? 'selected' : ''; ?>>Ave</option>
                                            <option value="Conejo" <?php echo ($mascota_editar['especie'] ?? '') === 'Conejo' ? 'selected' : ''; ?>>Conejo</option>
                                            <option value="Otro" <?php echo ($mascota_editar['especie'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="raza" class="form-label">
                                            <span class="material-icons">info</span> Raza
                                        </label>
                                        <input type="text" class="form-control" id="raza" name="raza" 
                                               value="<?php echo htmlspecialchars($mascota_editar['raza'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="edad" class="form-label">
                                            <span class="material-icons">cake</span> Edad (años)
                                        </label>
                                        <input type="number" class="form-control" id="edad" name="edad" min="0" max="30"
                                               value="<?php echo htmlspecialchars($mascota_editar['edad'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="sexo" class="form-label">
                                            <span class="material-icons">wc</span> Sexo
                                        </label>
                                        <select class="form-select" id="sexo" name="sexo">
                                            <option value="">Seleccionar...</option>
                                            <option value="macho" <?php echo ($mascota_editar['sexo'] ?? '') === 'macho' ? 'selected' : ''; ?>>Macho</option>
                                            <option value="hembra" <?php echo ($mascota_editar['sexo'] ?? '') === 'hembra' ? 'selected' : ''; ?>>Hembra</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="tamanio" class="form-label">
                                            <span class="material-icons">straighten</span> Tamaño
                                        </label>
                                        <select class="form-select" id="tamanio" name="tamanio">
                                            <option value="">Seleccionar...</option>
                                            <option value="pequeño" <?php echo ($mascota_editar['tamanio'] ?? '') === 'pequeño' ? 'selected' : ''; ?>>Pequeño</option>
                                            <option value="mediano" <?php echo ($mascota_editar['tamanio'] ?? '') === 'mediano' ? 'selected' : ''; ?>>Mediano</option>
                                            <option value="grande" <?php echo ($mascota_editar['tamanio'] ?? '') === 'grande' ? 'selected' : ''; ?>>Grande</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="color" class="form-label">
                                            <span class="material-icons">palette</span> Color
                                        </label>
                                        <input type="text" class="form-control" id="color" name="color" 
                                               value="<?php echo htmlspecialchars($mascota_editar['color'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="estado" class="form-label">
                                            <span class="material-icons">toggle_on</span> Estado
                                        </label>
                                        <select class="form-select" id="estado" name="estado" required>
                                            <option value="disponible" <?php echo ($mascota_editar['estado'] ?? 'disponible') === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                            <option value="adoptado" <?php echo ($mascota_editar['estado'] ?? '') === 'adoptado' ? 'selected' : ''; ?>>Adoptado</option>
                                            <option value="en riesgo" <?php echo ($mascota_editar['estado'] ?? '') === 'en riesgo' ? 'selected' : ''; ?>>En Riesgo</option>
                                        </select>
                                    </div>
                                    
                                    <?php if ($mascota_editar && $mascota_editar['estado'] === 'adoptado'): ?>
                                    <div class="col-md-4 mb-3" id="adoptanteDiv">
                                        <label for="id_adoptante" class="form-label">
                                            <span class="material-icons">person</span> Adoptante
                                        </label>
                                        <select class="form-select" id="id_adoptante" name="id_adoptante">
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?php echo $cliente['id_usuario']; ?>" 
                                                        <?php echo ($mascota_editar['id_adoptante'] ?? '') == $cliente['id_usuario'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cliente['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3" id="fechaAdopcionDiv">
                                        <label for="fecha_adopcion" class="form-label">
                                            <span class="material-icons">event</span> Fecha Adopción
                                        </label>
                                        <input type="date" class="form-control" id="fecha_adopcion" name="fecha_adopcion" 
                                               value="<?php echo $mascota_editar['fecha_adopcion'] ?? date('Y-m-d'); ?>">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!$mascota_editar): ?>
                                    <div class="col-md-4 mb-3">
                                        <label for="fecha_ingreso" class="form-label">
                                            <span class="material-icons">event</span> Fecha Ingreso
                                        </label>
                                        <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" 
                                               value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($_SESSION['usuario_rol'] === 'administrador' && !$mascota_editar): ?>
                                    <div class="col-md-4 mb-3">
                                        <label for="id_refugio" class="form-label">
                                            <span class="material-icons">home</span> Refugio
                                        </label>
                                        <select class="form-select" id="id_refugio" name="id_refugio">
                                            <?php foreach ($refugios as $refugio): ?>
                                                <option value="<?php echo $refugio['id_usuario']; ?>">
                                                    <?php echo htmlspecialchars($refugio['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <span class="material-icons">medical_services</span> Estado de Salud
                                        </label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="vacunado" name="vacunado" 
                                                   <?php echo ($mascota_editar['vacunado'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="vacunado">
                                                Vacunado
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="esterilizado" name="esterilizado" 
                                                   <?php echo ($mascota_editar['esterilizado'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="esterilizado">
                                                Esterilizado
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <?php if (!$mascota_editar): ?>
                                    <div class="col-md-6 mb-3">
                                        <label for="foto" class="form-label">
                                            <span class="material-icons">photo_camera</span> Foto
                                        </label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">
                                        <span class="material-icons">description</span> Descripción
                                    </label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($mascota_editar['descripcion'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <?php if ($mascota_editar): ?>
                                        <a href="gestion_mascotas.php" class="btn btn-secondary">
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
            
            <!-- Listado de Mascotas -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">list</span>
                            Lista de Mascotas
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Foto</th>
                                            <th>Nombre</th>
                                            <th>Especie/Raza</th>
                                            <th>Edad</th>
                                            <th>Estado</th>
                                            <th>Refugio</th>
                                            <th>Adoptante</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mascotas as $mascota): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($mascota['foto']): ?>
                                                        <img src="../../assets/img/uploads/<?php echo htmlspecialchars($mascota['foto']); ?>" 
                                                             alt="Foto" class="pet-image-thumbnail">
                                                    <?php else: ?>
                                                        <span class="material-icons text-muted">pets</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($mascota['especie'] . ' / ' . ($mascota['raza'] ?? 'N/A')); ?></td>
                                                <td><?php echo htmlspecialchars($mascota['edad'] ?? 'N/A') . ' años'; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo str_replace(' ', '-', $mascota['estado']); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $mascota['estado'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($mascota['refugio_nombre'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($mascota['adoptante_nombre'] ?? '-'); ?></td>
                                                <td>
                                                    <a href="?accion=editar&id=<?php echo $mascota['id_mascota']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <span class="material-icons">edit</span>
                                                    </a>
                                                    <a href="?accion=eliminar&id=<?php echo $mascota['id_mascota']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirmarEliminacion('¿Eliminar esta mascota?')" 
                                                       title="Eliminar">
                                                        <span class="material-icons">delete</span>
                                                    </a>
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
