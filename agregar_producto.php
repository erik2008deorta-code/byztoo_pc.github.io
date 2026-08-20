11<?php
/**
 * API para crear un nuevo producto en la base de datos.
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

// 2. Comprobar campos obligatorios
if (!isset($_POST['nombre']) || !isset($_POST['precio']) || !isset($_POST['imagen'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios (nombre, precio o imagen).']);
    exit();
}

$nombre = trim($_POST['nombre']);
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$precio = (float)$_POST['precio'];
$oferta = (isset($_POST['oferta']) && trim($_POST['oferta']) !== '') ? (float)$_POST['oferta'] : null;
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
$imagen = trim($_POST['imagen']);
$agotado = ($stock <= 0) ? 1 : 0;
$especificaciones = (isset($_POST['especificaciones']) && trim($_POST['especificaciones']) !== '') ? trim($_POST['especificaciones']) : null;

// Validar campos obligatorios
if (empty($nombre)) {
    echo json_encode(['status' => 'error', 'message' => 'El nombre del producto es obligatorio.']);
    exit();
}

if ($precio <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'El precio debe ser un número mayor que 0.']);
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
        echo json_encode(['status' => 'error', 'message' => 'Las especificaciones deben tener un formato JSON válido (ejemplo: {"Procesador": "Ryzen 5", "RAM": "16GB"}).']);
        exit();
    }
}

// 3. Ejecutar inserción en la BD
$stmt_ins = $conn->prepare('INSERT INTO productos (nombre, descripcion, precio, oferta, stock, agotado, imagen, especificaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt_ins->bind_param('ssddisss', $nombre, $descripcion, $precio, $oferta, $stock, $agotado, $imagen, $especificaciones);

if ($stmt_ins->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Producto creado exitosamente.',
        'id' => $stmt_ins->insert_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al crear el producto: ' . $stmt_ins->error]);
}

$stmt_ins->close();
$conn->close();
?>
