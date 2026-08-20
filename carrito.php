<?php
// Iniciar la sesión de PHP. Es OBLIGATORIO para poder guardar variables (como el carrito) que perduren mientras navegamos
session_start();

// Si no existe la variable de sesión del carrito, la creamos como un arreglo vacío
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ==============================================================================
// 1. LÓGICA PARA AGREGAR PRODUCTOS
// ==============================================================================
// Verificamos si los datos llegaron por el método POST (al enviar el formulario) y si la acción es "agregar"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'agregar') {

    // Obtenemos los datos del formulario
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $imagen = isset($_POST['imagen']) ? $_POST['imagen'] : '';

    // Buscamos si el producto ya existe en el carrito
    $producto_encontrado = false;
    foreach ($_SESSION['carrito'] as $indice => $producto) {
        if ($producto['id'] == $id_producto) {
            // Si el ID ya existe en el carrito, le sumamos 1 a la cantidad y frenamos la búsqueda
            $_SESSION['carrito'][$indice]['cantidad']++;
            $producto_encontrado = true;
            break;
        }
    }

    // Si recorrimos el carrito y no encontramos el producto, lo agregamos como uno nuevo
    if (!$producto_encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $id_producto,
            'nombre' => $nombre,
            'precio' => $precio,
            'imagen' => $imagen,
            'cantidad' => 1 // Al ser nuevo, la cantidad inicial es 1
        ];
    }

    // Mensaje de éxito que se mostrará en pantalla
    $mensaje = "¡El producto <strong>$nombre</strong> fue agregado al carrito exitosamente!";
}

// ==============================================================================
// 2. LÓGICA PARA VACIAR EL CARRITO
// ==============================================================================
// Si recibimos por URL la orden de vaciar (ej: carrito.php?accion=vaciar)
if (isset($_GET['accion']) && $_GET['accion'] == 'vaciar') {
    $_SESSION['carrito'] = []; // Limpiamos el arreglo
    $mensaje = "El carrito ha sido vaciado.";
}

// ==============================================================================
// 3. LÓGICA PARA ACTUALIZAR CANTIDADES (+ / -)
// ==============================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['accion'])) {
    $id_actualizar = $_POST['id'];
    $accion = $_POST['accion'];

    // Buscamos el producto en el carrito
    foreach ($_SESSION['carrito'] as $indice => $producto) {
        if ($producto['id'] == $id_actualizar) {
            if ($accion === 'sumar') {
                $_SESSION['carrito'][$indice]['cantidad']++;
            } elseif ($accion === 'restar') {
                $_SESSION['carrito'][$indice]['cantidad']--;
                // Si la cantidad llega a 0, eliminamos el producto usando unset
                if ($_SESSION['carrito'][$indice]['cantidad'] <= 0) {
                    unset($_SESSION['carrito'][$indice]);
                    // Reindexamos el arreglo para que no queden huecos en los índices
                    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                }
            }
            // Redirigimos a la misma página para ver los cambios y evitar reenvío de formulario
            header("Location: carrito.php");
            exit();
        }
    }
}

include("includes/header.php");
include("includes/productos_data.php"); // Incluir los datos de productos para los modales

// Calculamos el precio total recorriendo el carrito y sumando (precio * cantidad) de cada producto
$total_carrito = 0;
foreach ($_SESSION['carrito'] as $producto) {
    $total_carrito += ($producto['precio'] * $producto['cantidad']);
}
?>

