<?php

include_once 'sesiones.php';

class ApiSesiones
{
    function login($array)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $sesion = new Sesion();
        $usuarioEncontrado = $sesion->obtenerUsuarioPorUsername($array);

        if (!empty($usuarioEncontrado)) {
            $usuarioData = $usuarioEncontrado[0];
            $passIngresado = $array['pass'];
            $hashGuardado = $usuarioData['pass'];

            if (password_verify($passIngresado, $hashGuardado)) {

                $_SESSION['autenticado'] = true;
                $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
                $_SESSION['nombre'] = $usuarioData['nombre'];
                $_SESSION['apellido'] = $usuarioData['apellido'];
                $_SESSION['usuario'] = $usuarioData['usuario'];
                $_SESSION['tipo_usuario'] = $usuarioData['tipo_usuario'];
                $respuesta = array(
                    'nombre' => $usuarioData['nombre'],
                    'mensaje' => 'ok'
                );

                http_response_code(200);
                printJSON($respuesta);
            } else {
                header("HTTP/1.1 401 Unauthorized");
                error("El usuario o la contraseña son incorrectos.");
            }
        } else {
            header("HTTP/1.1 401 Unauthorized");
            error("El usuario o la contraseña son incorrectos.");
        }
    }
}

function error($mensaje)
{
    echo json_encode(array('mensaje' => $mensaje));
}

function printJSON($array)
{
    echo json_encode($array);
}
