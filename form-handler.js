// Form validation and handler
document.addEventListener('DOMContentLoaded', function() {
    // Login form handler
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }

    // Register form handler
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
});

// Handle login form submission
function handleLoginSubmit(e) {
    e.preventDefault();

    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;

    // Basic validation
    if (!email || !password) {
        showAlert('Please fill in all fields', 'error');
        return;
    }

    if (!validateEmail(email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }

    // Here you would typically send data to backend
    console.log('Login attempt:', { email, password });
    
    // Demo: redirect to dashboard after login
    showAlert('Login successful! Redirecting...', 'success');
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 1500);
}

// Handle register form submission
function handleRegisterSubmit(e) {
    e.preventDefault();

    const role = document.querySelector('input[name="role"]:checked').value;
    const fullName = document.getElementById('full-name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const terms = document.getElementById('terms').checked;

    // Basic validation
    if (!fullName || !email || !password || !confirmPassword) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }

    if (!validateEmail(email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }

    if (password.length < 6) {
        showAlert('Password must be at least 6 characters long', 'error');
        return;
    }

    if (password !== confirmPassword) {
        showAlert('Passwords do not match', 'error');
        return;
    }

    if (!terms) {
        showAlert('You must agree to the terms and conditions', 'error');
        return;
    }

    // Collect role-specific data
    let formData = {
        role,
        fullName,
        email,
        password
    };

    if (role === 'job-seeker') {
        formData.phone = document.getElementById('phone').value;
        formData.location = document.getElementById('location').value;
        formData.experience = document.getElementById('experience').value;
    } else if (role === 'employer') {
        formData.companyName = document.getElementById('company-name').value;
        formData.companyEmail = document.getElementById('company-email').value;
        formData.companySize = document.getElementById('company-size').value;
        formData.industry = document.getElementById('industry').value;
    }

    // Here you would typically send data to backend
    console.log('Registration data:', formData);
    
    // Demo: show success message
    showAlert('Account created successfully! Redirecting...', 'success');
    setTimeout(() => {
        window.location.href = 'login.html';
    }, 1500);
}

// Email validation function
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Alert function (you can replace with a custom alert box)
function showAlert(message, type = 'info') {
    // This is a simple demo alert. You can make it more sophisticated
    alert(`[${type.toUpperCase()}] ${message}`);
    
    // For production, you'd want to use a more elegant notification system
    // such as a toast notification or modal dialog
}