<main class="container my-5" style="min-height: 60vh;">
    <h1 class="mb-4"><i class="bi bi-cart3"></i> Tu Carrito de Compras</h1>

    <!-- Si existe un mensaje (producto agregado o carrito vaciado), lo mostramos en una alerta de Bootstrap -->
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Si el carrito está vacío, mostramos un mensaje bonito de vacío -->
    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 5rem;"></i>
            <h3 class="mt-3 text-muted">Tu carrito está vacío</h3>
            <p class="mb-4">Parece que aún no has agregado ningún producto.</p>
            <a href="productos.php" class="btn btn-primary btn-lg">Ir a comprar</a>
        </div>

        <!-- Si hay productos, mostramos la tabla del carrito -->
    <?php else: ?>

        <div class="table-responsive shadow-sm bg-white rounded-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 10%">Producto</th>
                        <th scope="col" style="width: 40%">Descripción</th>
                        <th scope="col" style="width: 15%">Precio Unit.</th>
                        <th scope="col" style="width: 15%">Cantidad</th>
                        <th scope="col" style="width: 20%">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Recorremos el carrito para imprimir fila por fila cada producto
                    foreach ($_SESSION['carrito'] as $prod):
                    ?>
                        <tr>
                            <!-- Columna: Imagen -->
                            <td>
                                <?php if (!empty($prod['imagen'])): ?>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalProducto<?php echo $prod['id']; ?>">
                                        <div class="img-carrito-container shadow-sm border">
                                            <img src="<?php echo $prod['imagen']; ?>" class="img-carrito-zoom" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" <?php echo ($prod['id'] == 17) ? 'style="transform: scale(1.1);"' : ''; ?>>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div class="bg-light text-center d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 8px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <!-- Columna: Nombre -->
                            <td>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modalProducto<?php echo $prod['id']; ?>" class="text-decoration-none text-reset">
                                    <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong>
                                </a>
                            </td>
                            <!-- Columna: Precio Unitario -->
                            <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                            <!-- Columna: Cantidad -->
                            <td>
                                <form action="carrito.php" method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <!-- Botón Restar -->
                                    <button type="submit" name="accion" value="restar" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                        <i class="bi bi-dash"></i>
                                    </button>

                                    <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6"><?php echo $prod['cantidad']; ?></span>

                                    <!-- Botón Sumar -->
                                    <button type="submit" name="accion" value="sumar" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </form>
                            </td>
                            <!-- Columna: Subtotal (Precio * Cantidad) -->
                            <td class="fw-bold text-primary">
                                $<?php echo number_format($prod['precio'] * $prod['cantidad'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <!-- Fila inferior con el Total -->
                    <tr>
                        <td colspan="4" class="text-end fw-bold fs-5">TOTAL:</td>
                        <td class="fw-bold fs-4 text-success">$<?php echo number_format($total_carrito, 0, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Botones de acción: Volver, Vaciar y Pagar -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="productos.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Seguir Comprando
            </a>

            <div>
                <!-- Botón que abre el modal de confirmación -->
                <button type="button" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#modalVaciarCarrito">
                    <i class="bi bi-trash"></i> Vaciar Carrito
                </button>
                <button class="btn btn-success btn-lg">
                    <i class="bi bi-credit-card"></i> Proceder al Pago
                </button>
            </div>
        </div>

    <?php endif; ?>

    <!-- Modal de Confirmación para Vaciar Carrito -->
    <div class="modal fade" id="modalVaciarCarrito" tabindex="-1" aria-labelledby="modalVaciarCarritoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="modalVaciarCarritoLabel"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">¿Estás completamente seguro de que querés vaciar tu carrito? Perderás todos los productos que agregaste.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <!-- El botón Sí envía al enlace original para vaciar -->
                    <a href="carrito.php?accion=vaciar" class="btn btn-danger">Sí, vaciar carrito</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales de Productos -->
    <?php
    if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
        $ids_en_carrito = array_column($_SESSION['carrito'], 'id');
        foreach ($productos_data as $prod_full) {
            if (in_array($prod_full['id'], $ids_en_carrito)) {
                $modalId = "Producto" . $prod_full['id'];
    ?>
                <!-- Modal para <?php echo htmlspecialchars($prod_full['nombre']); ?> -->
                <div class="modal fade" id="modal<?php echo $modalId; ?>" tabindex="-1" aria-labelledby="Label<?php echo $modalId; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <!-- Encabezado del modal con botón cerrar -->
                            <div class="modal-header border-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <!-- Cuerpo del modal -->
                            <div class="modal-body p-4">
                                <div class="row align-items-center">
                                    <!-- Lado izquierdo: Imagen grande -->
                                    <div class="col-md-6 text-center">
                                        <div class="img-zoom-container">
                                            <img src="<?php echo $prod_full['imagen']; ?>" class="img-fluid p-3 img-zoom" alt="<?php echo htmlspecialchars($prod_full['nombre']); ?>">
                                        </div>
                                    </div>

                                    <!-- Lado derecho: Título, descripción y botón de compra -->
                                    <div class="col-md-6">
                                        <h2 class="mb-3" id="Label<?php echo $modalId; ?>"><?php echo htmlspecialchars($prod_full['nombre']); ?></h2>
                                        <p class="lead text-muted"><?php echo htmlspecialchars($prod_full['descripcion']); ?></p>

                                        <?php
                                        // Si el producto tiene especificaciones (como la PC), las mostramos en una lista
                                        if (isset($prod_full['especificaciones'])):
                                        ?>
                                            <div class="mt-4 mb-4">
                                                <h5>Especificaciones:</h5>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($prod_full['especificaciones'] as $key => $value): ?>
                                                        <li class="list-group-item bg-transparent px-0 border-light text-muted">
                                                            <strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <h3 class="text-primary display-6 mb-4">$<?php echo number_format($prod_full['precio'], 0, ',', '.'); ?></h3>

                                        <!-- Formulario para agregar más cantidad -->
                                        <form action="api_carrito.php" method="POST" class="form-agregar-carrito">
                                            <input type="hidden" name="id_producto" value="<?php echo $prod_full['id']; ?>">
                                            <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($prod_full['nombre']); ?>">
                                            <input type="hidden" name="precio" value="<?php echo $prod_full['precio']; ?>">
                                            <input type="hidden" name="imagen" value="<?php echo htmlspecialchars($prod_full['imagen']); ?>">
                                            <input type="hidden" name="accion" value="agregar">

                                            <div class="d-grid gap-2">
                                                <button type="submit" class="btn btn-primary btn-lg btn-comprar transition-all">
                                                    <span class="icono-btn"><i class="bi bi-cart-plus me-2"></i></span>
                                                    <span class="texto-btn">Agregar otra unidad</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    <?php
            }
        }
    }
    ?>

</main>

<!-- Incluimos Bootstrap JS -->
<script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<!-- Script AJAX para que agregar otra unidad recargue la página o funcione silenciosamente -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.form-agregar-carrito');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = 'Cargando...';

                const formData = new FormData(this);
                fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Si estamos en el carrito, recargar la página es lo mejor para que se vea reflejado el cambio de precio y cantidad.
                            location.reload();
                        } else {
                            alert("Error al agregar el producto");
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        btn.disabled = false;
                    });
            });
        });
    });
</script>

<?php include("includes/footer.php"); ?>