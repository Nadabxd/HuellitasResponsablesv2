<?php
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - HuellasResponsables</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo $base_url ?? ''; ?>assets/css/style.css" rel="stylesheet">
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navbar Superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base_url ?? ''; ?>index.php">
                <span class="material-icons">pets</span>
                HuellasResponsables
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons">account_circle</span>
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            <small class="badge bg-success"><?php echo ucfirst($_SESSION['usuario_rol']); ?></small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#"><span class="material-icons">person</span> Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><span class="material-icons">settings</span> Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base_url ?? ''; ?>auth/logout.php">
                                <span class="material-icons">logout</span> Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
