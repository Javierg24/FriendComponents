<?php
class Usuario {
    private $conn;
    private $table = "Usuarios";

    public $id;
    public $nombre;
    public $correo;
    public $contrasenia;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los usuarios
    public function obtenerUsuarios() {
        $query = "SELECT id, nombre, correo FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un usuario por ID
    public function obtenerUsuarioPorId($id) {
        $query = "SELECT id, nombre, correo FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un usuario
    public function crearUsuario() {
        $query = "INSERT INTO " . $this->table . " (nombre, correo, contrasenia) VALUES (:nombre, :correo, :contrasenia)";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->contrasenia = password_hash($this->contrasenia, PASSWORD_BCRYPT); // Encriptación de la contraseña

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":contrasenia", $this->contrasenia);

        return $stmt->execute();
    }

    // Actualizar un usuario
    public function actualizarUsuario() {
        $query = "UPDATE " . $this->table . " SET nombre = :nombre, correo = :correo WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->correo = htmlspecialchars(strip_tags($this->correo));

        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);

        return $stmt->execute();
    }

    // Actualizar la contraseña de un usuario
    public function actualizarContrasenia() {
        $query = "UPDATE " . $this->table . " SET contrasenia = :contrasenia WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->contrasenia = password_hash($this->contrasenia, PASSWORD_BCRYPT);

        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":contrasenia", $this->contrasenia);

        return $stmt->execute();
    }

    // Eliminar un usuario
    public function eliminarUsuario() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Verificar el login del usuario
    public function verificarUsuario($correo, $contrasenia) {
        $query = "SELECT id, nombre, contrasenia FROM " . $this->table . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contrasenia, $usuario['contrasenia'])) {
            return [
                "id" => $usuario['id'],
                "nombre" => $usuario['nombre']
            ];
        }
        return false;
    }
}
?>
