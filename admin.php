<?php
/**
 * ============================================================
 * 1. CONTROL DE ACCESO AL PANEL DE ADMINISTRACIÓN
 * ============================================================
 *
 * ¿Qué hace?
 * Este bloque verifica si la persona que está viendo la página está
 * identificada como un usuario conectado y, además, si ese usuario
 * tiene permisos de administrador.
 *
 * ¿Por qué está aquí?
 * El panel de administración permite cambiar productos y datos importantes.
 * Solo las personas autorizadas deben poder usarlo.
 *
 * ¿Qué pasaría si no existiera?
 * Cualquiera podría entrar a esta página y modificar productos.
 * Eso sería inseguro.
 *
 * ¿Cómo trabaja internamente?
 * Usa información guardada en la sesión de PHP para saber quién es la persona
 * y luego consulta la base de datos para comprobar si el usuario tiene el permiso.
 */

// session_start() inicia un mecanismo llamado "sesiones" en PHP.
// Una sesión es una forma de guardar información del usuario mientras navega
// entre diferentes páginas de la web. Sin session_start() no podemos usar $_SESSION.
session_start();

// include() copia y pega el código de otro archivo aquí.
// En este caso, trae el archivo que abre la conexión con la base de datos.
include('includes/conexion.php');

// isset() verifica si una variable existe y tiene algún valor.
// $_SESSION['id_usuario'] es donde guardamos el identificador del usuario logueado.
if (!isset($_SESSION['id_usuario'])) {
    // header('Location: index.php') indica al navegador que debe ir a otra página.
    // Es como decir "vuelve a la página principal".
    header('Location: index.php');
    // exit() detiene todo el código que sigue. No queremos seguir si no hay usuario.
    exit();
}

// Tomamos el valor de la sesión y lo convertimos a número entero.
// El signo = significa "asignar". No es una comparación.
// (int) convierte el valor a un número, por seguridad.
$id_usuario = (int)$_SESSION['id_usuario'];

/**
 * ============================================================
 * 1.1 CONSULTA PARA SABER SI EL USUARIO ES ADMIN
 * ============================================================
 *
 * ¿Qué hace?
 * Consulta la base de datos para leer el campo es_admin del usuario.
 * Ese campo indica si el usuario puede ver el panel administrativo.
 *
 * ¿Qué es SELECT?
 * SELECT es una palabra de SQL que significa "traer datos".
 * En este caso pedimos el campo es_admin de la tabla usuarios.
 *
 * ¿Qué es WHERE?
 * WHERE es una condición. Aquí dice: "trae solo el usuario que tenga este id".
 */

$stmt = $conn->prepare('SELECT es_admin FROM usuarios WHERE id_usuario = ?');

// bind_param coloca el valor del usuario dentro de la consulta preparada.
// 'i' significa que el valor es un entero.
$stmt->bind_param('i', $id_usuario);
$stmt->execute();

// get_result() obtiene el resultado de la consulta.
// fetch_assoc() toma una fila de resultado y la convierte en un array asociativo.
// Un array asociativo es como una lista donde cada dato tiene un nombre.
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

// empty() verifica si la variable está vacía o no existe.
// Si no hay usuario o si el campo es_admin no está activo, enviamos al usuario a inicio.
if (!$user || empty($user['es_admin'])) {
    header('Location: index.php');
    exit();
}

/**
 * ============================================================
 * 2. CARGAR LOS PRODUCTOS PARA MOSTRARLOS EN LA PANTALLA
 * ============================================================
 *
 * ¿Qué hace?
 * Lee la lista de productos guardados en la base de datos.
 * Cada producto tiene nombre, precio, oferta, stock, imagen y descripción.
 *
 * ¿Por qué está aquí?
 * El panel necesita esa información para mostrarla y permitir editarla.
 *
 * ¿Qué pasaría si no existiera?
 * La página no tendría productos y estaría vacía.
 */

$resultado = $conn->query('SELECT id, nombre, precio, oferta, stock, agotado, imagen, descripcion FROM productos ORDER BY id');

// Creamos un arreglo vacío llamado productos.
// Un arreglo es una lista que puede guardar muchos elementos.
$productos = [];

