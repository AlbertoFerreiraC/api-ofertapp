<?php
include_once 'sesiones.php';

class ApiSesiones
{
    function login($array)
    {
        // session_start() ya se hace en funLogin.php
        $sesion = new Sesion();
        $usuarioEncontrado = $sesion->obtenerUsuarioPorUsername($array);

        if (!empty($usuarioEncontrado)) {
            $usuarioData   = $usuarioEncontrado[0];
            $passIngresado = $array['pass'];
            $hashGuardado  = $usuarioData['pass'];

            if (password_verify($passIngresado, $hashGuardado)) {

                // =========================================================
                // GUARDAR DATOS DEL USUARIO EN LA SESIÓN
                // =========================================================
                $_SESSION['iniciarSesion'] = "ok";      // tu variable de control
                $_SESSION['autenticado']   = true;
                $_SESSION['id_usuario']    = $usuarioData['id_usuario'];
                $_SESSION['nombre']        = $usuarioData['nombre'];
                $_SESSION['apellido']      = $usuarioData['apellido'];
                $_SESSION['usuario']       = $usuarioData['usuario'];
                $_SESSION['tipo_usuario']  = $usuarioData['tipo_usuario'];

                // =========================================================
                // GUARDAR DATOS DE LA EMPRESA (si existe)
                // =========================================================
                $_SESSION['idEmpresa']     = $usuarioData['idEmpresa'] ?? null;
                $_SESSION['empresa']       = $usuarioData['nombreEmpresa'] ?? null;

                return true; // Login correcto
            } else {
                return false; // Contraseña incorrecta
            }
        } else {
            return false; // Usuario no encontrado
        }
    }
}
