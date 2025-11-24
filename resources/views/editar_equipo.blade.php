@extends('layouts.base')

@section('contenido')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Equipo - EQ{{ $equipo_id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('detalle_equipo', $equipo_id) }}" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-arrow-left me-1"></i>Volver al Detalle
        </a>
        <a href="{{ route('inventario') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-list me-1"></i>Volver al Inventario
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow">
            <div class="card-header text-white bg-orange">
                <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>Editar Información del Equipo</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Equipo</label>
                            <select class="form-select">
                                <option value="notebook" selected>Notebook</option>
                                <option value="pc">PC Escritorio</option>
                                <option value="impresora">Impresora</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca</label>
                            <select class="form-select">
                                <option value="lenovo" selected>Lenovo</option>
                                <option value="dell">Dell</option>
                                <option value="hp">HP</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" value="ThinkPad X1 Carbon">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Serie</label>
                            <input type="text" class="form-control" value="LX123456789">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Compra</label>
                            <input type="date" class="form-control" value="2024-03-15">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Proveedor</label>
                            <select class="form-select">
                                <option value="proveedor1" selected>TecnoChile S.A.</option>
                                <option value="proveedor2">CompuMundo</option>
                                <option value="proveedor3">ImportTech</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select">
                            <option value="disponible" selected>Disponible</option>
                            <option value="asignado">Asignado</option>
                            <option value="mantencion">En Mantención</option>
                            <option value="baja">Dado de Baja</option>
                        </select>
                    </div>

                    <!-- Especificaciones Técnicas -->
                    <div class="card mt-4">
                        <div class="card-header text-white" style="background: #ff8e53;">
                            <h6 class="mb-0">Especificaciones Técnicas</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Procesador</label>
                                    <input type="text" class="form-control" value="Intel Core i7-1165G7">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">RAM (GB)</label>
                                    <input type="number" class="form-control" value="16">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Almacenamiento (GB)</label>
                                    <input type="number" class="form-control" value="512">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sistema Operativo</label>
                                    <input type="text" class="form-control" value="Windows 11 Pro">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Pantalla (Pulgadas)</label>
                                    <input type="number" class="form-control" value="14">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Batería (Horas)</label>
                                    <input type="number" class="form-control" value="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection