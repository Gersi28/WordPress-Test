document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.main-menu a').forEach(link => {
    if (link.textContent.toLowerCase().includes('sign')) {
      link.classList.add('button-link');
    }
  });
});