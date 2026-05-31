import '../shared/reorder.js';

const form = document.getElementById('uploadForm');
const imageInput = document.getElementById('imageInput');
const titleInput = document.getElementById('imageTitle');
const preview = document.getElementById('preview');
const imageFeedback = document.getElementById('image-input-feedback');
const submitImage = document.getElementById('submit-image');
const warningButton = document.getElementById('open-warning-button');

const MAX_SIZE = 10 * 1024 * 1024;
const allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/webp'
];

imageInput?.addEventListener('input', () => {
    const file = imageInput.files[0];
    if (!file) return;

    // Type MIME
    if (!allowedTypes.includes(file.type)) {
        imageFeedback.textContent = "Fichier invalide, veuillez sélectionner un format png, jpeg ou webp.";
        imageFeedback.style.color = "red";
        imageInput.value = '';
        return;
    }
    // Taille
    if (file.size > MAX_SIZE) {
        imageFeedback.textContent = "Fichier trop lourd, veuillez sélectionner une image en dessous de 10MB.";
        imageFeedback.style.color = "red";
        imageInput.value = '';
        return;
    }
    // Preview
    preview.src = URL.createObjectURL(file);
});

document.querySelectorAll('.modify-image')?.forEach(button => {
    button.addEventListener('click', () => 
    {
        submitImage.setAttribute('formaction', document.querySelector('.gallery').dataset.formaction)
        submitImage.setAttribute('name', 'id');
        submitImage.setAttribute('value', button.dataset.id);
        imageInput.removeAttribute('required');
        titleInput.value = button.dataset.title;
        preview.src = document.getElementById(`image-${button.dataset.id}`).src;
        warningButton.classList.remove('hidden');

        // modal confirmation suppression
        document.getElementById('image-name').textContent = button.dataset.title;
        document.getElementById('delete-image-button').value = button.dataset.id;
    })
})

document.querySelectorAll('.close-container')?.forEach(button => {
    button.addEventListener('click', () => {
        if (button.dataset.containerId === 'upload-form-container') {
            submitImage.removeAttribute('formaction');
            submitImage.removeAttribute('name');
            submitImage.removeAttribute('value');
            imageInput.setAttribute('required', true);
            warningButton.classList.add('hidden');
            titleInput.value = '';
            preview.src = '';
        }
    })
})