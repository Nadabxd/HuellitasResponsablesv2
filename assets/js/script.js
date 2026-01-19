/**
 * JavaScript Personalizado - HuellasResponsables
 * Sistema de Gestión de Adopciones
 */

// Toggle Sidebar en móvil
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}

// Confirmación de eliminación
function confirmarEliminacion(mensaje = '¿Está seguro de eliminar este registro?') {
    return confirm(mensaje);
}

// Validación de formularios
document.addEventListener('DOMContentLoaded', function() {
    // Validar formularios con clase 'needs-validation'
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Preview de imágenes antes de subir
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewId = input.dataset.preview;
                    if (previewId) {
                        const preview = document.getElementById(previewId);
                        if (preview) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
});

// Función para mostrar notificaciones toast
function mostrarNotificacion(mensaje, tipo = 'success') {
    // Crear elemento de notificación
    const toast = document.createElement('div');
    toast.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Filtrar tabla en tiempo real
function filtrarTabla(inputId, tablaId) {
    const input = document.getElementById(inputId);
    const tabla = document.getElementById(tablaId);
    
    if (input && tabla) {
        input.addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            const filas = tabla.getElementsByTagName('tr');
            
            for (let i = 1; i < filas.length; i++) {
                const fila = filas[i];
                const texto = fila.textContent.toLowerCase();
                
                if (texto.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });
    }
}

// Formatear fecha
function formatearFecha(fecha) {
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

// Calcular edad de mascota
function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

// Validar tamaño de archivo
function validarTamañoArchivo(input, maxSizeMB = 5) {
    const file = input.files[0];
    if (file) {
        const sizeMB = file.size / (1024 * 1024);
        if (sizeMB > maxSizeMB) {
            alert(`El archivo es demasiado grande. Tamaño máximo: ${maxSizeMB}MB`);
            input.value = '';
            return false;
        }
    }
    return true;
}

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