// if() es una condición. Si la consulta devolvió algo, entonces entramos.
if ($resultado) {
    // while() repite acciones mientras haya más filas.
    // fetch_assoc() trae una fila de datos cada vez.
    while ($fila = $resultado->fetch_assoc()) {
        // Agregamos cada fila a la lista de productos.
        $productos[] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Byztoo PC</title>
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fb; }
        .card { border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        .table td, .table th { vertical-align: middle; }
        .btn-sm { transition: all 0.2s ease-in-out; }
        .btn-sm:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Panel de administración</h1>
                <p class="text-muted mb-0">Gestioná stock, precios y ofertas de forma real.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Producto
                </button>
                <a href="logout.php" class="btn btn-outline-danger shadow-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </a>
            </div>
        </div>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Oferta</th>
                            <th>Stock</th>
                            <th>Imagen</th>
                            <th>Estado</th>
                            <th style="min-width: 170px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        /**
                         * ============================================================
                         * 3. MOSTRAR CADA PRODUCTO EN LA TABLA
                         * ============================================================
                         *
                         * ¿Qué hace?
                         * Recorre todos los productos cargados desde la base de datos y
                         * crea una fila en la tabla por cada producto.
                         *
                         * ¿Cómo trabaja?
                         * PHP usa foreach para repetir una acción para cada elemento.
                         * En este caso, cada elemento es un producto.
                         */
                        foreach ($productos as $producto): ?>
                            <?php
                                // $producto es un conjunto de información de un solo producto.
                                // Convertimos stock a número entero para poder comparar con 0 y 3.
                                $stock = (int)($producto['stock'] ?? 0);
                                $rowClass = '';
                                $statusLabel = '';

                                // if() es una condición. Pregunta si una expresión es verdadera.
                                // <= significa "menor o igual que".
                                if ($stock <= 0) {
                                    // Si el stock es 0 o negativo, marcamos la fila en rojo.
                                    $rowClass = 'table-danger';
                                    $statusLabel = '<span class="badge bg-danger">Agotado</span>';
                                } elseif ($stock <= 3) {
                                    // elseif significa "si no se cumplió lo anterior, pruebo esta otra condición".
                                    // Si el stock es 1, 2 o 3, marcamos la fila en amarillo.
                                    $rowClass = 'table-warning';
                                    $statusLabel = '<span class="badge bg-warning text-dark">Stock bajo</span>';
                                } else {
                                    // Si ninguna condición anterior se cumple, el stock está bien.
                                    $statusLabel = '<span class="badge bg-success">OK</span>';
                                }
                            ?>
                            <tr class="<?php echo $rowClass; ?> producto-row" data-id="<?php echo $producto['id']; ?>">
                                <td>
                                    <!-- htmlspecialchars() convierte caracteres especiales a texto seguro. -->
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                </td>
                                <td>
                                    <!-- Este campo permite editar el precio del producto. -->
                                    <input type="number" step="0.01" name="precio" class="form-control form-control-sm" value="<?php echo $producto['precio']; ?>">
                                </td>
                                <td>
                                    <!-- Este campo permite editar la oferta del producto. -->
                                    <input type="number" step="0.01" name="oferta" class="form-control form-control-sm" value="<?php echo $producto['oferta'] ?? ''; ?>">
                                </td>
                                <td>
                                    <!-- Este campo permite editar cuánto stock hay. -->
                                    <input type="number" min="0" name="stock" class="form-control form-control-sm" value="<?php echo $producto['stock'] ?? 0; ?>">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Muestra una miniatura de la imagen del producto. -->
                                        <img src="<?php echo htmlspecialchars($producto['imagen'] ?? ''); ?>" class="imagen-thumb rounded" style="width: 50px; height: 50px; object-fit: cover;" alt="Miniatura">
                                        <!-- Permite editar la ruta o URL de la imagen. -->
                                        <input type="text" name="imagen" class="form-control form-control-sm imagen-input" value="<?php echo htmlspecialchars($producto['imagen'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td class="estado-cell">
                                    <!-- Muestra si el producto está agotado, tiene poco stock o está bien. -->
                                    <?php echo $statusLabel; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Botón rápido para guardar cambios en línea -->
                                        <button type="button" class="btn btn-sm btn-success guardar-cambios" title="Guardar cambios rápidos">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <!-- Botón para abrir modal de edición completa -->
                                        <button type="button" class="btn btn-sm btn-primary abrir-editar-modal" 
                                                data-id="<?php echo $producto['id']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>"
                                                data-precio="<?php echo $producto['precio']; ?>"
                                                data-oferta="<?php echo $producto['oferta'] ?? ''; ?>"
                                                data-stock="<?php echo $producto['stock'] ?? 0; ?>"
                                                data-imagen="<?php echo htmlspecialchars($producto['imagen'] ?? ''); ?>"
                                                data-especificaciones="<?php echo htmlspecialchars($producto['especificaciones'] ?? ''); ?>"
                                                title="Editar detalles completos">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>
                                    </div>
                                    <div class="save-feedback mt-1" style="font-size: 0.85rem;"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        /**
         * ============================================================
         * 4. JAVASCRIPT PARA INTERACTIVIDAD EN EL PANEL
         * ============================================================
         *
         * ¿Qué hace?
         * Añade comportamiento dinámico a la página sin recargarla.
         * Permite que el color del estado cambie al instante y que el botón
         * de guardar envíe datos al servidor.
         *
         * ¿Por qué está aquí?
         * Para que el administrador vea cambios inmediatos y la aplicación sea más fácil de usar.
         *
         * ¿Qué pasaría si no existiera?
         * La página seguiría funcionando, pero el administrador no vería
         * actualizaciones instantáneas y tendría que recargar para ver cambios.
         */

        // document.addEventListener() espera a que el contenido de la página cargue.
        // "DOMContentLoaded" significa "todo el HTML ya está listo".
        document.addEventListener('DOMContentLoaded', function () {
            // querySelectorAll() busca todos los elementos que tienen la clase producto-row.
            // Devuelve una lista de líneas de productos.
            document.querySelectorAll('.producto-row').forEach(function (row) {
                var imagenInput = row.querySelector('.imagen-input');
                var thumb = row.querySelector('.imagen-thumb');
                var stockInput = row.querySelector('input[name="stock"]');
                var estadoCell = row.querySelector('.estado-cell');
                var saveBtn = row.querySelector('.guardar-cambios');
                var feedback = row.querySelector('.save-feedback');

                /**
                 * updateEstado(): función que actualiza el texto y el color según el stock.
                 *
                 * ¿Qué hace?
                 * Lee el valor actual del stock y decide si el producto está agotado,
                 * si tiene poco stock o si está bien.
                 */
                var updateEstado = function () {
                    // Number() convierte un texto en número.
                    // Si el usuario escribe "3", lo convierte en el número 3.
                    var stockValue = Number(stockInput.value);
                    if (stockValue <= 0) {
                        row.classList.add('table-danger');
                        row.classList.remove('table-warning');
                        estadoCell.innerHTML = '<span class="badge bg-danger">Agotado</span>';
                    } else if (stockValue <= 3) {
                        row.classList.add('table-warning');
                        row.classList.remove('table-danger');
                        estadoCell.innerHTML = '<span class="badge bg-warning text-dark">Stock bajo</span>';
                    } else {
                        row.classList.remove('table-danger');
                        row.classList.remove('table-warning');
                        estadoCell.innerHTML = '<span class="badge bg-success">OK</span>';
                    }
                };

                // Llamamos a updateEstado una vez al principio para que la fila ya muestre
                // el color y el texto correctos cuando la página se carga.
                updateEstado();

                // Escuchamos el evento input en el campo de imagen.
                // Cuando el usuario escribe una nueva URL, actualizamos la miniatura.
                imagenInput.addEventListener('input', function () {
                    if (this.value.trim() !== '') {
                        thumb.src = this.value.trim();
                    }
                });

                // Cada vez que cambia el stock, recalculamos el estado.
                stockInput.addEventListener('input', updateEstado);

                // Escuchamos el clic en el botón "Guardar cambios".
                saveBtn.addEventListener('click', function () {
                    var id = row.getAttribute('data-id');
                    var precio = row.querySelector('input[name="precio"]').value;
                    var oferta = row.querySelector('input[name="oferta"]').value;
                    var stock = row.querySelector('input[name="stock"]').value;
                    var imagen = imagenInput.value;

                    // URLSearchParams crea un conjunto de datos para enviar por POST.
                    var params = new URLSearchParams();
                    params.append('id_producto', id);
                    params.append('precio', precio);
                    params.append('oferta', oferta);
                    params.append('stock', stock);
                    params.append('imagen', imagen);

                    saveBtn.disabled = true; // Desactiva el botón para que no se haga clic dos veces.
                    saveBtn.textContent = 'Guardando...';

                    // fetch() envía una petición al servidor sin recargar la página.
                    fetch('actualizar_producto.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                        },
                        body: params.toString()
                    })
                        .then(function (response) {
                            // response.json() convierte la respuesta de texto en un objeto.
                            return response.json();
                        })
                        .then(function (data) {
                            if (data.status === 'success') {
                                // Si el servidor responde con status success, mostramos mensaje positivo.
                                feedback.textContent = 'Guardado ✓';
                                feedback.style.color = '#198754';
                                saveBtn.textContent = 'Guardado ✓';
                                setTimeout(function () {
                                    feedback.textContent = '';
                                    saveBtn.textContent = 'Guardar cambios';
                                }, 2000);
                            } else {
                                // Si el servidor responde con error, mostramos lo que vino del servidor.
                                feedback.textContent = data.message || 'Error al guardar';
                                feedback.style.color = '#dc3545';
                                saveBtn.textContent = 'Guardar cambios';
                            }
                        })
                        .catch(function () {
                            // catch() atrapa errores si la conexión falla.
                            feedback.textContent = 'Error en la conexión';
                            feedback.style.color = '#dc3545';
                            saveBtn.textContent = 'Guardar cambios';
                        })
                        .finally(function () {
                            // finally() se ejecuta siempre, haya habido éxito o error.
                            saveBtn.disabled = false;
                        });
                });
            });

            // Manejar la apertura del Modal de Editar Producto y rellenar sus valores
            var modalEditar = document.getElementById('modalEditarProducto');
            if (modalEditar) {
                document.querySelectorAll('.abrir-editar-modal').forEach(function (button) {
                    button.addEventListener('click', function () {
                        // Obtener datos del botón
                        var id = this.getAttribute('data-id');
                        var nombre = this.getAttribute('data-nombre');
                        var descripcion = this.getAttribute('data-descripcion');
                        var precio = this.getAttribute('data-precio');
                        var oferta = this.getAttribute('data-oferta');
                        var stock = this.getAttribute('data-stock');
                        var imagen = this.getAttribute('data-imagen');
                        var especificaciones = this.getAttribute('data-especificaciones');

                        // Rellenar formulario
                        document.getElementById('edit_id').value = id;
                        document.getElementById('edit_nombre').value = nombre;
                        document.getElementById('edit_descripcion').value = descripcion;
                        document.getElementById('edit_precio').value = precio;
                        document.getElementById('edit_oferta').value = oferta;
                        document.getElementById('edit_stock').value = stock;
                        document.getElementById('edit_imagen').value = imagen;

                        // Rellenar especificaciones
                        try {
                            if (especificaciones && especificaciones.trim() !== '') {
                                // Formatear JSON para que sea legible en el textarea
                                var parsed = JSON.parse(especificaciones);
                                document.getElementById('edit_especificaciones').value = JSON.stringify(parsed, null, 2);
                            } else {
                                document.getElementById('edit_especificaciones').value = '';
                            }
                        } catch (e) {
                            document.getElementById('edit_especificaciones').value = especificaciones;
                        }

                        // Limpiar feedback anterior
                        var feedback = document.getElementById('editarFeedback');
                        feedback.classList.add('d-none');
                        feedback.classList.remove('alert-success', 'alert-danger');

                        // Mostrar modal manualmente con la API de Bootstrap
                        var bsModal = new bootstrap.Modal(modalEditar);
                        bsModal.show();
                    });
                });
            }

            // Submit de Agregar Producto
            var formAgregar = document.getElementById('formAgregarProducto');
            if (formAgregar) {
                formAgregar.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var feedback = document.getElementById('agregarFeedback');
                    var submitBtn = document.getElementById('btnGuardarAgregar');

                    feedback.classList.add('d-none');
                    feedback.classList.remove('alert-success', 'alert-danger');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creando...';

                    var formData = new FormData(formAgregar);

                    fetch('agregar_producto.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.status === 'success') {
                            feedback.textContent = data.message || 'Producto creado correctamente.';
                            feedback.classList.add('alert-success');
                            feedback.classList.remove('d-none');
                            setTimeout(function () {
                                location.reload();
                            }, 1500);
                        } else {
                            feedback.textContent = data.message || 'Error al agregar producto.';
                            feedback.classList.add('alert-danger');
                            feedback.classList.remove('d-none');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Crear Producto';
                        }
                    })
                    .catch(function(err) {
                        feedback.textContent = 'Error de conexión con el servidor.';
                        feedback.classList.add('alert-danger');
                        feedback.classList.remove('d-none');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Crear Producto';
                    });
                });
            }

            // Submit de Editar Producto
            var formEditar = document.getElementById('formEditarProducto');
            if (formEditar) {
                formEditar.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var feedback = document.getElementById('editarFeedback');
                    var submitBtn = document.getElementById('btnGuardarEditar');

                    feedback.classList.add('d-none');
                    feedback.classList.remove('alert-success', 'alert-danger');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';

                    var formData = new FormData(formEditar);

                    fetch('actualizar_producto.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.status === 'success') {
                            feedback.textContent = data.message || 'Producto actualizado correctamente.';
                            feedback.classList.add('alert-success');
                            feedback.classList.remove('d-none');
                            setTimeout(function () {
                                location.reload();
                            }, 1500);
                        } else {
                            feedback.textContent = data.message || 'Error al actualizar producto.';
                            feedback.classList.add('alert-danger');
                            feedback.classList.remove('d-none');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Guardar Cambios';
                        }
                    })
                    .catch(function(err) {
                        feedback.textContent = 'Error de conexión con el servidor.';
                        feedback.classList.add('alert-danger');
                        feedback.classList.remove('d-none');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Guardar Cambios';
                    });
                });
            }
        });
    </script>

    <!-- Modales de Bootstrap 5 -->
    <!-- Modal Agregar Producto -->
    <div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h5 class="modal-title" id="modalAgregarProductoLabel"><i class="bi bi-plus-circle-fill me-2"></i>Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formAgregarProducto">
                    <div class="modal-body p-4">
                        <div id="agregarFeedback" class="alert d-none"></div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="add_nombre" class="form-label font-monospace fw-bold">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="add_nombre" name="nombre" required placeholder="Ej: Memoria RAM HyperX 8GB">
                            </div>
                            <div class="col-md-4">
                                <label for="add_stock" class="form-label font-monospace fw-bold">Stock *</label>
                                <input type="number" class="form-control" id="add_stock" name="stock" min="0" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_precio" class="form-label font-monospace fw-bold">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="add_precio" name="precio" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="add_oferta" class="form-label font-monospace fw-bold">Precio de Oferta (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="add_oferta" name="oferta" placeholder="Dejar vacío si no aplica">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="add_imagen" class="form-label font-monospace fw-bold">Ruta / URL de la Imagen *</label>
                                <input type="text" class="form-control" id="add_imagen" name="imagen" required placeholder="Ej: img/nuevoram.png">
                            </div>
                            <div class="col-12">
                                <label for="add_descripcion" class="form-label font-monospace fw-bold">Descripción *</label>
                                <textarea class="form-control" id="add_descripcion" name="descripcion" rows="3" required placeholder="Escribe los detalles y características del producto..."></textarea>
                            </div>
                            <div class="col-12">
                                <label for="add_especificaciones" class="form-label font-monospace fw-bold">Especificaciones (Formato JSON, Opcional)</label>
                                <textarea class="form-control font-monospace" id="add_especificaciones" name="especificaciones" rows="4" placeholder='{&#10;  "Marca": "Kingston",&#10;  "Frecuencia": "3200MHz",&#10;  "Tipo": "DDR4"&#10;}' style="font-size: 0.9rem;"></textarea>
                                <div class="form-text">Debe ser un objeto JSON válido entre llaves { } o quedar vacío.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-3 bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarAgregar">Crear Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                <div class="modal-header bg-success text-white" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <h5 class="modal-title" id="modalEditarProductoLabel"><i class="bi bi-pencil-square me-2"></i>Editar Producto Completo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formEditarProducto">
                    <input type="hidden" id="edit_id" name="id_producto">
                    <div class="modal-body p-4">
                        <div id="editarFeedback" class="alert d-none"></div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="edit_nombre" class="form-label font-monospace fw-bold">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_stock" class="form-label font-monospace fw-bold">Stock *</label>
                                <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_precio" class="form-label font-monospace fw-bold">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="edit_precio" name="precio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_oferta" class="form-label font-monospace fw-bold">Precio de Oferta (Opcional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="edit_oferta" name="oferta">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="edit_imagen" class="form-label font-monospace fw-bold">Ruta / URL de la Imagen *</label>
                                <input type="text" class="form-control" id="edit_imagen" name="imagen" required>
                            </div>
                            <div class="col-12">
                                <label for="edit_descripcion" class="form-label font-monospace fw-bold">Descripción *</label>
                                <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3" required></textarea>
                            </div>
                            <div class="col-12">
                                <label for="edit_especificaciones" class="form-label font-monospace fw-bold">Especificaciones (Formato JSON, Opcional)</label>
                                <textarea class="form-control font-monospace" id="edit_especificaciones" name="especificaciones" rows="4" placeholder='{&#10;  "Marca": "Valor"&#10;}' style="font-size: 0.9rem;"></textarea>
                                <div class="form-text">Debe ser un objeto JSON válido entre llaves { } o quedar vacío.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-3 bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="btnGuardarEditar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
