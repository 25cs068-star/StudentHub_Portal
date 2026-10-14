
<?php
session_start();

$errors = $_SESSION["registration_errors"] ?? [];
$old = $_SESSION["registration_old"] ?? [];
unset($_SESSION["registration_errors"], $_SESSION["registration_old"]);

$success = isset($_GET["success"]) && $_GET["success"] === "1";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudentHub Portal - Register</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #eef4ff;
    color: #333;
    transition: .4s;
}

header {
    background: linear-gradient(135deg, #003566, #0077b6, #00b4d8);
    text-align: center;
    padding: 25px;
    color: white;
    box-shadow: 0 5px 15px rgba(0,0,0,.3);
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo-section {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo-section img {
    width: 140px;
    height: 140px;
    background: white;
    padding: 10px;
    border-radius: 15px;
    object-fit: contain;
    box-shadow: 0 5px 15px rgba(0,0,0,.3);
    transition: .4s;
}

.logo-section img:hover {
    transform: scale(1.08);
}

.logo-section h1 {
    margin-top: 12px;
    font-size: 40px;
    letter-spacing: 2px;
}

.logo-section p {
    margin-top: 8px;
    font-size: 18px;
}

nav {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
}

nav a {
    display: inline-block;
    text-decoration: none;
    color: white;
    font-weight: bold;
    padding: 10px 15px;
    border-radius: 25px;
    transition: .3s;
}

nav a:hover {
    background: white;
    color: #0077b6;
    transform: translateY(-3px);
}

.theme-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    padding: 10px 16px;
    border: none;
    border-radius: 20px;
    background: white;
    color: #003566;
    font-weight: bold;
    cursor: pointer;
}

.menu-btn {
    display: none;
    position: absolute;
    top: 20px;
    right: 20px;
    background: white;
    color: #003566;
    border: none;
    font-size: 25px;
    padding: 5px 12px;
    border-radius: 8px;
    cursor: pointer;
}

.container {
    width: 90%;
    max-width: 1000px;
    margin: 35px auto;
}

.hero {
    background: white;
    padding: 35px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,.15);
    margin-bottom: 30px;
}

.hero h2 {
    color: #003566;
    font-size: 34px;
    margin-bottom: 15px;
}

.hero p {
    font-size: 17px;
    color: #555;
}

.form-card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,.15);
}

.form-group {
    margin-bottom: 20px;
}

.form-group > label {
    display: inline-block;
    width: 220px;
    font-weight: bold;
    font-size: 17px;
    color: #003566;
}

.form-group input,
.form-group select {
    width: 60%;
    padding: 12px;
    font-size: 15px;
    border: 2px solid #d5dce3;
    border-radius: 10px;
    outline: none;
    transition: .3s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #0077b6;
    box-shadow: 0 0 8px rgba(0,119,182,.25);
}

.password-box {
    display: inline-flex;
    width: 60%;
    position: relative;
}

.password-box input {
    width: 100%;
    padding-right: 50px;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 7px;
    border: none;
    background: transparent;
    font-size: 20px;
    cursor: pointer;
}

.error-message {
    display: none;
    color: #e53935;
    font-size: 14px;
    margin: 5px 0 0 220px;
}

.error-message.show {
    display: block;
}

.gender-options {
    display: inline-flex;
    width: 60%;
    gap: 25px;
    align-items: center;
}

.gender-options label {
    width: auto;
    font-weight: normal;
    font-size: 15px;
    color: #333;
}

.gender-options input {
    width: auto;
    margin-right: 5px;
}

.terms {
    margin-top: 10px;
}

.terms label {
    width: auto;
    font-weight: normal;
    color: #333;
}

.terms input {
    width: auto;
    margin-right: 8px;
}

.strength-container {
    margin: 8px 0 0 220px;
    width: 60%;
}

.strength-bar-container {
    width: 100%;
    height: 8px;
    background: #ddd;
    border-radius: 10px;
    overflow: hidden;
}

.strength-bar {
    width: 0%;
    height: 100%;
    background: #0077b6;
    transition: .3s;
}

