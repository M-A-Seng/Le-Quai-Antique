import './categories.js';

document.querySelectorAll('.close-modal').forEach(element => {
    element.addEventListener('click', () => {
        const modal = document.getElementById(element.dataset.modalId);
        if (modal) {
            modal.style.display = "none";
        }
    });
});