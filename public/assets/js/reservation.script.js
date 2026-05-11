const requireLoginModal = document.getElementById('require-login-modal');
const formFields = document.getElementById('form-fields');
const title = document.querySelector('h1');

// Vérifier les entrées et ouvrire le modal se connecter/s'inscrire
document.querySelector('.display-require-login-button')?.addEventListener('click', function()
{
    const formData = new FormData(form);
    formData.append('action', submit.value);
    fetch('/prepare/reservation', {
        method: 'POST',
        headers: {
            "Accept": "application/json",
            "X-CSRF-Token": csrf
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if ('success' in data && data.success === true) {
            feedback.textContent = ""; // effacer message d'erreur si existant
            requireLoginModal.style.display = 'block';
        } else {
            feedback.textContent = data.error_message;
            feedback.style.color = "red";
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        feedback.textContent = "Une erreur interne est survenue. Veuillez réessayer";
        feedback.style.color = "red";
    });
})

// Fermer le modal se connecter/s'inscrire
document.querySelectorAll('.close-modal')?.forEach(element => {
    element.addEventListener('click', function() {
        requireLoginModal.style.display = 'none';
    })
});

// Masque les inputs si le recap est actif
const recapDisplayObserver = new MutationObserver(() => {
  const isVisible = getComputedStyle(recap).display !== 'none';

  formFields.style.display = isVisible ? 'none' : 'block';
  title.textContent = isVisible ? 'Vérifiez votre réservation' : 'Réserver une table';
});
if (recap) {
    recapDisplayObserver.observe(recap, {
        attributes: true,
        attributeFilter: ['style'], // écoute uniquement style
    });
}