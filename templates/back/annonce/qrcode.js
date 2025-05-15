// Initialize QRious for QR code generation
let qr = new QRious({
    element: document.getElementById('qrcode'),
    size: 200,
    value: 'Initial QR code value' // Placeholder QR code value
});

function updateQRCode() {
    const form = document.querySelector('form'); // Target the first form element on the page

    // Fetch the values of 'titre_a' and 'description_a' from the form
    const titre = form.querySelector('input[name="titre_a"]').value;
    const description = form.querySelector('textarea[name="description_a"]').value;

    // Combine these values to form the QR code content
    const qrContent = `Titre: ${titre} - Description: ${description}`;

    // Update the QR code with the new content
    qr.value = qrContent;
}

// Add event listeners to form fields to trigger QR code update
document.querySelectorAll('form input, form textarea').forEach(function(element) {
    element.addEventListener('input', updateQRCode); // Update QR code on form field change
});
