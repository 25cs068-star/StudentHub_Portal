document.addEventListener("DOMContentLoaded", function () {

    let events = [];
    let currentPage = 1;
    const eventsPerPage = 6;

    const eventsContainer = document.getElementById("eventsContainer");
    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const sortSelect = document.getElementById("sortSelect");
    const fromDate = document.getElementById("fromDate");
    const toDate = document.getElementById("toDate");
    const pagination = document.getElementById("pagination");
    const resultInfo = document.getElementById("resultInfo");
    const featuredSection = document.querySelector(".featured-section");


    async function loadEvents() {

        try {

            const response = await fetch("events.json");

            if (!response.ok) {
                throw new Error("Unable to load events.json");
            }

            events = await response.json();

            if (!Array.isArray(events)) {
                throw new Error("Invalid JSON format");
            }

            displayEvents();

        } catch (error) {

            console.error(error);

            eventsContainer.innerHTML = `
                <div class="message">
                    <h3>Unable to Load Events</h3>
                    <p>Please make sure that events.json is in the same folder as events.html.</p>
                </div>
            `;

            resultInfo.textContent = "";
            pagination.innerHTML = "";
        }

    }


    function isFilterActive() {

        return (
            searchInput.value.trim() !== "" ||
            categoryFilter.value !== "all" ||
            fromDate.value !== "" ||
            toDate.value !== ""
        );

    }


    function getFilteredEvents() {

        let filtered = [...events];

        const search =
            searchInput.value.trim().toLowerCase();

        const category =
            categoryFilter.value;

        const from =
            fromDate.value;

        const to =
            toDate.value;


        if (search !== "") {

            filtered = filtered.filter(function (event) {

                const name =
                    String(event.name || "").toLowerCase();

                const eventCategory =
                    String(event.category || "").toLowerCase();

                const venue =
                    String(event.venue || "").toLowerCase();

                const description =
                    String(event.description || "").toLowerCase();


                return (
                    name.includes(search) ||
                    eventCategory.includes(search) ||
                    venue.includes(search) ||
                    description.includes(search)
                );

            });

        }


        if (category !== "all") {

            filtered = filtered.filter(function (event) {

                return event.category === category;

            });

        }


        if (from !== "" && to !== "") {

            filtered = filtered.filter(function (event) {

                return (
                    event.date >= from &&
                    event.date <= to
                );

            });

        } else if (from !== "") {

            filtered = filtered.filter(function (event) {

                return event.date === from;

            });

        } else if (to !== "") {

            filtered = filtered.filter(function (event) {

                return event.date === to;

            });

        }


        if (sortSelect.value === "dateAsc") {

            filtered.sort(function (a, b) {

                return a.date.localeCompare(b.date);

            });

        }


        if (sortSelect.value === "dateDesc") {

            filtered.sort(function (a, b) {

                return b.date.localeCompare(a.date);

            });

        }


        if (sortSelect.value === "nameAsc") {

            filtered.sort(function (a, b) {

                return String(a.name || "")
                    .localeCompare(String(b.name || ""));

            });

        }


        if (sortSelect.value === "nameDesc") {

            filtered.sort(function (a, b) {

                return String(b.name || "")
                    .localeCompare(String(a.name || ""));

            });

        }


        return filtered;

    }


    function formatDate(dateString) {

        if (!dateString) {
            return "Date not available";
        }

        const date =
            new Date(dateString + "T00:00:00");

        if (isNaN(date.getTime())) {
            return dateString;
        }

        return date.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        });

    }


    function getIcon(category) {

        if (category === "Technical") {
            return "💻";
        }

        if (category === "Sports") {
            return "🏆";
        }

        if (category === "Cultural") {
            return "🎭";
        }

        return "📅";

    }


    function displayEvents() {

        const filtered =
            getFilteredEvents();


        if (featuredSection) {

            if (isFilterActive()) {
                featuredSection.style.display = "none";
            } else {
                featuredSection.style.display = "block";
            }

        }


        if (filtered.length === 0) {

            resultInfo.textContent =
                "No events found";


            eventsContainer.innerHTML = `
                <div class="message">

                    <h3>No Events Found</h3>

                    <p>
                        Try another search, category or date.
                    </p>

                </div>
            `;

            pagination.innerHTML = "";

            return;

        }


        const totalPages =
            Math.ceil(filtered.length / eventsPerPage);


        if (currentPage > totalPages) {
            currentPage = totalPages;
        }


        const start =
            (currentPage - 1) * eventsPerPage;

        const end =
            start + eventsPerPage;


        const pageEvents =
            filtered.slice(start, end);


        if (isFilterActive()) {

            resultInfo.textContent =
                "Found " + filtered.length + " event(s)";

        } else {

            resultInfo.textContent =
                "Showing " + filtered.length + " event(s)";

        }


        eventsContainer.innerHTML = "";


        pageEvents.forEach(function (event, index) {

            const card =
                document.createElement("div");

            card.className = "event-card";


            card.innerHTML = `

                <div class="card-top">

                    <div class="event-icon">
                        ${getIcon(event.category)}
                    </div>

                    <div class="event-number">
                        ${start + index + 1}
                    </div>

                </div>


                <div class="event-date">
                    📅 ${formatDate(event.date)}
                </div>


                <h3>
                    ${event.name || "Event Name"}
                </h3>


                <p class="event-description">
                    ${event.description || "No description available."}
                </p>


                <div class="event-info">

                    <p>
                        <b>📂</b>
                        ${event.category || "General"}
                    </p>

                    <p>
                        <b>📍</b>
                        ${event.venue || "Venue not available"}
                    </p>

                </div>

            `;


            eventsContainer.appendChild(card);

        });


        displayPagination(totalPages);

    }


    function displayPagination(totalPages) {

        pagination.innerHTML = "";


        if (totalPages <= 1) {
            return;
        }


        const previous =
            document.createElement("button");

        previous.textContent = "Previous";

        previous.disabled =
            currentPage === 1;


        previous.addEventListener("click", function () {

            currentPage--;

            displayEvents();

        });


        pagination.appendChild(previous);


        for (let i = 1; i <= totalPages; i++) {

            const button =
                document.createElement("button");

            button.textContent = i;


            if (i === currentPage) {
                button.classList.add("active");
            }


            button.addEventListener("click", function () {

                currentPage = i;

                displayEvents();

            });


            pagination.appendChild(button);

        }


        const next =
            document.createElement("button");

        next.textContent = "Next";

        next.disabled =
            currentPage === totalPages;


        next.addEventListener("click", function () {

            currentPage++;

            displayEvents();

        });


        pagination.appendChild(next);

    }


    searchInput.addEventListener("input", function () {

        currentPage = 1;

        displayEvents();

    });


    categoryFilter.addEventListener("change", function () {

        currentPage = 1;

        displayEvents();

    });


    sortSelect.addEventListener("change", function () {

        currentPage = 1;

        displayEvents();

    });


    fromDate.addEventListener("change", function () {

        currentPage = 1;

        displayEvents();

    });


    toDate.addEventListener("change", function () {

        currentPage = 1;

        displayEvents();

    });


    loadEvents();

});