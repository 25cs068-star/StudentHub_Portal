// ===============================
// COMMON JAVASCRIPT
// ===============================


// ===============================
// DARK / LIGHT MODE
// ===============================

const themeToggle = document.getElementById("themeToggle");

if (themeToggle) {

    const savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {

        document.body.classList.add("dark-mode");
        themeToggle.innerHTML = "☀️ Light Mode";

    } else {

        document.body.classList.remove("dark-mode");
        themeToggle.innerHTML = "🌙 Dark Mode";

    }


    themeToggle.addEventListener("click", function () {

        document.body.classList.toggle("dark-mode");

        if (document.body.classList.contains("dark-mode")) {

            themeToggle.innerHTML = "☀️ Light Mode";
            localStorage.setItem("theme", "dark");

        } else {

            themeToggle.innerHTML = "🌙 Dark Mode";
            localStorage.setItem("theme", "light");

        }

    });

}


// ===============================
// HAMBURGER MENU
// ===============================

const menuToggle = document.getElementById("menuToggle");
const nav = document.querySelector("nav");

if (menuToggle && nav) {

    menuToggle.addEventListener("click", function () {

        nav.classList.toggle("active");

    });

}


// ===============================
// CURRENT PAGE HIGHLIGHT
// ===============================

const currentPage = window.location.pathname.split("/").pop();

document.querySelectorAll("nav a").forEach(function (link) {

    const linkPage = link.getAttribute("href");

    if (linkPage === currentPage) {

        link.classList.add("active");

    }

});