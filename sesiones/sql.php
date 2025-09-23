<?php

include_once '../db.php';

class Sql extends DB
{

  // ================== AGREGAR USUARIO ==================
  function agregar($item)
  {
    $pdo = $this->connect();

    $query = $pdo->prepare("
      INSERT INTO Usuario (id_usuario, nombre, apellido, email, tipo_usuario, estado, usuario, pass) 
      VALUES (0, :nombre, :apellido, :email, :tipo_usuario, 'activo', :usuario, :pass)
    ");

    $query->bindParam(":nombre", $item['nombre'], PDO::PARAM_STR);
    $query->bindParam(":apellido", $item['apellido'], PDO::PARAM_STR);
    $query->bindParam(":email", $item['email'], PDO::PARAM_STR);
    $query->bindParam(":tipo_usuario", $item['tipoCuenta'], PDO::PARAM_STR);
    $query->bindParam(":usuario", $item['usuario'], PDO::PARAM_STR);
    $query->bindParam(":pass", $item['password'], PDO::PARAM_STR);

    if ($query->execute()) {
      return $pdo->lastInsertId(); // devolvemos el ID del nuevo usuario
    } else {
      return 0; // error
    }
  }

  // ================== VERIFICAR EXISTENCIA ==================
  function verificar_existencia($item)
  {
    $query = $this->connect()->prepare("
      SELECT id_usuario 
      FROM Usuario 
      WHERE usuario = :usuario OR email = :email
    ");

    $query->bindParam(":usuario", $item['usuario'], PDO::PARAM_STR);
    $query->bindParam(":email", $item['email'], PDO::PARAM_STR);
    $query->execute();

    return $query->fetch(PDO::FETCH_ASSOC);
  }

  // ================== AGREGAR EMPRESA ==================
  function agregarEmpresa($item)
  {
    $pdo = $this->connect();

    $query = $pdo->prepare("
      INSERT INTO Empresa (Categoria_idCategoria, Usuario_id_usuario, nombre, direccion, estado) 
      VALUES (:categoria, :usuario_id, :nombre, :direccion, :estado)
    ");

    $query->bindParam(":categoria", $item['Categoria_idCategoria'], PDO::PARAM_INT);
    $query->bindParam(":usuario_id", $item['Usuario_id_usuario'], PDO::PARAM_INT);
    $query->bindParam(":nombre", $item['nombre'], PDO::PARAM_STR);
    $query->bindParam(":direccion", $item['direccion'], PDO::PARAM_STR);
    $query->bindParam(":estado", $item['estado'], PDO::PARAM_STR);

    if ($query->execute()) {
      return $pdo->lastInsertId(); // 🔹 devolvemos ID de la empresa
    } else {
      return 0;
    }
  }
  
  // ================== AGREGAR DIRECCIÓN ==================
  function agregarDireccion($item)
  {
    $pdo = $this->connect();

    $query = $pdo->prepare("
      INSERT INTO direccion (Empresa_idEmpresa, calle, numero, barrio, ciudad, departamento, pais, estado) 
      VALUES (:empresa_id, :calle, :numero, :barrio, :ciudad, :departamento, :pais, :estado)
    ");

    $query->bindParam(":empresa_id", $item['Empresa_idEmpresa'], PDO::PARAM_INT);
    $query->bindParam(":calle", $item['calle'], PDO::PARAM_STR);
    $query->bindParam(":numero", $item['numero'], PDO::PARAM_STR);
    $query->bindParam(":barrio", $item['barrio'], PDO::PARAM_STR);
    $query->bindParam(":ciudad", $item['ciudad'], PDO::PARAM_STR);
    $query->bindParam(":departamento", $item['departamento'], PDO::PARAM_STR);
    $query->bindParam(":pais", $item['pais'], PDO::PARAM_STR);
    $query->bindParam(":estado", $item['estado'], PDO::PARAM_STR);

    if ($query->execute()) {
      return $pdo->lastInsertId(); // 🔹 devolvemos ID de la dirección
    } else {
      return 0;
    }
  }

  // ================== AGREGAR GEOREFERENCIA ==================
  function agregarGeoreferencia($item)
  {
    $query = $this->connect()->prepare("
      INSERT INTO georeferencia (direccion_iddireccion, Usuario_id_usuario, Empresa_idEmpresa, latitud, longitud) 
      VALUES (:direccion_id, :usuario_id, :empresa_id, :latitud, :longitud)
    ");

    $query->bindParam(":direccion_id", $item['direccion_iddireccion'], PDO::PARAM_INT);
    $query->bindParam(":usuario_id", $item['Usuario_id_usuario'], PDO::PARAM_INT);
    $query->bindParam(":empresa_id", $item['Empresa_idEmpresa'], PDO::PARAM_INT);
    $query->bindParam(":latitud", $item['latitud'], PDO::PARAM_STR);
    $query->bindParam(":longitud", $item['longitud'], PDO::PARAM_STR);

    if ($query->execute()) {
      return "ok";
    } else {
      return "nok";
    }
  }
}
