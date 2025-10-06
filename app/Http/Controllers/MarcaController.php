<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = DB::SELECT('SELECT * FROM marcas');
        return view('marcas.index')->with('marcas', $marcas);
    }
    public function create()
    {
        return view('marcas.create');
    }
    public function store(Request $request)
    {
        //campos recibidos del formulario
        $input = $request->all();
        //validacion de los campos
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required'
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio'
            ]
        );
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        //insertar proveedores en la base de datos
        DB::insert(
            'INSERT INTO marcas (descripcion) values (?)',
            [
                $input['descripcion']
            ]
        );
        Alert::toast('Marca registrada con exito', 'success');
        return redirect()->route('marcas.index');
    }
    public function edit($id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        if (empty($marca)) {
            Alert::toast('Marca no encontrada', 'error');
            return redirect()->route('marcas.index');
        }
        return view('marcas.edit')->with('marcas', $marca);
    }
    public function update(Request $request, $id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        if (empty($marca)) {
            Alert::toast('Marca no encontrada', 'error');
            return redirect()->route('marcas.index');
        }
        $input = $request->all();
        $validacion = Validator::make(
            $input,
            [
                'descripcion' => 'required'
            ],
            [
                'descripcion.required' => 'El campo descripcion es obligatorio'
            ]
        );
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        DB::update(
            'UPDATE marcas 
       SET descripcion = ? 
       WHERE id_marca = ?',
            [
                $input['descripcion'],
                $id
            ]
        );
        Alert::toast('Marca actualizada con exito', 'success');
        return redirect()->route('marcas.index');
    }
    public function destroy($id)
    {
        $marca = DB::selectOne('SELECT * FROM marcas WHERE id_marca = ?', [$id]);
        if (empty($marca)) {
            Alert::toast('Marca no encontrada', 'error');
            return redirect()->route('marcas.index');
        }
        DB::delete('DELETE FROM marcas WHERE id_marca = ?', [$id]);
        Alert::toast('Marca eliminada con exito', 'success');
        return redirect()->route('marcas.index');
    }
}