.strength-text {
    font-size: 13px;
    margin-top: 5px;
    font-weight: bold;
    color: #0077b6;
}

.button-area {
    text-align: center;
    margin-top: 30px;
}

.button-area input {
    padding: 12px 30px;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    margin: 10px;
    transition: .3s;
}

input[type="submit"] {
    background: #0077b6;
    color: white;
}

input[type="submit"]:hover {
    background: #003566;
    transform: translateY(-3px);
}

input[type="reset"] {
    background: #e53935;
    color: white;
}

input[type="reset"]:hover {
    background: #b71c1c;
    transform: translateY(-3px);
}

.login-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,.15);
    text-align: center;
    margin-top: 30px;
}

.login-card h2 {
    color: #003566;
    margin-bottom: 15px;
}

.login-card a {
    text-decoration: none;
    color: #0077b6;
    font-weight: bold;
}

.login-card a:hover {
    text-decoration: underline;
}

.server-errors {
    background: #ffe8e8;
    color: #a40000;
    border: 1px solid #e53935;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
}

.server-errors ul {
    margin: 8px 0 0 20px;
}

.success-message {
    background: #e2f7e8;
    color: #176b31;
    border: 1px solid #67b77a;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
    text-align: center;
}

footer {
    background: #003566;
    color: white;
    text-align: center;
    padding: 20px;
    margin-top: 30px;
}

body.dark-mode {
    background: #121212;
    color: white;
}

body.dark-mode .hero,
body.dark-mode .form-card,
body.dark-mode .login-card {
    background: #1e1e1e;
    color: white;
}

body.dark-mode .hero h2,
body.dark-mode .login-card h2,
body.dark-mode .form-group > label {
    color: #00b4d8;
}

body.dark-mode .hero p {
    color: #ddd;
}

body.dark-mode .form-group input,
body.dark-mode .form-group select {
    background: #292929;
    color: white;
    border-color: #555;
}

body.dark-mode .gender-options label,
body.dark-mode .terms label {
    color: white;
}

