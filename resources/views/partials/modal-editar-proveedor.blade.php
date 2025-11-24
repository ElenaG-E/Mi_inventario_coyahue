@foreach($proveedores as $proveedor)
<div class="modal fade" id="modalEditar{{ $proveedor->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">RUT</label><input type="text" name="rut" class="form-control" value="{{ $proveedor->rut }}" required></div>
                <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" value="{{ $proveedor->nombre }}" required></div>
                <div class="mb-3"><label class="form-label">Teléfono</label><input type="tel" name="telefono" class="form-control" value="{{ $proveedor->telefono }}"></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="correo" class="form-control" value="{{ $proveedor->correo }}"></div>
                <div class="mb-3"><label class="form-label">Dirección</label><textarea name="direccion" class="form-control" rows="3">{{ $proveedor->direccion }}</textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endforeach