<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class StockController extends Controller
{
    // Definir una variable para el path
    private $path;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:stock index')->only(['index']);
    }
    public function index(Request $request)
    {
        // Obtener el término de búsqueda del request
        $buscar = $request->get('buscar');
        $input = $request->all();
        $filtro_sucursales = "";
        $filtro_productos = "";
        // validar que contenga un valor el buscar
        $sql = '';// definir una variable sql vacia

        if (!empty($buscar)) {
            $sql = "AND (p.descripcion iLIKE '%" . $buscar . "%' 
            or m.descripcion iLIKE '%" . $buscar . "%' 
            or sc.descripcion iLIKE '%" . $buscar . "%' 
            or m.descripcion iLIKE '%" . $buscar . "%' 
            or cast(s.cantidad as text)iLIKE '%" . $buscar . "%')"; // si tiene valor agregar la condicion a la variable sql
        }
        
        if (!empty($input['sucursales'])) {
            // concatenar los filtros y siempre dejar un espacio en blanco al comienzo del string " "
            $filtro_sucursales = " AND sc.id_sucursal = " . $input['sucursales'];
        }
        if (!empty($input['productos'])) {
            // concatenar los filtros y siempre dejar un espacio en blanco al comienzo del string " "
            $filtro_productos = " AND p.id_producto = " . $input['productos'];
        }
        
        // Consulta para obtener los productos con la marca asociada y si posee filtros
        $stock = DB::select(
            'SELECT s.cantidad, m.descripcion as marca, p.descripcion AS producto, sc.descripcion as sucursal,
            s.id_stock AS id, p.descripcion
             FROM stocks s
             JOIN sucursales sc USING(id_sucursal)
             JOIN productos p USING(id_producto) 
             JOIN marcas m USING(id_marca)
             WHERE 1=1
             ' . $sql . ' ' . $filtro_sucursales . ' ' . $filtro_productos . '
             ORDER BY p.id_producto desc'
        );

        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($stock);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($stock, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $stock = new LengthAwarePaginator(
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
        $sucursales = DB::table('sucursales')->pluck('descripcion', 'id_sucursal');
        $productos = DB::table('productos')->pluck('descripcion', 'id_producto');

         // Verificamos si la petición es AJAX
        if ($request->ajax()) { //devuelve true o false si es ajax o no
            //solo llmamamos a table.blade.php y mediante compact pasamos la variable users
            return view('stock.table')->with('stock', $stock)->with('sucursales', $sucursales)->with('productos', $productos);
        }


        return view('stock.index')->with('stock', $stock)->with('sucursales', $sucursales)->with('productos', $productos);
    }
}
