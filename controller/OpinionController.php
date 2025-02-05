<?php
  // Importar la clase Opinion

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../conection/database.php';
include_once '../model/Opinion.php';

$database = new Database();
$db = $database->getConnection();
$opinion = new Opinion($db);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Manejo de solicitudes
switch ($method) {
    case 'GET':
        if (isset($_GET['producto_id'])) {
            // Obtener opiniones por producto
            $producto_id = $_GET['producto_id'];
            $opiniones = $opinion->obtenerOpinionesPorProducto($producto_id);
            echo json_encode($opiniones);
        } else {
            // Obtener todas las opiniones
            $opiniones = $opinion->obtenerOpiniones();
            echo json_encode($opiniones);
        }
        break;

    case 'POST':
        // Obtener datos enviados en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data['producto_id']) && !empty($data['persona']) && !empty($data['mensaje']) && isset($data['nota'])) {
            $resultado = $opinion->agregarOpinion($data['producto_id'], $data['persona'], $data['mensaje'], $data['nota']);
            
            if ($resultado) {
                echo json_encode(["mensaje" => "Opinión añadida correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo añadir la opinión."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos."]);
        }
        break;

    case 'PUT':
        // Obtener datos enviados en formato JSON
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data['id']) && !empty($data['mensaje']) && isset($data['nota'])) {
            $resultado = $opinion->actualizarOpinion($data['id'], $data['mensaje'], $data['nota']);
            
            if ($resultado) {
                echo json_encode(["mensaje" => "Opinión actualizada correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo actualizar la opinión."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos."]);
        }
        break;

    case 'DELETE':
        // Obtener el ID de la opinión a eliminar
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!empty($data['id'])) {
            $resultado = $opinion->eliminarOpinion($data['id']);
            
            if ($resultado) {
                echo json_encode(["mensaje" => "Opinión eliminada correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo eliminar la opinión."]);
            }
        } else {
            echo json_encode(["error" => "ID de opinión no proporcionado."]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>
