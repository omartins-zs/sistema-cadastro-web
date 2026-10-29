/**
 * Alterna visibilidade da senha de forma acessível e dinâmica.
 * @param {string} inputId ID do campo de senha
 * @param {HTMLElement} btnElement Botão que disparou a ação
 */
function togglePasswordVisibility(inputId, btnElement) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    const icon = btnElement ? btnElement.querySelector('i') : null;
    if (icon) {
        if (isPassword) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            btnElement.setAttribute('aria-label', 'Ocultar senha');
        } else {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            btnElement.setAttribute('aria-label', 'Mostrar senha');
        }
    }
}

// Compatibilidade com chamadas legadas
function showPassword() {
    const fieldPassword = document.getElementById("field-password") || document.getElementById("txt_senha");
    if (!fieldPassword) return;

    const eye = document.getElementById("eye");
    const eyeSlash = document.getElementById("eye-slash");
    const mostrarSenha = fieldPassword.type === 'password';

    fieldPassword.type = mostrarSenha ? 'text' : 'password';

    if (eye && eyeSlash) {
        eye.style.display = mostrarSenha ? 'block' : 'none';
        eyeSlash.style.display = mostrarSenha ? 'none' : 'block';
    }
}

// ---- Melhorias de UX globais ----
document.addEventListener('DOMContentLoaded', function () {
    // Alertas de sucesso/erro desaparecem sozinhos após alguns segundos
    document.querySelectorAll('.alert[data-autohide]').forEach(function (el) {
        setTimeout(function () {
            el.classList.add('is-hiding');
            setTimeout(function () { el.remove(); }, 450);
        }, 6000);
    });

    // Botão de envio fica "carregando" e evita clique duplo
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) { return; }
            var btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.closest('form[onsubmit]')) {
                setTimeout(function () { btn.classList.add('is-loading'); }, 0);
            }
        });
    });
});
