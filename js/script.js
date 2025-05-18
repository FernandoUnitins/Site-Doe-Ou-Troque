// Interatividade do Botão
document.querySelector('.zap-button').addEventListener('click', () => {
  window.location.href = '#';
  alert('Parabéns! Sua jornada fitness começará em breve!');
});

// Animação de Carregamento
window.addEventListener('load', () => {
  document.body.classList.add('loaded');
});
