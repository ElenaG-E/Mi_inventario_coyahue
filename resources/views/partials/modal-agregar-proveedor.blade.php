<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('proveedores.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">RUT</label><input type="text" name="rut" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Teléfono</label><input type="tel" name="telefono" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="correo" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Dirección</label><textarea name="direccion" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
            </div>
        </form>
    </div>
</div>