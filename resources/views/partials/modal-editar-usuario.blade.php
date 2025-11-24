@foreach($usuarios as $usuario)
<div class="modal fade" id="modalEditarUsuario{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $usuario->nombre }}" required>
                    @error('nombre') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $usuario->email }}" required>
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="{{ $usuario->telefono }}" required>
                    @error('telefono') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" class="form-select" required>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ $usuario->rol_id == $rol->id ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" {{ $usuario->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ $usuario->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                    @error('estado') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control">
                    <small class="text-muted">Deja en blanco si no deseas cambiarla</small>
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Repetir Contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control">
                    <small class="text-muted">Solo si cambias la contraseña</small>
                    @error('password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endforeach