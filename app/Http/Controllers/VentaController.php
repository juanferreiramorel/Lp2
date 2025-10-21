<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use RealRashid\SweetAlert\Facades\Alert;
use Luecano\NumeroALetras\NumeroALetras;

use function Laravel\Prompts\error;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        if ($buscar) {
            $ventas = DB::select(
                "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
                users.name as usuario
                FROM ventas v
                    JOIN clientes c ON v.id_cliente = c.id_cliente
                    JOIN users ON v.user_id = users.id
                WHERE (CAST(v.id_venta AS TEXT) iLIKE ? OR CAST(c.clie_nombre AS TEXT) 
                iLIKE ? OR CAST(c.clie_apellido AS TEXT) iLIKE ? OR CAST(v.factura_nro AS TEXT) iLIKE ?
                OR CAST(c.clie_ci AS TEXT) iLIKE ?)
                order by v.fecha_venta desc",
                [
                    '%' . $buscar . '%',
                    '%' . $buscar . '%',
                    '%' . $buscar . '%',
                    '%' . $buscar . '%',
                    '%' . $buscar . '%',
                ]
            );
        } else {
            $ventas = DB::select(
                "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            order by v.fecha_venta desc"
            );
        }

        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($ventas);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($ventas, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $ventas = new LengthAwarePaginator(
            $items,        // registros de esta página
            $total,        // total de registros
            $perPage,      // registros por página
            $page,         // página actual
            [
                'path'  => $request->url(),     // mantiene la ruta base
                'query' => $request->query(),   // mantiene parámetros como "buscar"
            ]
        );

        // si la accion es buscardor entonces significa que se debe recargar mediante ajax la tabla
        if ($request->ajax()) {
            //solo llmamamos a table.blade.php y mediante compact pasamos la variable users
            return view('ventas.table')->with('ventas', $ventas);
        }

        // Consulta para recuperar cajas
        $caja = DB::table('cajas')
            ->where('id_sucursal', auth()->user()->id_sucursal) // filtrar por sucursal del usuario
            ->pluck('descripcion', 'id_caja');

        // Validar que el usuario no tenga una caja abierta en la fecha actual
        $caja_abierta = DB::selectOne(
            "SELECT * FROM apertura_cierre_cajas 
            WHERE user_id = ? AND estado = 'ABIERTA'",
            [auth()->user()->id]
        );

        return view('ventas.index')->with('ventas', $ventas)
            ->with('cajas', $caja)
            ->with('caja_abierta', $caja_abierta);
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

        // Enviar datos de sucursales segun sucursal del usuario
        $sucursales = DB::table('sucursales')->where('id_sucursal', auth()->user()->id_sucursal)
            ->pluck('descripcion', 'id_sucursal');

        // enviar datos de apertura de caja a ventas 
        $apertura_caja = DB::selectOne(
            "SELECT ap.id_apertura,
                ap.fecha_apertura,
                lpad('1', 3, '0')as establecimiento,
                lpad(cast(c.punto_expedicion as text), 3, '0')as punto_expedicion,
                lpad(cast(coalesce(max(c.ultima_factura_impresa), 0) + 1 as text), 7, '0') as nro_factura
            FROM apertura_cierre_cajas ap
                JOIN cajas c on c.id_caja = ap.id_caja 
            WHERE ap.user_id = ? and ap.estado = 'ABIERTA'
            GROUP BY
            ap.id_apertura,
            ap.fecha_apertura,
            c.punto_expedicion,
            c.ultima_factura_impresa",
            [auth()->user()->id]
        );

        // validar que no exista una caja abierta para el usuario en la fecha pasada
        if (
            !empty($apertura_caja)
            && Carbon::parse($apertura_caja->fecha_apertura)->format('Y-m-d')
            < Carbon::now()->format('Y-m-d')
        ) {
            Alert::toast('Debe cerrar la caja abierta para poder realizar una venta', 'error');
            // retornar al index de ventas
            return redirect()->route('ventas.index');
        }

        return view('ventas.create')->with('clientes', $clientes)
            ->with('usuario', $usuario)
            ->with('condicion_venta', $condicion_venta)
            ->with('intervalo_vencimiento', $intervalo_vencimiento)
            ->with('sucursales', $sucursales)
            ->with('apertura_caja', $apertura_caja);
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
                'user_id' => 'required|exists:users,id',
                'id_apertura' => 'required|exists:apertura_cierre_cajas,id_apertura',
                'id_sucursal' => 'required|exists:sucursales,id_sucursal',
                'factura_nro' => 'nullable|string|max:15',
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
                'user_id.exists' => 'El usuario seleccionado no es válido.',
                'id_apertura.required' => 'El campo apertura es obligatorio.',
                'id_apertura.exists' => 'La apertura seleccionada no es válida.',
                'id_sucursal.required' => 'El campo sucursal es obligatorio.',
                'id_sucursal.exists' => 'La sucursal seleccionada no es válida.',
                'factura_nro.max' => 'El número de factura no debe exceder los 15 caracteres.',
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        // validar que la nro de factura sea unica
        if (!empty($input['factura_nro'])) {
            // buscamos si ya existe la factura
            $factura_exist = DB::selectOne('SELECT * FROM ventas WHERE factura_nro = ?', [$input['factura_nro']]);
            // si existe mostramos error
            if (!empty($factura_exist)) {
                Alert::toast('El número de factura ya existe', 'error');
                return redirect()->back()->withInput($input);
            }
        }

        // validar fecha de venta no sea mayor a la fecha actual
        if (Carbon::parse($input['fecha_venta'])->format('Y-m-d') > Carbon::now()->format('Y-m-d')) {
            Alert::toast('La fecha de venta no puede ser mayor a la fecha actual', 'error');
            return redirect()->back()->withInput($input);
        }

        // Si la validación pasa, continuar con el almacenamiento de la venta
        // Recuperamos el usuario en session
        $user_id = auth()->user()->id;
        // Utilizamos insertGetId para obtener el ID de la venta registrada
        //quitamos los puntos del total
        $total = str_replace('.', '', $input['total']);
        // Agregar transacciones
        DB::beginTransaction();
        try {
            $ventas = DB::table('ventas')->insertGetId([
                'id_cliente' => $input['id_cliente'],
                'condicion_venta' => $input['condicion_venta'],
                'intervalo' => $input['intervalo'] ?? 0,
                'cantidad_cuota' => $input['cantidad_cuota'] ?? 0,
                'fecha_venta' => $input['fecha_venta'],
                'factura_nro' => $input['factura_nro'] ?? '0',
                'user_id' => $user_id,
                'total' => $total ?? 0,
                'id_sucursal' => $input['id_sucursal'],
                'estado' => 'COMPLETADO',
                'id_apertura' => $input['id_apertura'],
            ], 'id_venta');

            // insertar detalle ventas
            $subtotal = 0;
            // validar que existea $input['codigo'] de productos
            if ($request->has('codigo')) {
                foreach ($input['codigo'] as $key => $codigo) {
                    // quitar separador de miles
                    $monto = str_replace('.', '', $input['precio'][$key]);
                    // calculo de subtotal
                    $subtotal += $monto * $input['cantidad'][$key];
                    // insertar en detalle_ventas
                    DB::insert(
                        'INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)',
                        [
                            $ventas,
                            $codigo,
                            $input['cantidad'][$key],
                            $monto
                        ]
                    );

                    // disminuir stocks segun producto y sucursal
                    DB::update('UPDATE stocks SET cantidad = cantidad - ? WHERE id_producto = ? AND id_sucursal = ?', [
                        $input['cantidad'][$key],
                        $codigo,
                        $input['id_sucursal']
                    ]);
                }
                // actualizar el total en la tabla ventas valor total
                DB::update('UPDATE ventas SET total = ? WHERE id_venta = ?', [
                    $subtotal,
                    $ventas
                ]);
            }

            // actualizar la ultima factura impresa en cajas si es que se envio un nro de factura
            if (!empty($input['factura_nro'])) {
                // extraer el nro de factura utilizando explode en la posicion 2
                // 001-002-0000001
                $factura_nro = explode('-', $input['factura_nro'])[2];
                // update de cajas y recuperar segun la apertura de caja el codigo de id_caja utilizando subconsulta
                DB::update('UPDATE cajas SET ultima_factura_impresa = ? 
                WHERE id_caja = (SELECT id_caja FROM apertura_cierre_cajas WHERE id_apertura = ?)', [
                    (int) $factura_nro, // convertir a entero para quitar ceros a la izquierda
                    $input['id_apertura']
                ]);
            }
            // Si todo esta bien realiza el envio e inserta la venta y detalle
            DB::commit();
        } catch (\Exception $e) {
            // si algo salio mal lo revertimos
            Log::info('Error al registrar la venta: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage())->withInput($request->all());
        }


        Alert::toast('Venta registrada exitosamente.', 'success');
        return redirect()->route('ventas.index');
    }

    public function show($id)
    {
        // Obtener la cabecera de ventas
        $venta = DB::selectOne(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            WHERE v.id_venta = ?",
            [$id]
        );

        if (empty($venta)) {
            Alert::toast('Venta no encontrada', 'error');
            return redirect()->route('ventas.index');
        }

        // obtener los detalles de ventas
        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        return view('ventas.show')->with('ventas', $venta)->with('detalle_venta', $detalle_venta);
    }
    public function edit($id)
    {
        $venta = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($venta)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }

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

        // Enviar datos de sucursales segun sucursal del usuario
        $sucursales = DB::table('sucursales')->where('id_sucursal', auth()->user()->id_sucursal)
            ->pluck('descripcion', 'id_sucursal');

        // obtener los detalles de ventas
        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        return view('ventas.edit')->with('ventas', $venta)
            ->with('detalle_venta', $detalle_venta)
            ->with('clientes', $clientes)
            ->with('condicion_venta', $condicion_venta)
            ->with('intervalo_vencimiento', $intervalo_vencimiento)
            ->with('usuario', $usuario)
            ->with('sucursales', $sucursales);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $ventas = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($ventas)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }
        $input['intervalo'] = $input['intervalo'] ?? 0;
        $input['cantidad_cuota'] = $input['cantidad_cuota'] ?? 0;
        // Validaciones personalizadas para el formulario ventas
        $validacion = Validator::make(
            $input,
            [
                'condicion_venta' => 'required|in:CONTADO,CREDITO',
                'intervalo' => 'required_if:condicion_venta,CREDITO|in:0,7,15,30',
                'cantidad_cuota' => 'required_if:condicion_venta,CREDITO|integer',
                'id_sucursal' => 'required|exists:sucursales,id_sucursal',
            ],
            [
                'condicion_venta.required' => 'El campo condición de venta es obligatorio.',
                'condicion_venta.in' => 'La condición de venta seleccionada no es válida.',
                'intervalo.required_if' => 'El campo intervalo es obligatorio cuando la condición de venta es crédito.',
                'intervalo.in' => 'El intervalo seleccionado no es válido.',
                'cantidad_cuota.required_if' => 'El campo cantidad de cuota es obligatorio cuando la condición de venta es crédito.',
                'cantidad_cuota.integer' => 'El campo cantidad de cuota debe ser un número entero.',
                'id_sucursal.required' => 'El campo sucursal es obligatorio.',
                'id_sucursal.exists' => 'La sucursal seleccionada no es válida.',
            ]
        );

        if ($validacion->fails()) {
            return redirect()->back()->withErrors($validacion)->withInput();
        }

        DB::beginTransaction();
        try {
            // Actualizar la venta
            DB::update('UPDATE ventas SET condicion_venta = ?, intervalo = ?, cantidad_cuota = ?, id_sucursal = ? WHERE id_venta = ?', [
                $input['condicion_venta'],
                $input['intervalo'],
                $input['cantidad_cuota'],
                $input['id_sucursal'],
                $id
            ]);

            // Actualizar los detalles de venta
            foreach ($input['codigo'] as $key => $codigo) {
                // quitar separador de miles
                $monto = str_replace('.', '', $input['precio'][$key]);
                // obtener detalle de venta si existe segun producto recibido
                $detalle = DB::selectOne('SELECT * FROM detalle_ventas WHERE id_venta = ? AND id_producto = ?', [$id, $codigo]);

                if (!empty($detalle)) {
                    // actualizar en detalle_ventas
                    DB::update(
                        'UPDATE detalle_ventas SET cantidad = ?, precio = ? WHERE id_venta = ? AND id_producto = ?',
                        [
                            $input['cantidad'][$key],
                            $monto,
                            $id,
                            $codigo
                        ]
                    );
                    // calcular diferencia de cantidad para disminucion de stock
                    $diferencia = $input['cantidad'][$key] - $detalle->cantidad;
                    // actualizar stocks segun producto y sucursal
                    if ($diferencia != 0) {
                        DB::update('UPDATE stocks SET cantidad = cantidad - ? WHERE id_producto = ? AND id_sucursal = ?', [
                            $diferencia,
                            $codigo,
                            $ventas->id_sucursal
                        ]);
                    }
                } else {
                    // Si no existe insertar nuevo detalle
                    DB::insert(
                        'INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)',
                        [
                            $id,
                            $codigo,
                            $input['cantidad'][$key],
                            $monto
                        ]
                    );

                    // Disminuir stock
                    DB::update('UPDATE stocks SET cantidad = cantidad - ? WHERE id_producto = ? AND id_sucursal = ?', [
                        $input['cantidad'][$key],
                        $codigo,
                        $ventas->id_sucursal
                    ]);
                }
            }
            // Si todo esta bien realiza el envio e inserta la venta y detalle
            DB::commit();
        } catch (\Exception $e) {
            Log::info('Error al actualizar la venta: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }


        Alert::toast('Venta actualizada con exito.', 'success');
        return redirect()->route('ventas.index');
    }

    public function destroy($id)
    {
        $ventas = DB::selectOne('SELECT * FROM ventas WHERE id_venta = ?', [$id]);

        if (empty($ventas)) {
            Flash::error('Venta no encontrada');
            return redirect()->route('ventas.index');
        }

        // Usar transacciones 
        DB::beginTransaction();
        try {
            // Anular la venta
            DB::update(
                'UPDATE ventas SET estado = ? WHERE id_venta = ?',
                [
                    'ANULADO',
                    $id
                ]
            );

            // restaurar la cantidad de productos en stock para ello debemos consultar el detalle de venta para obtener datos del producto y cantidad
            $detalle_venta = DB::select('SELECT * FROM detalle_ventas WHERE id_venta = ?', [$id]);

            foreach ($detalle_venta as $item) {
                // actualizar las cantidades en stock segun la sucursal que obtendremos de la variable $ventas
                DB::update('UPDATE stocks SET cantidad = cantidad + ? WHERE id_producto = ? AND id_sucursal = ?', [
                    $item->cantidad,
                    $item->id_producto,
                    $ventas->id_sucursal
                ]);
            }
            // Si todo esta bien realiza el envio e inserta la venta y detalle
            DB::commit();
        } catch (\Exception $e) {
            Log::info('Error al anular la venta: ' . $e->getMessage());
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }

        Flash::success('Venta anulada exitosamente.');

        return redirect()->route('ventas.index');
    }

    public function buscarProducto(Request $request)
    {
        // Consulta personalizada para el buscador de productos con stock en la sucursal seleccionada segun parametro recibido
        $query   = $request->get('query'); //contenido a buscar
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

    public function pdf()
    {
        $data = ['<h1>Prueba de PDF</h1>']; // Aquí debes preparar los datos que necesitas para la vista PDF
        $pdf = Pdf::loadView('ventas.invoice', compact('data'));
        return $pdf->download('invoice.pdf');
    }

    public function factura($id)
    {
        // recuperar datos de la venta
        $ventas = DB::selectOne(
            "SELECT v.*, concat(c.clie_nombre,' ', c.clie_apellido) as cliente, c.clie_ci,
            users.name as usuario, c.clie_direccion, c.clie_telefono
            FROM ventas v
                JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN users ON v.user_id = users.id
            WHERE v.id_venta = ?",
            [$id]
        );

        // validacion de existencia
        if (empty($ventas)) {
            Alert::toast('Venta no encontrada', 'error');
            return redirect()->route('ventas.index');
        }

        // recuperar detalles de la venta
        $detalle_venta = DB::select(
            "SELECT dv.*, p.descripcion
            FROM detalle_ventas dv
                JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?",
            [$id]
        );

        // libreria para convertir numero a letras
        $formateo = new NumeroALetras();
        $numero_a_letras = $formateo->toWords($ventas->total);// recuperar total de la venta y convertir a letras

        return view('ventas.factura')->with('ventas', $ventas)
            ->with('detalle_venta', $detalle_venta)
            ->with('numero_a_letras', $numero_a_letras);
    }
}
