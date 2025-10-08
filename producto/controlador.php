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

    function listarOfertas()
    {
        $producto = new Sql();
        $ofertas = $producto->obtenerProductosEnOferta();

        if (!empty($ofertas)) {
            echo json_encode($ofertas);
        } else {
            echo json_encode([]);
        }
    }


    function listarApiProducto()
    {
        $db = new Sql();
        $productos = $db->listarProductosDetalle();

        $result = [];

        foreach ($productos as $row) {
            $result[] = [
                "id"        => $row["idProducto"],
                "nombre"    => $row["titulo"] ?? $row["descripcion"],
                "descripcion" => $row["descripcion"],
                "precio"    => (float)($row["costo"] ?? 0), 
                "categoria" => $row["categoria"] ?? "Sin categoría",
                "empresa"   => $row["empresa"] ?? "Sin empresa",
                "img"       => $row["imagen"] ?? "vistas/img/plantilla/no-image.png",
                "latitud"   => isset($row["latitud"]) ? (float)$row["latitud"] : null,
                "longitud"  => isset($row["longitud"]) ? (float)$row["longitud"] : null,
                "rating"    => isset($row["rating"]) ? (float)$row["rating"] : 0
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

    // -------- Detalle --------
    function detalleApi($id)
    {
        $sql = new Sql();

        $producto = $sql->obtenerProducto($id);
        if (!$producto) {
            echo json_encode(["error" => "Producto no encontrado"]);
            return;
        }

        // Agregar dirección
        $producto["direccion"] = $sql->obtenerDireccionEmpresa($producto["idEmpresa"]);

        // Agregar georeferencia
        $producto["georeferencia"] = $sql->obtenerGeoreferencia($producto["idEmpresa"]);

        // Agregar contactos
        $producto["contactos"] = $sql->obtenerContactosEmpresa($producto["idEmpresa"]);

        // Agregar reseñas
        $producto["resenas"] = $sql->obtenerResenasProducto($id);

        // Imágenes (por ahora una sola)
        $producto["imagenes"] = [$producto["imagen"]];

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($producto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    function agregarResenaApi($data)
    {
        header('Content-Type: application/json; charset=UTF-8');

        $sql = new Sql();
        $res = $sql->agregarResena($data);

        if ($res === "ok") {
            echo json_encode(["mensaje" => "ok"]);
        } else {
            echo json_encode(["error" => "No se pudo guardar"]);
        }
    }
}
