<?php

$success = isset($_GET["success"]) && $_GET["success"] === "1";

$successScript = "";

if ($success) {
    $successScript = '
    <script>
        window.addEventListener("load", function () {
            const modal = document.getElementById("successModal");

            if (modal) {
                modal.classList.add("show");
            }
        });
    </script>';
}

echo <<<HTML
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

.form-group label {
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

footer {
    background: #003566;
    color: white;
    text-align: center;
    padding: 20px;
    margin-top: 30px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 500;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,.65);
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal.show {
    display: flex;
}

.modal-box {
    background: white;
    width: 450px;
    max-width: 100%;
    padding: 35px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,.3);
    animation: modalOpen .4s;
}

@keyframes modalOpen {

    from {
        opacity: 0;
        transform: scale(.8);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }

}

.modal-box h2 {
    color: #003566;
    margin-bottom: 15px;
}

.modal-box button {
    margin-top: 20px;
    padding: 10px 25px;
    border: none;
    border-radius: 20px;
    background: #003566;
    color: white;
    cursor: pointer;
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
body.dark-mode .form-group label {
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

body.dark-mode .modal-box {
    background: #1e1e1e;
    color: white;
}

body.dark-mode .modal-box h2 {
    color: #00b4d8;
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

    .form-group label {
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

    <button id="themeToggle" class="theme-btn" type="button">
        🌙 Dark Mode
    </button>

    <button id="menuToggle" class="menu-btn" type="button">
        ☰
    </button>

    <nav>

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

        <p>
            Fill in the registration form to access all StudentHub services.
        </p>

    </div>

    <div class="form-card">

        <form id="registrationForm" action="process.php" method="POST" novalidate>

            <div class="form-group">

                <label for="fullName">Full Name</label>

                <input
                    type="text"
                    id="fullName"
                    name="fullName"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    aria-describedby="nameError">

                <div class="error-message" id="nameError" role="alert">
                    Please enter your full name.
                </div>

            </div>

            <div class="form-group">

                <label for="enrollment">Enrollment Number</label>

                <input
                    type="text"
                    id="enrollment"
                    name="enrollment"
                    placeholder="Enter enrollment number"
                    aria-describedby="enrollmentError">

                <div class="error-message" id="enrollmentError" role="alert">
                    Please enter your enrollment number.
                </div>

            </div>

            <div class="form-group">

                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="student@example.com"
                    autocomplete="email"
                    aria-describedby="emailError">

                <div class="error-message" id="emailError" role="alert">
                    Please enter a valid email address.
                </div>

            </div>

            <div class="form-group">

                <label for="mobile">Mobile Number</label>

                <input
                    type="tel"
                    id="mobile"
                    name="mobile"
                    placeholder="Enter 10-digit mobile number"
                    maxlength="10"
                    autocomplete="tel"
                    aria-describedby="mobileError">

                <div class="error-message" id="mobileError" role="alert">
                    Please enter a valid mobile number.
                </div>

            </div>

            <div class="form-group">

                <label for="department">Department</label>

                <select
                    id="department"
                    name="department"
                    aria-describedby="departmentError">

                    <option value="">
                        Select Department
                    </option>

                    <option value="Computer Science / IT">
                        Computer Science / IT
                    </option>

                    <option value="Electronics & Communication">
                        Electronics & Communication
                    </option>

                    <option value="Electrical Engineering">
                        Electrical Engineering
                    </option>

                    <option value="Mechanical Engineering">
                        Mechanical Engineering
                    </option>

                    <option value="Civil Engineering">
                        Civil Engineering
                    </option>

                </select>

                <div class="error-message" id="departmentError" role="alert">
                    Please select your department.
                </div>

            </div>

            <div class="form-group">

                <label for="year">Year</label>

                <select
                    id="year"
                    name="year"
                    aria-describedby="yearError">

                    <option value="">
                        Select Year
                    </option>

                    <option value="1st Year">
                        1st Year
                    </option>

                    <option value="2nd Year">
                        2nd Year
                    </option>

                    <option value="3rd Year">
                        3rd Year
                    </option>

                    <option value="4th Year">
                        4th Year
                    </option>

                </select>

                <div class="error-message" id="yearError" role="alert">
                    Please select your year.
                </div>

            </div>

            <div class="form-group">

                <label>Gender</label>

                <div class="gender-options">

                    <label>
                        <input
                            type="radio"
                            name="gender"
                            value="Male">
                        Male
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="gender"
                            value="Female">
                        Female
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="gender"
                            value="Other">
                        Other
                    </label>

                </div>

                <div class="error-message" id="genderError" role="alert">
                    Please select your gender.
                </div>

            </div>

            <div class="form-group">

                <label for="password">Password</label>

                <div class="password-box">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create Password"
                        autocomplete="new-password"
                        aria-describedby="passwordError strengthText">

                    <button
                        type="button"
                        class="password-toggle"
                        id="passwordToggle"
                        aria-label="Show password">

                        👁️

                    </button>

                </div>

                <div class="strength-container">

                    <div class="strength-bar-container">

                        <div
                            class="strength-bar"
                            id="strengthBar">
                        </div>

                    </div>

                    <div
                        class="strength-text"
                        id="strengthText">
                    </div>

                </div>

                <div class="error-message" id="passwordError" role="alert">
                    Password must contain at least 8 characters.
                </div>

            </div>

            <div class="form-group">

                <label for="confirmPassword">Confirm Password</label>

                <div class="password-box">

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                        aria-describedby="confirmPasswordError">

                    <button
                        type="button"
                        class="password-toggle"
                        id="confirmPasswordToggle"
                        aria-label="Show confirm password">

                        👁️

                    </button>

                </div>

                <div
                    class="error-message"
                    id="confirmPasswordError"
                    role="alert">

                    Passwords do not match.

                </div>

            </div>

            <div class="form-group terms">

                <label>

                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        value="accepted"
                        aria-describedby="termsError">

                    I agree to the Terms & Conditions.

                </label>

                <div class="error-message" id="termsError" role="alert">
                    You must accept the Terms & Conditions.
                </div>

            </div>

            <div class="button-area">

                <input
                    type="submit"
                    value="Register Now">

                <input
                    type="reset"
                    value="Clear Form"
                    id="clearButton">

            </div>

        </form>

    </div>

    <div class="login-card">

        <h2>Already Registered?</h2>

        <p>

            Already have an account?

            <a href="login.html">
                Login Here
            </a>

        </p>

    </div>

</div>

<div class="modal" id="successModal">

    <div class="modal-box">

        <h2>🎉 Registration Successful!</h2>

        <p>
            Your StudentHub account has been created successfully.
        </p>

        <button id="modalClose" type="button">
            Continue to Login
        </button>

    </div>

</div>

<footer>

    <p>
        &copy; 2026 StudentHub Portal | Designed for Students
    </p>

</footer>

<script src="scriptcomman.js"></script>

<script src="registration.js"></script>

$successScript

</body>

</html>
HTML;

?>