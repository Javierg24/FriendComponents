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

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Si es una petición GET y se define 'action', verificamos si es GET_CANTIDAD_TOTAL
if ($method === 'GET' && $action === 'GET_CANTIDAD_TOTAL') {
    if (isset($_GET['usuario_id'])) {
        $usuario_id = $_GET['usuario_id'];
        $cantidadTotal = $carrito->obtenerCantidadTotalProductos($usuario_id);
        echo json_encode(["cantidad_total" => $cantidadTotal]);
    } else {
        echo json_encode(["error" => "ID de usuario no proporcionado para obtener la cantidad total de productos."]);
    }
    exit; // Salir para evitar ejecutar el resto del switch
}

switch ($method) {
    case 'GET':
        if (isset($_GET['usuario_id'])) {
            $usuario_id = $_GET['usuario_id'];
            $stmt = $carrito->obtenerCarritoPorUsuario($usuario_id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                $productos_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $producto_item = [
                        "id" => $row['producto_id'],
                        "nombre" => $row['nombre'],
                        "imagen" => $row['imagen'],
                        "cantidad" => $row['cantidad'],
                        "precio" => $row['precio'],
                        "total" => $row['total']
                    ];
                    array_push($productos_arr, $producto_item);
                }
                echo json_encode($productos_arr);
            } else {
                echo json_encode([]);
            }
        } else {
            // Obtener todos los carritos sin necesidad de 'usuario_id'
            $stmt = $carrito->obtenerTodosLosCarritos();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $carritos_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Asegúrate de que las claves en $row coincidan con los nombres de columna
                    $carrito_item = [
                        "carrito_id" => $row['carrito_id'] ?? null,
                        "usuario_id" => $row['usuario_id'] ?? null,
                        "fecha_creado" => $row['creado_en'] ?? null,
                        "usuario" => $row['usuario'] ?? null,
                        "producto" => $row['producto'] ?? null,
                        "cantidad" => $row['cantidad'] ?? null,
                        "total" => $row['total'] ?? null
                    ];
                    array_push($carritos_arr, $carrito_item);
                }
                echo json_encode($carritos_arr);
            } else {
                echo json_encode(["mensaje" => "No hay carritos registrados."]);
            }
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

        // En tu archivo de backend PHP donde eliminas el producto
    case 'DELETE':
        // Intentar obtener datos desde el cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"));

        // Si no se reciben datos en el cuerpo, intentar obtenerlos desde $_GET
        if (empty($data->usuario_id) || empty($data->producto_id)) {
            if (isset($_GET['usuario_id']) && isset($_GET['producto_id'])) {
                // Crear un objeto temporal para simular los datos recibidos
                $data = new stdClass();
                $data->usuario_id = $_GET['usuario_id'];
                $data->producto_id = $_GET['producto_id'];
            }
        }

        if (!empty($data->usuario_id) && !empty($data->producto_id)) {
            $carrito->usuario_id = $data->usuario_id;
            $carrito->producto_id = $data->producto_id;

            // Log para ver los datos recibidos
            error_log("Eliminar producto: usuario_id = " . $data->usuario_id . " producto_id = " . $data->producto_id);

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

        if (!empty($data->usuario_id) && !empty($data->producto_id) && isset($data->cantidad)) {
            $carrito->usuario_id = $data->usuario_id;
            $carrito->producto_id = $data->producto_id;
            $carrito->cantidad = $data->cantidad;

            // Verificar la cantidad y tomar decisiones
            if ($carrito->cantidad <= 0) {
                // Si la cantidad es 0 o menor, eliminar el producto del carrito
                if ($carrito->eliminarDelCarrito()) {
                    echo json_encode(["mensaje" => "Producto eliminado del carrito."]);
                } else {
                    echo json_encode(["error" => "No se pudo eliminar el producto del carrito."]);
                }
            } else {
                // Si el producto ya existe en el carrito, actualizar la cantidad
                if ($carrito->productoExisteEnCarrito()) {
                    if ($carrito->actualizarCantidadProducto()) {
                        echo json_encode(["mensaje" => "Cantidad de producto actualizada en el carrito."]);
                    } else {
                        echo json_encode(["error" => "No se pudo actualizar la cantidad del producto en el carrito."]);
                    }
                } else {
                    // Si el producto no existe, agregarlo al carrito
                    if ($carrito->agregarAlCarrito()) {
                        echo json_encode(["mensaje" => "Producto agregado al carrito."]);
                    } else {
                        echo json_encode(["error" => "No se pudo agregar el producto al carrito."]);
                    }
                }
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
}
