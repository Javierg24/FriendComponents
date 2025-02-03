<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Incluimos los archivos necesarios
include_once '../conection/database.php';
include_once '../model/Categoria.php';

// Crear una conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear una instancia de la clase Categoria
$categoria = new Categoria($db);

// Obtener el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Si la solicitud es GET, se verificará si se pasa un ID para obtener una categoría específica
        if (isset($_GET['id'])) {
            // Obtener una categoría específica por ID
            $categoria->id = $_GET['id'];
            $stmt = $categoria->obtenerCategoriaPorId();
            $categoria_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($categoria_data) {
                echo json_encode($categoria_data);
            } else {
                echo json_encode(["message" => "Categoría no encontrada."]);
            }
        } else {
            // Obtener todas las categorías
            $stmt = $categoria->obtenerCategorias();
            $categorias = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categorias[] = $row;
            }

            echo json_encode($categorias);
        }
        break;

    case 'POST':
        // Si la solicitud es POST, creamos una nueva categoría
        $data = json_decode(file_get_contents("php://input"));

        // Verificamos que los campos necesarios estén presentes
        if (isset($data->nombre) && isset($data->descripcion)) {
            $categoria->nombre = $data->nombre;
            $categoria->descripcion = $data->descripcion;

            if ($categoria->crearCategoria()) {
                echo json_encode(["message" => "Categoría creada exitosamente."]);
            } else {
                echo json_encode(["message" => "No se pudo crear la categoría."]);
            }
        } else {
            echo json_encode(["message" => "Faltan datos para crear la categoría."]);
        }
        break;

    case 'PUT':
        // Si la solicitud es PUT, actualizamos una categoría existente
        $data = json_decode(file_get_contents("php://input"));

        // Verificamos que se haya pasado el ID y los campos necesarios
        if (isset($data->id) && isset($data->nombre) && isset($data->descripcion)) {
            $categoria->id = $data->id;
            $categoria->nombre = $data->nombre;
            $categoria->descripcion = $data->descripcion;

            if ($categoria->actualizarCategoria()) {
                echo json_encode(["message" => "Categoría actualizada exitosamente."]);
            } else {
                echo json_encode(["message" => "No se pudo actualizar la categoría."]);
            }
        } else {
            echo json_encode(["message" => "Faltan datos para actualizar la categoría."]);
        }
        break;

    case 'DELETE':
        // Si la solicitud es DELETE, eliminamos una categoría
        $data = json_decode(file_get_contents("php://input"));

        // Verificamos que se haya pasado el ID de la categoría a eliminar
        if (isset($data->id)) {
            $categoria->id = $data->id;

            if ($categoria->eliminarCategoria()) {
                echo json_encode(["message" => "Categoría eliminada exitosamente."]);
            } else {
                echo json_encode(["message" => "No se pudo eliminar la categoría."]);
            }
        } else {
            echo json_encode(["message" => "Faltan datos para eliminar la categoría."]);
        }
        break;

    default:
        // Si el método HTTP no es reconocido
        echo json_encode(["message" => "Método no permitido."]);
        break;
}
?>
