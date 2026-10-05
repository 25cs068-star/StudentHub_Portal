let students = [];

const studentsContainer =
    document.getElementById("studentsContainer");

const studentSearch =
    document.getElementById("studentSearch");

const departmentFilter =
    document.getElementById("departmentFilter");

const yearFilter =
    document.getElementById("yearFilter");

const sortSelect =
    document.getElementById("sortSelect");


// Load students from JSON

async function loadStudents() {

    try {

        studentsContainer.innerHTML =
            '<div class="message">Loading students...</div>';


        const response =
            await fetch("students.json");


        if (!response.ok) {

            throw new Error("Failed to load students");

        }


        students =
            await response.json();


        displayStudents();

    }

    catch (error) {

        studentsContainer.innerHTML =
            '<div class="message">❌ Error loading student data.</div>';

        console.error(error);

    }

}


// Filter and sort students

function getFilteredStudents() {

    let result = [...students];


    const searchText =
        studentSearch.value.toLowerCase().trim();


    const department =
        departmentFilter.value;


    const year =
        yearFilter.value;


    const sortValue =
        sortSelect.value;


    // Search

    if (searchText) {

        result = result.filter(function(student) {

            return (

                student.name
                    .toLowerCase()
                    .includes(searchText)

                ||

                student.email
                    .toLowerCase()
                    .includes(searchText)

                ||

                student.city
                    .toLowerCase()
                    .includes(searchText)

            );

        });

    }


    // Department filter

    if (department !== "all") {

        result = result.filter(function(student) {

            return student.department === department;

        });

    }


    // Year filter

    if (year !== "all") {

        result = result.filter(function(student) {

            return student.year == year;

        });

    }


    // Sorting

    if (sortValue === "nameAsc") {

        result.sort(function(a, b) {

            return a.name.localeCompare(b.name);

        });

    }


    if (sortValue === "nameDesc") {

        result.sort(function(a, b) {

            return b.name.localeCompare(a.name);

        });

    }


    return result;

}


// Display students

function displayStudents() {

    const result =
        getFilteredStudents();


    if (result.length === 0) {

        studentsContainer.innerHTML = `

            <div class="message">

                <h3>No students found</h3>

                <p>
                    Try another search or filter.
                </p>

            </div>

        `;

        return;

    }


    studentsContainer.innerHTML =
        result.map(function(student) {

            return `

                <div class="student-card">

                    <h3>${student.name}</h3>

                    <span class="department">
                        ${student.department}
                    </span>

                    <p>
                        <b>Year:</b>
                        ${student.year}
                    </p>

                    <p>
                        <b>Email:</b>
                        ${student.email}
                    </p>

                    <p>
                        <b>City:</b>
                        ${student.city}
                    </p>

                </div>

            `;

        }).join("");

}


// Search event

studentSearch.addEventListener("input", function() {

    displayStudents();

});


// Department filter

departmentFilter.addEventListener("change", function() {

    displayStudents();

});


// Year filter

yearFilter.addEventListener("change", function() {

    displayStudents();

});


// Sort

sortSelect.addEventListener("change", function() {

    displayStudents();

});


// Start loading

loadStudents();