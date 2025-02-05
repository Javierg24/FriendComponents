<?php

class Opinion {
    private $conn;
    private $table = "Opiniones";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 🔹 1. Obtener todas las opiniones
    public function obtenerOpiniones() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 2. Obtener opiniones de un producto por su ID
    public function obtenerOpinionesPorProducto($producto_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE producto_id = :producto_id ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $producto_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 3. Agregar una nueva opinión
    public function agregarOpinion($producto_id, $persona, $mensaje, $nota) {
        $query = "INSERT INTO Opiniones (producto_id, nombre, comentario, valoracion, fecha) 
                  VALUES (:producto_id, :persona, :mensaje, :nota, NOW())";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $producto_id);
        $stmt->bindParam(':persona', $persona);
        $stmt->bindParam(':mensaje', $mensaje);
        $stmt->bindParam(':nota', $nota);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    

    // 🔹 4. Actualizar una opinión existente
    public function actualizarOpinion($id, $mensaje, $nota) {
        $query = "UPDATE " . $this->table . " SET mensaje = :mensaje, nota = :nota WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
        $stmt->bindParam(':nota', $nota, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // 🔹 5. Eliminar una opinión
    public function eliminarOpinion($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
