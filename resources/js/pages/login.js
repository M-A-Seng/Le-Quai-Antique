// Vérification préliminaire d'email
document.getElementById('email').addEventListener('change', function()
{
    const regex = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
    let isValid = regex.test(this.value)
    const feedback = document.getElementById('email-feedback');
    feedback.style.color = !isValid ? 'red' : '';
    feedback.textContent = !isValid ? ' ✖ Email invalide' : '';
});

// Vérification préliminaire du mot de passe
document.getElementById('password').addEventListener('change', function() 
{
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;
    let isValid = regex.test(this.value);
    const feedback = document.getElementById('password-feedback');
    feedback.style.color = !isValid ? 'red' : '';
    feedback.textContent = !isValid ? ' ✖ Mot de passe invalide' : '';
});
