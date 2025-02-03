<?php

class Categoria {
    private $conn;
    private $table_name = "categorias"; // Nombre de la tabla de categorías

    public $id;
    public $nombre;
    public $descripcion;
    public $imagen;  // Nuevo atributo para la imagen

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para obtener todas las categorías
    public function obtenerCategorias() {
        $query = "SELECT id, nombre, descripcion, imagen FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Método para obtener una categoría por su ID
    public function obtenerCategoriaPorId() {
        $query = "SELECT id, nombre, descripcion, imagen FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Método para crear una nueva categoría
    public function crearCategoria() {
        $query = "INSERT INTO " . $this->table_name . " (nombre, descripcion, imagen) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->imagen = htmlspecialchars(strip_tags($this->imagen)); // Sanitizar imagen

        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        $stmt->bindParam(3, $this->imagen); // Vincular imagen

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para actualizar una categoría
    public function actualizarCategoria() {
        $query = "UPDATE " . $this->table_name . " SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->imagen = htmlspecialchars(strip_tags($this->imagen)); // Sanitizar imagen

        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        $stmt->bindParam(3, $this->imagen); // Vincular imagen
        $stmt->bindParam(4, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para eliminar una categoría
    public function eliminarCategoria() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>
