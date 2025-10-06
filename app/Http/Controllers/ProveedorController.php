<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = DB::select('SELECT * FROM proveedores');
        return view('proveedores.index')->with('proveedores', $proveedores);
    }
    public function create()
    {
        return view('proveedores.create');
    }
    public function store(Request $request)
    {
        //campos recibidos del formulario
        $input = $request->all();
        //validacion de los campos
        $validacion = Validator::make(
            $input,
        [
            'descripcion' => 'required' ,
            'telefono' => 'required'
        ],
        [
            'descripcion.required' => 'El campo descripcion es obligatorio',
            'telefono.required' => 'El campo telefono es obligatorio'
        ]);
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        //insertar proveedores en la base de datos
        DB::insert(
            'INSERT INTO proveedores (descripcion, direccion, telefono) values (?, ?, ?)',
            [
                $input['descripcion'],
                $input['direccion'],
                $input['telefono']
            ]);
            Alert::toast('Proveedor creado con exito', 'success');
            return redirect()->route('proveedores.index');
    }

    public function edit($id)
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);
        if(empty($proveedor)){
            Alert::toast('Proveedor no encontrado', 'error');
            return redirect()->route('proveedores.index');
        }
        return view('proveedores.edit')->with('proveedores', $proveedor);
    }
    public function update(Request $request, $id)
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);
        if(empty($proveedor)){
            Alert::toast('Proveedor no encontrado', 'error');
            return redirect()->route('proveedores.index');
        }
        $input = $request->all();
        $validacion = Validator::make(
            $input,
        [
            'descripcion' => 'required' ,
            'telefono' => 'required'
        ],
        [
            'descripcion.required' => 'El campo descripcion es obligatorio',
            'telefono.required' => 'El campo telefono es obligatorio'
        ]);
        if ($validacion->fails()) {
            return back()->withErrors($validacion)->withInput();
        }
        DB::update('UPDATE proveedores 
        SET descripcion = ?, 
        direccion = ?, 
        telefono = ? 
        WHERE id_proveedor = ?', 
        [
        $input['descripcion'], 
        $input['direccion'], 
        $input['telefono'], 
        $id
    ]
);
        Alert::toast('El proveedor actualizado con exito', 'success');
        return redirect()->route('proveedores.index');
    }
    public function destroy($id)
    {
        $proveedor = DB::selectOne('SELECT * FROM proveedores WHERE id_proveedor = ?', [$id]);
        if(empty($proveedor)){
            Alert::toast('Proveedor no encontrado', 'error');
            return redirect()->route('proveedores.index');
        }
        DB::delete('DELETE FROM proveedores 
        WHERE id_proveedor = ?', [$id]);
        Alert::toast('El proveedor eliminado con exito', 'success');
        return redirect()->route('proveedores.index');
    }
}
