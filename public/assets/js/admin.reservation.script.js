// Capacité du restaurant en temps réel, actualisation toutes les minutes.
function fetchServiceCapacity(serviceId, element) {
    fetch('/get/capacity', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-Token": csrf
        },
        body: JSON.stringify({ serviceId: serviceId })
    })
    .then(response => response.json())
    .then(data => {
        if ((!data || data.length === 0) || ('success' in data && data.success === false)) {
            console.error("Impossible de récupérer la capacité du restaurant.");
            return;
        }
        element.textContent = data.remaining_places;
    })
    .catch(err => console.error('Erreur :', err));
}
document.querySelectorAll('.remaining-places')?.forEach(element => 
{
    setInterval(() => {
        fetchServiceCapacity(element.dataset.serviceId, element);
    }, 60000);
});

// afficher/masquer les réservations par service
document.querySelectorAll('.service')?.forEach(element => {
    element.addEventListener('click', function() 
    {
        document.querySelectorAll('.reservations-table').forEach(table => {
            table.classList.add('hidden');
        })
        document.querySelectorAll('.capacity-info').forEach(element => {
            element.classList.add('hidden');
        })
        document.getElementById(this.dataset.service).classList.remove('hidden');
        document.getElementById(`${this.dataset.service}-capacity`).classList.remove('hidden');
    })
})

// dérouler/masquer les détails
document.querySelectorAll('.detail-button')?.forEach(button => {
    button.addEventListener('click', function () 
    {
        const details = document.getElementById(`${this.dataset.id}-details`);
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
        details.style.display = details.style.display === 'none' ? button.textContent = "✎ Détails" : button.textContent = "✖ Fermer";
    })
})

// changer de date
document.getElementById('search-form').addEventListener('submit', function(event) 
{
    event.preventDefault();
    const input = document.getElementById('date-search');
    const date = input.value;
    if (!date) {
        console.log('Aucune date sélectionnée');
        return;
    }
    const [year, month, day] = date.split('-');
    window.location.href = `/admin/${input.dataset.userId}/reservations/${day}-${month}-${year}`;
})
