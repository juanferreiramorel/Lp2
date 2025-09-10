<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class ComprasController extends Controller
{
    /** =============================
     *  LISTADO
     *  ============================= */
    public function index()
    {
        // Usa proveedores.descripcion como nombre del proveedor
        $compras = DB::select(
            "SELECT c.*,
                    p.descripcion AS proveedor,
                    u.name AS usuario
               FROM compras c
          LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
          LEFT JOIN users u       ON c.user_id = u.id
           ORDER BY c.fecha_compra DESC, c.id_compra DESC"
        );

        return view('compras.index', compact('compras'));
    }

    /** =============================
     *  CREAR
     *  ============================= */
    public function create()
    {
        $proveedores = $this->kvProveedores(); // [id_proveedor => descripcion]
        $usuario = auth()->user()->name ?? '';
        $user_id = auth()->id();
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');

        return view('compras.create', compact('proveedores', 'usuario', 'user_id', 'sucursales'));
    }

    /** =============================
     *  GUARDAR (AUMENTA STOCK)
     *  ============================= */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id_proveedor'   => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'   => 'required|date',
            'user_id'        => 'required|exists:users,id',

            // detalle desde vista de ventas: codigo[], cantidad[], precio[]
            'codigo'         => 'required|array|min:1',
            'codigo.*'       => 'required|integer',
            'cantidad'       => 'required|array|min:1',
            'cantidad.*'     => 'required|numeric|min:1',
            'precio'         => 'required|array|min:1',
            'precio.*'       => 'required',
        ], [
            'codigo.required' => 'Debe cargar al menos un producto.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $idSucursal = $request->input('id_sucursal'); // opcional si tu stock es por sucursal

        // Calcular total
        $total = 0;
        foreach ($input['codigo'] as $i => $codigo) {
            $monto = $this->normalizeNumber($input['precio'][$i] ?? 0);
            $cant  = (int)($input['cantidad'][$i] ?? 0);
            $total += ($cant * $monto);
        }

        DB::beginTransaction();
        try {
            // Cabecera
            $idCompra = DB::table('compras')->insertGetId([
                'id_proveedor' => (int)$input['id_proveedor'],
                'fecha_compra' => $input['fecha_compra'],
                'total'        => $total,
                'user_id'      => (int)$input['user_id'],
            ], 'id_compra');

            // Detalle + AUMENTO de stock
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = $this->normalizeNumber($input['precio'][$i] ?? 0);
                $cant  = (int)($input['cantidad'][$i] ?? 0);

                DB::table('detalle_compras')->insert([
                    'id_compra'       => $idCompra,
                    'id_producto'     => (int)$codigo,
                    'cantidad'        => $cant,
                    'precio_unitario' => $monto,
                ]);

                $this->increaseStock((int)$codigo, $cant, $idSucursal);
            }

            DB::commit();
            Flash::success('Compra registrada y stock actualizado.');
            return redirect()->route('compras.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en compras.store: '.$e->getMessage());
            return back()->withErrors(['db' => $e->getMessage()])->withInput();
        }
    }

    /** =============================
     *  VER
     *  ============================= */
    public function show($id)
    {
        $compra = DB::selectOne(
            "SELECT c.*,
                    p.descripcion AS proveedor,
                    u.name AS usuario
               FROM compras c
          LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
          LEFT JOIN users u       ON c.user_id = u.id
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

        return view('compras.show', compact('compra', 'detalles'));
    }

    /** =============================
     *  EDITAR
     *  ============================= */
    public function edit($id)
    {
        $compra = DB::selectOne("SELECT * FROM compras WHERE id_compra = ?", [$id]);
        if (!$compra) {
            Flash::error('Compra no encontrada.');
            return redirect()->route('compras.index');
        }

        $proveedores = $this->kvProveedores(); // [id_proveedor => descripcion]
        $usuario = auth()->user()->name ?? '';
        $user_id = auth()->id();

        $detalles = DB::select(
            "SELECT d.*, pr.descripcion
               FROM detalle_compras d
          LEFT JOIN productos pr ON pr.id_producto = d.id_producto
              WHERE d.id_compra = ?",
            [$id]
        );

        return view('compras.edit', compact('compra','proveedores','usuario','user_id'))
            ->with('detalles', $detalles);
    }

    /** =============================
     *  ACTUALIZAR (AJUSTA STOCK POR DIFERENCIA)
     *  ============================= */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'id_proveedor'   => 'required|exists:proveedores,id_proveedor',
            'fecha_compra'   => 'required|date',
            'user_id'        => 'required|exists:users,id',

            'codigo'         => 'required|array|min:1',
            'codigo.*'       => 'required|integer',
            'cantidad'       => 'required|array|min:1',
            'cantidad.*'     => 'required|numeric|min:1',
            'precio'         => 'required|array|min:1',
            'precio.*'       => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $idSucursal = $request->input('id_sucursal'); // opcional para stock por sucursal

        // Armar mapa actual (antes de cambios)
        $detalleAnt = DB::table('detalle_compras')->where('id_compra', $id)->get();
        $mapAnt = [];
        foreach ($detalleAnt as $d) {
            $mapAnt[$d->id_producto] = ($mapAnt[$d->id_producto] ?? 0) + (int)$d->cantidad;
        }

        // Mapa nuevo
        $mapNuevo = [];
        $total = 0;
        foreach ($input['codigo'] as $i => $codigo) {
            $monto = $this->normalizeNumber($input['precio'][$i] ?? 0);
            $cant  = (int)($input['cantidad'][$i] ?? 0);
            $total += ($cant * $monto);
            $mapNuevo[(int)$codigo] = ($mapNuevo[(int)$codigo] ?? 0) + $cant;
        }

        // Calcular delta por producto
        $todosIds = array_unique(array_merge(array_keys($mapAnt), array_keys($mapNuevo)));

        DB::beginTransaction();
        try {
            // Ajustar stock por diferencia
            foreach ($todosIds as $idProd) {
                $old = (int)($mapAnt[$idProd] ?? 0);
                $new = (int)($mapNuevo[$idProd] ?? 0);
                $delta = $new - $old;
                if ($delta > 0) {
                    $this->increaseStock($idProd, $delta, $idSucursal);
                } elseif ($delta < 0) {
                    $this->decreaseStock($idProd, -$delta, $idSucursal);
                }
            }

            // Actualizar cabecera
            DB::table('compras')->where('id_compra', $id)->update([
                'id_proveedor' => (int)$input['id_proveedor'],
                'fecha_compra' => $input['fecha_compra'],
                'total'        => $total,
                'user_id'      => (int)$input['user_id'],
            ]);

            // Reemplazar detalle
            DB::table('detalle_compras')->where('id_compra', $id)->delete();
            foreach ($input['codigo'] as $i => $codigo) {
                $monto = $this->normalizeNumber($input['precio'][$i] ?? 0);
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
            DB::rollBack();
            Log::error('Error en compras.update: '.$e->getMessage());
            return back()->withErrors(['db' => $e->getMessage()])->withInput();
        }
    }

    /** =============================
     *  ELIMINAR (REVERSA STOCK)
     *  ============================= */
    public function destroy($id)
    {
        $idSucursal = request()->get('id_sucursal'); // opcional

        DB::beginTransaction();
        try {
            // Reversa del stock
            $det = DB::table('detalle_compras')->where('id_compra', $id)->get();
            foreach ($det as $d) {
                $this->decreaseStock((int)$d->id_producto, (int)$d->cantidad, $idSucursal);
            }

            DB::table('detalle_compras')->where('id_compra', $id)->delete();
            DB::table('compras')->where('id_compra', $id)->delete();

            DB::commit();
            Flash::success('Compra eliminada y stock revertido.');
        } catch (\Throwable $e) {
            DB::RollBack();
            Log::error('Error en compras.destroy: '.$e->getMessage());
            Flash::error('No se pudo eliminar la compra: ' . $e->getMessage());
        }

        return redirect()->route('compras.index');
    }

    /** =============================
     *  BUSCADOR AJAX DE PRODUCTOS (opcional)
     *  ============================= */
    public function buscarProducto(Request $request)
    {
        $buscar = trim((string)$request->get('query', ''));
        $idSucursal = $request->get('sucursal'); // si tus vistas lo envían

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

    /** =============================
     *  HELPERS
     *  ============================= */
    private function normalizeNumber($value): float
    {
        if (is_null($value)) return 0.0;
        if (is_numeric($value)) return (float)$value;
        $s = trim((string)$value);
        if ($s === '') return 0.0;
        $s = str_replace([' ', "\u{00A0}"], '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        return (float)(is_numeric($s) ? $s : 0.0);
    }

    private function kvProveedores(): array
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('proveedores')) return [];
            // Con la estructura dada: id_proveedor, descripcion, direccion, telefono
            return DB::table('proveedores')
                ->orderBy('descripcion')
                ->pluck('descripcion', 'id_proveedor')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Aumenta stock (global o por sucursal si existe columna id_sucursal) */
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
                ])->update([ 'cantidad' => $row->cantidad + $cantidad ]);
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
                DB::table('stocks')->where('id_producto', $idProducto)->update([ 'cantidad' => $row->cantidad + $cantidad ]);
            } else {
                DB::table('stocks')->insert([ 'id_producto' => $idProducto, 'cantidad' => $cantidad ]);
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
                ])->update([ 'cantidad' => $newQty ]);
            }
        } else {
            $row = DB::table('stocks')->where('id_producto', $idProducto)->first();
            if ($row) {
                $newQty = max(0, ((int)$row->cantidad) - $cantidad);
                DB::table('stocks')->where('id_producto', $idProducto)->update([ 'cantidad' => $newQty ]);
            }
        }
    }
}