@media(max-width:768px) {
    .menu-btn {
        display: block;
    }

    nav {
        display: none;
        flex-direction: column;
        align-items: center;
    }

    nav.active {
        display: flex;
    }

    .logo-section h1 {
        font-size: 30px;
    }

    .logo-section p {
        font-size: 15px;
    }

    .container {
        width: 95%;
    }

    .form-card {
        padding: 25px;
    }

    .form-group > label {
        display: block;
        width: 100%;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group select,
    .password-box {
        width: 100%;
    }

    .error-message {
        margin-left: 0;
    }

    .gender-options {
        width: 100%;
        flex-wrap: wrap;
    }

    .strength-container {
        margin-left: 0;
        width: 100%;
    }
}
</style>
</head>

<body>

<header>
    <div class="logo-section">
        <img src="logo.jfif" alt="StudentHub Logo">
        <h1>StudentHub Portal</h1>
        <p>Your Digital Campus and Academic Companion</p>
    </div>

    <button id="themeToggle" class="theme-btn" type="button">🌙 Dark Mode</button>
    <button id="menuToggle" class="menu-btn" type="button">☰</button>

    <nav id="mainNav">
        <a href="index.html">Home</a>
        <a href="about.html">About</a>
        <a href="register.php">Register</a>
        <a href="login.html">Login</a>
        <a href="dashboard.html">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="profile.html">Profile</a>
        <a href="contact.html">Contact</a>
        <a href="faq.html">FAQ</a>
        <a href="feedback.html">Feedback</a>
    </nav>
</header>

<div class="container">

    <div class="hero">
        <h2>Create Your StudentHub Account</h2>
        <p>Fill in the registration form to access all StudentHub services.</p>
    </div>

    <div class="form-card">

        <?php if ($success): ?>
            <div class="success-message">
                <h2>Registration Successful!</h2>
                <p>Your StudentHub account has been created successfully.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="server-errors">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="registrationForm" action="process_registration.php" method="POST">

            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="fullName"
                       placeholder="Enter your full name"
                       autocomplete="name" required maxlength="50"
                       value="<?= htmlspecialchars($old['fullName'] ?? '') ?>">
                <div class="error-message" id="nameError">Please enter a valid full name.</div>
            </div>

            <div class="form-group">
                <label for="enrollment">Enrollment Number</label>
                <input type="text" id="enrollment" name="enrollment"
                       placeholder="Enter enrollment number" required
                       value="<?= htmlspecialchars($old['enrollment'] ?? '') ?>">
                <div class="error-message" id="enrollmentError">Please enter a valid enrollment number.</div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="student@example.com"
                       autocomplete="email" maxlength="100" required
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                <div class="error-message" id="emailError">Please enter a valid email address.</div>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" name="mobile"
                       placeholder="Enter 10-digit mobile number"
                       maxlength="10" required
                       value="<?= htmlspecialchars($old['mobile'] ?? '') ?>">
                <div class="error-message" id="mobileError">Please enter a valid mobile number.</div>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department" required>
                    <option value="">Select Department</option>
                    <option value="Computer Science / IT">Computer Science / IT</option>
                    <option value="Electronics & Communication">Electronics &amp; Communication</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                    <option value="Civil Engineering">Civil Engineering</option>
                </select>
                <div class="error-message" id="departmentError">Please select your department.</div>
            </div>

            <div class="form-group">
                <label for="year">Year</label>
                <select id="year" name="year" required>
                    <option value="">Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
                <div class="error-message" id="yearError">Please select your year.</div>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <div class="gender-options">
                    <label><input type="radio" name="gender" value="Male" required> Male</label>
                    <label><input type="radio" name="gender" value="Female"> Female</label>
                    <label><input type="radio" name="gender" value="Other"> Other</label>
                </div>
                <div class="error-message" id="genderError">Please select your gender.</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-box">
                    <input type="password" id="password" name="password"
                           placeholder="Create Password"
                           autocomplete="new-password" required>
                    <button type="button" class="password-toggle" id="passwordToggle"
                            aria-label="Show password">👁️</button>
                </div>

                <div class="strength-container">
                    <div class="strength-bar-container">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>
                <div class="error-message" id="passwordError">Use at least 8 characters with uppercase, lowercase, number and special character.</div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="password-box">
                    <input type="password" id="confirmPassword" name="confirmPassword"
                           placeholder="Confirm Password"
                           autocomplete="new-password" required>
                    <button type="button" class="password-toggle" id="confirmPasswordToggle"
                            aria-label="Show confirm password">👁️</button>
                </div>
                <div class="error-message" id="confirmPasswordError">Passwords do not match.</div>
            </div>

            <div class="form-group terms">
                <label>
                    <input type="checkbox" id="terms" name="terms" value="accepted" required>
                    I agree to the Terms &amp; Conditions.
                </label>
                <div class="error-message" id="termsError">You must accept the Terms &amp; Conditions.</div>
            </div>

            <div class="button-area">
                <input type="submit" value="Register Now">
                <input type="reset" value="Clear Form" id="clearButton">
            </div>

        </form>
    </div>

    <div class="login-card">
        <h2>Already Registered?</h2>
        <p>Already have an account? <a href="login.html">Login Here</a></p>
    </div>

</div>

<footer>
    <p>&copy; 2026 StudentHub Portal | Designed for Students</p>
</footer>

<script>
const form = document.getElementById("registrationForm");
const fullName = document.getElementById("fullName");
const enrollment = document.getElementById("enrollment");
const email = document.getElementById("email");
const mobile = document.getElementById("mobile");
const department = document.getElementById("department");
const year = document.getElementById("year");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

function showError(id, show) {
    document.getElementById(id).classList.toggle("show", show);
}

function togglePassword(input, button) {
    button.addEventListener("click", function() {
        input.type = input.type === "password" ? "text" : "password";
        button.textContent = input.type === "password" ? "👁️" : "🙈";
    });
}

togglePassword(password, document.getElementById("passwordToggle"));
togglePassword(confirmPassword, document.getElementById("confirmPasswordToggle"));

function validName() {
    return /^[A-Za-z ]{2,50}$/.test(fullName.value.trim());
}

function validEnrollment() {
    return /^(?:[Dd])?[0-9]{2}[A-Za-z]{2,4}[0-9]{2,4}$/.test(enrollment.value.trim());
}

function validEmail() {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
}

function validMobile() {
    return /^[6-9][0-9]{9}$/.test(mobile.value);
}

function validPassword() {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&]).{8,}$/.test(password.value);
}

