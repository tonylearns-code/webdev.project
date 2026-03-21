<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HireVo - Find Your Next Job</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="container">
            <div class="logo">HireVo</div>
            <ul class="nav-links">
                <li><a href="#jobs">Browse Jobs</a></li>
                <li><a href="#companies">Companies</a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn btn-login">Login</a>
                <a href="register.php" class="btn btn-register">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Find Your Dream Job</h1>
            <p>Discover thousands of job opportunities from top companies around the world.</p>
        </div>
    </section>

    <!-- Search Bar -->
    <div class="container">
        <div class="search-section">
            <div class="search-group">
                <label for="search-job">Job Title</label>
                <input type="text" id="search-job" placeholder="e.g., Software Engineer">
            </div>
            <div class="search-group">
                <label for="search-location">Location</label>
                <input type="text" id="search-location" placeholder="e.g., New York">
            </div>
            <button class="search-btn">Search</button>
        </div>
    </div>

    <!-- Featured Jobs Section -->
    <section class="featured-section">
        <h2 id="jobs">Featured Job Postings</h2>
        <div class="job-cards">
            <div class="job-card">
                <h3>Senior Frontend Developer</h3>
                <div class="company">TechCorp Inc.</div>
                <div class="location">📍 San Francisco, CA</div>
                <p>Build scalable web applications with React and Node.js. We're looking for experienced developers.</p>
                <div class="salary">$120,000 - $150,000</div>
                <button class="btn btn-register" onclick="alert('Job details coming soon!')">View Details</button>
            </div>

            <div class="job-card">
                <h3>Product Manager</h3>
                <div class="company">InnovateHub</div>
                <div class="location">📍 New York, NY</div>
                <p>Lead cross-functional teams to create innovative products. 5+ years PM experience required.</p>
                <div class="salary">$100,000 - $140,000</div>
                <button class="btn btn-register" onclick="alert('Job details coming soon!')">View Details</button>
            </div>

            <div class="job-card">
                <h3>UX/UI Designer</h3>
                <div class="company">DesignStudio</div>
                <div class="location">📍 Remote</div>
                <p>Design beautiful and intuitive user interfaces. Portfolio of 3+ projects required.</p>
                <div class="salary">$80,000 - $110,000</div>
                <button class="btn btn-register" onclick="alert('Job details coming soon!')">View Details</button>
            </div>

            <div class="job-card">
                <h3>Data Scientist</h3>
                <div class="company">DataFlow Analytics</div>
                <div class="location">📍 Boston, MA</div>
                <p>Work with cutting-edge ML technologies. Strong Python and statistics background needed.</p>
                <div class="salary">$110,000 - $160,000</div>
                <button class="btn btn-register" onclick="alert('Job details coming soon!')">View Details</button>
            </div>

            <div class="job-card">
                <h3>Marketing Manager</h3>
                <div class="company">BrandForward</div>
                <div class="location">📍 Chicago, IL</div>
                <p>Develop and execute marketing strategies for B2B tech products. 3+ years experience required.</p>
                <div class="salary">$70,000 - $95,000</div>
                <button class="btn btn-register" onclick="alert('Job details coming soon!')">View Details</button>
            </div>

            <div class="job-card">
                <h3>DevOps Engineer</h3>
                <div class="company">CloudSystems</div>
                <div class="location">📍 Remote</div>
                <p>Manage cloud infrastructure and CI/CD pipelines. AWS or GCP experience required.</p>
                <div class="salary">$115,000 - $155,000</div>
                <button class="btn btn-register" onclick="alert('Job details coming soon!')">View Details</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 HireVo. All rights reserved. Your career starts here.</p>
    </footer>
</body>
</html>
