function generateTimeOptions(select, startHour, endHour, startDefault = null, stepMinutes=15) {
    let hours = startHour;
    do {
        for (let m = 0; m < 60; m += stepMinutes) {
            if (hours === endHour && m > 0) break; // arrête la boucle si l'heure de fin est atteinte
            const hh = String(hours).padStart(2,'0'); //formater l'heure en ajoutant 0 si 1 seul chiffre
            const mm = String(m).padStart(2,'0');
            const option = document.createElement('option');
            option.value = `${hh}:${mm}`;
            option.textContent = `${hh}:${mm}`;
            select.appendChild(option);

            if (startDefault && `${hh}:${mm}` === startDefault) {
                option.selected = true; // valeur par défaut si existante
            }
        }
        hours = (hours + 1) % 24; // boucle après minuit
    } while (hours !== (endHour + 1) % 24);
}
const lunchOpeningInput = document.getElementById('lunch_opening_time');
const dinnerOpeningInput = document.getElementById('dinner_opening_time');
const defaultLunch = document.getElementById('default_lunch_opening_time').value;
const defaultDinner = document.getElementById('default_dinner_opening_time').value;

generateTimeOptions(lunchOpeningInput, 4, 3, defaultLunch);
generateTimeOptions(dinnerOpeningInput, 4, 3, defaultDinner);

function calculateClosingTime(openingTime, duration) {
    // ouverture en minutes
    let [h1, m1] = openingTime.split(':').map(Number);
    let openingMinutes = h1 * 60 + m1;
    // durée en minutes
    let [h2, m2, s2] = duration.split(':').map(Number);
    let durationMinutes = h2 * 60 + m2 + Math.floor(s2 / 60);
    // fermeture
    let totalMinutes = openingMinutes + durationMinutes;
    let h = Math.floor(totalMinutes / 60) % 24;
    let m = totalMinutes % 60;

    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0'); // heure de fermeture en H:i
}

const lunchClosingInput = document.getElementById('lunch_closing_time');
const lunchDuration = document.getElementById('lunch_service_duration').value;
lunchOpeningInput.addEventListener('input', function(event) {
    if (!event.target.value) return; // si l'input est vide il ne se passe rien
    lunchClosingInput.value = calculateClosingTime(event.target.value, lunchDuration); // calcul l'heure de fin en fonction de l'input
})

const dinnerClosingInput = document.getElementById('dinner_closing_time');
const dinnerDuration = document.getElementById('dinner_service_duration').value;
dinnerOpeningInput.addEventListener('input', function(event) {
    if (!event.target.value) return;
    dinnerClosingInput.value = calculateClosingTime(event.target.value, dinnerDuration);
})