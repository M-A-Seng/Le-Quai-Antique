import '../css/app.css';

export const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

// ouvrir conteneur
document.querySelectorAll('.open-container').forEach(element => {
    element.addEventListener('click', () => {
        const container = document.getElementById(element.dataset.containerId);
        if (container) {
            container.classList.remove('hidden');
        }
    });
});

// fermer conteneur
document.querySelectorAll('.close-container').forEach(element => {
    element.addEventListener('click', () => {
        const container = document.getElementById(element.dataset.containerId);
        if (container) {
            container.classList.add('hidden');
        }
    });
});

// activer bouton soumission dans formulaires
document.querySelectorAll('.form-check-validity').forEach(form => {
    form.addEventListener('input', () => {
        document.getElementById(form.dataset.submitButton).disabled = !form.checkValidity();
    });
});