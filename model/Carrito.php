<?php
class Carrito
{
    private $conn;
    private $table = "Carrito"; // Tabla Carrito
    private $productosTable = "Productos"; // Tabla Productos
    private $productosCarritoTable = "Productos_Carrito"; // Tabla Productos_Carrito

    public $id;
    public $usuario_id;
    public $producto_id;
    public $cantidad;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Obtener los productos del carrito de un usuario
    public function obtenerCarritoPorUsuario($usuario_id)
    {
        $query = "SELECT c.id AS carrito_id, 
                         p.id AS producto_id, 
                         p.nombre, 
                         p.imagen, 
                         c.cantidad, 
                         p.precio, 
                         (c.cantidad * p.precio) AS total
                  FROM " . $this->productosCarritoTable . " c
                  JOIN " . $this->productosTable . " p ON c.producto_id = p.id
                  WHERE c.carrito_id = (SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }


    // Obtener todos los carritos y sus productos
    public function obtenerTodosLosCarritos()
    {
        // Consulta para obtener todos los carritos de los usuarios junto con los productos
        $query = "SELECT 
                    ca.id AS carrito_id, 
                    ca.usuario_id, 
                    ca.creado_en, 
                    u.nombre AS usuario, 
                    p.nombre AS producto, 
                    c.cantidad, 
                    (c.cantidad * p.precio) AS total
                  FROM " . $this->productosCarritoTable . " c
                  JOIN " . $this->productosTable . " p ON c.producto_id = p.id
                  JOIN " . $this->table . " ca ON c.carrito_id = ca.id
                  JOIN Usuarios u ON ca.usuario_id = u.id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }



    // Agregar un producto al carrito
    public function agregarAlCarrito()
    {
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
    public function eliminarDelCarrito()
    {
        // Obtener el carrito_id para el usuario
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$carrito) {
            return json_encode(["error" => "Carrito no encontrado para el usuario"]);
        }

        $carrito_id = $carrito['id'];

        // Verificar si el producto realmente existe en el carrito
        $query = "SELECT * FROM " . $this->productosCarritoTable . " WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Eliminar el producto del carrito
            $query = "DELETE FROM " . $this->productosCarritoTable . " WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
            $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return json_encode(["mensaje" => "Producto eliminado del carrito."]);
            } else {
                return json_encode(["error" => "No se pudo eliminar el producto del carrito."]);
            }
        } else {
            return json_encode(["error" => "Producto no encontrado en el carrito."]);
        }
    }



    // Calcular el total del carrito
    public function calcularTotal($usuario_id)
    {
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

    // Actualizar la cantidad de un producto en el carrito
    public function actualizarCantidadProducto()
    {
        // Obtener el carrito_id para el usuario
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$carrito) {
            error_log("No se encontró carrito para usuario_id: " . $this->usuario_id);
            return false;
        }

        $carrito_id = $carrito['id'];
        // Depuración: Loguear los valores que se van a usar
        error_log("Actualizar cantidad: carrito_id = " . $carrito_id .
            ", producto_id = " . $this->producto_id .
            ", cantidad = " . $this->cantidad);

        // Actualizar la cantidad del producto en la tabla Productos_Carrito
        $query = "UPDATE " . $this->productosCarritoTable . " 
                  SET cantidad = :cantidad 
                  WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cantidad", $this->cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);

        return $stmt->execute();
    }


    public function productoExisteEnCarrito()
    {
        // 1. Obtener el carrito_id para el usuario desde la tabla "Carrito"
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $this->usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$carrito) {
            // No existe carrito para el usuario
            return false;
        }

        $carrito_id = $carrito['id'];

        // 2. Verificar en la tabla "Productos_Carrito" si existe el producto
        $query = "SELECT id FROM " . $this->productosCarritoTable . " 
                  WHERE carrito_id = :carrito_id AND producto_id = :producto_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->bindParam(":producto_id", $this->producto_id, PDO::PARAM_INT);
        $stmt->execute();

        return ($stmt->rowCount() > 0);
    }

    public function obtenerCantidadTotalProductos($usuario_id)
    {
        // Obtener el id del carrito para el usuario
        $query = "SELECT id FROM " . $this->table . " WHERE usuario_id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $carrito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$carrito) {
            return 0; // Si no hay carrito, la cantidad total es 0
        }

        $carrito_id = $carrito['id'];

        // Sumar la cantidad total de productos en el carrito
        $query = "SELECT SUM(cantidad) AS cantidad_total FROM " . $this->productosCarritoTable . " WHERE carrito_id = :carrito_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":carrito_id", $carrito_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['cantidad_total'] ?? 0;
    }
}
