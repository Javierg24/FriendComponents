<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

include_once '../conection/database.php';
include_once '../model/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Obtener un usuario por ID
            $usuarioEncontrado = $usuario->obtenerUsuarioPorId($_GET['id']);
            if ($usuarioEncontrado) {
                echo json_encode($usuarioEncontrado);
            } else {
                echo json_encode(["mensaje" => "Usuario no encontrado."]);
            }
        } elseif (isset($_GET['correo']) && isset($_GET['contrasenia'])) {
            // Verificar login
            $correo = htmlspecialchars(strip_tags($_GET['correo']));
            $contrasenia = $_GET['contrasenia']; 

            $usuarioLogin = $usuario->verificarUsuario($correo, $contrasenia);
            if ($usuarioLogin) {
                echo json_encode(["mensaje" => "Login exitoso", "usuario" => $usuarioLogin]);
            } else {
                echo json_encode(["error" => "Correo o contraseña incorrectos"]);
            }
        } else {
            // Obtener todos los usuarios
            $stmt = $usuario->obtenerUsuarios();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $usuarios_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $usuario_item = [
                        "id" => $id,
                        "nombre" => $nombre,
                        "correo" => $correo
                    ];
                    array_push($usuarios_arr, $usuario_item);
                }
                echo json_encode($usuarios_arr);
            } else {
                echo json_encode(["mensaje" => "No hay usuarios registrados."]);
            }
        }
        break;

    case 'POST':
        // Insertar un nuevo usuario
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nombre) && !empty($data->correo) && !empty($data->contrasenia)) {
            $usuario->nombre = htmlspecialchars(strip_tags($data->nombre));
            $usuario->correo = htmlspecialchars(strip_tags($data->correo));
            $usuario->contrasenia = $data->contrasenia; // La clase Usuario se encarga de encriptarla

            if ($usuario->crearUsuario()) {
                echo json_encode(["mensaje" => "Usuario registrado correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo registrar el usuario."]);
            }
        } else {
            echo json_encode(["error" => "Datos incompletos."]);
        }
        break;

    case 'PUT':
        // Actualizar un usuario
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $usuario->id = intval($data->id);
            if (!empty($data->nombre)) $usuario->nombre = htmlspecialchars(strip_tags($data->nombre));
            if (!empty($data->correo)) $usuario->correo = htmlspecialchars(strip_tags($data->correo));
            if (!empty($data->contrasenia)) $usuario->contrasenia = $data->contrasenia;

            if (isset($data->contrasenia) && !empty($data->contrasenia)) {
                // Actualizar solo la contraseña
                if ($usuario->actualizarContrasenia()) {
                    echo json_encode(["mensaje" => "Contraseña actualizada correctamente."]);
                } else {
                    echo json_encode(["error" => "No se pudo actualizar la contraseña."]);
                }
            } else {
                // Actualizar nombre y correo
                if ($usuario->actualizarUsuario()) {
                    echo json_encode(["mensaje" => "Usuario actualizado correctamente."]);
                } else {
                    echo json_encode(["error" => "No se pudo actualizar el usuario."]);
                }
            }
        } else {
            echo json_encode(["error" => "ID de usuario no proporcionado."]);
        }
        break;

    case 'DELETE':
        // Eliminar un usuario
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $usuario->id = intval($data->id);

            if ($usuario->eliminarUsuario()) {
                echo json_encode(["mensaje" => "Usuario eliminado correctamente."]);
            } else {
                echo json_encode(["error" => "No se pudo eliminar el usuario."]);
            }
        } else {
            echo json_encode(["error" => "ID de usuario no proporcionado."]);
        }
        break;

    default:
        echo json_encode(["error" => "Método no permitido."]);
        break;
}
?>
