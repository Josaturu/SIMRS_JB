// Patient Card Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const patientCardToggle = document.getElementById('patientCardToggle');
    const patientCardContent = document.getElementById('patientCardContent');
    
    if (patientCardToggle && patientCardContent) {
        // Load saved state from localStorage
        const isCollapsed = localStorage.getItem('patientCardCollapsed') === 'true';
        if (isCollapsed) {
            patientCardContent.classList.add('collapsed');
            patientCardToggle.classList.add('collapsed');
        }
        
        // Toggle functionality
        patientCardToggle.addEventListener('click', function() {
            patientCardContent.classList.toggle('collapsed');
            patientCardToggle.classList.toggle('collapsed');
            
            // Save state to localStorage
            const collapsed = patientCardContent.classList.contains('collapsed');
            localStorage.setItem('patientCardCollapsed', collapsed);
        });
    }
});
