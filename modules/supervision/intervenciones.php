<?php
/**
 * Gestión de Intervenciones y Visitas Domiciliarias
 * Para Administradores y Refugios
 */

session_start();
require_once '../../config/conexion.php';

// Verificar acceso
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$page_title = 'Intervenciones';
$current_page = 'intervenciones';
$base_url = '../../';

$mensaje = '';
$tipo_mensaje = '';
$accion = $_GET['accion'] ?? '';
$id_intervencion = $_GET['id'] ?? null;

// Crear intervención
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {
    $id_mascota = $_POST['id_mascota'] ?? null;
    $id_adoptante = $_POST['id_adoptante'] ?? null;
    $tipo = $_POST['tipo'] ?? '';
    $fecha_intervencion = $_POST['fecha_intervencion'] ?? '';
    $motivo = $_POST['motivo'] ?? '';
    $responsable = $_POST['responsable'] ?? '';
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO intervenciones (id_mascota, id_adoptante, tipo, fecha_intervencion, motivo, responsable, estado)
            VALUES (?, ?, ?, ?, ?, ?, 'programada')
        ");
        $stmt->execute([$id_mascota, $id_adoptante, $tipo, $fecha_intervencion, $motivo, $responsable]);
        $mensaje = "Intervención programada exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Actualizar intervención
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'actualizar' && $id_intervencion) {
    $estado = $_POST['estado'] ?? '';
    $resultado = $_POST['resultado'] ?? '';
    
    try {
        $stmt = $conn->prepare("
            UPDATE intervenciones 
            SET estado = ?, resultado = ?
            WHERE id_intervencion = ?
        ");
        $stmt->execute([$estado, $resultado, $id_intervencion]);
        $mensaje = "Intervención actualizada exitosamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener mascotas adoptadas para el formulario
$stmt = $conn->query("
    SELECT m.id_mascota, m.nombre, m.especie, u.id_usuario as id_adoptante, u.nombre as adoptante_nombre
    FROM mascotas m
    JOIN usuarios u ON m.id_adoptante = u.id_usuario
    WHERE m.estado = 'adoptado'
    ORDER BY m.nombre
");
$mascotas_adoptadas = $stmt->fetchAll();

// Obtener intervenciones
$stmt = $conn->query("
    SELECT i.*, m.nombre as mascota_nombre, u.nombre as adoptante_nombre
    FROM intervenciones i
    JOIN mascotas m ON i.id_mascota = m.id_mascota
    JOIN usuarios u ON i.id_adoptante = u.id_usuario
    ORDER BY i.fecha_intervencion DESC
");
$intervenciones = $stmt->fetchAll();

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
                        <span class="material-icons" style="font-size: 2rem;">home</span>
                        Intervenciones y Visitas Domiciliarias
                    </h2>
                    <p class="text-muted">Programar y registrar visitas e intervenciones</p>
                </div>
            </div>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Formulario para nueva intervención -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">add</span>
                            Programar Nueva Intervención
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?accion=crear" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="id_mascota" class="form-label">
                                            <span class="material-icons">pets</span> Mascota / Adoptante
                                        </label>
                                        <select class="form-select" id="id_mascota" name="id_mascota" required 
                                                onchange="setAdoptante(this)">
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($mascotas_adoptadas as $m): ?>
                                                <option value="<?php echo $m['id_mascota']; ?>" 
                                                        data-adoptante="<?php echo $m['id_adoptante']; ?>">
                                                    <?php echo htmlspecialchars($m['nombre'] . ' - ' . $m['adoptante_nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" id="id_adoptante" name="id_adoptante">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="tipo" class="form-label">
                                            <span class="material-icons">category</span> Tipo de Intervención
                                        </label>
                                        <select class="form-select" id="tipo" name="tipo" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="visita_domiciliaria">Visita Domiciliaria</option>
                                            <option value="llamada">Llamada Telefónica</option>
                                            <option value="advertencia">Advertencia</option>
                                            <option value="rescate">Rescate de Mascota</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_intervencion" class="form-label">
                                            <span class="material-icons">event</span> Fecha Programada
                                        </label>
                                        <input type="date" class="form-control" id="fecha_intervencion" 
                                               name="fecha_intervencion" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="responsable" class="form-label">
                                            <span class="material-icons">person</span> Responsable
                                        </label>
                                        <input type="text" class="form-control" id="responsable" name="responsable" 
                                               value="<?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="motivo" class="form-label">
                                        <span class="material-icons">description</span> Motivo
                                    </label>
                                    <textarea class="form-control" id="motivo" name="motivo" rows="3" 
                                              required placeholder="Describe el motivo de esta intervención..."></textarea>
                                </div>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn btn-success">
                                        <span class="material-icons">save</span> Programar Intervención
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de intervenciones -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span class="material-icons">list</span>
                            Registro de Intervenciones
                        </div>
                        <div class="card-body">
                            <?php if (count($intervenciones) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>Mascota</th>
                                                <th>Adoptante</th>
                                                <th>Responsable</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($intervenciones as $int): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($int['fecha_intervencion'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo ucfirst(str_replace('_', ' ', $int['tipo'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($int['mascota_nombre']); ?></td>
                                                    <td><?php echo htmlspecialchars($int['adoptante_nombre']); ?></td>
                                                    <td><?php echo htmlspecialchars($int['responsable']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $int['estado'] === 'realizada' ? 'success' : 
                                                                 ($int['estado'] === 'programada' ? 'warning' : 'secondary'); 
                                                        ?>">
                                                            <?php echo ucfirst($int['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detalleModal<?php echo $int['id_intervencion']; ?>">
                                                            <span class="material-icons">visibility</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Modal de detalles -->
                                                <div class="modal fade" id="detalleModal<?php echo $int['id_intervencion']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Detalle de Intervención</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Tipo:</strong> <?php echo ucfirst(str_replace('_', ' ', $int['tipo'])); ?></p>
                                                                <p><strong>Mascota:</strong> <?php echo htmlspecialchars($int['mascota_nombre']); ?></p>
                                                                <p><strong>Adoptante:</strong> <?php echo htmlspecialchars($int['adoptante_nombre']); ?></p>
                                                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($int['fecha_intervencion'])); ?></p>
                                                                <p><strong>Responsable:</strong> <?php echo htmlspecialchars($int['responsable']); ?></p>
                                                                <p><strong>Motivo:</strong></p>
                                                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($int['motivo'])); ?></p>
                                                                
                                                                <?php if ($int['resultado']): ?>
                                                                    <p><strong>Resultado:</strong></p>
                                                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($int['resultado'])); ?></p>
                                                                <?php endif; ?>
                                                                
                                                                <form method="POST" action="?accion=actualizar&id=<?php echo $int['id_intervencion']; ?>" class="mt-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Estado</label>
                                                                        <select class="form-select" name="estado">
                                                                            <option value="programada" <?php echo $int['estado'] === 'programada' ? 'selected' : ''; ?>>Programada</option>
                                                                            <option value="realizada" <?php echo $int['estado'] === 'realizada' ? 'selected' : ''; ?>>Realizada</option>
                                                                            <option value="cancelada" <?php echo $int['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Resultado</label>
                                                                        <textarea class="form-control" name="resultado" rows="3"><?php echo htmlspecialchars($int['resultado'] ?? ''); ?></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-success w-100">
                                                                        <span class="material-icons">save</span> Actualizar
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No hay intervenciones registradas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function setAdoptante(select) {
    if (select.selectedIndex >= 0) {
        const selectedOption = select.options[select.selectedIndex];
        const adoptanteId = selectedOption.getAttribute('data-adoptante');
        if (adoptanteId) {
            document.getElementById('id_adoptante').value = adoptanteId;
        }
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
