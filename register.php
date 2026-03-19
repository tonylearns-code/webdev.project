<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HireVo</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="container">
            <div class="logo">HireVo</div>
            <ul class="nav-links">
                <li><a href="index.php">Browse Jobs</a></li>
                <li><a href="index.php">Companies</a></li>
                <li><a href="index.php">About</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn btn-login">Login</a>
                <a href="register.php" class="btn btn-register">Register</a>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="form-container">
        <h2>Create Your Account</h2>
        <p class="subtitle">Join HireVo and start your journey today</p>

        <form id="register-form">
            <!-- Role Selection -->
            <div class="form-group">
                <label>I am a:</label>
                <div class="role-selection">
                    <div class="role-option">
                        <input type="radio" id="role-seeker" name="role" value="job-seeker" required>
                        <label for="role-seeker">👤 Job Seeker</label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="role-employer" name="role" value="employer" required>
                        <label for="role-employer">🏢 Employer</label>
                    </div>
                </div>
            </div>

            <!-- Common Fields -->
            <div class="form-group">
                <label for="full-name">Full Name</label>
                <input type="text" id="full-name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Create a strong password" required>
            </div>

            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" placeholder="Re-enter your password" required>
            </div>

            <!-- Job Seeker Fields -->
            <div id="seeker-fields" style="display: none;">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" placeholder="Your contact number">
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" placeholder="City, Country">
                </div>

                <div class="form-group">
                    <label for="experience">Years of Experience</label>
                    <select id="experience">
                        <option value="">Select experience level</option>
                        <option value="0-1">Entry Level (0-1 years)</option>
                        <option value="1-3">Junior (1-3 years)</option>
                        <option value="3-5">Mid-level (3-5 years)</option>
                        <option value="5-10">Senior (5-10 years)</option>
                        <option value="10+">Expert (10+ years)</option>
                    </select>
                </div>
            </div>

            <!-- Employer Fields -->
            <div id="employer-fields" style="display: none;">
                <div class="form-group">
                    <label for="company-name">Company Name</label>
                    <input type="text" id="company-name" placeholder="Your company name">
                </div>

                <div class="form-group">
                    <label for="company-email">Company Email</label>
                    <input type="email" id="company-email" placeholder="company@example.com">
                </div>

                <div class="form-group">
                    <label for="company-size">Company Size</label>
                    <select id="company-size">
                        <option value="">Select company size</option>
                        <option value="1-10">1-10 employees</option>
                        <option value="11-50">11-50 employees</option>
                        <option value="51-200">51-200 employees</option>
                        <option value="201-500">201-500 employees</option>
                        <option value="500+">500+ employees</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="industry">Industry</label>
                    <select id="industry">
                        <option value="">Select industry</option>
                        <option value="tech">Technology</option>
                        <option value="healthcare">Healthcare</option>
                        <option value="finance">Finance</option>
                        <option value="retail">Retail</option>
                        <option value="manufacturing">Manufacturing</option>
                        <option value="education">Education</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="checkbox-group">
                <input type="checkbox" id="terms" required>
                <label for="terms">I agree to the Terms and Conditions</label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="newsletter">
                <label for="newsletter">Send me job updates and news</label>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-submit">Create Account</button>
            </div>

            <div class="form-link">
                Already have an account? <a href="login.php">Sign in here</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 HireVo. All rights reserved.</p>
    </footer>

    <script src="assets/js/form-handler.js"></script>
    <script src="assets/js/register.js"></script>
</body>
</html>
