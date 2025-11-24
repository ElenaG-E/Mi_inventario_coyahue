<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroEquipoController; // <--- Queda solo la primera

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\SucursalController;

// Página por defecto
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// AI search route for equipment registration
Route::middleware(['auth'])->group(function () {
    Route::get('/registro-equipo/ia-buscar', [RegistroEquipoController::class, 'buscarConIA'])->name('registro_equipo.buscarConIA');
});

// Login personalizado
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard protegido
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Inventario
    Route::resource('inventario', InventarioController::class)->except(['show']);
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario');
    Route::get('/inventario/autocomplete', [InventarioController::class, 'autocomplete'])->name('inventario.autocomplete');

    // ✅ Nueva ruta para asignaciones múltiples dentro de InventarioController
    Route::post('/inventario/asignaciones', [InventarioController::class, 'storeAsignaciones'])->name('inventario.asignaciones');

    // Detalle de equipo e insumo
    Route::get('/inventario/equipo/{id}', [InventarioController::class, 'detalleEquipo'])->name('inventario.equipo');
    Route::get('/inventario/insumo/{id}', [InventarioController::class, 'detalleInsumo'])->name('inventario.insumo');

    // Actualización desde modal combinado
    Route::put('/equipo/{id}', [RegistroEquipoController::class, 'update'])->name('equipo.update');
    Route::put('/insumo/{id}', [RegistroEquipoController::class, 'update'])->name('insumo.update');

    // Registro de equipos
    Route::get('/registro-equipo', [RegistroEquipoController::class, 'create'])->name('registro_equipo.create');
    Route::post('/registro-equipo', [RegistroEquipoController::class, 'store'])->name('registro_equipo.store');

    // Registro de insumos
    Route::get('/registro-insumo', [RegistroEquipoController::class, 'create'])->name('registro_insumo.create');
    Route::post('/registro-insumo', [RegistroEquipoController::class, 'storeInsumo'])->name('registro_insumo.store');

    // Eliminación de equipos
    Route::delete('/equipo/{id}', [RegistroEquipoController::class, 'destroy'])->name('equipo.destroy');

    // Eliminación de insumos
    Route::delete('/insumo/{id}', [RegistroEquipoController::class, 'destroy'])->name('insumo.destroy');

    // Autocomplete de insumos
    Route::get('/insumos/autocomplete', [RegistroEquipoController::class, 'autocompleteInsumos'])->name('insumos.autocomplete');

    // Edición de equipos (vista externa)
    Route::get('/editar-equipo/{id}', [RegistroEquipoController::class, 'edit'])->name('editar_equipo');

    // Gestión de usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('gestion_usuarios');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/usuarios/autocomplete', [UsuarioController::class, 'autocomplete'])->name('usuarios.autocomplete');

    // Gestión de proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('gestion_proveedores');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
    Route::get('/proveedores/autocomplete', [ProveedorController::class, 'autocomplete'])->name('proveedores.autocomplete');

    // Gestión de sucursales
    Route::get('/gestion-sucursales', [SucursalController::class, 'index'])->name('gestion_sucursales');
    Route::post('/gestion-sucursales', [SucursalController::class, 'store'])->name('sucursales.store');
    Route::put('/gestion-sucursales/{sucursal}', [SucursalController::class, 'update'])->name('sucursales.update');
    Route::delete('/gestion-sucursales/{sucursal}', [SucursalController::class, 'destroy'])->name('sucursales.destroy');
    Route::get('/gestion-sucursales/autocomplete', [SucursalController::class, 'autocomplete'])->name('sucursales.autocomplete');
});

require __DIR__.'/auth.php';
