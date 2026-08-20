<?php
include("includes/header.php");
include("includes/productos_data.php");
?>

<main class="container my-5" style="min-height: 60vh;">
    <h1 class="mb-4"><i class="bi bi-tags"></i> Ofertas de Memoria RAM</h1>

    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        foreach ($productos_data as $producto) {
            if (stripos($producto['nombre'], 'Memoria RAM') !== false) {
                $precio_oferta = round($producto['precio'] * 0.8); // 20% de descuento
                $modalId = "modalOferta" . $producto['id'];
        ?>
            <!-- Tarjeta del producto en oferta -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <!-- Imagen clickable que abre el modal -->
                    <a href="#" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                        <img src="<?php echo $producto['imagen']; ?>"
                             class="card-img-top p-3"
                             style="object-fit: contain; height: 200px; cursor: pointer;"
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                             title="Hacé clic para ver el detalle">
                    </a>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="card-text small text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></p>

                        <!-- Precio con tachado y oferta -->
                        <p class="card-text mb-3">
                            <span class="old-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                            <span class="new-price ms-2">$<?php echo number_format($precio_oferta, 0, ',', '.'); ?></span>
                        </p>

                        <!-- Botón Ver detalle -->
                        <button type="button"
                                class="btn btn-outline-primary w-100 mt-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#<?php echo $modalId; ?>">
                            <i class="bi bi-eye"></i> Ver detalle
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal del producto -->
            <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1"
                 aria-labelledby="Label<?php echo $modalId; ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body pt-0">
                            <div class="row align-items-center">
                                <!-- Imagen grande con zoom -->
                                <div class="col-md-6 text-center">
                                    <div class="img-zoom-container">
                                        <img src="<?php echo $producto['imagen']; ?>"
                                             class="img-fluid p-3 img-zoom"
                                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    </div>
                                </div>

                                <!-- Info del producto -->
                                <div class="col-md-6">
                                    <h2 class="mb-3" id="Label<?php echo $modalId; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </h2>
                                    <p class="lead text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></p>

                                    <!-- Precios en el modal -->
                                    <p class="mb-3">
                                        <span class="old-price fs-5">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                                        <span class="new-price ms-2 fs-3">$<?php echo number_format($precio_oferta, 0, ',', '.'); ?></span>
                                    </p>

                                    <!-- Formulario AJAX para agregar al carrito -->
                                    <form action="api_carrito.php" method="POST" class="form-agregar-carrito">
                                        <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                        <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                        <input type="hidden" name="precio" value="<?php echo $precio_oferta; ?>">
                                        <input type="hidden" name="imagen" value="<?php echo htmlspecialchars($producto['imagen']); ?>">
                                        <input type="hidden" name="accion" value="agregar">

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
            <!-- Fin Modal -->

        <?php
            }
        }
        ?>
    </div>
</main>

<!-- Bootstrap JS -->
<script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<!-- Script AJAX para agregar al carrito sin recargar -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.form-agregar-carrito');
    const cartBadge = document.getElementById('cartBadge');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = this.querySelector('button[type="submit"]');
            const icono = btn.querySelector('.icono-btn');
            const texto = btn.querySelector('.texto-btn');
            const originalHtml = btn.innerHTML;

            // Estado "Agregando..."
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
            texto.innerText = 'Agregando...';
            icono.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>';
            btn.disabled = true;

            fetch(this.action, { method: 'POST', body: new FormData(this) })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Actualizar burbuja del carrito
                        if (cartBadge) {
                            cartBadge.innerText = data.cantidad_total;
                            cartBadge.style.display = 'inline-block';
                        }

                        // Estado "¡Agregado!"
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-success');
                        texto.innerText = '¡Agregado!';
                        icono.innerHTML = '<i class="bi bi-check-circle me-2"></i>';

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
                .catch(() => {
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