<?php
include_once 'sql.php';

class ApiEmpresa
{
    // -------- Listar --------
    function listarApi()
    {
        $empresa = new Sql();
        $lista = $empresa->listarEmpresas();
        if (!empty($lista)) {
            echo json_encode($lista);
        } else {
            echo json_encode([]);
        }
    }

    function listarApiPorUsuario($idUsuario)
    {
        $empresa = new Sql();
        $lista = $empresa->listarEmpresasPorUsuario($idUsuario);

        if (!empty($lista)) {
            echo json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            echo json_encode([]);
        }
    }



    // -------- Agregar --------
    function agregarApi($array)
    {
        $empresa = new Sql();
        $verificar = $empresa->verificar_existencia($array);

        if (empty($verificar)) {
            $datosEmpresa = array(
                'Categoria_idCategoria' => $array['categoria_id'],
                'Usuario_id_usuario'    => $array['usuario_id'],
                'nombre'                => $array['nombre'],
                'direccion'             => $array['calle'] . ' ' . $array['numero'] . ', ' . $array['ciudad'],
                'estado'                => $array['estado']
            );
            $idEmpresa = $empresa->agregarEmpresa($datosEmpresa);

            if ($idEmpresa > 0) {
                $datosDireccion = array(
                    'Empresa_idEmpresa' => $idEmpresa,
                    'calle'             => $array['calle'],
                    'numero'            => $array['numero'],
                    'barrio'            => $array['barrio'],
                    'ciudad'            => $array['ciudad'],
                    'departamento'      => $array['departamento'],
                    'pais'              => $array['pais']
                );
                $idDireccion = $empresa->agregarDireccion($datosDireccion);

                $datosGeo = array(
                    'direccion_iddireccion' => $idDireccion,
                    'Usuario_id_usuario'    => $array['usuario_id'],
                    'Empresa_idEmpresa'     => $idEmpresa,
                    'latitud'               => $array['latitud'],
                    'longitud'              => $array['longitud']
                );
                $empresa->agregarGeoreferencia($datosGeo);

                echo json_encode(["mensaje" => "ok"]);
            } else {
                echo json_encode(["mensaje" => "nok"]);
            }
        } else {
            echo json_encode(["mensaje" => "registro_existente"]);
        }
    }

    // -------- Obtener --------
    function obtenerDatosParaModificarApi($array)
    {
        $empresa = new Sql();
        $lista = $empresa->obtenerDatosParaModificar($array);
        echo json_encode($lista);
    }

    // -------- Modificar --------
    function modificarApi($array)
    {
        $empresa = new Sql();
        $editarEmpresa    = $empresa->modificarEmpresa($array);
        $editarDireccion  = $empresa->modificarDireccion($array);
        $editarGeo        = $empresa->modificarGeoreferencia($array);

        if ($editarEmpresa == "ok" && $editarDireccion == "ok" && $editarGeo == "ok") {
            echo json_encode(["mensaje" => "ok"]);
        } else {
            echo json_encode(["mensaje" => "nok"]);
        }
    }

    // -------- Eliminar --------
    function eliminarApi($array)
    {
        $empresa = new Sql();
        $eliminar = $empresa->eliminarEmpresa($array);
        echo json_encode(["mensaje" => $eliminar]);
    }
}
