const formContainer = document.getElementById('form-container');
const reservationFeedback = document.getElementById('reservation-feedback');
const userId = document.getElementById('user').value;
const currentPathname = window.location.pathname;

// Ouvrir le formulaire prérempli
document.querySelectorAll('.modify-button').forEach(button => {
    button.addEventListener('click', function() 
    {
        fetch('/get/reservation', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-Token": csrf
            },
            body: JSON.stringify({ id: this.value })
        })
        .then(response => response.json())
        .then(data => {
            if ((!data || data.length === 0) || ('success' in data && data.success === false)) {
                reservationFeedback.textContent = "Impossible de charger les données, veuillez réessayer.";
                reservationFeedback.style.color = "red";
            }
            else {
                if (currentPathname !== `/profil/${userId}/reservation/${this.value}/modifier`) {
                    // éviter doublons dans l'historique navigateur
                    history.pushState({}, "", `/profil/${userId}/reservation/${this.value}/modifier`); 
                }
                reservationFeedback.textContent = ""; // efface message d'erreur si existant
                feedback.textContent = "";

                document.getElementById('default_reservation_time').value = data.default_reservation_time; // car n'a pas l'attribut name
                Object.entries(data).forEach(([key, value]) => 
                {
                    if (Array.isArray(value)) {
                        const checkboxes = form.querySelectorAll(`[name="${key}[]"]`);
                        checkboxes.forEach(checkboxe => {
                            checkboxe.checked = value.includes(checkboxe.value); // check si la valeur existe dans le tableau (checkbox tableau)
                        });
                        return; //fin de la boucle
                    }
                    const field = form.elements[key];
                    if (!field) return; // si le champ n'existe pas, ignorer

                    // checker les radios et checkboxes classiques
                    if (field.type === 'checkbox') {
                        field.checked = Boolean(value);
                        return;
                    }
                    if (field.type === 'radio') {
                        const radio = form.querySelector(`[name="${key}"][value="${value}"]`);
                        if (radio) radio.checked = true; 
                        return;
                    }
                    field.value = value; // input classique
                });
                const cancel = document.getElementById('form-cancel-button');
                cancel.value = this.value;
                cancel.dataset.datetime = this.dataset.datetime;
                updateDateTimeInput(date.value);
                updateFormState();
                formContainer.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            reservationFeedback.textContent = "Une erreur est survenue, veuillez réessayer.";
            feedback.textContent = "Une erreur est survenue, veuillez réessayer.";
            feedback.style.color = "red";
            reservationFeedback.style.color = "red";
        });
    })
})
// Revenir à la page normale si l'utilisateur ferme le formulaire.
document.getElementById('close-form-button')?.addEventListener('click', function()
{
    window.location.href = currentPathname;
})

const cancelWarning = document.getElementById('cancel-warning');
// afficher warning au bouton annuler
document.querySelectorAll('.cancel-button').forEach(button => {
    button.addEventListener('click', function()
    {
        const warnModifButt = document.querySelector('.warning-modify-button');
        warnModifButt.value = this.value;
        warnModifButt.dataset.datetime = this.dataset.datetime;
        document.getElementById('submit-cancel').value = this.value;
        document.getElementById('warning-datetime').textContent = this.dataset.datetime;
        document.getElementById('reservation_datetime').value = this.dataset.datetime;
        document.getElementById('cancel-form').action = `/profil/${userId}/reservation/${this.value}/annuler`;
        cancelWarning.style.display = "block";
    })
})
// fermer warning
document.querySelectorAll('.close-warning-button').forEach(button => {
    button.addEventListener('click', function() {
        cancelWarning.style.display = "none";
    })
})

