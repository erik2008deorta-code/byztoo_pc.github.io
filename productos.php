<?php
include("includes/header.php");
include("includes/conexion.php"); // Aseguramos la conexión a la base de datos

$productos = [];
$termino_busqueda = "";

// 1. Captura de datos: Detectar el término de búsqueda
if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
    $termino_busqueda = trim($_GET['buscar']);
    
    // 2. Lógica de filtrado: Filtrar productos desde SQL usando LIKE
    // Preparamos la consulta para evitar inyecciones SQL
    $sql = "SELECT * FROM productos WHERE nombre LIKE ? OR descripcion LIKE ?";
    $stmt = $conn->prepare($sql);
    
    $parametro_like = "%" . $termino_busqueda . "%";
    $stmt->bind_param("ss", $parametro_like, $parametro_like);
    
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            // Decodificar especificaciones si existen
            if (!empty($fila['especificaciones'])) {
                $fila['especificaciones'] = json_decode($fila['especificaciones'], true);
            }
            $productos[] = $fila;
        }
    }
    $stmt->close();
} else {
    // 3. Estado inicial: Si no hay búsqueda, mostramos todos los productos
    $sql = "SELECT * FROM productos";
    $resultado = $conn->query($sql);
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            // Decodificar especificaciones si existen
            if (!empty($fila['especificaciones'])) {
                $fila['especificaciones'] = json_decode($fila['especificaciones'], true);
            }
            $productos[] = $fila;
        }
    }
}
?>

<main class="container my-5">
    <h1 class="text-center mb-5">Nuestros Productos</h1>

    <!-- Contenedor de la grilla -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">

        <?php
        // 6. Manejo de resultados: Mensaje si no hay coincidencias
        if (empty($productos)):
        ?>
            <div class="col-12 text-center mt-5">
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-search me-2"></i> No se encontraron productos relacionados con "<strong><?php echo htmlspecialchars($termino_busqueda); ?></strong>".
                </div>
            </div>
        <?php
        else:
            // ==============================================================================
            // BUCLE FOREACH: Recorre cada producto del arreglo $productos
            // ==============================================================================
        foreach ($productos as $prod):
            $modalId = "modalProducto" . $prod['id']; // Genera un ID único para cada modal (ej: modalProducto1)
        ?>

            <!-- Tarjeta del Producto -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <!-- Imagen clickable. 'data-bs-toggle' y 'data-bs-target' abren el modal -->
                    <a href="#" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                        <img src="<?php echo $prod['imagen']; ?>" class="card-img-top p-3" style="object-fit: contain; height: 200px; cursor: pointer;" alt="<?php echo $prod['nombre']; ?>" title="Hacé clic para agrandar">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $prod['nombre']; ?></h5>
                        <p class="card-text text-muted small"><?php echo $prod['descripcion']; ?></p>

                        <!-- Precio: muestra oferta si existe, precio normal si no -->
                        <div class="precio-bloque">
                            <?php if (isset($prod['oferta'])): ?>
                                <span class="old-price me-2">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></span>
                                <span class="new-price">$<?php echo number_format($prod['oferta'], 0, ',', '.'); ?></span>
                            <?php else: ?>
                                <h4 class="text-primary mb-0">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></h4>
                            <?php endif; ?>
                        </div>

                        <!-- Botón para abrir el detalle (Modal) -->
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                            <i class="bi bi-eye"></i> Ver detalle
                        </button>
                    </div>
                </div>
            </div>

            <!-- ==============================================================================
             MODAL DEL PRODUCTO (Se oculta por defecto, se abre al hacer clic)
             'modal-lg' hace que el modal sea grande (rectangular)
             ============================================================================== -->
            <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-labelledby="Label<?php echo $modalId; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header border-0 pb-0">
                            <!-- Botón X para cerrar -->
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body pt-0">
                            <div class="row align-items-center">
                                <!-- Lado izquierdo: Imagen grande -->
                                <div class="col-md-6 text-center">
                                    <div class="img-zoom-container">
                                        <img src="<?php echo $prod['imagen']; ?>" class="img-fluid p-3 img-zoom" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                                    </div>
                                </div>

                                <!-- Lado derecho: Título, descripción y botón de compra -->
                                <div class="col-md-6">
                                    <h2 class="mb-3" id="Label<?php echo $modalId; ?>"><?php echo $prod['nombre']; ?></h2>
                                    <p class="lead text-muted"><?php echo $prod['descripcion']; ?></p>

                                    <?php
                                    // Si el producto tiene especificaciones (como la PC), las mostramos en una lista
                                    if (isset($prod['especificaciones'])):
                                    ?>
                                        <div class="mt-4 mb-4">
                                            <h5>Especificaciones:</h5>
                                            <ul class="list-group list-group-flush">
                                                <?php foreach ($prod['especificaciones'] as $key => $value): ?>
                                                    <li class="list-group-item bg-transparent px-0 border-light text-muted">
                                                        <strong><?php echo $key; ?>:</strong> <?php echo $value; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($prod['oferta'])): ?>
                                        <p class="mb-3">
                                            <span class="old-price fs-5">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></span>
                                            <span class="new-price ms-2 fs-3">$<?php echo number_format($prod['oferta'], 0, ',', '.'); ?></span>
                                        </p>
                                    <?php else: ?>
                                        <h3 class="text-primary display-6 mb-4">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></h3>
                                    <?php endif; ?>

                                    <!-- Formulario de compra dentro del modal -->
                                    <form action="api_carrito.php" method="POST" class="form-agregar-carrito">
                                        <input type="hidden" name="id_producto" value="<?php echo $prod['id']; ?>">
                                        <input type="hidden" name="nombre" value="<?php echo $prod['nombre']; ?>">
                                        <input type="hidden" name="precio" value="<?php echo isset($prod['oferta']) ? $prod['oferta'] : $prod['precio']; ?>">
                                        <input type="hidden" name="imagen" value="<?php echo $prod['imagen']; ?>">
                                        <input type="hidden" name="accion" value="agregar"> <!-- Identifica que se está agregando -->

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg btn-comprar transition-all">
                                                <span class="icono-btn"><i class="bi bi-cart-plus me-2"></i></span>
                                                <span class="texto-btn">Agregar al carrito</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fin del Modal -->

        <?php
            endforeach;
            // Fin del Bucle
        endif;
        ?>

    </div>
