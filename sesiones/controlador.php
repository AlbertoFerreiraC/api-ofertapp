<?php

include_once 'sql.php';

class ApiControlador
{

    function agregarApi($array)
    {
        $clasificacion = new Sql();

        // 🔍 Verificamos si ya existe usuario o email
        $verificarExistencia = $clasificacion->verificar_existencia($array);

        if (empty($verificarExistencia)) {
            // Armamos datos a guardar
            $datos = array(
                'nombre'     => $array['nombre'],
                'apellido'   => $array['apellido'],
                'usuario'    => $array['usuario'],
                'email'      => $array['email'],
                'password'   => $array['password'], // ya viene encriptado desde funAgregar.php
                'tipoCuenta' => $array['tipoCuenta'],
                'latitud'    => $array['latitud'],
                'longitud'   => $array['longitud']
            );

            $guardar = $clasificacion->agregar($datos);

            if ($guardar == "ok") {
                exito("ok");
            } else {
                error("nok");
            }
        } else {
            // 🚫 Usuario o email ya existe
            error("registro_existente");
        }
    }
} // FIN API SESIONES


// Helpers para respuestas
function error($mensaje)
{
    echo json_encode(array('mensaje' => $mensaje));
}

function exito($mensaje)
{
    echo json_encode(array('mensaje' => $mensaje));
}

function printJSON($array)
{
    echo json_encode($array);
}
