import Sortable from 'sortablejs';

const categoryFormContainer = document.getElementById('category-form-container');
// afficher formulaire
document.getElementById('new-category-button').addEventListener('click', function() 
{
    categoryFormContainer.style.display = 'block';
})

let activeButton = null;
// Liste des catégories
document.querySelectorAll('.category-button').forEach(button => 
{
    button.addEventListener('click', () => {
        const input = document.getElementById(`cat-${button.dataset.id}`);

        if (activeButton && activeButton !== button) {
            const oldInput = document.getElementById(`cat-${activeButton.dataset.id}`);
            resetInitialState(activeButton, oldInput);
        }
        if (button.textContent === 'Modifier') {
            button.textContent = 'Annuler';
            input.setAttribute("name", "title");
            input.removeAttribute("readonly");
            document.querySelectorAll(`.cat-${button.dataset.id}`).forEach(element => {
                element.style.display = 'inline';
            })
            activeButton = button;
        } else {
            resetInitialState(button, input)
        }
    })
})
function resetInitialState(button, input) {
    button.textContent = 'Modifier';
    input.value = button.dataset.title;
    input.removeAttribute("name");
    input.setAttribute("readonly", true);
    document.querySelectorAll(`.cat-${button.dataset.id}`).forEach(element => {
        element.style.display = 'none';
    })
}

const csrf = document.getElementById('csrf_token')?.value;
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
                document.getElementById('delete-category').style.display = 'block';
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
                document.getElementById('cant-delete-category').style.display = 'block';
            }
        })
        .catch(err => console.error('Erreur :', err));
    })
})

const categories = document.getElementById('categories');
const category = document.querySelectorAll('.category');
const saveCategoryOrder = document.getElementById('save-category-order');
// SortableJS changer l'ordre des catégories
if (categories) {
    const initialOrder = getCategoriesOrder();
    new Sortable(categories, {
        handle: '.dragdrop',
        animation: 150,
        onEnd: function() {
            const currentOrder = getCategoriesOrder();
            saveCategoryOrder.style.display = JSON.stringify(initialOrder) !== JSON.stringify(currentOrder) ?
                                              'block' : 'none';
        }
    });
}
function getCategoriesOrder() {
    return [...document.querySelectorAll('.category')]
        .map(element => element.dataset.id);
}
// AJAX Enregistrer l'ordre des catégorie
saveCategoryOrder?.addEventListener('click', function() {

    fetch('/update/categories-order', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-Token": csrf
        },
        body: JSON.stringify({
            order: getCategoriesOrder()
        })
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
    });
});