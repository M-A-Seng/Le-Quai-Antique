import Sortable from 'sortablejs';
import { csrf } from '../../app.js';
import { saveElementsOrder } from './index.js';

// Nouveau plat
document.getElementById('new-dish-button').addEventListener('click', () => {
    document.getElementById('new-dish-container').classList.remove('hidden');
})
document.getElementById('new-dish-form').addEventListener('input', function() {
    document.getElementById('submit-dish').disabled = !this.checkValidity();
})

// Modifier un plat
let activeButton = null;
document.querySelectorAll('.modify-dish-button')?.forEach(button => 
{
    button.addEventListener('click', () => {
        const row = button.closest('li');
        const inputs = document.querySelectorAll(`.dish-${button.dataset.id}`);
        const hiddens = document.querySelectorAll(`.dish-edit-${button.dataset.id}`);

        if (activeButton && activeButton !== button) {
            const oldRow = activeButton.closest('li');
            const oldInputs = document.querySelectorAll(`.dish-${activeButton.dataset.id}`);
            const oldHiddens = document.querySelectorAll(`.dish-edit-${activeButton.dataset.id}`);
            resetInitialState(activeButton, oldInputs, oldHiddens, oldRow);
        }
        if (button.textContent === 'Modifier') {
            row.classList.add('no-dragdrop');
            inputs.forEach(input => {input.removeAttribute('disabled');})
            hiddens.forEach(hidden => {hidden.classList.remove('hidden');})
            button.textContent = "Annuler";
            activeButton = button;
            document.getElementById(`view-dish-${button.dataset.id}`).classList.add('hidden');
            document.getElementById(`edit-dish-${button.dataset.id}`).classList.remove(('hidden'));
        } else {
            resetInitialState(button, inputs, hiddens, row);
            activeButton = null;
        }
        
    })
})
function resetInitialState(button, inputs, hiddens, row) {
    hiddens.forEach(hidden => {hidden.classList.add('hidden');})
    inputs.forEach(field => {
        if (field.tagName === 'SELECT') {
            field.selectedIndex = [...field.options].findIndex(option => option.defaultSelected);
        } 
        else if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = field.defaultChecked;
        } 
        else if (field.type === 'text' || field.tagName === 'TEXTAREA') {
            field.value = field.defaultValue;
        }
        field.setAttribute('disabled', true)
    })
    row.classList.remove('no-dragdrop');
    button.textContent = 'Modifier';
    document.getElementById(`view-dish-${button.dataset.id}`).classList.remove('hidden');
    document.getElementById(`edit-dish-${button.dataset.id}`).classList.add(('hidden'));
}

// Supprimer plat
document.querySelectorAll('.delete-dish-button')?.forEach(button =>
{
    button.addEventListener('click', () => {
        document.getElementById('delete-dish-button').setAttribute('value', button.dataset.id);
        document.getElementById('dish-name').textContent = button.dataset.title;
        document.getElementById('delete-dish').classList.remove('hidden');
    })
})

// drag et drop l'ordre des plats (SortableJS)
document.querySelectorAll(".dishes")?.forEach(dishesList => {
    const initialOrder = getDishesOrder(dishesList.dataset.categoryId);
    
    new Sortable(dishesList, {
        animation: 130,
        filter: '.no-dragdrop',
        preventOnFilter: false,
        onEnd: function() {
            const currentOrder = getDishesOrder(dishesList.dataset.categoryId);
            document.getElementById(`save-dish-order-${dishesList.dataset.categoryId}`)
                .classList.toggle('hidden', JSON.stringify(initialOrder) === JSON.stringify(currentOrder));
        }
    });
})
function getDishesOrder(categoryId) {
    return [...document.querySelectorAll(`.dish-${categoryId}`)]
        .map(element => element.dataset.id);
}
// enregistrer l'ordre des plats (AJAX)
document.querySelectorAll('.save-dish-order')?.forEach(button => {
    button.addEventListener('click', () => 
    {
        const body = {order: getDishesOrder(button.dataset.categoryId)};
        saveElementsOrder('/update/dishes-order', body, csrf);
    })
})