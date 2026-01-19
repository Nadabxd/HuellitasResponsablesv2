# HuellasResponsables - Sistema de Gestión de Adopciones

## 🐾 Descripción
Sistema web completo para la gestión de adopciones responsables de mascotas. Desarrollado con PHP 8, MySQL (MariaDB), Bootstrap 5 y JavaScript.

## 📋 Características

### Módulos Principales:
1. **Gestión de Usuarios** - Login seguro con sesiones y CRUD de perfiles según roles
2. **Control de Mascotas** - Registro detallado con estados (Disponible, Adoptado, En Riesgo)
3. **Seguimiento de Adopciones** - Sistema de evidencias con validación del refugio
4. **Supervisión de Adoptantes** - Historial y calificación de confianza
5. **Intervenciones** - Registro de visitas domiciliarias y seguimientos
6. **Reportes y Estadísticas** - Dashboard visual con gráficos

### Roles de Usuario:
- **Administrador**: Acceso completo al sistema
- **Refugio**: Gestión de mascotas y validación de seguimientos
- **Cliente (Adoptante)**: Subir evidencias y ver sus adopciones

## 🛠️ Requisitos
- XAMPP (Apache + MySQL/MariaDB)
- PHP 8.0 o superior
- Navegador web moderno

## 📦 Instalación

### 1. Clonar o descargar el proyecto
Coloca los archivos en la carpeta `htdocs` de XAMPP:
```
C:\xampp\htdocs\HuellasResponsables\
```

### 2. Configurar la Base de Datos
1. Inicia XAMPP (Apache y MySQL)
2. Abre phpMyAdmin: `http://localhost/phpmyadmin`
3. Importa el archivo `database.sql` para crear la base de datos
   - O copia y ejecuta el contenido del archivo en la pestaña SQL

### 3. Configurar la Conexión
Edita el archivo `config/conexion.php` si tus credenciales son diferentes:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'huellas_responsables');
```

### 4. Configurar Permisos de Carpeta
Asegúrate de que la carpeta de uploads tenga permisos de escritura:
```
assets/img/uploads/
```

### 5. Acceder al Sistema
Abre tu navegador y visita:
```
http://localhost/HuellasResponsables/auth/login.php
```

## 🔐 Credenciales de Prueba

### Administrador
- Email: `admin@huellas.com`
- Contraseña: `admin123`

### Refugio
- Email: `refugio@huellas.com`
- Contraseña: `admin123`

### Cliente (Adoptante)
- Email: `juan@example.com`
- Contraseña: `admin123`

## 📁 Estructura del Proyecto
```
HuellasResponsables/
├── config/                 # Configuración de BD
├── assets/                 # Recursos estáticos
│   ├── css/               # Estilos personalizados
│   ├── js/                # JavaScript
│   └── img/uploads/       # Imágenes subidas
├── auth/                  # Sistema de autenticación
├── includes/              # Componentes reutilizables
├── modules/               # Módulos del sistema
│   ├── usuarios/         # Gestión de usuarios
│   ├── mascotas/         # Gestión de mascotas
│   ├── seguimiento/      # Seguimientos y evidencias
│   ├── supervision/      # Supervisión de adoptantes
│   └── reportes/         # Dashboard estadístico
├── database.sql          # Script SQL
└── index.php             # Dashboard principal
```

## 🎨 Tecnologías Utilizadas
- **Backend**: PHP 8 (PDO para BD)
- **Base de Datos**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: Vanilla JS + Chart.js
- **Iconos**: Google Material Icons

## 🔒 Seguridad
- Contraseñas hasheadas con `password_hash()`
- Sesiones seguras con validación de roles
- Prepared statements para prevenir SQL Injection
- Sanitización de inputs

## 📊 Funcionalidades Destacadas
- Dashboard dinámico según rol de usuario
- Sistema de calificación de bienestar de mascotas (1-5 estrellas)
- Validación de evidencias por el refugio
- Registro de intervenciones y visitas domiciliarias
- Gráficos estadísticos interactivos
- Diseño responsive y moderno

## 🤝 Contribuir
Este es un proyecto educativo y de demostración. Siéntete libre de mejorarlo.

## 📄 Licencia
Proyecto de código abierto para fines educativos.

---
Desarrollado con ❤️ para el bienestar animal
