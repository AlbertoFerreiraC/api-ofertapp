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

  function listarProductosPorUsuario($idUsuario)
  {
    $db = $this->connect();

    $sql = "
        SELECT 
            p.idProducto,
            p.titulo,
            p.descripcion,
            p.costo,
            p.imagen,
            c.descripcion AS categoria,
            e.nombre AS empresa,
            MAX(g.latitud) AS latitud,
            MAX(g.longitud) AS longitud,
            COALESCE(AVG(r.calificacion), 0) AS rating
        FROM dosisma_ofertapp.Producto p
        INNER JOIN dosisma_ofertapp.Empresa e 
            ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN dosisma_ofertapp.Categoria c 
            ON p.Categoria_idCategoria = c.idCategoria
        LEFT JOIN dosisma_ofertapp.resena r 
            ON r.Producto_idProducto = p.idProducto
        LEFT JOIN dosisma_ofertapp.georeferencia g 
            ON g.Empresa_idEmpresa = e.idEmpresa
        WHERE e.Usuario_id_usuario = :idUsuario
          AND e.estado = 'activo'
          AND p.estado = 'activo'
        GROUP BY 
            p.idProducto, p.titulo, p.descripcion, p.costo, p.imagen,
            c.descripcion, e.nombre
        ORDER BY p.idProducto DESC
    ";

    $q = $db->prepare($sql);
    $q->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $q->execute();

    return $q->fetchAll(PDO::FETCH_ASSOC);
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
           (Empresa_idEmpresa, Categoria_idCategoria, titulo, descripcion, cantidad, costo, color, tamano, estado, condicion, imagen, en_oferta)
        VALUES 
           (:empresa, :categoria, :titulo, :descripcion, :cantidad, :costo, :color, :tamano, :estado, :condicion, :imagen, :en_oferta)";
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
    $q->bindParam(":en_oferta", $item['en_oferta']);
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
                imagen = :imagen,
                en_oferta = :en_oferta
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
    $q->bindParam(":en_oferta", $item['en_oferta']);
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

  // ================== DETALLE ==================
  function obtenerProducto($id)
  {
    $sql = "SELECT 
          p.idProducto,
          p.titulo,
          p.descripcion,
          p.costo AS precio,
          p.color,
          p.tamano,
          p.condicion,
          p.imagen,
          c.descripcion AS categoria,
          e.idEmpresa,
          p.cantidad,
          e.nombre AS empresa
      FROM Producto p
      INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
      INNER JOIN Empresa e   ON p.Empresa_idEmpresa = e.idEmpresa
      WHERE p.idProducto = :id
        AND p.estado = 'activo'
      LIMIT 1
      ";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":id", $id, PDO::PARAM_INT);
    $q->execute();
    return $q->fetch(PDO::FETCH_ASSOC);
  }

  // ================== DIRECCION ==================
  function obtenerDireccionEmpresa($empresaId)
  {
    $sql = "SELECT 
                calle, numero, barrio, ciudad, departamento, pais
            FROM direccion
            WHERE Empresa_idEmpresa = :empresaId
              AND estado = 'activo'
            LIMIT 1";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":empresaId", $empresaId, PDO::PARAM_INT);
    $q->execute();
    return $q->fetch(PDO::FETCH_ASSOC);
  }

  // ================== GEOREFERENCIA ==================
  function obtenerGeoreferencia($empresaId)
  {
    $sql = "SELECT latitud, longitud
            FROM georeferencia
            WHERE Empresa_idEmpresa = :empresaId
            LIMIT 1";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":empresaId", $empresaId, PDO::PARAM_INT);
    $q->execute();
    return $q->fetch(PDO::FETCH_ASSOC);
  }

  // ================== CONTACTO ==================
  function obtenerContactosEmpresa($empresaId)
  {
    $sql = "SELECT telefono, correo
            FROM contacto
            WHERE Empresa_idEmpresa = :empresaId
              AND estado = 'activo'";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":empresaId", $empresaId, PDO::PARAM_INT);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  // ================== RESEÑAS Y COMENTARIOS ==================
  function obtenerResenasProducto($productoId)
  {
    $sql = "SELECT r.idresena, r.calificacion, r.fecha_agregado,
                   c.comentario, c.fecha_agregado AS fecha_comentario
            FROM resena r
            LEFT JOIN comentario c ON c.resena_idresena = r.idresena
            WHERE r.Producto_idProducto = :productoId";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":productoId", $productoId, PDO::PARAM_INT);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  // ================== AGREGAR RESEÑA Y COMENTARIO ==================
  function agregarResena($data)
  {
    $db = $this->connect();

    try {
      $db->beginTransaction();

      // Insertar reseña con usuario
      $sql = "INSERT INTO resena (Empresa_idEmpresa, Producto_idProducto, Usuario_id_usuario, calificacion, fecha_agregado)
                VALUES (
                    (SELECT Empresa_idEmpresa FROM Producto WHERE idProducto = :producto_id),
                    :producto_id,
                    :usuario_id,
                    :calificacion,
                    NOW()
                )";

      $q = $db->prepare($sql);
      $q->bindParam(":producto_id", $data['producto_id'], PDO::PARAM_INT);
      $q->bindParam(":usuario_id", $data['id_usuario'], PDO::PARAM_INT);
      $q->bindParam(":calificacion", $data['calificacion'], PDO::PARAM_INT);

      if ($q->execute()) {
        $idResena = $db->lastInsertId();

        // Si hay comentario, lo insertamos
        if (!empty($data['comentario'])) {
          $sqlC = "INSERT INTO comentario (resena_idresena, comentario, fecha_agregado)
                         VALUES (:idResena, :comentario, NOW())";
          $qc = $db->prepare($sqlC);
          $qc->bindParam(":idResena", $idResena, PDO::PARAM_INT);
          $qc->bindParam(":comentario", $data['comentario']);
          $qc->execute();
        }

        $db->commit();
        return "ok";
      } else {
        $db->rollBack();
        return "nok";
      }
    } catch (PDOException $e) {
      $db->rollBack();
      error_log("Error al agregar reseña: " . $e->getMessage());
      return "nok";
    }
  }

  // ================== LISTAR POR USUARIO ACTIVO ==================
  function listarProductosPorUsuarioActivo($idUsuario)
  {
    $db = $this->connect();

    $sql = "
        SELECT 
            p.idProducto,
            p.titulo,
            p.descripcion,
            p.cantidad,
            p.costo,
            p.color,
            p.tamano,
            p.condicion,
            p.estado,
            p.imagen,
            c.descripcion AS categoria,
            e.nombre AS empresa,
            MAX(g.latitud) AS latitud,
            MAX(g.longitud) AS longitud,
            COALESCE(AVG(r.calificacion), 0) AS rating
        FROM dosisma_ofertapp.Producto p
        INNER JOIN dosisma_ofertapp.Empresa e 
            ON p.Empresa_idEmpresa = e.idEmpresa
        LEFT JOIN dosisma_ofertapp.Categoria c 
            ON p.Categoria_idCategoria = c.idCategoria
        LEFT JOIN dosisma_ofertapp.georeferencia g 
            ON e.idEmpresa = g.Empresa_idEmpresa
        LEFT JOIN dosisma_ofertapp.resena r 
            ON p.idProducto = r.Producto_idProducto
        WHERE e.Usuario_id_usuario = :idUsuario
          AND e.estado = 'activo'
          AND p.estado = 'activo'
        GROUP BY 
            p.idProducto, p.titulo, p.descripcion, p.cantidad, p.costo,
            p.color, p.tamano, p.condicion, p.estado, p.imagen,
            c.descripcion, e.nombre
        ORDER BY p.idProducto DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  function obtenerProductosEnOferta()
  {
    try {
      $sql = "SELECT 
                    p.idProducto,
                    p.titulo,
                    p.descripcion,
                    p.cantidad,
                    p.costo,
                    p.color,
                    p.tamano,
                    p.estado,
                    p.condicion,
                    p.imagen,
                    p.en_oferta,
                    c.descripcion AS categoria,
                    e.nombre AS empresa,
                    d.calle,
                    d.numero,
                    d.barrio,
                    d.ciudad,
                    d.departamento,
                    d.pais,
                    ct.telefono,
                    ct.correo
                FROM Producto p
                INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
                INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
                LEFT JOIN direccion d ON e.idEmpresa = d.Empresa_idEmpresa AND d.estado = 'activo'
                LEFT JOIN contacto ct ON e.idEmpresa = ct.Empresa_idEmpresa AND ct.estado = 'activo'
                WHERE p.en_oferta = 1
                  AND p.estado = 'activo'
                ORDER BY p.idProducto DESC";

      $stmt = $this->connect()->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log('Error al obtener productos en oferta: ' . $e->getMessage());
      return [];
    }
  }

  function obtenerProductosEnOfertaPorUsuario($idUsuario)
  {
    try {
      $db = $this->connect();

      $sql = "
      SELECT 
          p.idProducto,
          p.titulo,
          p.descripcion,
          p.cantidad,
          p.costo,
          p.color,
          p.tamano,
          p.condicion,
          p.estado,
          p.imagen,
          p.en_oferta,
          c.descripcion AS categoria,
          e.nombre AS empresa,
          MAX(g.latitud) AS latitud,
          MAX(g.longitud) AS longitud,
          COALESCE(AVG(r.calificacion), 0) AS rating
      FROM dosisma_ofertapp.Producto p
      INNER JOIN dosisma_ofertapp.Empresa e 
          ON p.Empresa_idEmpresa = e.idEmpresa
      LEFT JOIN dosisma_ofertapp.Categoria c 
          ON p.Categoria_idCategoria = c.idCategoria
      LEFT JOIN dosisma_ofertapp.georeferencia g 
          ON e.idEmpresa = g.Empresa_idEmpresa
      LEFT JOIN dosisma_ofertapp.resena r 
          ON p.idProducto = r.Producto_idProducto
      WHERE p.en_oferta = 1
        AND p.estado = 'activo'
        AND e.estado = 'activo'
        AND e.Usuario_id_usuario = :idUsuario
      GROUP BY 
          p.idProducto, p.titulo, p.descripcion, p.cantidad, p.costo,
          p.color, p.tamano, p.condicion, p.estado, p.imagen, p.en_oferta,
          c.descripcion, e.nombre
      ORDER BY p.idProducto DESC
    ";

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log('Error al obtener productos en oferta por usuario: ' . $e->getMessage());
      return [];
    }
  }
}
