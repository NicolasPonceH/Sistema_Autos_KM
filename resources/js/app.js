// Feedback de carga genérico: al enviar cualquier form, deshabilita su botón
// de submit y le agrega un spinner. Comunica estado (no decoración) y de
// paso evita el doble-submit accidental.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const boton = form.querySelector('button[type="submit"]');

    if (!boton || boton.disabled) {
        return;
    }

    boton.disabled = true;
    boton.classList.add('cursor-wait', 'opacity-70');

    const spinner = document.createElement('span');
    spinner.className = '-ml-0.5 mr-1.5 inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent align-[-2px]';
    boton.prepend(spinner);
});
