import '../../shared/reorder.js';
import './categories.js';
// import './dishes.js'; // vide
// import './setmenus.js'; // vide

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

// Afficher modal confirmation de suppression
document.querySelectorAll('.delete-button')?.forEach(button => {
    button.addEventListener('click', () => 
    {
        document.getElementById(button.dataset.deleteButtonId).value = button.dataset.id;
        document.getElementById(button.dataset.placeholderId).textContent = button.dataset.title;
        document.getElementById(button.dataset.containerId).classList.remove('hidden');
    })
})