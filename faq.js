let faqs = [];
let currentPage = 1;

const faqsPerPage = 6;

const faqContainer = document.getElementById("faqContainer");
const searchInput = document.getElementById("searchInput");


// Load FAQ data
async function loadFAQs() {

    try {

        faqContainer.innerHTML = "<p>Loading FAQs...</p>";

        await new Promise(function(resolve) {
            setTimeout(resolve, 3000);
        });

        const response = await fetch("faqs.json");

        if (!response.ok) {
            throw new Error("Failed to load FAQ data");
        }

        faqs = await response.json();

        showWelcome();

    }
    catch (error) {

        faqContainer.innerHTML = `
            <div class="no-result">

                <h3>❌ Unable to Load FAQs</h3>

                <p>
                    Please make sure that faqs.json
                    is in the same folder as faq.html.
                </p>

            </div>
        `;

        console.error(error);
    }
}


// Welcome screen
function showWelcome() {

    currentPage = 1;

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

    currentPage = 1;

    const result = faqs.filter(function(faq) {

        return faq.category === category;

    });

    displayFAQs(result, category + " FAQs");
}


// Get filtered FAQs
function getFilteredFAQs() {

    let result = [...faqs];

    const text =
        searchInput.value.toLowerCase().trim();


    if (text !== "") {

        result = result.filter(function(faq) {

            return (

                faq.question.toLowerCase().includes(text)

                ||

                faq.answer.toLowerCase().includes(text)

                ||

                faq.category.toLowerCase().includes(text)

            );

        });

    }


    return result;
}


// Display FAQs
function displayFAQs(data, title = "Search Results") {

    if (data.length === 0) {

        faqContainer.innerHTML = `

            <div class="no-result">

                <h3>😕 No FAQ Found</h3>

                <p>
                    Try another search or choose a different topic.
                </p>

            </div>

        `;

        return;
    }


    const totalPages =
        Math.ceil(data.length / faqsPerPage);


    if (currentPage > totalPages) {
        currentPage = totalPages;
    }


    const start =
        (currentPage - 1) * faqsPerPage;

    const end =
        start + faqsPerPage;


    const pageData =
        data.slice(start, end);


    faqContainer.innerHTML = `

        <div class="result-header">

            <h3>${title}</h3>

            <button onclick="showWelcome()">
                ← Back to Topics
            </button>

        </div>


        <div class="faq-controls">

            <select id="sortSelect">

                <option value="default">
                    Sort By
                </option>

                <option value="az">
                    Question A-Z
                </option>

                <option value="za">
                    Question Z-A
                </option>

            </select>

        </div>


        <div class="faq-list">

            ${pageData.map(function(faq) {

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


        <div class="pagination">

            <button
                onclick="previousPage()"
                ${currentPage === 1 ? "disabled" : ""}
            >
                Previous
            </button>

            ${createPageButtons(totalPages)}

            <button
                onclick="nextPage()"
                ${currentPage === totalPages ? "disabled" : ""}
            >
                Next
            </button>

        </div>

    `;


    addFAQEvents();


    const sortSelect =
        document.getElementById("sortSelect");


    sortSelect.addEventListener("change", function() {

        sortFAQs(this.value, data, title);

    });

}


// Sort FAQs
function sortFAQs(sortValue, data, title) {

    let sortedData = [...data];


    if (sortValue === "az") {

        sortedData.sort(function(a, b) {

            return a.question.localeCompare(b.question);

        });

    }


    if (sortValue === "za") {

        sortedData.sort(function(a, b) {

            return b.question.localeCompare(a.question);

        });

    }


    currentPage = 1;

    displayFAQs(sortedData, title);
}


// Create pagination buttons
function createPageButtons(totalPages) {

    let buttons = "";


    for (let i = 1; i <= totalPages; i++) {

        buttons += `

            <button
                class="${i === currentPage ? "active" : ""}"
                onclick="goToPage(${i})"
            >
                ${i}
            </button>

        `;

    }


    return buttons;
}


// Go to selected page
function goToPage(page) {

    currentPage = page;

    const result = getFilteredFAQs();

    let title = "Search Results";


    if (searchInput.value.trim() === "") {
        title = "All FAQs";
    }


    displayFAQs(result, title);
}


// Previous page
function previousPage() {

    if (currentPage > 1) {

        currentPage--;

        const result = getFilteredFAQs();

        displayFAQs(result, "Search Results");

    }
}


// Next page
function nextPage() {

    const result = getFilteredFAQs();

    const totalPages =
        Math.ceil(result.length / faqsPerPage);


    if (currentPage < totalPages) {

        currentPage++;

        displayFAQs(result, "Search Results");

    }
}


// FAQ open and close
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


    currentPage = 1;

    const result = getFilteredFAQs();

    displayFAQs(result, "Search Results");

});


// Start
loadFAQs();