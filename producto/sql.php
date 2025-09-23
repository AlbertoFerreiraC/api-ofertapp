<?php
include_once '../db.php';

class Sql extends DB
{
  // ================== LISTAR ==================
  function listarProductos()
  {
    $sql = "SELECT p.idProducto, p.titulo, p.descripcion, p.cantidad, p.costo, 
                   p.color, p.tamano, p.estado, p.condicion, p.imagen,
                   c.descripcion AS categoria,
                   e.nombre AS empresa
            FROM Producto p
            INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
            INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
            WHERE p.estado = 'activo';";
    $q = $this->connect()->prepare($sql);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  function listarProductosDetalle()
  {
    $sql = "SELECT 
                p.idProducto,
                p.titulo,
                p.descripcion,
                p.costo,
                p.imagen,
                c.descripcion AS categoria,
                e.nombre AS empresa,
                d.calle,
                d.numero,
                d.barrio,
                d.ciudad,
                d.departamento,
                d.pais,
                g.latitud,
                g.longitud
            FROM Producto p
            LEFT JOIN Categoria c     ON c.idCategoria = p.Categoria_idCategoria
            LEFT JOIN Empresa e       ON e.idEmpresa = p.Empresa_idEmpresa
            LEFT JOIN direccion d     ON d.Empresa_idEmpresa = e.idEmpresa
            LEFT JOIN georeferencia g ON g.direccion_iddireccion = d.iddireccion
            WHERE p.estado = 'activo'";

    $query = $this->connect()->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
  }
  
  // ================== VERIFICAR DUPLICADO ==================
  function verificar_existencia_producto($item)
  {
    $sql = "SELECT * FROM Producto 
            WHERE titulo = :titulo 
              AND Empresa_idEmpresa = :empresa_id 
              AND estado = 'activo'";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":titulo", $item['titulo']);
    $q->bindParam(":empresa_id", $item['empresa_id']);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  // ================== AGREGAR ==================
  function agregarProducto($item)
  {
    $pdo = $this->connect();
    $sql = "INSERT INTO Producto 
           (Empresa_idEmpresa, Categoria_idCategoria, titulo, descripcion, cantidad, costo, color, tamano, estado, condicion, imagen)
        VALUES 
           (:empresa, :categoria, :titulo, :descripcion, :cantidad, :costo, :color, :tamano, :estado, :condicion, :imagen)";
    $q = $pdo->prepare($sql);

    $q->bindParam(":empresa", $item['Empresa_idEmpresa'], PDO::PARAM_INT);
    $q->bindParam(":categoria", $item['Categoria_idCategoria'], PDO::PARAM_INT);
    $q->bindParam(":titulo", $item['titulo']);
    $q->bindParam(":descripcion", $item['descripcion']);
    $q->bindParam(":cantidad", $item['cantidad'], PDO::PARAM_INT);
    $q->bindParam(":costo", $item['costo'], PDO::PARAM_INT);
    $q->bindParam(":color", $item['color']);
    $q->bindParam(":tamano", $item['tamano']);
    $q->bindParam(":estado", $item['estado']);
    $q->bindParam(":condicion", $item['condicion']);
    $q->bindParam(":imagen", $item['imagen']);
    $q->execute();

    return $pdo->lastInsertId();
  }

  // ================== OBTENER ==================
  function obtenerDatosParaModificarProducto($item)
  {
    $sql = "SELECT * FROM Producto WHERE idProducto = :idProducto";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":idProducto", $item['idProducto']);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  // ================== MODIFICAR ==================
  function modificarProducto($item)
  {
    $sql = "UPDATE Producto 
            SET Categoria_idCategoria = :categoria,
                titulo = :titulo,
                descripcion = :descripcion,
                cantidad = :cantidad,
                costo = :costo,
                color = :color,
                tamano = :tamano,
                estado = :estado,
                condicion = :condicion,
                imagen = :imagen
            WHERE idProducto = :idProducto";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":categoria", $item['categoria_id']);
    $q->bindParam(":titulo", $item['titulo']);
    $q->bindParam(":descripcion", $item['descripcion']);
    $q->bindParam(":cantidad", $item['cantidad']);
    $q->bindParam(":costo", $item['costo']);
    $q->bindParam(":color", $item['color']);
    $q->bindParam(":tamano", $item['tamano']);
    $q->bindParam(":estado", $item['estado']);
    $q->bindParam(":condicion", $item['condicion']);
    $q->bindParam(":imagen", $item['imagen']);
    $q->bindParam(":idProducto", $item['idProducto']);
    $q->execute();
    return "ok";
  }

  // ================== ELIMINAR ==================
  function eliminarProducto($item)
  {
    $sql = "UPDATE Producto SET estado = 'inactivo' WHERE idProducto = :idProducto";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":idProducto", $item['idProducto']);
    if ($q->execute()) {
      return "ok";
    } else {
      return "nok";
    }
  }
}
