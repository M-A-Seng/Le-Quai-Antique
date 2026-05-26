import { csrf } from '../../app.js';

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
