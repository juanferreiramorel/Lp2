<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laracasts\Flash\Flash;

class DepartamentoController extends Controller
{
    public function index()
    {
        $departamentos = DB::select('SELECT * FROM departamentos');
        return view('departamentos.index')->with('departamentos', $departamentos);
    }

    public function create()
    {
        return view('departamentos.create');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make(
            $input,
            [
                'descripcion' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::insert('INSERT INTO departamentos (descripcion) VALUES (?)', [
            $input['descripcion']
        ]);

        Flash::success("El departamento fue creado con éxito.");
        return redirect(route('departamentos.index'));
    }

    public function edit($id)
    {
        $departamento = DB::selectOne('SELECT * FROM departamentos WHERE id_departamento = ?', [$id]);

        if (empty($departamento)) {
            Flash::error("El departamento no fue encontrado.");
            return redirect()->route('departamentos.index');
        }

        return view('departamentos.edit')->with('departamento', $departamento);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $departamento = DB::selectOne('SELECT * FROM departamentos WHERE id_departamento = ?', [$id]);

        if (empty($departamento)) {
            Flash::error("El departamento no fue encontrado.");
            return redirect()->route('departamentos.index');
        }

        $validator = Validator::make(
            $input,
            [
                'descripcion' => 'required',
            ],
            [
                'descripcion.required' => 'El campo descripción es obligatorio.',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::update('UPDATE departamentos SET descripcion = ? WHERE id_departamento = ?', [
            $input['descripcion'],
            $id
        ]);

        Flash::success("El departamento fue actualizado con éxito.");
        return redirect()->route('departamentos.index');
    }

    public function destroy($id)
    {
        $departamento = DB::selectOne('SELECT * FROM departamentos WHERE id_departamento = ?', [$id]);

        if (empty($departamento)) {
            Flash::error("El departamento no fue encontrado.");
            return redirect()->route('departamentos.index');
        }

        DB::delete('DELETE FROM departamentos WHERE id_departamento = ?', [$id]);

        Flash::success("El departamento fue eliminado con éxito.");
        return redirect()->route('departamentos.index');
    }
}
