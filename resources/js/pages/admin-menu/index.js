import Sortable from 'sortablejs';
import { csrf } from '../../app.js';
import './categories.js';
// import './dishes.js';
// import './setmenus.js';

// swith branches catégorie/plats/menus
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

// formulaire nouveau catégorie/plats/menus
document.querySelectorAll('.new-element-form').forEach(form => {
    const submitButton = document.getElementById(form.dataset.submitButton);

    form.addEventListener('input', () => {
        submitButton.disabled = !form.checkValidity();
    });
});

// basculer view/edit des éléments de liste catégorie/plats/menus
let activeButton = null;
document.querySelectorAll('.modify-button').forEach(button => {
    button.addEventListener('click', () => 
    {
        const row = button.closest('li');
        const view = document.getElementById(`view-${button.dataset.branch}-${button.dataset.id}`);
        const edit = document.getElementById(`edit-${button.dataset.branch}-${button.dataset.id}`);
        const formElements = document.querySelectorAll(`.${button.dataset.branch}-${button.dataset.id}`);

        if (activeButton !== null && activeButton !== button) {
            resetListInitialState(activeButton);
        }
        if (button.textContent.trim() === 'Modifier')
        {
            row.classList.add('no-dragdrop');
            formElements.forEach(element => {
                element.removeAttribute("disabled");
            })
            activeButton = button;
            button.textContent = 'Annuler';
            view.classList.add('hidden');
            edit.classList.remove('hidden');
        } 
        else {
            resetListInitialState(button);
            activeButton = null;
        }
    })
})
// restaurer les valeurs initiales
function resetListInitialState(button)
{
    const row = button.closest('li');
    const view = document.getElementById(`view-${button.dataset.branch}-${button.dataset.id}`);
    const edit = document.getElementById(`edit-${button.dataset.branch}-${button.dataset.id}`);
    const formElements = document.querySelectorAll(`.${button.dataset.branch}-${button.dataset.id}`);

    formElements.forEach(element => {
        element.setAttribute("disabled", true);

        if (element.tagName === 'SELECT') {
            element.selectedIndex = [...element.options].findIndex(option => option.defaultSelected);
        } 
        else if (element.type === 'checkbox' || element.type === 'radio') {
            element.checked = element.defaultChecked;
        } 
        else if (element.type === 'text' || element.tagName === 'TEXTAREA') {
            element.value = element.defaultValue;
        }
    })
    button.textContent = 'Modifier';
    row.classList.remove('no-dragdrop');
    edit.classList.add('hidden');
    view.classList.remove('hidden');
}

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

// Afficher modal confirmation de suppression
document.querySelectorAll('.delete-button')?.forEach(button => {
    button.addEventListener('click', () => 
    {
        document.getElementById(button.dataset.deleteButtonId).value = button.dataset.id;
        document.getElementById(button.dataset.placeholderId).textContent = button.dataset.title;
        document.getElementById(button.dataset.containerId).classList.remove('hidden');
    })
})