fullName.addEventListener("input", function() {
    showError("nameError", !validName());
});

enrollment.addEventListener("input", function() {
    showError("enrollmentError", !validEnrollment());
});

email.addEventListener("input", function() {
    showError("emailError", !validEmail());
});

mobile.addEventListener("input", function() {
    showError("mobileError", !validMobile());
});

department.addEventListener("change", function() {
    showError("departmentError", department.value === "");
});

year.addEventListener("change", function() {
    showError("yearError", year.value === "");
});

document.querySelectorAll('input[name="gender"]').forEach(function(item) {
    item.addEventListener("change", function() {
        showError("genderError", false);
    });
});

password.addEventListener("input", function() {
    const valid = validPassword();
    showError("passwordError", !valid && password.value !== "");

    let strength = 0;
    if (password.value.length >= 8) strength++;
    if (/[a-z]/.test(password.value)) strength++;
    if (/[A-Z]/.test(password.value)) strength++;
    if (/[0-9]/.test(password.value)) strength++;
    if (/[@$!%*?&]/.test(password.value)) strength++;

    document.getElementById("strengthBar").style.width = (strength * 20) + "%";

    const messages = ["", "Very Weak", "Weak", "Medium", "Good", "Strong"];
    document.getElementById("strengthText").textContent = messages[strength];

    if (confirmPassword.value !== "") {
        showError("confirmPasswordError", password.value !== confirmPassword.value);
    }
});

confirmPassword.addEventListener("input", function() {
    showError("confirmPasswordError", password.value !== confirmPassword.value);
});

document.getElementById("terms").addEventListener("change", function() {
    showError("termsError", !this.checked);
});

form.addEventListener("submit", function(event) {
    const nameOK = validName();
    const enrollmentOK = validEnrollment();
    const emailOK = validEmail();
    const mobileOK = validMobile();
    const passwordOK = validPassword();
    const confirmOK = password.value === confirmPassword.value;
    const genderOK = document.querySelector('input[name="gender"]:checked') !== null;
    const termsOK = document.getElementById("terms").checked;

    showError("nameError", !nameOK);
    showError("enrollmentError", !enrollmentOK);
    showError("emailError", !emailOK);
    showError("mobileError", !mobileOK);
    showError("departmentError", department.value === "");
    showError("yearError", year.value === "");
    showError("genderError", !genderOK);
    showError("passwordError", !passwordOK);
    showError("confirmPasswordError", !confirmOK);
    showError("termsError", !termsOK);

    if (!nameOK || !enrollmentOK || !emailOK || !mobileOK ||
        department.value === "" || year.value === "" ||
        !genderOK || !passwordOK || !confirmOK || !termsOK) {
        event.preventDefault();
    }
});

form.addEventListener("reset", function() {
    setTimeout(function() {
        document.querySelectorAll(".error-message").forEach(function(item) {
            item.classList.remove("show");
        });
        document.getElementById("strengthBar").style.width = "0%";
        document.getElementById("strengthText").textContent = "";
    }, 0);
});

document.getElementById("themeToggle").addEventListener("click", function() {
    document.body.classList.toggle("dark-mode");
    this.textContent = document.body.classList.contains("dark-mode")
        ? "☀️ Light Mode"
        : "🌙 Dark Mode";
});

document.getElementById("menuToggle").addEventListener("click", function() {
    document.getElementById("mainNav").classList.toggle("active");
});
</script>

</body>
</html>
