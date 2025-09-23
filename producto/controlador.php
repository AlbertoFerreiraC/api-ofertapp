<?php
include_once 'sql.php';

class ApiProducto
{
    // -------- Listar --------
    function listarApi()
    {
        $producto = new Sql();
        $lista = $producto->listarProductos();
        if (!empty($lista)) {
            echo json_encode($lista);
        } else {
            echo json_encode([]);
        }
    }

    // -------- Agregar --------
    function agregarApi($array)
    {
        $producto = new Sql();
        $verificar = $producto->verificar_existencia_producto($array);

        if (empty($verificar)) {

            $datosProducto = array(
                'Empresa_idEmpresa'     => $array['empresa_id'],
                'Categoria_idCategoria' => $array['categoria_id'],
                'titulo'                => $array['titulo'],
                'descripcion'           => $array['descripcion'],
                'cantidad'              => $array['cantidad'],
                'costo'                 => $array['costo'],
                'color'                 => isset($array['color']) ? $array['color'] : '',
                'tamano'                => isset($array['tamano']) ? $array['tamano'] : '',
                'estado'                => isset($array['estado']) ? $array['estado'] : 'activo',
                'condicion'             => isset($array['condicion']) ? $array['condicion'] : 'nuevo',
                'imagen'                => $array['imagen'] // ruta del archivo subido
            );

            $idProducto = $producto->agregarProducto($datosProducto);

            if ($idProducto > 0) {
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
        $producto = new Sql();
        $lista = $producto->obtenerDatosParaModificarProducto($array);
        echo json_encode($lista);
    }

    // -------- Modificar --------
    function modificarApi($array)
    {
        $producto = new Sql();
        $editar = $producto->modificarProducto($array);

        if ($editar == "ok") {
            echo json_encode(["mensaje" => "ok"]);
        } else {
            echo json_encode(["mensaje" => "nok"]);
        }
    }

    // -------- Eliminar --------
    function eliminarApi($array)
    {
        $producto = new Sql();
        $eliminar = $producto->eliminarProducto($array);
        echo json_encode(["mensaje" => $eliminar]);
    }
}
