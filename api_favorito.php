<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/includes/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'error' => 'no_autenticado',
        'mensaje' => 'Debes iniciar sesión para realizar esta acción.'
    ]);
    exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $stmt = $conn->prepare("SELECT id_producto FROM favoritos WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $ids_favoritos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $ids_favoritos[] = (int)$fila['id_producto'];
    }
    $stmt->close();

    echo json_encode([
        'ok' => true,
        'favoritos' => $ids_favoritos
    ]);
    exit;
}

if ($metodo === 'POST') {
    $id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;

    if ($id_producto <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'id_invalido', 'mensaje' => 'ID de producto inválido.']);
        exit;
    }

    // Verificar si ya existe en favoritos
    $stmt_check = $conn->prepare("SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_producto = ?");
    $stmt_check->bind_param("ii", $id_usuario, $id_producto);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $ya_es_favorito = ($res_check->num_rows > 0);
    $stmt_check->close();

    if ($ya_es_favorito) {
        // Toggle: eliminar si ya existía
        $stmt_del = $conn->prepare("DELETE FROM favoritos WHERE id_usuario = ? AND id_producto = ?");
        $stmt_del->bind_param("ii", $id_usuario, $id_producto);
        $stmt_del->execute();
        $stmt_del->close();

        echo json_encode([
            'ok' => true,
            'favorito' => false
        ]);
        exit;
    } else {
        // Toggle: agregar si no existía
        try {
            $stmt_ins = $conn->prepare("INSERT INTO favoritos (id_usuario, id_producto) VALUES (?, ?)");
            $stmt_ins->bind_param("ii", $id_usuario, $id_producto);
            $stmt_ins->execute();
            $stmt_ins->close();

            echo json_encode([
                'ok' => true,
                'favorito' => true
            ]);
            exit;
        } catch (mysqli_sql_exception $e) {
            // Red de seguridad: si falló por restricción UNIQUE de duplicado (carrera de clics)
            if ($e->getCode() === 1062) {
                echo json_encode([
                    'ok' => true,
                    'favorito' => true
                ]);
                exit;
            }
            throw $e;
        }
    }
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'metodo_no_permitido']);
