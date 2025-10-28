<?php
include_once '../db.php';

class Sql extends DB
{
  // ================== LISTAR ==================
  function listarEmpresas()
  {
    $sql = "SELECT e.idEmpresa, e.nombre, e.estado, e.direccion, 
       c.descripcion AS categoria,
       d.calle, d.numero, d.barrio, d.ciudad, d.departamento, d.pais,
       g.latitud, g.longitud
FROM Empresa e
LEFT JOIN direccion d ON e.idEmpresa = d.Empresa_idEmpresa
LEFT JOIN georeferencia g ON e.idEmpresa = g.Empresa_idEmpresa
LEFT JOIN Categoria c ON e.Categoria_idCategoria = c.idCategoria
WHERE e.estado = 'activo';";
    $q = $this->connect()->prepare($sql);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  function listarEmpresasPorUsuario($idUsuario)
  {
    $sql = "
      SELECT 
          e.idEmpresa,
          e.nombre,
          d.calle,
          d.numero,
          d.barrio,
          d.ciudad,
          d.departamento,
          d.pais,
          g.latitud,
          g.longitud
      FROM Empresa e
      LEFT JOIN Categoria c ON e.Categoria_idCategoria = c.idCategoria
      LEFT JOIN direccion d ON e.idEmpresa = d.Empresa_idEmpresa AND d.estado = 'activo'
      LEFT JOIN georeferencia g ON e.idEmpresa = g.Empresa_idEmpresa
      WHERE e.Usuario_id_usuario = :idUsuario
        AND e.estado = 'activo'
      ORDER BY e.idEmpresa DESC;
  ";

    $q = $this->connect()->prepare($sql);
    $q->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $q->execute();

    return $q->fetchAll(PDO::FETCH_ASSOC);
  }


  // ================== VERIFICAR DUPLICADO ==================
  function verificar_existencia($item)
  {
    $sql = "SELECT * FROM Empresa WHERE nombre = :nombre AND direccion = :direccion AND estado = 'activo'";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":nombre", $item['nombre']);
    $q->bindParam(":direccion", $item['direccion']);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  // ================== AGREGAR ==================
  function agregarEmpresa($item)
  {
    $pdo = $this->connect();
    $sql = "INSERT INTO Empresa (Categoria_idCategoria, Usuario_id_usuario, nombre, direccion, estado)
            VALUES (:categoria, :usuario, :nombre, :direccion, 'activo')";
    $q = $pdo->prepare($sql);
    $q->bindParam(":categoria", $item['Categoria_idCategoria']);
    $q->bindParam(":usuario", $item['Usuario_id_usuario']);
    $q->bindParam(":nombre", $item['nombre']);
    $q->bindParam(":direccion", $item['direccion']);
    $q->execute();
    return $pdo->lastInsertId();
  }

  function agregarDireccion($item)
  {
    $pdo = $this->connect();
    $sql = "INSERT INTO direccion (Empresa_idEmpresa, calle, numero, barrio, ciudad, departamento, pais, estado)
            VALUES (:empresa, :calle, :numero, :barrio, :ciudad, :departamento, :pais, 'activo')";
    $q = $pdo->prepare($sql);
    $q->bindParam(":empresa", $item['Empresa_idEmpresa']);
    $q->bindParam(":calle", $item['calle']);
    $q->bindParam(":numero", $item['numero']);
    $q->bindParam(":barrio", $item['barrio']);
    $q->bindParam(":ciudad", $item['ciudad']);
    $q->bindParam(":departamento", $item['departamento']);
    $q->bindParam(":pais", $item['pais']);
    $q->execute();
    return $pdo->lastInsertId();
  }

  function agregarGeoreferencia($item)
  {
    $sql = "INSERT INTO georeferencia (direccion_iddireccion, Usuario_id_usuario, Empresa_idEmpresa, latitud, longitud)
            VALUES (:direccion, :usuario, :empresa, :latitud, :longitud)";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":direccion", $item['direccion_iddireccion']);
    $q->bindParam(":usuario", $item['Usuario_id_usuario']);
    $q->bindParam(":empresa", $item['Empresa_idEmpresa']);
    $q->bindParam(":latitud", $item['latitud']);
    $q->bindParam(":longitud", $item['longitud']);
    $q->execute();
    return "ok";
  }

  // ================== OBTENER ==================
  function obtenerDatosParaModificar($item)
  {
    $sql = "SELECT e.idEmpresa, e.nombre, e.estado, e.direccion,
                   e.Categoria_idCategoria,
                   d.calle, d.numero, d.barrio, d.ciudad, d.departamento, d.pais,
                   g.latitud, g.longitud
            FROM Empresa e
            LEFT JOIN direccion d ON e.idEmpresa = d.Empresa_idEmpresa
            LEFT JOIN georeferencia g ON e.idEmpresa = g.Empresa_idEmpresa
            WHERE e.idEmpresa = :idEmpresa";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":idEmpresa", $item['idEmpresa']);
    $q->execute();
    return $q->fetchAll(PDO::FETCH_ASSOC);
  }

  // ================== MODIFICAR ==================
  function modificarEmpresa($item)
  {
    $sql = "UPDATE Empresa 
            SET nombre = :nombre, direccion = :direccion
            WHERE idEmpresa = :idEmpresa";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":nombre", $item['nombre']);
    $q->bindParam(":direccion", $item['calle']);
    $q->bindParam(":idEmpresa", $item['idEmpresa']);
    $q->execute();
    return "ok";
  }

  function modificarDireccion($item)
  {
    $sql = "UPDATE direccion 
            SET calle = :calle, numero = :numero, barrio = :barrio, ciudad = :ciudad, 
                departamento = :departamento, pais = :pais, estado = 'activo'
            WHERE Empresa_idEmpresa = :idEmpresa";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":calle", $item['calle']);
    $q->bindParam(":numero", $item['numero']);
    $q->bindParam(":barrio", $item['barrio']);
    $q->bindParam(":ciudad", $item['ciudad']);
    $q->bindParam(":departamento", $item['departamento']);
    $q->bindParam(":pais", $item['pais']);
    $q->bindParam(":idEmpresa", $item['idEmpresa']);
    $q->execute();
    return "ok";
  }

  function modificarGeoreferencia($item)
  {
    $sql = "UPDATE georeferencia 
            SET latitud = :latitud, longitud = :longitud
            WHERE Empresa_idEmpresa = :idEmpresa";
    $q = $this->connect()->prepare($sql);
    $q->bindParam(":latitud", $item['latitud']);
    $q->bindParam(":longitud", $item['longitud']);
    $q->bindParam(":idEmpresa", $item['idEmpresa']);
    $q->execute();
    return "ok";
  }

  // ================== ELIMINAR ==================
  function eliminarEmpresa($item)
  {
    // Borrar georeferencia
    $sql1 = "DELETE FROM georeferencia WHERE Empresa_idEmpresa = :idEmpresa";
    $q1 = $this->connect()->prepare($sql1);
    $q1->bindParam(":idEmpresa", $item['idEmpresa']);
    $q1->execute();

    // Marcar dirección como inactiva
    $sql2 = "UPDATE direccion SET estado = 'inactivo' WHERE Empresa_idEmpresa = :idEmpresa";
    $q2 = $this->connect()->prepare($sql2);
    $q2->bindParam(":idEmpresa", $item['idEmpresa']);
    $q2->execute();

    // Marcar empresa como inactiva
    $sql3 = "UPDATE Empresa SET estado = 'inactivo' WHERE idEmpresa = :idEmpresa";
    $q3 = $this->connect()->prepare($sql3);
    $q3->bindParam(":idEmpresa", $item['idEmpresa']);

    if ($q3->execute()) {
      return "ok";
    } else {
      return "nok";
    }
  }
}
