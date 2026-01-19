<?php
/**
 * Dashboard Principal
 * HuellasResponsables - Sistema de Gestión de Adopciones
 */

session_start();
require_once 'config/conexion.php';

$page_title = 'Dashboard';
$current_page = 'dashboard';
$base_url = '';

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <!-- Encabezado del Dashboard -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="text-primary-custom mb-1">
                        <span class="material-icons" style="font-size: 2rem;">dashboard</span>
                        Dashboard Principal
                    </h2>
                    <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> 
                        <span class="badge bg-success"><?php echo ucfirst($_SESSION['usuario_rol']); ?></span>
                    </p>
                </div>
            </div>
            
            <?php
            // Estadísticas según el rol del usuario
            $estadisticas = [];
            
            if ($_SESSION['usuario_rol'] === 'administrador') {
                // Estadísticas para Administrador
                $stmt = $conn->query("SELECT COUNT(*) as total FROM mascotas");
                $estadisticas['total_mascotas'] = $stmt->fetch()['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'disponible'");
                $estadisticas['disponibles'] = $stmt->fetch()['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'adoptado'");
                $estadisticas['adoptadas'] = $stmt->fetch()['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM mascotas WHERE estado = 'en riesgo'");
                $estadisticas['en_riesgo'] = $stmt->fetch()['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'");
                $estadisticas['clientes'] = $stmt->fetch()['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM seguimientos WHERE estado_validacion = 'pendiente'");
                $estadisticas['seguimientos_pendientes'] = $stmt->fetch()['total'];
            } elseif ($_SESSION['usuario_rol'] === 'refugio') {
                // Estadísticas para Refugio
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM mascotas WHERE id_refugio = ?");
                $stmt->execute([$_SESSION['usuario_id']]);
                $estadisticas['mis_mascotas'] = $stmt->fetch()['total'];
                
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM mascotas WHERE id_refugio = ? AND estado = 'disponible'");
                $stmt->execute([$_SESSION['usuario_id']]);
                $estadisticas['disponibles'] = $stmt->fetch()['total'];
                
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM mascotas WHERE id_refugio = ? AND estado = 'adoptado'");
                $stmt->execute([$_SESSION['usuario_id']]);
                $estadisticas['adoptadas'] = $stmt->fetch()['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM seguimientos WHERE estado_validacion = 'pendiente'");
                $estadisticas['seguimientos_pendientes'] = $stmt->fetch()['total'];
            } elseif ($_SESSION['usuario_rol'] === 'cliente') {
                // Estadísticas para Cliente
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM mascotas WHERE id_adoptante = ?");
                $stmt->execute([$_SESSION['usuario_id']]);
                $estadisticas['mis_adopciones'] = $stmt->fetch()['total'];
                
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM seguimientos WHERE id_cliente = ?");
                $stmt->execute([$_SESSION['usuario_id']]);
                $estadisticas['mis_reportes'] = $stmt->fetch()['total'];
                
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM seguimientos WHERE id_cliente = ? AND estado_validacion = 'pendiente'");
                $stmt->execute([$_SESSION['usuario_id']]);
                $estadisticas['reportes_pendientes'] = $stmt->fetch()['total'];
            }
            ?>
            
            <!-- Tarjetas de Estadísticas -->
            <div class="row mb-4">
                <?php if ($_SESSION['usuario_rol'] === 'administrador'): ?>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card success">
                            <span class="material-icons stat-icon">pets</span>
                            <div class="stat-value"><?php echo $estadisticas['total_mascotas']; ?></div>
                            <div class="stat-label">Total Mascotas</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card info">
                            <span class="material-icons stat-icon">check_circle</span>
                            <div class="stat-value"><?php echo $estadisticas['disponibles']; ?></div>
                            <div class="stat-label">Disponibles</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card success">
                            <span class="material-icons stat-icon">favorite</span>
                            <div class="stat-value"><?php echo $estadisticas['adoptadas']; ?></div>
                            <div class="stat-label">Adoptadas</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card danger">
                            <span class="material-icons stat-icon">warning</span>
                            <div class="stat-value"><?php echo $estadisticas['en_riesgo']; ?></div>
                            <div class="stat-label">En Riesgo</div>
                        </div>
                    </div>
                <?php elseif ($_SESSION['usuario_rol'] === 'refugio'): ?>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card success">
                            <span class="material-icons stat-icon">pets</span>
                            <div class="stat-value"><?php echo $estadisticas['mis_mascotas']; ?></div>
                            <div class="stat-label">Mis Mascotas</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card info">
                            <span class="material-icons stat-icon">check_circle</span>
                            <div class="stat-value"><?php echo $estadisticas['disponibles']; ?></div>
                            <div class="stat-label">Disponibles</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card success">
                            <span class="material-icons stat-icon">favorite</span>
                            <div class="stat-value"><?php echo $estadisticas['adoptadas']; ?></div>
                            <div class="stat-label">Adoptadas</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="stat-card warning">
                            <span class="material-icons stat-icon">pending</span>
                            <div class="stat-value"><?php echo $estadisticas['seguimientos_pendientes']; ?></div>
                            <div class="stat-label">Seguimientos Pendientes</div>
                        </div>
                    </div>
                <?php elseif ($_SESSION['usuario_rol'] === 'cliente'): ?>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card success">
                            <span class="material-icons stat-icon">favorite</span>
                            <div class="stat-value"><?php echo $estadisticas['mis_adopciones']; ?></div>
                            <div class="stat-label">Mis Adopciones</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card info">
                            <span class="material-icons stat-icon">fact_check</span>
                            <div class="stat-value"><?php echo $estadisticas['mis_reportes']; ?></div>
                            <div class="stat-label">Mis Reportes</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="stat-card warning">
                            <span class="material-icons stat-icon">pending</span>
                            <div class="stat-value"><?php echo $estadisticas['reportes_pendientes']; ?></div>
                            <div class="stat-label">Reportes Pendientes</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Contenido según el rol -->
            <?php if ($_SESSION['usuario_rol'] === 'administrador'): ?>
                <!-- Vista de Administrador -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <span class="material-icons">pets</span>
                                Mascotas Recientes
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $conn->query("
                                    SELECT m.*, u.nombre as refugio_nombre 
                                    FROM mascotas m
                                    LEFT JOIN usuarios u ON m.id_refugio = u.id_usuario
                                    ORDER BY m.fecha_registro DESC
                                    LIMIT 5
                                ");
                                $mascotas_recientes = $stmt->fetchAll();
                                ?>
                                
                                <?php if (count($mascotas_recientes) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Especie</th>
                                                    <th>Estado</th>
                                                    <th>Refugio</th>
                                                    <th>Fecha Registro</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mascotas_recientes as $mascota): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars($mascota['especie']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $mascota['estado']; ?>">
                                                                <?php echo ucfirst($mascota['estado']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($mascota['refugio_nombre'] ?? 'N/A'); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($mascota['fecha_registro'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">No hay mascotas registradas aún.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <span class="material-icons">notifications</span>
                                Notificaciones
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if ($estadisticas['seguimientos_pendientes'] > 0): ?>
                                        <a href="modules/seguimiento/validar_reporte.php" class="list-group-item list-group-item-action">
                                            <span class="material-icons text-warning">pending_actions</span>
                                            <?php echo $estadisticas['seguimientos_pendientes']; ?> seguimiento(s) pendiente(s)
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($estadisticas['en_riesgo'] > 0): ?>
                                        <a href="modules/mascotas/gestion_mascotas.php" class="list-group-item list-group-item-action">
                                            <span class="material-icons text-danger">warning</span>
                                            <?php echo $estadisticas['en_riesgo']; ?> mascota(s) en riesgo
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($estadisticas['seguimientos_pendientes'] == 0 && $estadisticas['en_riesgo'] == 0): ?>
                                        <div class="text-center text-muted py-3">
                                            <span class="material-icons" style="font-size: 3rem; opacity: 0.3;">check_circle</span>
                                            <p>No hay notificaciones pendientes</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($_SESSION['usuario_rol'] === 'refugio'): ?>
                <!-- Vista de Refugio -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <span class="material-icons">pets</span>
                                Mis Mascotas Registradas
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT m.*, u.nombre as adoptante_nombre
                                    FROM mascotas m
                                    LEFT JOIN usuarios u ON m.id_adoptante = u.id_usuario
                                    WHERE m.id_refugio = ?
                                    ORDER BY m.fecha_registro DESC
                                    LIMIT 10
                                ");
                                $stmt->execute([$_SESSION['usuario_id']]);
                                $mis_mascotas = $stmt->fetchAll();
                                ?>
                                
                                <?php if (count($mis_mascotas) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Especie/Raza</th>
                                                    <th>Estado</th>
                                                    <th>Adoptante</th>
                                                    <th>Fecha Registro</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mis_mascotas as $mascota): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars($mascota['especie'] . ' / ' . ($mascota['raza'] ?? 'N/A')); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $mascota['estado']; ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $mascota['estado'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($mascota['adoptante_nombre'] ?? '-'); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($mascota['fecha_registro'])); ?></td>
                                                        <td>
                                                            <a href="modules/mascotas/gestion_mascotas.php?id=<?php echo $mascota['id_mascota']; ?>" 
                                                               class="btn btn-sm btn-primary">
                                                                <span class="material-icons">visibility</span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3">No tienes mascotas registradas aún.</p>
                                    <div class="text-center">
                                        <a href="modules/mascotas/gestion_mascotas.php" class="btn btn-success">
                                            <span class="material-icons">add</span> Registrar Mascota
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($_SESSION['usuario_rol'] === 'cliente'): ?>
                <!-- Vista de Cliente -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <span class="material-icons">favorite</span>
                                Mis Mascotas Adoptadas
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT m.*, 
                                           (SELECT COUNT(*) FROM seguimientos WHERE id_mascota = m.id_mascota) as total_seguimientos
                                    FROM mascotas m
                                    WHERE m.id_adoptante = ?
                                    ORDER BY m.fecha_adopcion DESC
                                ");
                                $stmt->execute([$_SESSION['usuario_id']]);
                                $mis_adopciones = $stmt->fetchAll();
                                ?>
                                
                                <?php if (count($mis_adopciones) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($mis_adopciones as $mascota): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            <span class="material-icons text-primary-custom">pets</span>
                                                            <?php echo htmlspecialchars($mascota['nombre']); ?>
                                                        </h5>
                                                        <p class="card-text">
                                                            <strong>Especie:</strong> <?php echo htmlspecialchars($mascota['especie']); ?><br>
                                                            <strong>Raza:</strong> <?php echo htmlspecialchars($mascota['raza'] ?? 'N/A'); ?><br>
                                                            <strong>Fecha de Adopción:</strong> <?php echo date('d/m/Y', strtotime($mascota['fecha_adopcion'])); ?><br>
                                                            <strong>Seguimientos:</strong> <?php echo $mascota['total_seguimientos']; ?>
                                                        </p>
                                                        <a href="modules/seguimiento/subir_evidencia.php?mascota=<?php echo $mascota['id_mascota']; ?>" 
                                                           class="btn btn-success btn-sm">
                                                            <span class="material-icons">upload</span> Subir Evidencia
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <span class="material-icons text-muted" style="font-size: 5rem; opacity: 0.3;">pets</span>
                                        <p class="text-muted">No tienes mascotas adoptadas aún.</p>
                                        <p class="text-muted small">Contacta con un refugio para iniciar el proceso de adopción.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
