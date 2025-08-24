<?php
// apiSesiones.php

include_once 'sesiones.php';

class ApiSesiones
{
    function login($array)
    {
        // PASO 1: INICIAMOS LA SESIÓN DE PHP.
        // Esto le permite a PHP recordar al usuario entre páginas.
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

                // PASO 2: GUARDAMOS LOS DATOS DEL USUARIO EN LA SESIÓN.
                // Esta información estará disponible en dashboard.php y otras páginas.
                $_SESSION['autenticado'] = true;
                $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
                $_SESSION['nombre'] = $usuarioData['nombre'];
                $_SESSION['apellido'] = $usuarioData['apellido'];
                $_SESSION['usuario'] = $usuarioData['usuario'];
                $_SESSION['tipo_usuario'] = $usuarioData['tipo_usuario'];

                // Ya no necesitamos el código para la tabla 'sesiones', por lo que fue eliminado.

                // Preparamos una respuesta simple para el frontend.
                // Solo necesita saber que todo salió bien y el nombre para el saludo.
                $respuesta = array(
                    'nombre' => $usuarioData['nombre'],
                    'mensaje' => 'ok'
                );

                http_response_code(200);
                printJSON($respuesta);
            } else {
                // La contraseña es incorrecta
                header("HTTP/1.1 401 Unauthorized");
                error("El usuario o la contraseña son incorrectos.");
            }
        } else {
            // El usuario no existe
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
