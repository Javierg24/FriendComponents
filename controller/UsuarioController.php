<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

session_start();

include_once '../conection/database.php';
include_once '../model/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $usuarioEncontrado = $usuario->obtenerUsuarioPorId($_GET['id']);
            echo json_encode($usuarioEncontrado ?: ["mensaje" => "Usuario no encontrado."]);
        } elseif (isset($_GET['correo']) && isset($_GET['contrasenia'])) {
            $correo = htmlspecialchars(strip_tags($_GET['correo']));
            $contrasenia = $_GET['contrasenia'];

            $usuarioLogin = $usuario->verificarUsuario($correo, $contrasenia);
            if ($usuarioLogin) {
                $_SESSION['user'] = $usuarioLogin;

                setcookie('user', json_encode($usuarioLogin), time() + 86400, "/", "localhost", false, true);

                echo json_encode(["mensaje" => "Login exitoso", "usuario" => $usuarioLogin]);
            } else {
                echo json_encode(["error" => "Correo o contraseña incorrectos"]);
            }
        } elseif (isset($_GET['auth'])) {
            // Verificar si el usuario tiene sesión activa
            if (isset($_SESSION['user'])) {
                echo json_encode(["autenticado" => true, "usuario" => $_SESSION['user']]);
            } else {
                echo json_encode(["autenticado" => false]);
            }
        } else {
            $stmt = $usuario->obtenerUsuarios();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $usuarios_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    array_push($usuarios_arr, ["id" => $id, "nombre" => $nombre, "correo" => $correo]);
                }
                echo json_encode($usuarios_arr);
            } else {
                echo json_encode(["mensaje" => "No hay usuarios registrados."]);
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->nombre) && !empty($data->correo) && !empty($data->contrasenia)) {
            $usuario->nombre = htmlspecialchars(strip_tags($data->nombre));
            $usuario->correo = htmlspecialchars(strip_tags($data->correo));
            $usuario->contrasenia = $data->contrasenia;

            echo json_encode($usuario->crearUsuario() ? ["mensaje" => "Usuario registrado correctamente."] : ["error" => "No se pudo registrar el usuario."]);
        } else {
            echo json_encode(["error" => "Datos incompletos."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $usuario->id = intval($data->id);
            if (!empty($data->nombre)) $usuario->nombre = htmlspecialchars(strip_tags($data->nombre));
            if (!empty($data->correo)) $usuario->correo = htmlspecialchars(strip_tags($data->correo));
            if (!empty($data->contrasenia)) $usuario->contrasenia = $data->contrasenia;

            echo json_encode(
                isset($data->contrasenia) && !empty($data->contrasenia)
                ? ($usuario->actualizarContrasenia() ? ["mensaje" => "Contraseña actualizada."] : ["error" => "No se pudo actualizar la contraseña."])
                : ($usuario->actualizarUsuario() ? ["mensaje" => "Usuario actualizado."] : ["error" => "No se pudo actualizar el usuario."])
            );
        } else {
            echo json_encode(["error" => "ID de usuario no proporcionado."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $usuario->id = intval($data->id);
            echo json_encode($usuario->eliminarUsuario() ? ["mensaje" => "Usuario eliminado."] : ["error" => "No se pudo eliminar el usuario."]);
        } else {
            echo json_encode(["error" => "ID de usuario no proporcionado."]);
        }
        break;

    case 'OPTIONS':
        http_response_code(200);
        break;

    default:
        echo json_encode(["error" => "Método no permitido."]);
        break;
}
?>
