<?php
/**
 * Mis Adopciones
 * Solo para Clientes
 */

session_start();
require_once '../../config/conexion.php';

// Verificar que es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'cliente') {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Mis Adopciones';
$current_page = 'mis_adopciones';
$base_url = '../../';

// Obtener mis mascotas adoptadas con detalles
$stmt = $conn->prepare("
    SELECT m.*, u.nombre as refugio_nombre, u.correo as refugio_correo,
           (SELECT COUNT(*) FROM seguimientos WHERE id_mascota = m.id_mascota) as total_seguimientos,
           (SELECT COUNT(*) FROM seguimientos WHERE id_mascota = m.id_mascota AND estado_validacion = 'aprobado') as seguimientos_aprobados,
           (SELECT AVG(calificacion_bienestar) FROM seguimientos WHERE id_mascota = m.id_mascota) as promedio_bienestar
    FROM mascotas m
    LEFT JOIN usuarios u ON m.id_refugio = u.id_usuario
    WHERE m.id_adoptante = ?
    ORDER BY m.fecha_adopcion DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$mis_adopciones = $stmt->fetchAll();

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
                        <span class="material-icons" style="font-size: 2rem;">favorite</span>
                        Mis Adopciones
                    </h2>
                    <p class="text-muted">Detalles de las mascotas que he adoptado</p>
                </div>
            </div>
            
            <?php if (count($mis_adopciones) > 0): ?>
                <!-- Lista de adopciones -->
                <?php foreach ($mis_adopciones as $mascota): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Foto de la mascota -->
                                        <div class="col-md-3 text-center">
                                            <?php if ($mascota['foto']): ?>
                                                <img src="../../assets/img/uploads/<?php echo htmlspecialchars($mascota['foto']); ?>" 
                                                     alt="<?php echo htmlspecialchars($mascota['nombre']); ?>" 
                                                     class="img-fluid rounded pet-image">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="height: 200px;">
                                                    <span class="material-icons text-muted" style="font-size: 5rem;">pets</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mt-3">
                                                <span class="badge badge-<?php echo $mascota['estado']; ?> w-100">
                                                    <?php echo ucfirst(str_replace('_', ' ', $mascota['estado'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Información de la mascota -->
                                        <div class="col-md-5">
                                            <h3 class="text-primary-custom">
                                                <span class="material-icons">pets</span>
                                                <?php echo htmlspecialchars($mascota['nombre']); ?>
                                            </h3>
                                            
                                            <div class="mb-2">
                                                <strong><span class="material-icons" style="font-size: 1rem;">category</span> Especie:</strong> 
                                                <?php echo htmlspecialchars($mascota['especie']); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong><span class="material-icons" style="font-size: 1rem;">info</span> Raza:</strong> 
                                                <?php echo htmlspecialchars($mascota['raza'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong><span class="material-icons" style="font-size: 1rem;">cake</span> Edad:</strong> 
                                                <?php echo htmlspecialchars($mascota['edad'] ?? 'N/A'); ?> años
                                            </div>
                                            <div class="mb-2">
                                                <strong><span class="material-icons" style="font-size: 1rem;">wc</span> Sexo:</strong> 
                                                <?php echo ucfirst($mascota['sexo'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong><span class="material-icons" style="font-size: 1rem;">straighten</span> Tamaño:</strong> 
                                                <?php echo ucfirst($mascota['tamanio'] ?? 'N/A'); ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong><span class="material-icons" style="font-size: 1rem;">palette</span> Color:</strong> 
                                                <?php echo htmlspecialchars($mascota['color'] ?? 'N/A'); ?>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <span class="badge bg-<?php echo $mascota['vacunado'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $mascota['vacunado'] ? '✓ Vacunado' : '✗ No Vacunado'; ?>
                                                </span>
                                                <span class="badge bg-<?php echo $mascota['esterilizado'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $mascota['esterilizado'] ? '✓ Esterilizado' : '✗ No Esterilizado'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Estadísticas y acciones -->
                                        <div class="col-md-4">
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">Información de Adopción</h6>
                                                    <p class="mb-1">
                                                        <strong>Fecha de Adopción:</strong><br>
                                                        <?php echo date('d/m/Y', strtotime($mascota['fecha_adopcion'])); ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Refugio:</strong><br>
                                                        <?php echo htmlspecialchars($mascota['refugio_nombre'] ?? 'N/A'); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="card bg-light mb-3">
                                                <div class="card-body">
                                                    <h6 class="card-title">Mis Seguimientos</h6>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Total Reportes:</span>
                                                        <strong><?php echo $mascota['total_seguimientos']; ?></strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>Aprobados:</span>
                                                        <strong class="text-success"><?php echo $mascota['seguimientos_aprobados']; ?></strong>
                                                    </div>
                                                    <div class="mb-2">
                                                        <span>Promedio Bienestar:</span><br>
                                                        <?php if ($mascota['promedio_bienestar']): ?>
                                                            <?php 
                                                            $promedio = round($mascota['promedio_bienestar'], 1);
                                                            for ($i = 0; $i < 5; $i++): 
                                                            ?>
                                                                <span class="material-icons" style="font-size: 1.2rem; color: <?php echo $i < $promedio ? '#F39C12' : '#ddd'; ?>">star</span>
                                                            <?php endfor; ?>
                                                            <small class="ms-1"><?php echo $promedio; ?>/5</small>
                                                        <?php else: ?>
                                                            <span class="text-muted">Sin reportes</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <a href="../seguimiento/subir_evidencia.php?mascota=<?php echo $mascota['id_mascota']; ?>" 
                                               class="btn btn-success w-100 mb-2">
                                                <span class="material-icons">upload</span> Subir Evidencia
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Descripción -->
                                    <?php if ($mascota['descripcion']): ?>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <strong><span class="material-icons" style="font-size: 1rem;">description</span> Descripción:</strong>
                                                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($mascota['descripcion'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Últimos seguimientos -->
                                    <?php
                                    $stmt_seg = $conn->prepare("
                                        SELECT * FROM seguimientos 
                                        WHERE id_mascota = ? 
                                        ORDER BY fecha_reporte DESC 
                                        LIMIT 3
                                    ");
                                    $stmt_seg->execute([$mascota['id_mascota']]);
                                    $seguimientos = $stmt_seg->fetchAll();
                                    ?>
                                    
                                    <?php if (count($seguimientos) > 0): ?>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <h6><strong>Últimos Seguimientos:</strong></h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Calificación</th>
                                                                <th>Estado</th>
                                                                <th>Observaciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($seguimientos as $seg): ?>
                                                                <tr>
                                                                    <td><?php echo date('d/m/Y', strtotime($seg['fecha_reporte'])); ?></td>
                                                                    <td>
                                                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                                                            <span class="material-icons" style="font-size: 0.9rem; color: <?php echo $i < $seg['calificacion_bienestar'] ? '#F39C12' : '#ddd'; ?>">star</span>
                                                                        <?php endfor; ?>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge badge-<?php echo $seg['estado_validacion']; ?>">
                                                                            <?php echo ucfirst($seg['estado_validacion']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?php echo htmlspecialchars(substr($seg['observaciones'], 0, 50)) . (strlen($seg['observaciones']) > 50 ? '...' : ''); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            <?php else: ?>
                <!-- No tiene adopciones -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <span class="material-icons text-muted" style="font-size: 5rem; opacity: 0.3;">pets</span>
                                <h4 class="text-muted mt-3">Aún no tienes mascotas adoptadas</h4>
                                <p class="text-muted">Contacta con un refugio para iniciar el proceso de adopción.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include '../../includes/footer.php'; ?>
