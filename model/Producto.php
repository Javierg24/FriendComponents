<?php
class Producto
{
    private $conn;
    private $table_name = "Productos";

    public $id;
    public $nombre;
    public $descripcion;
    public $precio;
    public $valoracion;
    public $imagen;
    public $categoria_id;

    // Constructor con la conexión a la base de datos
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Obtener todos los productos
    public function obtenerProductos()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un producto por su ID
    public function obtenerProductoPorId($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener productos por categoría
    public function obtenerProductosPorCategoria($nombre_categoria) {
        $query = "SELECT p.* 
                  FROM " . $this->table_name . " p
                  JOIN categorias c ON p.categoria_id = c.id
                  WHERE c.nombre = :nombre_categoria";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre_categoria", $nombre_categoria, PDO::PARAM_STR);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo con los resultados
    }
    
    

    // Crear un nuevo producto
    public function crearProducto()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, precio, valoracion, imagen, categoria_id) 
                  VALUES (:nombre, :descripcion, :precio, :valoracion, :imagen, :categoria_id)";

        $stmt = $this->conn->prepare($query);

        // Sanitización de datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = floatval($this->precio);
        $this->valoracion = floatval($this->valoracion);
        $this->imagen = htmlspecialchars(strip_tags($this->imagen));
        $this->categoria_id = intval($this->categoria_id);

        // Bind de valores
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":valoracion", $this->valoracion);
        $stmt->bindParam(":imagen", $this->imagen);
        $stmt->bindParam(":categoria_id", $this->categoria_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar producto
    public function actualizarProducto()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, descripcion = :descripcion, precio = :precio, 
                      valoracion = :valoracion, imagen = :imagen, categoria_id = :categoria_id
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitización de datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = floatval($this->precio);
        $this->valoracion = floatval($this->valoracion);
        $this->imagen = htmlspecialchars(strip_tags($this->imagen));
        $this->categoria_id = intval($this->categoria_id);
        $this->id = intval($this->id);

        // Bind de valores
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":valoracion", $this->valoracion);
        $stmt->bindParam(":imagen", $this->imagen);
        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar producto
    public function eliminarProducto()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->id = intval($this->id);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
