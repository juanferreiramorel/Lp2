<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = DB::select(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            order by v.fecha_venta desc"
        );

        return view('ventas.index')->with('ventas', $ventas);
    }

    public function create()
    {
        // Crear select para clientes utilizamos selectRaw para consultas puras
        $clientes = DB::table('clientes')
            ->selectRaw("id_cliente, concat(clie_nombre,' ', clie_apellido) as cliente")
            ->pluck('cliente', 'id_cliente');

        // Compartir el usuario en session para el formulario ventas utilizando auth()
        $usuario = auth()->user()->name;

        // Condicion de venta opciones: CONTADO O CREDITO
        $condicion_venta = [
            'CONTADO' => 'CONTADO',
            'CREDITO' => 'CREDITO'
        ];

        // Intervalo de vencimiento
        $intervalo_vencimiento = [
            '7' => '7 Días',
            '15' => '15 Días',
            '30' => '30 Días'
        ];

        // Enviar datos de sucursales
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        return view('ventas.create')->with('clientes', $clientes)
            ->with('usuario', $usuario)
            ->with('condicion_venta', $condicion_venta)
            ->with('intervalo_vencimiento', $intervalo_vencimiento)
            ->with('sucursales', $sucursales);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // Para que el validator no falle al momento de validacion require_if
        $input['intervalo'] = $input['intervalo'] ?? 0;
        $input['cantidad_cuota'] = $input['cantidad_cuota'] ?? 0;
        // Validaciones personalizadas para el formulario ventas
        $validacion = Validator::make(
            $input,
            [
                'id_cliente' => 'required|exists:clientes,id_cliente',
                'condicion_venta' => 'required|in:CONTADO,CREDITO',
                'intervalo' => 'required_if:condicion_venta,CREDITO|in:0,7,15,30',
                'cantidad_cuota' => 'required_if:condicion_venta,CREDITO|integer',
                'fecha_venta' => 'required|date',
                'user_id' => 'required|exists:users,id'
            ],
            [
                'id_cliente.required' => 'El campo cliente es obligatorio.',
                'id_cliente.exists' => 'El cliente seleccionado no es válido.',
                'condicion_venta.required' => 'El campo condición de venta es obligatorio.',
                'condicion_venta.in' => 'La condición de venta seleccionada no es válida.',
                'intervalo.required_if' => 'El campo intervalo es obligatorio cuando la condición de venta es crédito.',
                'intervalo.in' => 'El intervalo seleccionado no es válido.',
                'cantidad_cuota.required_if' => 'El campo cantidad de cuota es obligatorio cuando la condición de venta es crédito.',
                'cantidad_cuota.integer' => 'El campo cantidad de cuota debe ser un número entero.',
                'fecha_venta.required' => 'El campo fecha de venta es obligatorio.',
                'fecha_venta.date' => 'El campo fecha de venta debe ser una fecha válida.',
                'user_id.required' => 'El campo usuario es obligatorio.',
                'user_id.exists' => 'El usuario seleccionado no es válido.'
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        // Si la validación pasa, continuar con el almacenamiento de la venta
        // Recuperamos el usuario en session
        $user_id = auth()->user()->id;
        // Utilizamos insertGetId para obtener el ID de la venta registrada
        $ventas = DB::table('ventas')->insertGetId([
            'id_cliente' => $input['id_cliente'],
            'condicion_venta' => $input['condicion_venta'],
            'intervalo' => $input['intervalo'] ?? 0,
            'cantidad_cuota' => $input['cantidad_cuota'] ?? 0,
            'fecha_venta' => $input['fecha_venta'],
            'factura_nro' => $input['factura_nro'] ?? '0',
            'user_id' => $user_id,
            'total' => $input['total'] ?? 0,
            'estado' => 'COMPLETADO'
        ], 'id_venta');

        Flash::success('Venta registrada exitosamente.');
        return redirect()->route('ventas.index');
    }

    public function edit($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function buscarProducto(Request $request)
    {
        // Consulta personalizada para el buscador de productos con stock en la sucursal seleccionada segun parametro recibido
        $query   = $request->get('query');//contenido a buscar
        $cod_suc = $request->get('cod_suc');

        ## Si query es vacio mostrar todo los productos utilizando un limitador
        if ($query) {
            $productos = DB::select(
                "SELECT productos.*, stocks.cantidad, stocks.id_sucursal
                    FROM productos
                        JOIN stocks ON productos.id_producto = stocks.id_producto 
                    WHERE (CAST(productos.id_producto AS TEXT) iLIKE ? OR CAST(productos.descripcion AS TEXT) iLIKE ?)
                        AND stocks.id_sucursal = ?
                    LIMIT 20",
                [
                    '%' . $query . '%',
                    '%' . $query . '%',
                    $cod_suc
                ]
            );
        } else {
            ## Cargar los primeros 20 productos si no hay búsqueda
            $productos = DB::select(
                "SELECT productos.*, stocks.cantidad, stocks.id_sucursal
                    FROM productos
                        JOIN stocks ON productos.id_producto = stocks.id_producto
                 WHERE stocks.id_sucursal = ?
                 LIMIT 20",
                [$cod_suc]
            );
        }

        ## Retornar la variable productos segun el filtro a nuestro html de buscar_productos
        return view('ventas.body_producto')->with('productos', $productos);
    }

}
