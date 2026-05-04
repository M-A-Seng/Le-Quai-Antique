// Fonction générer les données pour le champ select
function generateTimeSlots(ranges, interval = 15) {
    const slots = [];
    // fonction converti l'heure en minute
    const toMinutes = (time) => {
        const [h, m] = time.split(':').map(Number);
        return h * 60 + m;
    };
    // fonction formate les minutes en H:i
    const toTime = (minutes) => {
        minutes = minutes % (24 * 60); // wrap sur 24h
        return String(minutes / 60 | 0).padStart(2, '0') + ':' + String(minutes % 60).padStart(2, '0');
    };
    // Générer la liste d'heure
    for (const {start, end, complete} of ranges) {
        let s = toMinutes(start);
        let e = toMinutes(end);
        if (e < s) e += 24 * 60; //dépassement minuit

        for (; s <= e; s += interval) {
            // tant que start <= end
            slots.push({
                time: toTime(s),
                complete
            });
        }
    }
    return slots; // objet heure + bool (complete)
}

// Fonction remplir le champ select d'heures ouvrées
function fillSelect(select, slots, defaultTime = null) {
    select.innerHTML = '';
    const fragment = document.createDocumentFragment();

    for (const { time, complete } of slots) {
        const option = document.createElement('option');

        option.value = time;
        option.textContent = complete ? `${time} COMPLET` : time; // suffix si créneau plein
        option.disabled = complete;
        if (defaultTime === time) {
            option.selected = true; // désactivé si créneau plein
        }
        fragment.appendChild(option);
    }
    select.appendChild(fragment);
}

const form = document.querySelector('form');
const csrf = document.getElementById('csrf_token').value;
const feedback = document.getElementById('feedback');
const date = document.getElementById('reservation_date');
const time = document.getElementById('reservation_time');
const defaultTime = document.getElementById('default_reservation_time').value;
const guestCount = document.getElementById('guest_count');
const clientName = document.getElementById('client_name');

// Activer bouton de soumission si tout est ok
function updateFormState() {
    const hasDate = date.value.trim() !== '';
    const hasTime = time.value.trim() !== '' && !time.disabled;
    guestCount.disabled = !(hasDate && hasTime); // activer input nombre d'invités si date heure fourni

    const valid =
        hasDate &&
        hasTime &&
        guestCount.value.trim() !== '' &&
        clientName.value.trim() !== '' &&
        !date.disabled &&
        !time.disabled &&
        !guestCount.disabled;
    document.getElementById('submit-button').disabled = !valid;
}
date.addEventListener('change', updateFormState);
time.addEventListener('change', updateFormState);
guestCount.addEventListener('change', updateFormState);
clientName.addEventListener('change', updateFormState);

// AJAX récupérer les horaires et générer les options du select
function updateDateTimeInput(date) {
    const dateFeedback = document.getElementById('date-feedback');
    if (!date) return; // empêche fetch si le champ date est vide

    fetch('/get/restaurant-services', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-Token": csrf
        },
        body: JSON.stringify({ date: date })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.services || data.services.length === 0) {
            time.disabled = true;
            time.value = '';
            dateFeedback.textContent = "Le restaurant est fermé ce jour, veuillez sélectionner une autre date.";
            dateFeedback.style.color = "red";
            updateFormState();
        } else {
            time.disabled = false;
            dateFeedback.textContent = ""; // efface message d'erreur
            const slots = generateTimeSlots(data.services);
            fillSelect(time, slots, defaultTime);
            updateFormState();
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        time.disabled = true;
        dateFeedback.textContent = "Impossible de récupérer les horaires du restaurant. Essayez une autre date.";
        dateFeedback.style.color = "red";
        updateFormState();
    });
}
window.addEventListener('load', () => { updateDateTimeInput(date.value); });
date.addEventListener('input', function() { updateDateTimeInput(this.value); });

// AJAX vérifier la capacité du restaurant
guestCount.addEventListener('change', function()
{
    fetch('/check/availability', {
        method: 'POST',
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-Token": csrf
        },
        body: JSON.stringify({
            reservation_date: date.value,
            reservation_time: time.value,
            guest_count: this.value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.canReserve === false) {
            feedback.textContent = "Le créneau que vous avez choisi n'a plus assez de places pour votre groupe. Essayez un autre horaire ou date.";
            feedback.style.color = "red";
        } else {
            feedback.textContent = "";// effacer message d'erreur
        }
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
        feedback.textContent = "Une erreur interne est survenue, veuillez réessayer.";
        feedback.style.color = "red";
        updateFormState();
    });
})

// Fermer le modal se connecter/s'inscrire
document.querySelectorAll('.close-modal').forEach(element => {
    element.addEventListener('click', function() {
        document.getElementById('require-login-modal').style.display = 'none';
    })
});