<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conection/database.php';
include_once '../model/Producto.php';

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Obtener un solo producto por ID
            $id = intval($_GET['id']);
            $resultado = $producto->obtenerProductoPorId($id);
            if ($resultado) {
                echo json_encode($resultado);
            } else {
                echo json_encode(["error" => "Producto no encontrado."]);
            }
        } elseif (isset($_GET['nombreCategoria'])) {
            // Obtener productos por categoría (según el nombre de la categoría)
            $nombre_categoria = $_GET['nombreCategoria'];
            
            $productos = $producto->obtenerProductosPorCategoria($nombre_categoria);
        
            if (!empty($productos)) {
                $productos_arr = array();
                foreach ($productos as $row) {
                    $producto_item = array(
                        "id" => $row['id'],
                        "nombre" => $row['nombre'],
                        "descripcion" => $row['descripcion'],
                        "precio" => $row['precio'],
                        "valoracion" => $row['valoracion'],
                        "imagen" => $row['imagen'],
                        "categoria_nombre" => $nombre_categoria // Ahora devuelve el nombre en lugar del ID
                    );
                    array_push($productos_arr, $producto_item);
                }
                echo json_encode($productos_arr);
            } else {
                echo json_encode(["mensaje" => "No hay productos en esta categoría."]);
            }
        }
        else {
            // Obtener todos los productos
            $stmt = $producto->obtenerProductos();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $productos_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $producto_item = array(
                        "id" => $id,
                        "nombre" => $nombre,
                        "descripcion" => $descripcion,
                        "precio" => $precio,
                        "valoracion" => $valoracion,
                        "imagen" => $imagen,
                        "categoria_id" => $categoria_id
                    );
                    array_push($productos_arr, $producto_item);
                }
                echo json_encode($productos_arr);
            } else {
                echo json_encode(["mensaje" => "No hay productos disponibles."]);
            }
        }
        break;

    case 'POST':
        // Insertar un nuevo producto
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nombre) && !empty($data->descripcion) && !empty($data->precio) && !empty($data->categoria_id)) {
            $producto->nombre = htmlspecialchars(strip_tags($data->nombre));
            $producto->descripcion = htmlspecialchars(strip_tags($data->descripcion));
            $producto->precio = floatval($data->precio);
            $producto->valoracion = isset($data->valoracion) ? floatval($data->valoracion) : 0.0;
            $producto->imagen = htmlspecialchars(strip_tags($data->imagen));
            $producto->categoria_id = intval($data->categoria_id);

            if ($producto->crearProducto()) {
                echo json_encode(["mensaje" => "Producto agregado correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo agregar el producto."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos."]);
        }
        break;

    case 'PUT':
        // Actualizar un producto
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id) && !empty($data->nombre) && !empty($data->descripcion) && !empty($data->precio) && !empty($data->categoria_id)) {
            $producto->id = intval($data->id);
            $producto->nombre = htmlspecialchars(strip_tags($data->nombre));
            $producto->descripcion = htmlspecialchars(strip_tags($data->descripcion));
            $producto->precio = floatval($data->precio);
            $producto->valoracion = isset($data->valoracion) ? floatval($data->valoracion) : 0.0;
            $producto->imagen = htmlspecialchars(strip_tags($data->imagen));
            $producto->categoria_id = intval($data->categoria_id);

            if ($producto->actualizarProducto()) {
                echo json_encode(["mensaje" => "Producto actualizado correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo actualizar el producto."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos."]);
        }
        break;

    case 'DELETE':
        // Eliminar un producto
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $producto->id = intval($data->id);

            if ($producto->eliminarProducto()) {
                echo json_encode(["mensaje" => "Producto eliminado correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo eliminar el producto."]);
            }
        } else {
            echo json_encode(["error" => "ID del producto requerido."]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido."]);
        break;
}
