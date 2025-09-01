document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalAdicionar');
    const btnAdicionar = document.getElementById('btnAdicionar');
    const btnCancelar = document.getElementById('btnCancelar');
    const form = document.getElementById('formAdicionarUsuario');

    btnAdicionar.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    btnCancelar.addEventListener('click', () => {
        modal.style.display = 'none';
        form.reset();
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            form.reset();
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const data = {
            nome: form.nome.value,
            email: form.email.value,
            senha: form.senha.value,
            perfil: form.perfil.value
        };

        fetch('ADICIONAR_USUARIO.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.text())
          .then(msg => {
              alert(msg);
              modal.style.display = 'none';
              form.reset();
              location.reload();
          }).catch(() => {
              alert('Erro ao adicionar usuário.');
          });
    });
});