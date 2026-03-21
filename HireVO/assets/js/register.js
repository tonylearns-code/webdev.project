// Handle role selection and show/hide relevant fields
document.addEventListener('DOMContentLoaded', function() {
    const roleSelected = document.querySelectorAll('input[name="role"]');
    
    roleSelected.forEach(radio => {
        radio.addEventListener('change', function() {
            handleRoleChange(this.value);
        });
    });
});

function handleRoleChange(role) {
    const seekerFields = document.getElementById('seeker-fields');
    const employerFields = document.getElementById('employer-fields');

    if (role === 'job-seeker') {
        seekerFields.style.display = 'block';
        employerFields.style.display = 'none';
        
        // Remove required from employer fields
        document.getElementById('company-name').removeAttribute('required');
        document.getElementById('company-email').removeAttribute('required');
        
        // Add required to seeker fields if needed
        // (Optional: uncomment if you want these fields to be required)
        // document.getElementById('phone').setAttribute('required', 'required');
        // document.getElementById('location').setAttribute('required', 'required');
    } else if (role === 'employer') {
        seekerFields.style.display = 'none';
        employerFields.style.display = 'block';
        
        // Remove required from seeker fields
        document.getElementById('phone').removeAttribute('required');
        document.getElementById('location').removeAttribute('required');
        
        // Add required to employer fields if needed
        // (Optional: uncomment if you want these fields to be required)
        // document.getElementById('company-name').setAttribute('required', 'required');
        // document.getElementById('company-email').setAttribute('required', 'required');
    }
}
