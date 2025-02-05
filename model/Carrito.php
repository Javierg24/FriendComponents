<?php
class Carrito {
    private $conn;
    private $table = "Carrito"; // Tabla Carrito
    private $productosTable = "Productos"; // Tabla Productos
    private $productosCarritoTable = "Productos_Carrito"; // Tabla Productos_Carrito

    public $id;
    public $usuario_id;
    public $producto_id;
    public $cantidad;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener los productos del carrito de un usuario
    public function obtenerCarritoPorUsuario($usuario_id) {
        $query = "SELECT p.nombre, c.cantidad, p.precio, (c.cantidad * p.precio) AS total
                  FROM " . $this->productosCarritoTable . " c
                  JOIN " . $this->productosTable . " p ON c.producto_id = p.id
                  WHERE c.carrito_id = (SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Agregar un producto al carrito
    public function agregarAlCarrito() {
        // Verificar si el carrito ya existe para el usuario
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        $carrito_id = null;
        if ($stmt->rowCount() > 0) {
            // Si el carrito existe, obtenemos el id
            $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
            $carrito_id = $carrito['id'];
        } else {
            // Si no existe un carrito para el usuario, lo creamos
            $query = "INSERT INTO " . $this->table . " (usuario_id) VALUES (:usuario_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            $carrito_id = $this->conn->lastInsertId();
        }

        // Verificar si el producto ya está en el carrito
        $query = "SELECT id FROM " . $this->productosCarritoTable . " WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Si el producto ya está en el carrito, actualizar la cantidad
            $query = "UPDATE " . $this->productosCarritoTable . " SET cantidad = cantidad + :cantidad WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
            $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);
            $stmt->bindParam(":cantidad", $this->cantidad, PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            // Si el producto no está en el carrito, agregarlo
            $query = "INSERT INTO " . $this->productosCarritoTable . " (carrito_id, producto_id, cantidad) VALUES (:carrito_id, :producto_id, :cantidad)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
            $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);
            $stmt->bindParam(":cantidad", $this->cantidad, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }

    // Eliminar un producto del carrito
    public function eliminarDelCarrito() {
        // Obtener el carrito_id para el usuario
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
        $carrito_id = $carrito['id'];

        // Eliminar el producto del carrito
        $query = "DELETE FROM " . $this->productosCarritoTable . " WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Calcular el total del carrito
    public function calcularTotal($usuario_id) {
        // Obtener el id del carrito para el usuario
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
        $carrito_id = $carrito['id'];

        // Calcular el total del carrito
        $query = "SELECT SUM(p.precio * c.cantidad) AS total
                  FROM " . $this->productosCarritoTable . " c
                  JOIN " . $this->productosTable . " p ON c.producto_id = p.id
                  WHERE c.carrito_id = :carrito_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>
