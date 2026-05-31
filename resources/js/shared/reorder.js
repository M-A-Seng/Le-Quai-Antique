import Sortable from 'sortablejs';
import { csrf } from '../app.js';

// drag & drop + display bouton save
document.querySelectorAll('.sortable')?.forEach(list => {
    const initialOrder = getListOrder(list.dataset.liClassname);
    const saveButton = document.getElementById(list.dataset.saveButtonId);
    
    new Sortable(list, {
        animation: 130,
        filter: '.no-dragdrop',
        preventOnFilter: false,
        onEnd: function() {
            const currentOrder = getListOrder(list.dataset.liClassname);
            saveButton.classList.toggle('hidden', JSON.stringify(initialOrder) === JSON.stringify(currentOrder));
        }
    });
})

// retourne la liste des éléments dans l'ordre html
function getListOrder(li_className) {
    return [...document.querySelectorAll(`.${li_className}`)]
        .map(element => element.dataset.id);
}

// sauvegarder l'ordre des listes
document.querySelectorAll('.save-list-order').forEach(button => {
    button.addEventListener('click', () => {
        saveElementsOrder(button.dataset.url, {order: getListOrder(button.dataset.liClassname)}, csrf);
    })
})

// sauvegarder l'ordre des éléments (après drag & drop)
function saveElementsOrder(url, body, csrf)
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