</main>

<!-- Incluimos Bootstrap JS para que los modales funcionen -->
<script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para manejar el agregado al carrito sin recargar (AJAX) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar todos los formularios de agregar al carrito
        const forms = document.querySelectorAll('.form-agregar-carrito');
        const cartBadge = document.getElementById('cartBadge');

        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                // Prevenir que la página se recargue
                e.preventDefault();

                const btn = this.querySelector('button[type="submit"]');
                const icono = btn.querySelector('.icono-btn');
                const texto = btn.querySelector('.texto-btn');

                // Guardar el estado original del botón
                const originalColor = btn.classList.contains('btn-primary') ? 'btn-primary' : btn.className;
                const originalHtml = btn.innerHTML;

                // Cambiar visualmente a "Agregando..."
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
                texto.innerText = 'Agregando...';
                icono.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>';
                btn.disabled = true;

                // Recolectar los datos del formulario
                const formData = new FormData(this);

                // Enviar los datos por AJAX hacia la API
                fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Actualizar el número de la burbuja en el header
                            if (cartBadge) {
                                cartBadge.innerText = data.cantidad_total;
                                cartBadge.style.display = 'inline-block';
                                // Hacer que la burbuja palpite para llamar la atención
                                cartBadge.classList.add('animate__animated', 'animate__bounceIn');
                                setTimeout(() => cartBadge.classList.remove('animate__animated', 'animate__bounceIn'), 1000);
                            }

                            // Efecto visual de "¡Agregado!" en el botón (verde)
                            btn.classList.remove('btn-secondary');
                            btn.classList.add('btn-success');
                            texto.innerText = '¡Agregado!';
                            icono.innerHTML = '<i class="bi bi-check-circle me-2"></i>';

                            // Volver el botón a la normalidad después de 2 segundos
                            setTimeout(() => {
                                btn.classList.remove('btn-success');
                                btn.classList.add('btn-primary');
                                btn.innerHTML = originalHtml;
                                btn.disabled = false;
                            }, 2000);
                        } else {
                            alert("Error al agregar el producto");
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.disabled = false;
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-danger');
                        texto.innerText = 'Error';
                    });
            });
        });
    });
</script>

<?php include("includes/footer.php"); ?>