<?php

include_once 'sesiones.php';

class ApiSesiones
{
    function login($array)
    {
        // session_start() ya se llama en el script principal (funLogin.php)

        $sesion = new Sesion();
        $usuarioEncontrado = $sesion->obtenerUsuarioPorUsername($array);

        if (!empty($usuarioEncontrado)) {
            $usuarioData = $usuarioEncontrado[0];
            $passIngresado = $array['pass'];
            $hashGuardado = $usuarioData['pass'];

            if (password_verify($passIngresado, $hashGuardado)) {

                // =========================================================
                // CORRECCIÓN: GUARDAMOS TODOS LOS DATOS EN LA SESIÓN
                // =========================================================
                $_SESSION['iniciarSesion'] = "ok"; // Tu variable de control
                $_SESSION['autenticado'] = true;   // Una variable estándar
                $_SESSION['id_usuario'] = $usuarioData['id_usuario'];
                $_SESSION['nombre'] = $usuarioData['nombre'];
                $_SESSION['apellido'] = $usuarioData['apellido'];
                $_SESSION['usuario'] = $usuarioData['usuario'];
                $_SESSION['tipo_usuario'] = $usuarioData['tipo_usuario'];

                // Devolvemos 'true' para indicar que el login fue exitoso
                return true;
            } else {
                return false; // Contraseña incorrecta
            }
        } else {
            return false; // Usuario no encontrado
        }
    }
}
