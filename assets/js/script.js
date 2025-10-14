// Script.js - Basic functionality
console.log('Script loaded successfully');

// Form submit handler (jika diperlukan)
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Ready');
    
    // Tambahkan event listener untuk form jika diperlukan
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            console.log('Form submitting...', form.action);
            // Don't prevent default - let form submit normally
        });
    });
});
