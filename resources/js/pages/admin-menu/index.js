import './categories.js';
import './dishes.js';

// fermer les pop-up
document.querySelectorAll('.close-modal').forEach(element => {
    element.addEventListener('click', () => {
        const modal = document.getElementById(element.dataset.modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    });
});

// Afficher les branches
document.querySelectorAll('.branch-button')?.forEach(button => {
    button.addEventListener('click', () => 
    {
        document.querySelectorAll('.branch').forEach(branch => {
            branch.classList.add('hidden');
        })
        history.pushState({}, "", button.dataset.pathname);
        document.getElementById(button.dataset.branchId).classList.remove('hidden');
    })
})

// sauvegarder l'ordre des éléments (après drag & drop)
export function saveElementsOrder(url, body, csrf)
{
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': csrf
        },
        body: JSON.stringify(body)
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.success === true) {
            window.location.href = data.redirect;
        } else {
            throw new Error(data.message || 'Erreur serveur');
        }
    })
    .catch(err => {
        console.error('Erreur :', err);
        throw err;
    });
}