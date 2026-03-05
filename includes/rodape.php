  </div><!-- fim conteudo -->
</main>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
// Fechar modais ao clicar fora
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('aberto'); });
});
function abrirModal(id){ document.getElementById(id).classList.add('aberto'); }
function fecharModal(id){ document.getElementById(id).classList.remove('aberto'); }
</script>
</body>
</html>
