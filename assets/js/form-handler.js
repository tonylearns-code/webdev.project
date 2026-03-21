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

    // Send login request to backend
    const loginData = {
        action: 'login',
        email: email,
        password: password
    };

    fetch('api/process_login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(loginData)
    })
    .then(async response => {
        const responseText = await response.text();
        let payload = null;

        try {
            payload = JSON.parse(responseText);
        } catch (e) {
            throw new Error('HTTP ' + response.status + ' - ' + responseText.substring(0, 200));
        }

        if (!response.ok) {
            throw new Error(payload.message || ('HTTP ' + response.status));
        }

        return payload;
    })
    .then(data => {
        if (data.success) {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || 'index.php';
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message || 'An error occurred. Please try again.', 'error');
    });
}

// Handle register form submission
function handleRegisterSubmit(e) {
    e.preventDefault();

    const roleEl = document.querySelector('input[name="role"]:checked');
    if (!roleEl) {
        showAlert('Please select a role (Job Seeker or Employer)', 'error');
        return;
    }
    const role = roleEl.value;
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
        action: 'register',
        role,
        fullName,
        email,
        password,
        confirmPassword
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

        // Validate employer required fields
        if (!formData.companyName || !formData.companySize || !formData.industry) {
            showAlert('Please fill in all company details', 'error');
            return;
        }
    }

    // Convert role format from 'job-seeker' to 'job_seeker' for database
    if (formData.role === 'job-seeker') {
        formData.role = 'job_seeker';
    }

    // Send registration request to backend
    fetch('api/process_register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(async response => {
        const responseText = await response.text();
        let payload = null;

        try {
            payload = JSON.parse(responseText);
        } catch (e) {
            throw new Error('HTTP ' + response.status + ' - ' + responseText.substring(0, 200));
        }

        if (!response.ok) {
            throw new Error(payload.message || ('HTTP ' + response.status));
        }

        return payload;
    })
    .then(data => {
        if (data.success) {
            showAlert('Account created successfully! Redirecting to login...', 'success');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert(error.message || 'An error occurred. Please try again.', 'error');
    });
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
