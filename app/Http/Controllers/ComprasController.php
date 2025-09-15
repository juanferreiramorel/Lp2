<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class ComprasController extends Controller
{
    public function index()
    {
        // Incluimos sucursal y usuario para mostrar en la tabla
        $compras = DB::select(
            "SELECT c.*,
                    p.descripcion AS proveedor,
                    u.name        AS usuario,
                    s.descripcion AS sucursal
               FROM compras c
          LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
          LEFT JOIN users u       ON c.user_id = u.id
          LEFT JOIN sucursales s  ON c.id_sucursal = s.id_sucursal
           ORDER BY c.fecha_compra DESC, c.id_compra DESC"
        );

        return view('compras.index', compact('compras'));
    }
    
    public function create()
    {
        $proveedores = $this->kvProveedores(); // [id_proveedor => descripcion]
        $usuario     = auth()->user()->name ?? '';
        $user_id     = auth()->id();
        $sucursales  = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        // Campos de crédito
        $condicion_compra = ['CONTADO' => 'CONTADO', 'CREDITO' => 'CREDITO'];
        $intervalo = [
            '7'  => '7 Días',
            '15' => '15 Días',
            '30' => '30 Días'
        ];
        // Cantidad de intervalos (cuotas) 1..12
        $cantidad_cuotas = [];
        for ($i = 1; $i <= 12; $i++) {
            $cantidad_cuotas[(string)$i] = (string)$i;
        }

        return view('compras.create', compact('proveedores', 'usuario', 'user_id', 'sucursales', 'condicion_compra', 'intervalo', 'cantidad_cuotas'));
    }
    
    public function buscarProducto(Request $request)
    {
        $buscar = trim((string)$request->get('query', ''));

        if ($buscar !== '') {
            $buscarLower = mb_strtolower($buscar, 'UTF-8');
            $sql = "SELECT p.* FROM productos p 
                    WHERE LOWER(p.descripcion) LIKE ? OR CAST(p.id_producto AS TEXT) LIKE ?
                    LIMIT 50";
            $productos = DB::select($sql, ["%{$buscarLower}%", "%{$buscarLower}%"]);
        } else {
            $productos = DB::select("SELECT p.* FROM productos p LIMIT 20");
        }

        return view('compras.body_producto')->with('productos', $productos);
    }
    
    public function store(Request $request)
    {
        $input = $request->all();
        // Defaults para required_if
        $input['intervalo'] = $input['intervalo'] ?? 0;
        $input['cantidad_cuotas'] = $input['cantidad_cuotas'] ?? 0;

        $validator = Validator::make($input, [
            'id_proveedor'        => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'        => 'required|date',
            'user_id'             => 'required|exists:users,id',
            'id_sucursal'         => 'required|exists:sucursales,id_sucursal',

            'factura'             => 'nullable|string|max:20',

            'condicion_compra'    => 'required|in:CONTADO,CREDITO',
            'intervalo'           => 'required_if:condicion_compra,CREDITO|in:0,7,15,30',
            'cantidad_cuotas'     => 'required_if:condicion_compra,CREDITO|integer|min:0|max:36', // CORREGIDO: min:0 a min:1

            // detalle desde vista (igual que ventas): codigo[], cantidad[], precio[]
            'codigo'              => 'required|array|min:1',
            'codigo.*'            => 'required|integer',
            'cantidad'            => 'required|array|min:1',
            'cantidad.*'          => 'required|numeric|min:1',
            'precio'              => 'required|array|min:1',
            'precio.*'            => 'required',
        ], [
            'id_proveedor.required'        => 'El proveedor es obligatorio.',
            'id_proveedor.exists'          => 'El proveedor no es válido.',
            'fecha_compra.required'        => 'La fecha es obligatoria.',
            'fecha_compra.date'            => 'La fecha no es válida.',
            'user_id.required'             => 'El usuario es obligatorio.',
            'user_id.exists'               => 'El usuario no es válido.',
            'id_sucursal.required'         => 'La sucursal es obligatoria.',
            'id_sucursal.exists'           => 'La sucursal no es válida.',
            'condicion_compra.required'    => 'La condición es obligatoria.',
            'condicion_compra.in'          => 'La condición no es válida.',
            'intervalo.required_if'        => 'El intervalo es obligatorio cuando es crédito.',
            'intervalo.in'                 => 'El intervalo debe ser 0, 7, 15 o 30.',
            'cantidad_cuotas.required_if'  => 'La cantidad de cuotas es obligatoria cuando es crédito.',
            'cantidad_cuotas.integer'      => 'La cantidad de cuotas debe ser un número entero.',
            'cantidad_cuotas.min'          => 'La cantidad de cuotas debe ser al menos 1 cuando es crédito.', // CORREGIDO
            'cantidad_cuotas.max'          => 'La cantidad de cuotas no debe superar 36.',
            'codigo.required'              => 'Debe agregar al menos un producto.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $idSucursal = (int)$input['id_sucursal'];

        // Calcular total con normalización robusta (1.234.567,89 -> 1234567.89)
        $total = 0.0;
        foreach ($input['codigo'] as $i => $codigo) {
            $monto = str_replace('.', '', $input['precio'][$i] ?? '0');
            $cant  = (int)($input['cantidad'][$i] ?? 0);
            $total += ($cant * $monto);
        }
        $total = round($total, 2);

        DB::beginTransaction();
        try {
            // Si es contado, forzamos 0s en crédito
            if (($input['condicion_compra'] ?? 'CONTADO') === 'CONTADO') {
                $input['intervalo'] = 0;
                $input['cantidad_cuotas'] = 0;
            }

            // Cabecera
            $idCompra = DB::table('compras')->insertGetId([
                'id_proveedor'        => (int)$input['id_proveedor'],
                'fecha_compra'        => $input['fecha_compra'],
                'total'               => $total,
                'user_id'             => (int)$input['user_id'],
                'id_sucursal'         => $idSucursal,
                'factura'             => $input['factura'] ?? null,
                'condicion_compra'    => $input['condicion_compra'],
                'intervalo'           => (int)$input['intervalo'],
                'cantidad_cuotas'     => (int)$input['cantidad_cuotas'],
            ], 'id_compra');

            // Detalle + AUMENTO de stock por sucursal
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = str_replace('.', '', $input['precio_unitario'][$i] ?? '0');
                $cant  = (int)($input['cantidad'][$i] ?? 0);

                DB::table('detalle_compras')->insert([
                    'id_compra'       => $idCompra,
                    'id_producto'     => (int)$codigo,
                    'cantidad'        => $cant,
                    'precio_unitario' => $monto,
                ]);

                // stock += cantidad
                $this->increaseStock((int)$codigo, $cant, $idSucursal);
            }

            DB::commit();
            Flash::success('Compra registrada, stock actualizado.');
            return redirect()->route('compras.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en compras.store: ' . $e->getMessage());
            return back()->withErrors(['db' => $e->getMessage()])->withInput();
        }
    }
    
    public function show($id)
    {
        $compra = DB::selectOne(
            "SELECT c.*,
                    p.descripcion AS proveedor,
                    u.name        AS usuario,
                    s.descripcion AS sucursal
               FROM compras c
               JOIN proveedores p ON c.id_proveedor = p.id_proveedor
               JOIN users u       ON c.user_id = u.id
               JOIN sucursales s  ON c.id_sucursal = s.id_sucursal
              WHERE c.id_compra = ?",
            [$id]
        );
        if (!$compra) {
            Flash::error('Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        $detalles = DB::select(
            "SELECT d.*, pr.descripcion
               FROM detalle_compras d
          LEFT JOIN productos pr ON pr.id_producto = d.id_producto
              WHERE d.id_compra = ?",
            [$id]
        );

        return view('compras.show', compact('compra'))->with('detalles', $detalles);
    }
    
    public function edit($id)
    {
        $compra = DB::selectOne("SELECT c.*, descripcion AS sucursal FROM compras c JOIN sucursales USING(id_sucursal)   WHERE id_compra = ?", [$id]);
        if (!$compra) {
            Flash::error('Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        $proveedores = $this->kvProveedores(); // [id_proveedor => descripcion]
        $usuario     = auth()->user()->name ?? '';
        $user_id     = auth()->id();
        $condicion_compra = [
            'CONTADO' => 'CONTADO',
            'CREDITO' => 'CREDITO'
        ];

        $intervalo = [
            '7'  => '7 Días',
            '15' => '15 Días',
            '30' => '30 Días'
        ];

        // Enviar datos de sucursales
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        $detalles = DB::select(
            "SELECT d.*, pr.descripcion
               FROM detalle_compras d
          LEFT JOIN productos pr ON pr.id_producto = d.id_producto
              WHERE d.id_compra = ?",
            [$id]
        );

        return view('compras.edit', compact('compra', 'proveedores', 'usuario', 'user_id', 'intervalo', 'condicion_compra', 'sucursales'))
            ->with('detalles', $detalles);
    }
    
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $input['intervalo'] = $input['intervalo'] ?? 0;
        $input['cantidad_cuotas'] = $input['cantidad_cuotas'] ?? 0;

        $validator = Validator::make($input, [
            'id_proveedor'        => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'        => 'required|date',
            'user_id'             => 'required|exists:users,id',
            'id_sucursal'         => 'required|exists:sucursales,id_sucursal',

            'factura'             => 'nullable|string|max:20',

            'condicion_compra'    => 'required|in:CONTADO,CREDITO',
            'intervalo'           => 'required_if:condicion_compra,CREDITO|in:0,7,15,30',
            'cantidad_cuotas'     => 'required_if:condicion_compra,CREDITO|integer|min:0|max:36', // CORREGIDO: min:0 a min:1

            'codigo'              => 'required|array|min:1',
            'codigo.*'            => 'required|integer',
            'cantidad'            => 'required|array|min:1',
            'cantidad.*'          => 'required|numeric|min:1',
            'precio_unitario'     => 'required|array|min:1',
            'precio_unitario.*'   => 'required',
        ], [
            'id_proveedor.required'        => 'El proveedor es obligatorio.',
            'id_proveedor.exists'          => 'El proveedor no es válido.',
            'fecha_compra.required'        => 'La fecha es obligatoria.',
            'fecha_compra.date'            => 'La fecha no es válida.',
            'user_id.required'             => 'El usuario es obligatorio.',
            'user_id.exists'               => 'El usuario no es válido.',
            'id_sucursal.required'         => 'La sucursal es obligatoria.',
            'id_sucursal.exists'           => 'La sucursal no es válida.',
            'condicion_compra.required'    => 'La condición es obligatoria.',
            'condicion_compra.in'          => 'La condición no es válida.',
            'intervalo.required_if'        => 'El intervalo es obligatorio cuando es crédito.',
            'intervalo.in'                 => 'El intervalo debe ser 0, 7, 15 o 30.',
            'cantidad_cuotas.required_if'  => 'La cantidad de cuotas es obligatoria cuando es crédito.',
            'cantidad_cuotas.integer'      => 'La cantidad de cuotas debe ser un número entero.',
            'cantidad_cuotas.min'          => 'La cantidad de cuotas debe ser al menos 1 cuando es crédito.', // CORREGIDO
            'cantidad_cuotas.max'          => 'La cantidad de cuotas no debe superar 36.',
            'codigo.required'              => 'Debe agregar al menos un producto.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $idSucursal = (int)$request->input('id_sucursal');

        // Mapa actual (antes de cambios) para ajustar stock por diferencia
        $detalleAnt = DB::table('detalle_compras')->where('id_compra', $id)->get();
        $mapAnt = [];
        foreach ($detalleAnt as $d) {
            Log::error('Detalle actual: id_producto=' . $d->id_producto . ', cantidad=' . $d->cantidad);
            $mapAnt[$d->id_producto] = ($mapAnt[$d->id_producto] ?? 0) + (int)$d->cantidad;
        }

        // Mapa nuevo + total
        $mapNuevo = [];
        $total = 0.0;
        foreach ($input['codigo'] as $i => $codigo) {
            $monto = $this->normalizeNumber($input['precio'][$i] ?? 0);
            $cant  = (int)($input['cantidad'][$i] ?? 0);
            $total += ($cant * $monto);
            $mapNuevo[(int)$codigo] = ($mapNuevo[(int)$codigo] ?? 0) + $cant;
        }
        $total = round($total, 2);

        DB::beginTransaction();
        try {
            // Si es contado, forzamos 0s
            if (($input['condicion_compra'] ?? 'CONTADO') === 'CONTADO') {
                $input['intervalo'] = 0;
                $input['cantidad_cuotas'] = 0;
            }

            // Ajustar stock por diferencia
            $todosIds = array_unique(array_merge(array_keys($mapAnt), array_keys($mapNuevo)));
            foreach ($todosIds as $idProd) {
                $old   = (int)($mapAnt[$idProd] ?? 0);
                $new   = (int)($mapNuevo[$idProd] ?? 0);
                $delta = $new - $old;
                if ($delta > 0) {
                    $this->increaseStock($idProd, $delta, $idSucursal);
                } elseif ($delta < 0) {
                    $this->decreaseStock($idProd, abs($delta), $idSucursal);
                }
            }

            // Actualizar cabecera
            DB::table('compras')->where('id_compra', $id)->update([
                'id_proveedor'        => (int)$input['id_proveedor'],
                'fecha_compra'        => $input['fecha_compra'],
                'total'               => $total,
                'user_id'             => (int)$input['user_id'],
                'id_sucursal'         => $idSucursal,
                'factura'             => $input['factura'] ?? null,
                'condicion_compra'    => $input['condicion_compra'],
                'intervalo'           => (int)$input['intervalo'],
                'cantidad_cuotas'     => (int)$input['cantidad_cuotas'],
            ]);

            // Reemplazar detalle (simplifica; alternativamente upsert)
            DB::table('detalle_compras')->where('id_compra', $id)->delete();
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = $this->normalizeNumber($input['precio_unitario'][$i] ?? 0);
                $cant  = (int)($input['cantidad'][$i] ?? 0);
                DB::table('detalle_compras')->insert([
                    'id_compra'       => $id,
                    'id_producto'     => (int)$codigo,
                    'cantidad'        => $cant,
                    'precio_unitario' => $monto,
                ]);
            }

            DB::commit();
            Flash::success('Compra actualizada y stock ajustado.');
            return redirect()->route('compras.show', $id);
        } catch (\Throwable $e) {
            DB::RollBack();
            Log::error('Error en compras.update: ' . $e->getMessage());
            return back()->withErrors(['db' => $e->getMessage()])->withInput();
        }
    }
    
    public function destroy($id)
    {
        // Si anulas compras, probablemente debas revertir el stock
        $compra = DB::selectOne("SELECT * FROM compras WHERE id_compra = ?", [$id]);
        if (!$compra) {
            Flash::error('Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        // Cargar detalle para ajustar stock en -
        $detalles = DB::table('detalle_compras')->where('id_compra', $id)->get();
        DB::beginTransaction();
        try {
            // Revertir stock
            foreach ($detalles as $d) {
                $this->decreaseStock((int)$d->id_producto, (int)$d->cantidad, (int)($compra->id_sucursal ?? 0));
            }

            // Marcar estado si existe; si no, opcionalmente borrar detalle + cabecera
            if (DB::getSchemaBuilder()->hasColumn('compras', 'estado')) {
                DB::update("UPDATE compras SET estado = 'ANULADO' WHERE id_compra = ?", [$id]);
            }

            DB::commit();
            Flash::success('Compra anulada y stock revertido.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en compras.destroy: ' . $e->getMessage());
            return back()->withErrors(['db' => $e->getMessage()]);
        }

        return redirect()->route('compras.index');
    }
    
    private function kvProveedores(): array
    {
        try {
            return DB::table('proveedores')->orderBy('descripcion')->pluck('descripcion', 'id_proveedor')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Normaliza "1.234.567,89" -> 1234567.89 y "1234.50" -> 1234.50 */
    private function normalizeNumber($value): float
    {
        if (is_null($value)) return 0.0;
        if (is_numeric($value)) return (float)$value;
        $s = trim((string)$value);
        if ($s === '') return 0.0;
        $s = str_replace([' ', "\u{00A0}"], '', $s); // espacios
        $s = str_replace('.', '', $s);               // miles
        $s = str_replace(',', '.', $s);              // decimal
        return (float)(is_numeric($s) ? $s : 0.0);
    }

    /** Aumenta stock (global o por sucursal) */
    private function increaseStock(int $idProducto, int $cantidad, $idSucursal = null): void
    {
        if ($cantidad <= 0) return;
        if (!DB::getSchemaBuilder()->hasTable('stocks')) return;

        $hasSucursal = DB::getSchemaBuilder()->hasColumn('stocks', 'id_sucursal');

        if ($hasSucursal) {
            if ($idSucursal === null) {
                Log::warning('increaseStock: id_sucursal no provisto; no se actualiza stock por sucursal.');
                return;
            }
            $row = DB::table('stocks')->where([
                'id_producto' => $idProducto,
                'id_sucursal' => $idSucursal,
            ])->first();

            if ($row) {
                DB::table('stocks')->where([
                    'id_producto' => $idProducto,
                    'id_sucursal' => $idSucursal,
                ])->update(['cantidad' => ((int)$row->cantidad) + $cantidad]);
            } else {
                DB::table('stocks')->insert([
                    'id_producto' => $idProducto,
                    'id_sucursal' => $idSucursal,
                    'cantidad'    => $cantidad,
                ]);
            }
        } else {
            $row = DB::table('stocks')->where('id_producto', $idProducto)->first();
            if ($row) {
                DB::table('stocks')->where('id_producto', $idProducto)->update(['cantidad' => ((int)$row->cantidad) + $cantidad]);
            } else {
                DB::table('stocks')->insert(['id_producto' => $idProducto, 'cantidad' => $cantidad]);
            }
        }
    }

    /** Disminuye stock (global o por sucursal) */
    private function decreaseStock(int $idProducto, int $cantidad, $idSucursal = null): void
    {
        if ($cantidad <= 0) return;
        if (!DB::getSchemaBuilder()->hasTable('stocks')) return;

        $hasSucursal = DB::getSchemaBuilder()->hasColumn('stocks', 'id_sucursal');

        if ($hasSucursal) {
            if ($idSucursal === null) {
                Log::warning('decreaseStock: id_sucursal no provisto; no se actualiza stock por sucursal.');
                return;
            }
            $row = DB::table('stocks')->where([
                'id_producto' => $idProducto,
                'id_sucursal' => $idSucursal,
            ])->first();

            if ($row) {
                $newQty = max(0, ((int)$row->cantidad) - $cantidad);
                DB::table('stocks')->where([
                    'id_producto' => $idProducto,
                    'id_sucursal' => $idSucursal,
                ])->update(['cantidad' => $newQty]);
            }
        } else {
            $row = DB::table('stocks')->where('id_producto', $idProducto)->first();
            if ($row) {
                $newQty = max(0, ((int)$row->cantidad) - $cantidad);
                DB::table('stocks')->where('id_producto', $idProducto)->update(['cantidad' => $newQty]);
            }
        }
    }
}