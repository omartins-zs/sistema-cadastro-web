function showPassword() {
    const eye = document.getElementById("eye");
    const eyeSlash = document.getElementById("eye-slash");
    const fieldPassword = document.getElementById("field-password");

    const mostrarSenha = fieldPassword.type === 'password';
    fieldPassword.type = mostrarSenha ? 'text' : 'password';
    eye.style.display = mostrarSenha ? 'block' : 'none';
    eyeSlash.style.display = mostrarSenha ? 'none' : 'block';
}
