<?php
/**
 * API para actualizar productos.
 * Soporta actualización rápida desde la grilla (precio, oferta, stock, imagen)
 * y actualización completa desde el modal (nombre, descripción, especificaciones, etc.).
 */

session_start();
include('includes/conexion.php');

// Asegurar que la respuesta sea JSON
header('Content-Type: application/json; charset=utf-8');

// 1. Control de acceso para administradores
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Inicie sesión.']);
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT es_admin FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$user || empty($user['es_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Se requiere cuenta de administrador.']);
    exit();
}

// 2. Comprobar ID de producto
if (!isset($_POST['id_producto'])) {
    echo json_encode(['status' => 'error', 'message' => 'El ID del producto es obligatorio.']);
    exit();
}

$id_producto = (int)$_POST['id_producto'];

// Campos comunes
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0.0;
$oferta = (isset($_POST['oferta']) && trim($_POST['oferta']) !== '') ? (float)$_POST['oferta'] : null;
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
$imagen = isset($_POST['imagen']) ? trim($_POST['imagen']) : '';
$agotado = ($stock <= 0) ? 1 : 0;

// Validar que el producto exista en la BD antes de actualizar
$check = $conn->prepare('SELECT id FROM productos WHERE id = ?');
$check->bind_param('i', $id_producto);
$check->execute();
$check_res = $check->get_result();
if ($check_res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'El producto especificado no existe.']);
    $check->close();
    exit();
}
$check->close();

// 3. Ejecutar actualización según los datos enviados
if (isset($_POST['nombre']) && isset($_POST['descripcion'])) {
    // Actualización completa (desde el Modal de edición)
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $especificaciones = (isset($_POST['especificaciones']) && trim($_POST['especificaciones']) !== '') ? trim($_POST['especificaciones']) : null;

    if (empty($nombre)) {
        echo json_encode(['status' => 'error', 'message' => 'El nombre del producto es obligatorio.']);
        exit();
    }

    if (empty($imagen)) {
        echo json_encode(['status' => 'error', 'message' => 'La ruta o URL de la imagen es obligatoria.']);
        exit();
    }

    // Validar formato JSON para especificaciones si no está vacío
    if ($especificaciones !== null) {
        json_decode($especificaciones);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'Las especificaciones deben ser un formato JSON válido (ej: {"Color": "Negro", "Marca": "ASUS"}).']);
            exit();
        }
    }

    $stmt_upd = $conn->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, oferta = ?, stock = ?, agotado = ?, imagen = ?, especificaciones = ? WHERE id = ?');
    $stmt_upd->bind_param('ssddisssi', $nombre, $descripcion, $precio, $oferta, $stock, $agotado, $imagen, $especificaciones, $id_producto);
} else {
    // Actualización básica (desde la tabla inline)
    if (empty($imagen)) {
        echo json_encode(['status' => 'error', 'message' => 'La ruta de la imagen no puede estar vacía.']);
        exit();
    }

    $stmt_upd = $conn->prepare('UPDATE productos SET precio = ?, oferta = ?, stock = ?, agotado = ?, imagen = ? WHERE id = ?');
    $stmt_upd->bind_param('ddisii', $precio, $oferta, $stock, $agotado, $imagen, $id_producto);
}

if ($stmt_upd->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Producto guardado exitosamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar los cambios: ' . $stmt_upd->error]);
}

$stmt_upd->close();
$conn->close();
?>
