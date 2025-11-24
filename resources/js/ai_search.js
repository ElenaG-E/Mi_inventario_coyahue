document.addEventListener('DOMContentLoaded', () => {
    const iaButton = document.querySelector('#buscarIAButton');
    const numeroSerieInput = document.querySelector('#numero_serie');
    const marcaInput = document.querySelector('input[name="marca"]');
    const modeloInput = document.querySelector('input[name="modelo"]');
    const tipoEquipoSelect = document.querySelector('select[name="tipo_equipo_id"]');
    const precioInput = document.querySelector('input[name="precio"]');
    const sucursalSelect = document.querySelector('select[name="sucursal_id"]');

    if (iaButton && numeroSerieInput) {
        iaButton.disabled = false;
        iaButton.addEventListener('click', () => {
            const numeroSerie = numeroSerieInput.value.trim();
            if (!numeroSerie) {
                alert('Por favor, introduce un número de serie para buscar con IA.');
                return;
            }

            iaButton.disabled = true;
            iaButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';

            fetch(`/registro-equipo/ia-buscar?numero_serie=${encodeURIComponent(numeroSerie)}`)
                .then(response => response.json())
                .then(data => {
                    iaButton.disabled = false;
                    iaButton.innerHTML = '<i class="fas fa-robot me-2"></i>Buscar con IA';
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    if (data.message) {
                        alert(data.message);
                        return;
                    }

                    // Fill form fields with returned data
                    if (marcaInput) marcaInput.value = data.marca || '';
                    if (modeloInput) modeloInput.value = data.modelo || '';
                    if (tipoEquipoSelect) tipoEquipoSelect.value = data.tipo_equipo_id || '';
                    if (precioInput) precioInput.value = data.precio || '';
                    if (sucursalSelect) sucursalSelect.value = data.sucursal_id || '';
                })
                .catch(error => {
                    iaButton.disabled = false;
                    iaButton.innerHTML = '<i class="fas fa-robot me-2"></i>Buscar con IA';
                    alert('Error al buscar con IA. Por favor intenta más tarde.');
                    console.error('Error in IA search:', error);
                });
        });
    }
});
