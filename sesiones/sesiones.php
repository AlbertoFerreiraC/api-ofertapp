<?php
// sesiones.php

include_once '../db.php';

class Sesion extends DB
{
  /**
   * Obtiene los datos de un usuario buscando por su nombre de usuario (username).
   * Esta función ya NO comprueba la contraseña.
   */
  function obtenerUsuarioPorUsername($item)
  {
    // CORRECCIÓN: La consulta ahora busca por la columna `usuario` y ya no por la contraseña.
    $query = $this->connect()->prepare("SELECT * FROM Usuario WHERE estado = 'activo' AND usuario = :usuario");
    $query->bindParam(":usuario", $item['usuario'], PDO::PARAM_STR);

    if ($query->execute()) {
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return null;
    }
  }

  function generarCodigoSesion()
  {
    $query = $this->connect()->prepare("SELECT DATE_FORMAT(now(),'%Y%m%d%H%i%S') AS codigo");
    if ($query->execute()) {
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return null;
    }
  }

  // El resto de tus funciones...
  function verificarId($item)
  {
    $query = $this->connect()->prepare("SELECT * FROM Usuario WHERE estado = 'activo' AND id_usuario = :id_usuario");
    $query->bindParam(":id_usuario", $item['id_usuario'], PDO::PARAM_INT);
    if ($query->execute()) {
      return $query->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return null;
    }
  }

  function actualizarDatosUsuario($item)
  {
    // IMPORTANTE: Esta función también debe usar password_hash() para actualizar la contraseña.
    $nuevoPassHash = password_hash($item['nuevoPass'], PASSWORD_DEFAULT);

    $query = $this->connect()->prepare("UPDATE usuario SET pass = :nuevoPass WHERE id_usuario = :idUsuario AND estado = 'activo'");
    $query->bindParam(":idUsuario", $item['idUsuario'], PDO::PARAM_INT);
    $query->bindParam(":nuevoPass", $nuevoPassHash, PDO::PARAM_STR);
    if ($query->execute()) {
      return "ok";
    } else {
      return "nok";
    }
  }
}
