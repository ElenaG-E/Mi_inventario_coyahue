@foreach($usuarios as $usuario)
<div class="modal fade" id="confirmarEliminar{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="modal-content">
            @csrf @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Seguro que deseas eliminar al usuario <strong>{{ $usuario->nombre }}</strong>?</p>
                <div class="mb-3">
                    <label class="form-label">Ingresa tu contraseña para confirmar</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                    @error('password_confirm') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endforeach