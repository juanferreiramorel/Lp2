<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        // Obtener el término de búsqueda del request
        $buscar = $request->get('buscar');
        // validar que contenga un valor el buscar
        $sql = '';// definir una variable sql vacia

        if (!empty($buscar)) {
            $sql = " WHERE u.name iLIKE '%" . $buscar . "%' 
            or sc.descripcion iLIKE '%" . $buscar . "%' 
            or s.table_name iLIKE '%" . $buscar . "%' 
            or s.operation iLIKE '%" . $buscar . "%' 
            or cast(s.id as text)iLIKE '%" . $buscar . "%'"; // si tiene valor agregar la condicion a la variable sql
        }

        // Consulta para obtener los productos con la marca asociada y si posee filtros
        $auditoria = DB::select(
            'SELECT 
                s.id AS id,
                sc.descripcion AS sucursal,
                u.name AS usuario,
                s.operation AS operacion,
                s.table_name AS tabla,
                s.changed_at AS fecha
                FROM audit.log s
            JOIN users u ON s.user_id = u.id
             JOIN sucursales sc USING(id_sucursal)
                ' . $sql . '
             ORDER BY s.id desc'
        );

        // Definimos los valores de paginación
        $page = $request->input('page', 1);   // página actual (por defecto 1)
        $perPage = 10;                        // cantidad de registros por página
        $total = count($auditoria);           // total de registros

        // Cortamos el array para solo devolver los registros de la página actual
        $items = array_slice($auditoria, ($page - 1) * $perPage, $perPage);

        // Creamos el paginador manualmente
        $auditoria = new LengthAwarePaginator(
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
        if ($request->ajax()) { //devuelve true o false si es ajax o no
            //solo llmamamos a table.blade.php y mediante compact pasamos la variable users
            return view('auditoria.table')->with('auditoria', $auditoria);
        }


        return view('auditoria.index')->with('auditoria', $auditoria);
    }
}
