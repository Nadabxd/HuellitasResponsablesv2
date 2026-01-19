<!-- Sidebar Lateral -->
<div class="sidebar bg-white shadow-sm" id="sidebar">
    <div class="sidebar-header">
        <h5 class="text-primary-custom mb-0">
            <span class="material-icons">menu</span> Menú Principal
        </h5>
    </div>
    
    <ul class="sidebar-menu">
        <!-- Dashboard - Visible para todos -->
        <li>
            <a href="<?php echo $base_url ?? ''; ?>index.php" class="<?php echo ($current_page ?? '') == 'dashboard' ? 'active' : ''; ?>">
                <span class="material-icons">dashboard</span>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Módulo de Usuarios - Solo Administrador -->
        <?php if ($_SESSION['usuario_rol'] === 'administrador'): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/usuarios/crud_usuarios.php" class="<?php echo ($current_page ?? '') == 'usuarios' ? 'active' : ''; ?>">
                <span class="material-icons">people</span>
                <span>Gestión de Usuarios</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Módulo de Mascotas - Administrador y Refugio -->
        <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/mascotas/gestion_mascotas.php" class="<?php echo ($current_page ?? '') == 'mascotas' ? 'active' : ''; ?>">
                <span class="material-icons">pets</span>
                <span>Gestión de Mascotas</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Módulo de Seguimiento -->
        <?php if ($_SESSION['usuario_rol'] === 'cliente'): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/seguimiento/subir_evidencia.php" class="<?php echo ($current_page ?? '') == 'seguimiento' ? 'active' : ''; ?>">
                <span class="material-icons">upload_file</span>
                <span>Subir Evidencias</span>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/seguimiento/validar_reporte.php" class="<?php echo ($current_page ?? '') == 'validar_seguimiento' ? 'active' : ''; ?>">
                <span class="material-icons">fact_check</span>
                <span>Validar Seguimientos</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Módulo de Supervisión - Administrador y Refugio -->
        <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'refugio'])): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/supervision/historial_adoptante.php" class="<?php echo ($current_page ?? '') == 'historial' ? 'active' : ''; ?>">
                <span class="material-icons">history</span>
                <span>Historial de Adoptantes</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/supervision/intervenciones.php" class="<?php echo ($current_page ?? '') == 'intervenciones' ? 'active' : ''; ?>">
                <span class="material-icons">home</span>
                <span>Intervenciones</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Módulo de Reportes - Solo Administrador -->
        <?php if ($_SESSION['usuario_rol'] === 'administrador'): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/reportes/dashboard_estadistico.php" class="<?php echo ($current_page ?? '') == 'reportes' ? 'active' : ''; ?>">
                <span class="material-icons">analytics</span>
                <span>Reportes y Estadísticas</span>
            </a>
        </li>
        <?php endif; ?>
        
        <!-- Mis Adopciones - Solo Cliente -->
        <?php if ($_SESSION['usuario_rol'] === 'cliente'): ?>
        <li>
            <a href="<?php echo $base_url ?? ''; ?>modules/seguimiento/mis_adopciones.php" class="<?php echo ($current_page ?? '') == 'mis_adopciones' ? 'active' : ''; ?>">
                <span class="material-icons">favorite</span>
                <span>Mis Adopciones</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
