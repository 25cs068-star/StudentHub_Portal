// ===============================
// HOME PAGE JAVASCRIPT
// ===============================


// ===============================
// NOTIFICATION CLOSE
// ===============================

const notification = document.getElementById("notification");
const closeNotification = document.getElementById("closeNotification");

if (closeNotification && notification) {

    closeNotification.addEventListener("click", function() {

        notification.style.display = "none";

    });

}


// ===============================
// IMAGE BANNER SLIDER
// ===============================

const bannerImages = document.querySelectorAll(".banner-image");

const nextBanner = document.getElementById("nextBanner");
const prevBanner = document.getElementById("prevBanner");

let currentBanner = 0;


function showBanner(index) {

    if (bannerImages.length === 0) {
        return;
    }

    bannerImages.forEach(function(image) {

        image.classList.remove("active");

    });

    bannerImages[index].classList.add("active");

}


// NEXT IMAGE

if (nextBanner) {

    nextBanner.addEventListener("click", function() {

        currentBanner++;

        if (currentBanner >= bannerImages.length) {

            currentBanner = 0;

        }

        showBanner(currentBanner);

    });

}


// PREVIOUS IMAGE

if (prevBanner) {

    prevBanner.addEventListener("click", function() {

        currentBanner--;

        if (currentBanner < 0) {

            currentBanner = bannerImages.length - 1;

        }

        showBanner(currentBanner);

    });

}


// AUTOMATIC IMAGE CHANGE EVERY 3 SECONDS

if (bannerImages.length > 0) {

    showBanner(currentBanner);

    setInterval(function() {

        currentBanner++;

        if (currentBanner >= bannerImages.length) {

            currentBanner = 0;

        }

        showBanner(currentBanner);

    }, 3000);

}


// ===============================
// OLD CONTENT SLIDER
// ===============================

const slides = document.querySelectorAll(".slide");

const nextSlide = document.getElementById("nextSlide");
const prevSlide = document.getElementById("prevSlide");

let currentSlide = 0;


function showSlide(index) {

    if (slides.length === 0) {
        return;
    }

    slides.forEach(function(slide) {

        slide.classList.remove("active");

    });

    slides[index].classList.add("active");

}


// NEXT CONTENT SLIDE

if (nextSlide) {

    nextSlide.addEventListener("click", function() {

        currentSlide++;

        if (currentSlide >= slides.length) {

            currentSlide = 0;

        }

        showSlide(currentSlide);

    });

}


// PREVIOUS CONTENT SLIDE

if (prevSlide) {

    prevSlide.addEventListener("click", function() {

        currentSlide--;

        if (currentSlide < 0) {

            currentSlide = slides.length - 1;

        }

        showSlide(currentSlide);

    });

}


// AUTOMATIC CONTENT SLIDER

if (slides.length > 0) {

    showSlide(currentSlide);

    setInterval(function() {

        currentSlide++;

        if (currentSlide >= slides.length) {

            currentSlide = 0;

        }

        showSlide(currentSlide);

    }, 4000);

}