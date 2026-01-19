<?php
/**
 * Configuración de Conexión a Base de Datos
 * HuellasResponsables - Sistema de Gestión de Adopciones
 * 
 * NOTA DE SEGURIDAD: En producción, usar variables de entorno
 * o un archivo de configuración fuera del directorio web
 */

// Configuración de la base de datos
// TODO: Mover a variables de entorno en producción
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'huellas_responsables');
define('DB_CHARSET', 'utf8mb4');

// Crear conexión
function conectarDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Conexión global
$conn = conectarDB();
?>
