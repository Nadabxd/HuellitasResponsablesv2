<?php
/**
 * Dashboard Estadístico y Reportes
 * Solo para Administradores
 */

session_start();
require_once '../../config/conexion.php';

// Verificar que es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'administrador') {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Reportes y Estadísticas';
$current_page = 'reportes';
$base_url = '../../';

// Obtener estadísticas generales
$stats = [];

// Mascotas por estado
$stmt = $conn->query("
    SELECT estado, COUNT(*) as total 
    FROM mascotas 
    GROUP BY estado
");
while ($row = $stmt->fetch()) {
    $stats['mascotas_' . str_replace(' ', '_', $row['estado'])] = $row['total'];
}

// Total de mascotas
$stmt = $conn->query("SELECT COUNT(*) as total FROM mascotas");
$stats['total_mascotas'] = $stmt->fetch()['total'];

// Adopciones por mes (últimos 6 meses)
$stmt = $conn->query("
    SELECT DATE_FORMAT(fecha_adopcion, '%Y-%m') as mes, COUNT(*) as total
    FROM mascotas
    WHERE fecha_adopcion IS NOT NULL
    GROUP BY mes
    ORDER BY mes DESC
    LIMIT 6
");
$adopciones_mes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Seguimientos por estado
$stmt = $conn->query("
    SELECT estado_validacion, COUNT(*) as total
    FROM seguimientos
    GROUP BY estado_validacion
");
$seguimientos_estado = [];
while ($row = $stmt->fetch()) {
    $seguimientos_estado[$row['estado_validacion']] = $row['total'];
}

// Top 5 adoptantes con más adopciones
$stmt = $conn->query("
    SELECT u.nombre, COUNT(m.id_mascota) as total_adopciones
    FROM usuarios u
    LEFT JOIN mascotas m ON u.id_usuario = m.id_adoptante
    WHERE u.rol = 'cliente' AND m.id_mascota IS NOT NULL
    GROUP BY u.id_usuario
    ORDER BY total_adopciones DESC
    LIMIT 5
");
$top_adoptantes = $stmt->fetchAll();

// Intervenciones por tipo
$stmt = $conn->query("
    SELECT tipo, COUNT(*) as total
    FROM intervenciones
    GROUP BY tipo
");
$intervenciones_tipo = [];
while ($row = $stmt->fetch()) {
    $intervenciones_tipo[$row['tipo']] = $row['total'];
}

// Promedio de calificación de bienestar
$stmt = $conn->query("
    SELECT AVG(calificacion_bienestar) as promedio
    FROM seguimientos
    WHERE estado_validacion = 'aprobado'
");
$promedio_bienestar = round($stmt->fetch()['promedio'] ?? 0, 2);

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
                        <span class="material-icons" style="font-size: 2rem;">analytics</span>
                        Reportes y Estadísticas
                    </h2>
                    <p class="text-muted">Dashboard con métricas del sistema</p>
                </div>
            </div>
            
            <!-- Estadísticas principales -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <span class="material-icons stat-icon">pets</span>
                        <div class="stat-value"><?php echo $stats['total_mascotas']; ?></div>
                        <div class="stat-label">Total Mascotas</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card info">
                        <span class="material-icons stat-icon">check_circle</span>
                        <div class="stat-value"><?php echo $stats['mascotas_disponible'] ?? 0; ?></div>
                        <div class="stat-label">Disponibles</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <span class="material-icons stat-icon">favorite</span>
                        <div class="stat-value"><?php echo $stats['mascotas_adoptado'] ?? 0; ?></div>
                        <div class="stat-label">Adoptadas</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stat-card danger">
                        <span class="material-icons stat-icon">warning</span>
                        <div class="stat-value"><?php echo $stats['mascotas_en_riesgo'] ?? 0; ?></div>
                        <div class="stat-label">En Riesgo</div>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="row mb-4">
                <!-- Gráfico de Mascotas por Estado -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">pie_chart</span>
                            Distribución de Mascotas por Estado
                        </div>
                        <div class="card-body">
                            <canvas id="chartMascotasEstado"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de Adopciones por Mes -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">show_chart</span>
                            Adopciones por Mes (Últimos 6 Meses)
                        </div>
                        <div class="card-body">
                            <canvas id="chartAdopcionesMes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <!-- Gráfico de Seguimientos -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">donut_large</span>
                            Seguimientos por Estado de Validación
                        </div>
                        <div class="card-body">
                            <canvas id="chartSeguimientos"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de Intervenciones -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">bar_chart</span>
                            Intervenciones por Tipo
                        </div>
                        <div class="card-body">
                            <canvas id="chartIntervenciones"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Adoptantes -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">emoji_events</span>
                            Top 5 Adoptantes
                        </div>
                        <div class="card-body">
                            <?php if (count($top_adoptantes) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($top_adoptantes as $index => $adoptante): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong>#<?php echo $index + 1; ?></strong>
                                                <?php echo htmlspecialchars($adoptante['nombre']); ?>
                                            </span>
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo $adoptante['total_adopciones']; ?> adopciones
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">No hay datos disponibles</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Indicadores adicionales -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">assessment</span>
                            Indicadores de Calidad
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6>Promedio de Bienestar de Mascotas Adoptadas</h6>
                                <div class="d-flex align-items-center">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <span class="material-icons" style="font-size: 2rem; color: <?php echo $i < $promedio_bienestar ? '#F39C12' : '#ddd'; ?>">star</span>
                                    <?php endfor; ?>
                                    <h3 class="ms-3 mb-0"><?php echo $promedio_bienestar; ?>/5</h3>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Tasa de Éxito en Adopciones</h6>
                                <?php 
                                $total_adoptados = $stats['mascotas_adoptado'] ?? 0;
                                $en_riesgo = $stats['mascotas_en_riesgo'] ?? 0;
                                $tasa_exito = $total_adoptados > 0 ? (($total_adoptados - $en_riesgo) / $total_adoptados) * 100 : 0;
                                ?>
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $tasa_exito; ?>%;" 
                                         aria-valuenow="<?php echo $tasa_exito; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo round($tasa_exito, 1); ?>%
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h6>Total de Seguimientos Realizados</h6>
                                <h3 class="text-primary-custom">
                                    <?php 
                                    $total_seguimientos = array_sum($seguimientos_estado);
                                    echo $total_seguimientos; 
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// Datos para los gráficos
const mascotasData = {
    labels: ['Disponibles', 'Adoptadas', 'En Riesgo'],
    datasets: [{
        data: [
            <?php echo $stats['mascotas_disponible'] ?? 0; ?>,
            <?php echo $stats['mascotas_adoptado'] ?? 0; ?>,
            <?php echo $stats['mascotas_en_riesgo'] ?? 0; ?>
        ],
        backgroundColor: ['#3498db', '#27AE60', '#E74C3C']
    }]
};

const adopcionesData = {
    labels: <?php echo json_encode(array_reverse(array_keys($adopciones_mes))); ?>,
    datasets: [{
        label: 'Adopciones',
        data: <?php echo json_encode(array_reverse(array_values($adopciones_mes))); ?>,
        borderColor: '#27AE60',
        backgroundColor: 'rgba(39, 174, 96, 0.1)',
        tension: 0.4,
        fill: true
    }]
};

const seguimientosData = {
    labels: ['Pendientes', 'Aprobados', 'Rechazados'],
    datasets: [{
        data: [
            <?php echo $seguimientos_estado['pendiente'] ?? 0; ?>,
            <?php echo $seguimientos_estado['aprobado'] ?? 0; ?>,
            <?php echo $seguimientos_estado['rechazado'] ?? 0; ?>
        ],
        backgroundColor: ['#F39C12', '#27AE60', '#E74C3C']
    }]
};

const intervencionesData = {
    labels: <?php echo json_encode(array_map(function($k) { return ucfirst(str_replace('_', ' ', $k)); }, array_keys($intervenciones_tipo))); ?>,
    datasets: [{
        label: 'Intervenciones',
        data: <?php echo json_encode(array_values($intervenciones_tipo)); ?>,
        backgroundColor: '#2C3E50'
    }]
};

// Crear gráficos
new Chart(document.getElementById('chartMascotasEstado'), {
    type: 'doughnut',
    data: mascotasData,
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

new Chart(document.getElementById('chartAdopcionesMes'), {
    type: 'line',
    data: adopcionesData,
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('chartSeguimientos'), {
    type: 'pie',
    data: seguimientosData,
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

new Chart(document.getElementById('chartIntervenciones'), {
    type: 'bar',
    data: intervencionesData,
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
