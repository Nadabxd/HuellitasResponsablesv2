<?php
/**
 * Subir Evidencias de Seguimiento
 * Solo para Clientes (Adoptantes)
 */

session_start();
require_once '../../config/conexion.php';

// Verificar que es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'cliente') {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Subir Evidencias';
$current_page = 'seguimiento';
$base_url = '../../';

$mensaje = '';
$tipo_mensaje = '';

// Obtener mascotas adoptadas por el usuario
$stmt = $conn->prepare("
    SELECT id_mascota, nombre, especie, raza 
    FROM mascotas 
    WHERE id_adoptante = ? AND estado = 'adoptado'
");
$stmt->execute([$_SESSION['usuario_id']]);
$mis_mascotas = $stmt->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_mascota = $_POST['id_mascota'] ?? null;
    $fecha_reporte = $_POST['fecha_reporte'] ?? date('Y-m-d');
    $observaciones = $_POST['observaciones'] ?? '';
    $calificacion_bienestar = $_POST['calificacion_bienestar'] ?? 5;
    
    if ($id_mascota) {
        // Procesar imagen
        $foto_evidencia = null;
        if (isset($_FILES['foto_evidencia']) && $_FILES['foto_evidencia']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['foto_evidencia']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'seguimiento_' . uniqid() . '.' . $ext;
                $upload_path = '../../assets/img/uploads/' . $new_filename;
                
                if (move_uploaded_file($_FILES['foto_evidencia']['tmp_name'], $upload_path)) {
                    $foto_evidencia = $new_filename;
                }
            }
        }
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO seguimientos (id_mascota, id_cliente, fecha_reporte, foto_evidencia, observaciones, calificacion_bienestar)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_mascota, $_SESSION['usuario_id'], $fecha_reporte, $foto_evidencia, $observaciones, $calificacion_bienestar]);
            $mensaje = "Evidencia subida exitosamente. Esperando validación del refugio.";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al subir la evidencia: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "Por favor seleccione una mascota";
        $tipo_mensaje = "warning";
    }
}

// Obtener historial de seguimientos
$stmt = $conn->prepare("
    SELECT s.*, m.nombre as mascota_nombre
    FROM seguimientos s
    JOIN mascotas m ON s.id_mascota = m.id_mascota
    WHERE s.id_cliente = ?
    ORDER BY s.fecha_reporte DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['usuario_id']]);
$seguimientos = $stmt->fetchAll();

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
                        <span class="material-icons" style="font-size: 2rem;">upload_file</span>
                        Subir Evidencias de Seguimiento
                    </h2>
                    <p class="text-muted">Comparte el progreso y bienestar de tu mascota adoptada</p>
                </div>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (count($mis_mascotas) > 0): ?>
            <!-- Formulario para subir evidencia -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">add_photo_alternate</span>
                            Nueva Evidencia
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="id_mascota" class="form-label">
                                        <span class="material-icons">pets</span> Mascota
                                    </label>
                                    <select class="form-select" id="id_mascota" name="id_mascota" required>
                                        <option value="">Seleccionar mascota...</option>
                                        <?php foreach ($mis_mascotas as $mascota): ?>
                                            <option value="<?php echo $mascota['id_mascota']; ?>">
                                                <?php echo htmlspecialchars($mascota['nombre'] . ' - ' . $mascota['especie']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="fecha_reporte" class="form-label">
                                        <span class="material-icons">event</span> Fecha del Reporte
                                    </label>
                                    <input type="date" class="form-control" id="fecha_reporte" name="fecha_reporte" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="calificacion_bienestar" class="form-label">
                                        <span class="material-icons">star</span> Calificación de Bienestar (1-5)
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="range" class="form-range" id="calificacion_bienestar" 
                                               name="calificacion_bienestar" min="1" max="5" value="5" 
                                               oninput="document.getElementById('calificacionValue').textContent = this.value">
                                        <span class="ms-3 badge bg-success" id="calificacionValue">5</span>
                                    </div>
                                    <small class="text-muted">1 = Muy mal, 5 = Excelente</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="foto_evidencia" class="form-label">
                                        <span class="material-icons">photo_camera</span> Foto de Evidencia
                                    </label>
                                    <input type="file" class="form-control" id="foto_evidencia" 
                                           name="foto_evidencia" accept="image/*" data-preview="previewImage">
                                    <img id="previewImage" src="#" alt="Preview" class="mt-3 pet-image" style="display: none;">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">
                                        <span class="material-icons">description</span> Observaciones
                                    </label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" 
                                              rows="4" required placeholder="Describe el estado de tu mascota, actividades, alimentación, comportamiento, etc."></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <span class="material-icons">upload</span> Subir Evidencia
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Info lateral -->
                <div class="col-lg-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">
                                <span class="material-icons text-primary-custom">info</span>
                                Información Importante
                            </h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="material-icons text-success">check_circle</span>
                                    Sube evidencias regularmente
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-success">check_circle</span>
                                    Incluye fotos claras de tu mascota
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-success">check_circle</span>
                                    Describe su estado de salud y comportamiento
                                </li>
                                <li class="mb-2">
                                    <span class="material-icons text-success">check_circle</span>
                                    El refugio validará tus reportes
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historial de seguimientos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">history</span>
                            Mis Reportes Recientes
                        </div>
                        <div class="card-body">
                            <?php if (count($seguimientos) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Mascota</th>
                                                <th>Fecha</th>
                                                <th>Calificación</th>
                                                <th>Estado</th>
                                                <th>Foto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($seguimientos as $seg): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($seg['mascota_nombre']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($seg['fecha_reporte'])); ?></td>
                                                    <td>
                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                            <span class="material-icons" style="font-size: 1rem; color: <?php echo $i < $seg['calificacion_bienestar'] ? '#F39C12' : '#ddd'; ?>">star</span>
                                                        <?php endfor; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $seg['estado_validacion']; ?>">
                                                            <?php echo ucfirst($seg['estado_validacion']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($seg['foto_evidencia']): ?>
                                                            <span class="material-icons text-success">check_circle</span>
                                                        <?php else: ?>
                                                            <span class="material-icons text-muted">cancel</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">No has enviado reportes aún.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- No tiene mascotas adoptadas -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <span class="material-icons text-muted" style="font-size: 5rem; opacity: 0.3;">pets</span>
                            <h4 class="text-muted mt-3">No tienes mascotas adoptadas</h4>
                            <p class="text-muted">Contacta con un refugio para iniciar el proceso de adopción.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php include '../../includes/footer.php'; ?>
