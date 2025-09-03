<?php

include_once '../db.php';

class Sql extends DB
{

  function agregar($item)
  {
    $query = $this->connect()->prepare("
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
      return "ok";
    } else {
      return "nok";
    }
  }

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
}
