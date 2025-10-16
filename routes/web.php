<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ComprasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider y todas serán
| asignadas al grupo "web" middleware.
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

// Login
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

// Recursos principales
Route::resource('cargos', App\Http\Controllers\CargoController::class);
Route::resource('departamentos', App\Http\Controllers\DepartamentoController::class);
Route::resource('proveedores', App\Http\Controllers\ProveedorController::class);
Route::resource('ciudades', App\Http\Controllers\CiudadController::class);
Route::resource('sucursales', App\Http\Controllers\SucursalController::class);
Route::resource('marcas', App\Http\Controllers\MarcaController::class);
Route::resource('productos', App\Http\Controllers\ProductoController::class);
Route::resource('users', App\Http\Controllers\UserController::class);
Route::resource('clientes', App\Http\Controllers\ClienteController::class);
Route::resource('ventas', App\Http\Controllers\VentaController::class);
Route::resource('cajas', App\Http\Controllers\CajaController::class);
Route::resource('pedidos', App\Http\Controllers\PedidosController::class);
Route::resource('compras', App\Http\Controllers\ComprasController::class);
Route::resource('stock', App\Http\Controllers\StockController::class);
Route::resource('auditoria', App\Http\Controllers\AuditoriaController::class);
Route::resource('cobros', App\Http\Controllers\CobroController::class);
Route::get('perfil', [App\Http\Controllers\UserController::class, 'perfil']);
Route::post('users/perfil/cambiar-password', [App\Http\Controllers\UserController::class, 'cambiarPassword']);

// Buscadores
Route::get('buscar-productos', [App\Http\Controllers\VentaController::class, 'buscarProducto']);
Route::get('buscar-productoscompras', [ComprasController::class, 'buscarProducto']);
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');

// PDF y Reportes
Route::get('pdf', [App\Http\Controllers\VentaController::class, 'pdf']);
Route::get('reporte-cargos', [App\Http\Controllers\ReporteController::class, 'rpt_cargos']);
Route::get('reporte-clientes', [App\Http\Controllers\ReporteController::class, 'rpt_clientes']);
Route::get('reporte-proveedores', [App\Http\Controllers\ReporteController::class, 'rpt_proveedores']);
Route::get('reporte-productos', [App\Http\Controllers\ReporteController::class, 'rpt_productos']);
Route::get('reporte-sucursales', [App\Http\Controllers\ReporteController::class, 'rpt_sucursales']);
Route::get('reporte-sucursales', [App\Http\Controllers\ReporteController::class, 'rpt_sucursales']);
Route::get('reporte-ventas', [App\Http\Controllers\ReporteController::class, 'rpt_ventas']);

// Roles y permisos
Route::resource('permissions', App\Http\Controllers\PermissionController::class);
Route::resource('roles', App\Http\Controllers\RoleController::class);

// Apertura y cierre de caja
Route::resource('apertura-cierre-caja', App\Http\Controllers\AperturaCierreCajaController::class);

## Ruta para imprimir factura
Route::get('imprimir-factura/{id}', [App\Http\Controllers\VentaController::class, 'factura']);

## Ruta para cerrar caja y recuperar los valores
Route::get('apertura_cierre/editCierre/{id}',
[App\Http\Controllers\AperturaCierreCajaController::class, 'editCierre']);

## Ruta para guardar caja cerrada
Route::get('apertura_cierre/cerrar_caja/{id}',
[App\Http\Controllers\AperturaCierreCajaController::class, 'cerrar_caja']);

