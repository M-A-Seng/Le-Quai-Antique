import Sortable from 'sortablejs';
import { csrf } from '../../app.js';
import { saveElementsOrder } from './index.js';

const categoryFormContainer = document.getElementById('category-form-container');
// afficher formulaire
document.getElementById('new-category-button').addEventListener('click', function() 
{
    categoryFormContainer.classList.remove('hidden');
})

let activeButton = null;
// Liste des catégories
document.querySelectorAll('.category-button').forEach(button => 
{
    button.addEventListener('click', () => {
        const row = button.closest('li');
        const view = document.getElementById(`view-category-${button.dataset.id}`);
        const edit = document.getElementById(`edit-category-${button.dataset.id}`);

        if (activeButton && activeButton !== button) {
            const oldRow = activeButton.closest('li');
            const oldView = document.getElementById(`view-category-${activeButton.dataset.id}`);
            const oldEdit = document.getElementById(`edit-category-${activeButton.dataset.id}`);
            resetInitialState(activeButton, oldRow, oldView, oldEdit);
        }
        if (button.textContent === 'Modifier') {
            button.textContent = 'Annuler';
            row.classList.add('no-dragdrop');
            document.querySelectorAll(`.cat-${button.dataset.id}`).forEach(element => {
                element.removeAttribute("disabled");
                element.classList.remove('hidden');
            })
            activeButton = button;
            view.classList.add('hidden');
            edit.classList.remove('hidden');
        } else {
            resetInitialState(button, row, view, edit);
            activeButton = null;
        }
    })
})
function resetInitialState(button, row, view, edit) {
    button.textContent = 'Modifier';
    row.classList.remove('no-dragdrop');
    document.querySelectorAll(`.cat-${button.dataset.id}`).forEach(element => {
        element.classList.add('hidden');
        element.setAttribute("disabled", true);
        if (element.tagName === 'INPUT') {
            element.value = element.defaultValue;
        }
    })
    edit.classList.add('hidden');
    view.classList.remove('hidden');
}

const dishesContainer = document.getElementById('dishes-in-category');
// AJAX check avant suppression categorie
document.querySelectorAll('.delete-category-button').forEach(button => {
    button.addEventListener('click', function() 
    {
        fetch('/check/category', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-Token": csrf
            },
            body: JSON.stringify({ id: button.dataset.id })
        })
        .then(response => response.json())
        .then(data => {
            if ((!data || data.length === 0)) {
                console.error("Aucune donnée reçue de AJAX.");
                return;
            }
            if ('can_delete' in data && data.can_delete === true) {
                document.getElementById('delete-category-button').setAttribute('value', button.dataset.id);
                document.getElementById('category-name').textContent = button.dataset.title;
                document.getElementById('delete-category').classList.remove('hidden');
            } 
            else {
                const ul = document.createElement('ul');
                data.forEach(dish => {
                    const li = document.createElement('li');
                    li.textContent = dish;
                    ul.appendChild(li);
                });
                dishesContainer.innerHTML = '';
                dishesContainer.appendChild(ul);
                document.getElementById('cant-delete-category').classList.remove('hidden');
            }
        })
        .catch(err => console.error('Erreur :', err));
    })
})

const categories = document.getElementById('categories');
// const category = document.querySelectorAll('.category');
const saveCategoryOrder = document.getElementById('save-category-order');
// SortableJS changer l'ordre des catégories
if (categories) {
    const initialOrder = getCategoriesOrder();
    new Sortable(categories, {
        animation: 130,
        filter: '.no-dragdrop',
        preventOnFilter: false,
        onEnd: function() {
            const currentOrder = getCategoriesOrder();
            saveCategoryOrder.classList.toggle('hidden', JSON.stringify(initialOrder) === JSON.stringify(currentOrder));
        }
    });
}
function getCategoriesOrder() {
    return [...document.querySelectorAll('.category')]
        .map(element => element.dataset.id);
}
// AJAX Enregistrer l'ordre des catégorie
saveCategoryOrder?.addEventListener('click', function() {
    const body = {order: getCategoriesOrder()};
    saveElementsOrder('/update/categories-order', body, csrf);
});