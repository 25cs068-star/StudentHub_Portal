const form = document.getElementById("registrationForm");

const fullName = document.getElementById("fullName");
const enrollment = document.getElementById("enrollment");
const email = document.getElementById("email");
const mobile = document.getElementById("mobile");
const department = document.getElementById("department");
const year = document.getElementById("year");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

const passwordToggle = document.getElementById("passwordToggle");
const confirmPasswordToggle = document.getElementById("confirmPasswordToggle");

const successModal = document.getElementById("successModal");
const modalClose = document.getElementById("modalClose");
const clearButton = document.getElementById("clearButton");

const strengthText = document.getElementById("strengthText");
const strengthBar = document.getElementById("strengthBar");

const namePattern = /^[A-Za-z ]{2,50}$/;
const enrollmentPattern = /^(?:[Dd])?[0-9]{2}[A-Za-z]{2,4}[0-9]{2,4}$/;
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const mobilePattern = /^[6-9][0-9]{9}$/;
const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&]).{8,}$/;


function showError(id, message) {
    const error = document.getElementById(id);
    error.textContent = message;
    error.classList.add("show");
}


function hideError(id) {
    document.getElementById(id).classList.remove("show");
}


function clearErrors() {
    document.querySelectorAll(".error-message").forEach(function(error) {
        error.classList.remove("show");
    });
}


passwordToggle.addEventListener("click", function() {

    if (password.type === "password") {
        password.type = "text";
        passwordToggle.textContent = "🙈";
    } else {
        password.type = "password";
        passwordToggle.textContent = "👁️";
    }

});


confirmPasswordToggle.addEventListener("click", function() {

    if (confirmPassword.type === "password") {
        confirmPassword.type = "text";
        confirmPasswordToggle.textContent = "🙈";
    } else {
        confirmPassword.type = "password";
        confirmPasswordToggle.textContent = "👁️";
    }

});


password.addEventListener("input", function() {

    const value = password.value;
    let strength = 0;

    if (value.length >= 8) strength++;
    if (/[A-Z]/.test(value)) strength++;
    if (/[a-z]/.test(value)) strength++;
    if (/[0-9]/.test(value)) strength++;
    if (/[@$!%*?&]/.test(value)) strength++;

    if (value === "") {

        strengthText.textContent = "";
        strengthBar.style.width = "0%";

    } else if (strength <= 2) {

        strengthText.textContent = "Weak Password";
        strengthBar.style.width = "35%";

    } else if (strength <= 4) {

        strengthText.textContent = "Medium Password";
        strengthBar.style.width = "65%";

    } else {

        strengthText.textContent = "Strong Password";
        strengthBar.style.width = "100%";

    }

});


form.addEventListener("submit", function(event) {

    event.preventDefault();

    clearErrors();

    let valid = true;

    const nameValue = fullName.value.trim();
    const enrollmentValue = enrollment.value.trim();
    const emailValue = email.value.trim();
    const mobileValue = mobile.value.trim();
    const departmentValue = department.value;
    const yearValue = year.value;
    const passwordValue = password.value;
    const confirmPasswordValue = confirmPassword.value;

    const gender = document.querySelector(
        'input[name="gender"]:checked'
    );

    const terms = document.getElementById("terms");


    if (nameValue === "") {

        showError(
            "nameError",
            "Please enter your full name."
        );

        valid = false;

    } else if (!namePattern.test(nameValue)) {

        showError(
            "nameError",
            "Name can contain only letters and spaces."
        );

        valid = false;

    }


    if (enrollmentValue === "") {

        showError(
            "enrollmentError",
            "Please enter your enrollment number."
        );

        valid = false;

    } else if (!enrollmentPattern.test(enrollmentValue)) {

        showError(
            "enrollmentError",
            "Please enter a valid enrollment number."
        );

        valid = false;

    }


    if (emailValue === "") {

        showError(
            "emailError",
            "Please enter your email address."
        );

        valid = false;

    } else if (!emailPattern.test(emailValue)) {

        showError(
            "emailError",
            "Please enter a valid email address."
        );

        valid = false;

    }


    if (mobileValue === "") {

        showError(
            "mobileError",
            "Please enter your mobile number."
        );

        valid = false;

    } else if (!mobilePattern.test(mobileValue)) {

        showError(
            "mobileError",
            "Please enter a valid 10-digit mobile number."
        );

        valid = false;

    }


    if (departmentValue === "") {

        showError(
            "departmentError",
            "Please select your department."
        );

        valid = false;

    }


    if (yearValue === "") {

        showError(
            "yearError",
            "Please select your year."
        );

        valid = false;

    }


    if (!gender) {

        showError(
            "genderError",
            "Please select your gender."
        );

        valid = false;

    }


    if (passwordValue === "") {

        showError(
            "passwordError",
            "Please enter your password."
        );

        valid = false;

    } else if (!passwordPattern.test(passwordValue)) {

        showError(
            "passwordError",
            "Password must contain 8 characters, uppercase, lowercase, number and special character."
        );

        valid = false;

    }


    if (confirmPasswordValue === "") {

        showError(
            "confirmPasswordError",
            "Please confirm your password."
        );

        valid = false;

    } else if (passwordValue !== confirmPasswordValue) {

        showError(
            "confirmPasswordError",
            "Passwords do not match."
        );

        valid = false;

    }


    if (!terms.checked) {

        showError(
            "termsError",
            "Please accept the Terms & Conditions."
        );

        valid = false;

    }


    if (valid) {

        form.submit();

    }

});


clearButton.addEventListener("click", function() {

    clearErrors();

    strengthText.textContent = "";
    strengthBar.style.width = "0%";

});


if (modalClose) {

    modalClose.addEventListener("click", function() {

        successModal.classList.remove("show");

        form.reset();

        strengthText.textContent = "";
        strengthBar.style.width = "0%";

        window.location.href = "login.html";

    });

}


if (successModal) {

    successModal.addEventListener("click", function(event) {

        if (event.target === successModal) {
            successModal.classList.remove("show");
        }

    });

}