<?php
/**
 * Historial de Adoptantes
 * Para Administradores y Refugios
 */

session_start();
require_once '../../config/conexion.php';

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Historial de Adoptantes';
$current_page = 'historial';
$base_url = '../../';

// Obtener adoptantes con sus estadísticas
$stmt = $conn->query("
    SELECT 
        u.id_usuario,
        u.nombre,
        u.correo,
        u.telefono,
        u.direccion,
        COUNT(DISTINCT m.id_mascota) as total_adopciones,
        COUNT(DISTINCT s.id_seguimiento) as total_reportes,
        SUM(CASE WHEN s.estado_validacion = 'aprobado' THEN 1 ELSE 0 END) as reportes_aprobados,
        AVG(s.calificacion_bienestar) as promedio_bienestar,
        ca.puntuacion_confianza,
        ca.adopciones_exitosas,
        ca.adopciones_fallidas
    FROM usuarios u
    LEFT JOIN mascotas m ON u.id_usuario = m.id_adoptante
    LEFT JOIN seguimientos s ON u.id_usuario = s.id_cliente
    LEFT JOIN calificaciones_adoptantes ca ON u.id_usuario = ca.id_adoptante
    WHERE u.rol = 'cliente'
    GROUP BY u.id_usuario
    ORDER BY total_adopciones DESC, u.nombre ASC
");
$adoptantes = $stmt->fetchAll();

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
                        <span class="material-icons" style="font-size: 2rem;">history</span>
                        Historial de Adoptantes
                    </h2>
                    <p class="text-muted">Seguimiento y calificación de adoptantes</p>
                </div>
            </div>
            
            <!-- Lista de adoptantes -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">people</span>
                            Adoptantes Registrados
                        </div>
                        <div class="card-body">
                            <?php if (count($adoptantes) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Adoptante</th>
                                                <th>Contacto</th>
                                                <th>Adopciones</th>
                                                <th>Reportes</th>
                                                <th>Aprobados</th>
                                                <th>Promedio Bienestar</th>
                                                <th>Confianza</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($adoptantes as $adoptante): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($adoptante['nombre']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($adoptante['correo']); ?><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($adoptante['telefono'] ?? 'Sin teléfono'); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $adoptante['total_adopciones']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $adoptante['total_reportes']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $adoptante['reportes_aprobados']; ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($adoptante['promedio_bienestar']): ?>
                                                            <div class="d-flex align-items-center">
                                                                <?php 
                                                                $promedio = round($adoptante['promedio_bienestar'], 1);
                                                                for ($i = 0; $i < 5; $i++): 
                                                                ?>
                                                                    <span class="material-icons" style="font-size: 1rem; color: <?php echo $i < $promedio ? '#F39C12' : '#ddd'; ?>">star</span>
                                                                <?php endfor; ?>
                                                                <small class="ms-2"><?php echo $promedio; ?></small>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $confianza = $adoptante['puntuacion_confianza'] ?? 5.0;
                                                        $color = $confianza >= 4 ? 'success' : ($confianza >= 3 ? 'warning' : 'danger');
                                                        ?>
                                                        <span class="badge bg-<?php echo $color; ?>">
                                                            <?php echo number_format($confianza, 2); ?>/5.00
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detalleModal<?php echo $adoptante['id_usuario']; ?>">
                                                            <span class="material-icons">visibility</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Modal de detalles -->
                                                <div class="modal fade" id="detalleModal<?php echo $adoptante['id_usuario']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    <span class="material-icons">person</span>
                                                                    Detalle de <?php echo htmlspecialchars($adoptante['nombre']); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php
                                                                // Obtener mascotas adoptadas
                                                                $stmt_mascotas = $conn->prepare("
                                                                    SELECT * FROM mascotas WHERE id_adoptante = ?
                                                                ");
                                                                $stmt_mascotas->execute([$adoptante['id_usuario']]);
                                                                $mascotas = $stmt_mascotas->fetchAll();
                                                                
                                                                // Obtener seguimientos
                                                                $stmt_seguimientos = $conn->prepare("
                                                                    SELECT s.*, m.nombre as mascota_nombre
                                                                    FROM seguimientos s
                                                                    JOIN mascotas m ON s.id_mascota = m.id_mascota
                                                                    WHERE s.id_cliente = ?
                                                                    ORDER BY s.fecha_reporte DESC
                                                                    LIMIT 5
                                                                ");
                                                                $stmt_seguimientos->execute([$adoptante['id_usuario']]);
                                                                $seguimientos = $stmt_seguimientos->fetchAll();
                                                                ?>
                                                                
                                                                <h6><strong>Información de Contacto:</strong></h6>
                                                                <p>
                                                                    <strong>Email:</strong> <?php echo htmlspecialchars($adoptante['correo']); ?><br>
                                                                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($adoptante['telefono'] ?? 'N/A'); ?><br>
                                                                    <strong>Dirección:</strong> <?php echo htmlspecialchars($adoptante['direccion'] ?? 'N/A'); ?>
                                                                </p>
                                                                
                                                                <h6 class="mt-4"><strong>Mascotas Adoptadas:</strong></h6>
                                                                <?php if (count($mascotas) > 0): ?>
                                                                    <ul class="list-group mb-3">
                                                                        <?php foreach ($mascotas as $m): ?>
                                                                            <li class="list-group-item">
                                                                                <strong><?php echo htmlspecialchars($m['nombre']); ?></strong> 
                                                                                - <?php echo htmlspecialchars($m['especie']); ?>
                                                                                (<?php echo htmlspecialchars($m['raza'] ?? 'N/A'); ?>)
                                                                                <span class="badge badge-<?php echo str_replace(' ', '-', $m['estado']); ?> float-end">
                                                                                    <?php echo ucfirst($m['estado']); ?>
                                                                                </span>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                <?php else: ?>
                                                                    <p class="text-muted">Sin adopciones registradas</p>
                                                                <?php endif; ?>
                                                                
                                                                <h6 class="mt-4"><strong>Últimos Seguimientos:</strong></h6>
                                                                <?php if (count($seguimientos) > 0): ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Mascota</th>
                                                                                    <th>Fecha</th>
                                                                                    <th>Calificación</th>
                                                                                    <th>Estado</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($seguimientos as $s): ?>
                                                                                    <tr>
                                                                                        <td><?php echo htmlspecialchars($s['mascota_nombre']); ?></td>
                                                                                        <td><?php echo date('d/m/Y', strtotime($s['fecha_reporte'])); ?></td>
                                                                                        <td><?php echo $s['calificacion_bienestar']; ?>/5</td>
                                                                                        <td>
                                                                                            <span class="badge badge-<?php echo $s['estado_validacion']; ?>">
                                                                                                <?php echo ucfirst($s['estado_validacion']); ?>
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <p class="text-muted">Sin seguimientos registrados</p>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No hay adoptantes registrados.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include '../../includes/footer.php'; ?>
