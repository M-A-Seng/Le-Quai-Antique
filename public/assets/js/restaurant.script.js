function generateTimeOptions(select, startHour, endHour, startDefault = null, stepMinutes=15) {
    let hours = startHour;
    do {
        for (let m = 0; m < 60; m += stepMinutes) {
            if (hours === endHour && m > 0) break;
            const hh = String(hours).padStart(2,'0');
            const mm = String(m).padStart(2,'0');
            const option = document.createElement('option');
            option.value = `${hh}:${mm}`;
            option.textContent = `${hh}:${mm}`;
            select.appendChild(option);

            if (startDefault && `${hh}:${mm}` === startDefault) {
                option.selected = true;
            }
        }
        hours = (hours + 1) % 24; // boucle après minuit
    } while (hours !== (endHour + 1) % 24);
}
const lunchOpeningInput = document.getElementById('lunch_opening_time');
const eveningOpeningInput = document.getElementById('evening_opening_time');
const defaultLunch = document.getElementById('default_lunch_opening_time').value;
const defaultEvening = document.getElementById('default_evening_opening_time').value;
generateTimeOptions(lunchOpeningInput, 7, 16, defaultLunch);
generateTimeOptions(eveningOpeningInput, 16, 2, defaultEvening);

function calculateClosingTime(time) {
    const date = new Date(`1970-01-01T${time}`);
    date.setHours(date.getHours() + 2);
    return date.toTimeString().slice(0, 5);
}

const lunchClosingInput = document.getElementById('lunch_closing_time');
lunchOpeningInput.addEventListener('input', function(event) {
    if (!event.target.value) return;
    lunchClosingInput.value = calculateClosingTime(event.target.value);
})

const eveningClosingInput = document.getElementById('evening_closing_time');
eveningOpeningInput.addEventListener('input', function(event) {
    if (!event.target.value) return;
    eveningClosingInput.value = calculateClosingTime(event.target.value);
})