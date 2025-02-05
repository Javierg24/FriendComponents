<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conection/database.php';
include_once '../model/Carrito.php';

$database = new Database();
$db = $database->getConnection();
$carrito = new Carrito($db);

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['usuario_id'])) {
            // Obtener los productos en el carrito de un usuario
            $usuario_id = $_GET['usuario_id'];
            $stmt = $carrito->obtenerCarritoPorUsuario($usuario_id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                $productos_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $producto_item = [
                        "id" => $id,
                        "nombre" => $nombre,
                        "cantidad" => $cantidad,
                        "precio" => $precio,
                        "total" => $cantidad * $precio
                    ];
                    array_push($productos_arr, $producto_item);
                }
                echo json_encode($productos_arr);
            } else {
                echo json_encode(["mensaje" => "Carrito vacío."]);
            }
        } else {
            echo json_encode(["error" => "ID de usuario no proporcionado."]);
        }
        break;

    case 'POST':
        // Agregar un producto al carrito
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->usuario_id) && !empty($data->producto_id) && !empty($data->cantidad)) {
            $carrito->usuario_id = $data->usuario_id;
            $carrito->producto_id = $data->producto_id;
            $carrito->cantidad = $data->cantidad;

            if ($carrito->agregarAlCarrito()) {
                echo json_encode(["mensaje" => "Producto agregado al carrito."]);
            } else {
                echo json_encode(["error" => "No se pudo agregar el producto al carrito."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos para agregar el producto al carrito."]);
        }
        break;

    case 'DELETE':
        // Eliminar un producto del carrito
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->usuario_id) && !empty($data->producto_id)) {
            $carrito->usuario_id = $data->usuario_id;
            $carrito->producto_id = $data->producto_id;

            if ($carrito->eliminarDelCarrito()) {
                echo json_encode(["mensaje" => "Producto eliminado del carrito."]);
            } else {
                echo json_encode(["error" => "No se pudo eliminar el producto del carrito."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos para eliminar el producto del carrito."]);
        }
        break;

    case 'PUT':
        // Actualizar la cantidad de un producto en el carrito
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->usuario_id) && !empty($data->producto_id) && !empty($data->cantidad)) {
            $carrito->usuario_id = $data->usuario_id;
            $carrito->producto_id = $data->producto_id;
            $carrito->cantidad = $data->cantidad;

            if ($carrito->agregarAlCarrito()) {
                echo json_encode(["mensaje" => "Cantidad de producto actualizada en el carrito."]);
            } else {
                echo json_encode(["error" => "No se pudo actualizar la cantidad del producto en el carrito."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos para actualizar la cantidad."]);
        }
        break;

    case 'CALCULAR_TOTAL':
        // Calcular el total del carrito de un usuario
        if (isset($_GET['usuario_id'])) {
            $usuario_id = $_GET['usuario_id'];
            $total = $carrito->calcularTotal($usuario_id);

            if ($total !== null) {
                echo json_encode(["total" => $total]);
            } else {
                echo json_encode(["error" => "No se pudo calcular el total del carrito."]);
            }
        } else {
            echo json_encode(["error" => "ID de usuario no proporcionado para calcular el total."]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido."]);
        break;
}
?>
