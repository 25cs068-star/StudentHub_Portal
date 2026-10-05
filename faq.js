let faqs = [];

const faqContainer = document.getElementById("faqContainer");
const searchInput = document.getElementById("searchInput");


// Load FAQ data
async function loadFAQs() {

    try {

        faqContainer.innerHTML = "<p>Loading FAQs...</p>";

        const response = await fetch("faqs.json");

        if (!response.ok) {
            throw new Error("Failed to load FAQ data");
        }

        faqs = await response.json();

        showWelcome();

    }
    catch (error) {

        faqContainer.innerHTML =
            "<p>❌ Error loading FAQs.</p>";

        console.error(error);
    }
}


// Initial welcome screen
function showWelcome() {

    faqContainer.innerHTML = `

        <div class="welcome-box">

            <h3>👋 Welcome to StudentHub FAQ</h3>

            <p>
                Search your question or choose a topic below.
            </p>

            <div class="category-buttons">

                <button onclick="showCategory('Account')">
                    🎓 Account
                </button>

                <button onclick="showCategory('Events')">
                    📅 Events
                </button>

                <button onclick="showCategory('Profile')">
                    👤 Profile
                </button>

                <button onclick="showCategory('Support')">
                    💬 Support
                </button>

                <button onclick="showCategory('General')">
                    ℹ️ General
                </button>

            </div>

        </div>

    `;
}


// Show FAQ according to category
function showCategory(category) {

    const result = faqs.filter(function(faq) {

        return faq.category === category;

    });

    displayFAQs(result, category);
}


// Display FAQ
function displayFAQs(data, title = "Search Results") {

    if (data.length === 0) {

        faqContainer.innerHTML = `

            <div class="no-result">

                <h3>😕 No FAQ Found</h3>

                <p>Try another search or choose a different topic.</p>

            </div>

        `;

        return;
    }


    faqContainer.innerHTML = `

        <div class="result-header">

            <h3>${title}</h3>

            <button onclick="showWelcome()">
                ← Back to Topics
            </button>

        </div>

        <div class="faq-list">

            ${data.map(function(faq) {

                return `

                    <div class="faq-item">

                        <button class="faq-question">

                            <span>${faq.question}</span>

                            <b>+</b>

                        </button>

                        <div class="faq-answer">

                            ${faq.answer}

                        </div>

                    </div>

                `;

            }).join("")}

        </div>

    `;


    addFAQEvents();
}


// FAQ open / close
function addFAQEvents() {

    const questions =
        document.querySelectorAll(".faq-question");


    questions.forEach(function(question) {

        question.addEventListener("click", function() {

            const answer =
                this.nextElementSibling;

            const symbol =
                this.querySelector("b");


            answer.classList.toggle("show");


            if (answer.classList.contains("show")) {

                symbol.innerHTML = "−";

            }
            else {

                symbol.innerHTML = "+";

            }

        });

    });

}


// Search FAQ
searchInput.addEventListener("input", function() {

    const text =
        searchInput.value.toLowerCase().trim();


    if (text === "") {

        showWelcome();

        return;
    }


    const result = faqs.filter(function(faq) {

        return (

            faq.question.toLowerCase().includes(text)

            ||

            faq.answer.toLowerCase().includes(text)

            ||

            faq.category.toLowerCase().includes(text)

        );

    });


    displayFAQs(result, "Search Results");

});


// Start
loadFAQs();