<?php
/**
 * Validar Reportes de Seguimiento
 * Para Administradores y Refugios
 */

session_start();
require_once '../../config/conexion.php';

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Validar Seguimientos';
$current_page = 'validar_seguimiento';
$base_url = '../../';

$mensaje = '';
$tipo_mensaje = '';

// Procesar validación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_seguimiento = $_POST['id_seguimiento'] ?? null;
    $estado_validacion = $_POST['estado_validacion'] ?? '';
    $comentarios_refugio = $_POST['comentarios_refugio'] ?? '';
    
    if ($id_seguimiento && $estado_validacion) {
        try {
            $stmt = $conn->prepare("
                UPDATE seguimientos 
                SET estado_validacion = ?, comentarios_refugio = ?, fecha_validacion = NOW()
                WHERE id_seguimiento = ?
            ");
            $stmt->execute([$estado_validacion, $comentarios_refugio, $id_seguimiento]);
            $mensaje = "Seguimiento validado exitosamente";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al validar: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}

// Obtener seguimientos pendientes
$stmt = $conn->query("
    SELECT s.*, m.nombre as mascota_nombre, m.especie, u.nombre as cliente_nombre, u.correo as cliente_correo
    FROM seguimientos s
    JOIN mascotas m ON s.id_mascota = m.id_mascota
    JOIN usuarios u ON s.id_cliente = u.id_usuario
    ORDER BY 
        CASE WHEN s.estado_validacion = 'pendiente' THEN 0 ELSE 1 END,
        s.fecha_reporte DESC
");
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
                        <span class="material-icons" style="font-size: 2rem;">fact_check</span>
                        Validar Seguimientos
                    </h2>
                    <p class="text-muted">Revisar y validar evidencias de seguimiento de adoptantes</p>
                </div>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Lista de seguimientos -->
            <div class="row">
                <?php if (count($seguimientos) > 0): ?>
                    <?php foreach ($seguimientos as $seg): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card <?php echo $seg['estado_validacion'] === 'pendiente' ? 'border-warning' : ''; ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="material-icons">pets</span>
                                        <?php echo htmlspecialchars($seg['mascota_nombre']); ?>
                                    </span>
                                    <span class="badge badge-<?php echo $seg['estado_validacion']; ?>">
                                        <?php echo ucfirst($seg['estado_validacion']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <!-- Información del reporte -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Adoptante:</strong> <?php echo htmlspecialchars($seg['cliente_nombre']); ?></p>
                                            <p class="mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($seg['cliente_correo']); ?></p>
                                            <p class="mb-1"><strong>Fecha Reporte:</strong> <?php echo date('d/m/Y', strtotime($seg['fecha_reporte'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Calificación Bienestar:</strong></p>
                                            <div>
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <span class="material-icons" style="font-size: 1.5rem; color: <?php echo $i < $seg['calificacion_bienestar'] ? '#F39C12' : '#ddd'; ?>">star</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Foto de evidencia -->
                                    <?php if ($seg['foto_evidencia']): ?>
                                        <div class="mb-3">
                                            <img src="../../assets/img/uploads/<?php echo htmlspecialchars($seg['foto_evidencia']); ?>" 
                                                 alt="Evidencia" class="img-fluid rounded" style="max-height: 300px; object-fit: cover; width: 100%;">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Observaciones -->
                                    <div class="mb-3">
                                        <strong>Observaciones del Adoptante:</strong>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($seg['observaciones'])); ?></p>
                                    </div>
                                    
                                    <!-- Comentarios del refugio -->
                                    <?php if ($seg['comentarios_refugio']): ?>
                                        <div class="alert alert-info mb-3">
                                            <strong>Comentarios del Refugio:</strong>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($seg['comentarios_refugio'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Formulario de validación -->
                                    <?php if ($seg['estado_validacion'] === 'pendiente'): ?>
                                        <form method="POST" class="border-top pt-3">
                                            <input type="hidden" name="id_seguimiento" value="<?php echo $seg['id_seguimiento']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <span class="material-icons">rate_review</span> Estado de Validación
                                                </label>
                                                <select class="form-select" name="estado_validacion" required>
                                                    <option value="">Seleccionar...</option>
                                                    <option value="aprobado">Aprobar</option>
                                                    <option value="rechazado">Rechazar</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <span class="material-icons">comment</span> Comentarios (opcional)
                                                </label>
                                                <textarea class="form-control" name="comentarios_refugio" rows="2" 
                                                          placeholder="Agregar comentarios sobre este seguimiento..."></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success w-100">
                                                <span class="material-icons">check</span> Validar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-muted text-center">
                                            <span class="material-icons">check_circle</span>
                                            Validado el <?php echo date('d/m/Y', strtotime($seg['fecha_validacion'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <span class="material-icons text-muted" style="font-size: 5rem; opacity: 0.3;">fact_check</span>
                                <h4 class="text-muted mt-3">No hay seguimientos para validar</h4>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include '../../includes/footer.php'; ?>
