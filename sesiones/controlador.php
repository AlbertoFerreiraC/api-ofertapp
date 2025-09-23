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
            
            // ================== USUARIO ==================
            $datosUsuario = array(
                'nombre'     => $array['nombre'],
                'apellido'   => $array['apellido'],
                'usuario'    => $array['usuario'],
                'email'      => $array['email'],
                'password'   => $array['password'], // ya encriptado desde funAgregar.php
                'tipoCuenta' => $array['tipoCuenta']
            );

            $idUsuario = $clasificacion->agregar($datosUsuario);

            if ($idUsuario > 0) {

                // ================== SI ES COMERCIAL ==================
                if ($array['tipoCuenta'] === 'comercial') {

                    // -------- EMPRESA --------
                    $datosEmpresa = array(
                        'Usuario_id_usuario'    => $idUsuario,
                        'nombre'                => $array['nombreEmpresa'] ?? '',
                        'direccion'             => $array['calleEmpresa'] ?? '',
                        'Categoria_idCategoria' => $array['categoriaEmpresa'] ?? 0,
                        'estado'                => 'activo'
                    );

                    $idEmpresa = $clasificacion->agregarEmpresa($datosEmpresa);

                    if ($idEmpresa > 0) {

                        // -------- DIRECCIÓN --------
                        $datosDireccion = array(
                            'Empresa_idEmpresa' => $idEmpresa,
                            'calle'             => $array['calleEmpresa']        ?? '',
                            'numero'            => $array['numeroEmpresa']       ?? '',
                            'barrio'            => $array['barrioEmpresa']       ?? '',
                            'ciudad'            => $array['ciudadEmpresa']       ?? '',
                            'departamento'      => $array['departamentoEmpresa'] ?? '',
                            'pais'              => $array['paisEmpresa']         ?? 'Paraguay',
                            'estado'            => 'activo'
                        );

                        $idDireccion = $clasificacion->agregarDireccion($datosDireccion);

                        // -------- GEOREFERENCIA --------
                        if ($idDireccion > 0 && !empty($array['latitud']) && !empty($array['longitud'])) {
                            $datosGeo = array(
                                'direccion_iddireccion' => $idDireccion,
                                'Usuario_id_usuario'    => $idUsuario,
                                'Empresa_idEmpresa'     => $idEmpresa,
                                'latitud'               => $array['latitud'],
                                'longitud'              => $array['longitud']
                            );
                            $clasificacion->agregarGeoreferencia($datosGeo);
                        }

                        exito("ok");

                    } else {
                        error("error_empresa");
                    }

                } else {
                    // ✅ Cuenta personal
                    exito("ok");
                }

            } else {
                error("nok");
            }

        } else {
            error("registro_existente");
        }
    }
} 

// ================== HELPERS ==================
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
