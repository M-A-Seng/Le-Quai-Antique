// Vérification email en base de données avec AJAX
document.getElementById('email').addEventListener('blur', function() 
{
    let email = this.value;
    fetch("/inscription/check-email", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        let message = document.getElementById("email-message");
        message.textContent = data.isValid ? " ✔" : data.errorMessage;
        message.style.color = data.isValid ? "green" : "red";
    })
    .catch(error => {
        console.error('Erreur AJAX:', error);
    });
});

const password = document.getElementById('password');
const regex = {
    lowercase: /[a-z]/,
    uppercase: /[A-Z]/,
    number: /\d/,
    special: /[^A-Za-z\d]/,
    length: (valeur) => valeur.length >= 8,
    total: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/
};

// Validation des critères de mot de passe
password.addEventListener('input', function() 
{
    let pswdValue = this.value;
    const criteria = [
        { id: 'password-lowercase', valid: regex.lowercase.test(pswdValue) },
        { id: 'password-uppercase', valid: regex.uppercase.test(pswdValue) },
        { id: 'password-number', valid: regex.number.test(pswdValue) },
        { id: 'password-special-char', valid: regex.special.test(pswdValue) },
        { id: 'password-length', valid: regex.length(pswdValue) }
    ];
    criteria.forEach(criterion => {
        const element = document.getElementById(criterion.id);
        if (!element) return;

        element.style.color = criterion.valid ? 'green' : 'red';
        const firstChild = element.querySelector('.status-symbol');
        if (firstChild) firstChild.remove(); // Si .status-symbol existe déjà, le supprime

        const symbol = document.createElement('span');
        symbol.className = 'status-symbol';
        symbol.textContent = criterion.valid ? '✔ ' : '✖ ';
        element.prepend(symbol);
    });
});

// Validation input mot de passe
password.addEventListener('blur', function() 
{
    let pswdValue = this.value;
    const pswdFeedback = document.getElementById('password-feedback');
    pswdFeedback.style.color = regex.total.test(pswdValue) ? 'green' : 'red';
    pswdFeedback.textContent = regex.total.test(pswdValue) ? ' ✔' : ' ✖ Mot de passe insuffisant';
});

// Vérification confirmation mot de passe
const passwordConfirm = document.getElementById('password-confirm');
passwordConfirm.addEventListener('blur', function() 
{
    let pswdMatch = password.value === this.value;
    const pswdConfirmFeedback = document.getElementById('password-confirm-feedback');
    pswdConfirmFeedback.style.color = pswdMatch ? 'green' : 'red';
    pswdConfirmFeedback.textContent = pswdMatch ? ' ✔' : ' ✖ Mot de passe incorecte';
});

// Faire apparaître text input si allergies non mentionnées dans la liste prédéfinit
document.getElementById('other-allergy').addEventListener('change', function()
{
    const otherAllergyInput = document.getElementById('other-allergy-input');
    otherAllergyInput.type = this.checked ? 'text' : 'hidden';
});

// Activer/Désactiver le bouton de soumission
document.querySelector('form').addEventListener('input', function() 
{
    const pswdMatch = password.value === passwordConfirm.value;
    document.getElementById('submit-button').disabled = !(this.checkValidity() && pswdMatch);
});

