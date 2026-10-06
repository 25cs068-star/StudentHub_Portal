(function () {

    let students = [];
    let currentPage = 1;

    const studentsPerPage = 6;


    document.addEventListener("DOMContentLoaded", function () {

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

        const pagination =
            document.getElementById("pagination");


        async function loadStudents() {

            studentsContainer.innerHTML =
                '<div class="message">Loading students...</div>';

            try {

                await new Promise(function (resolve) {
                    setTimeout(resolve, 3000);
                });

                const response =
                    await fetch("students.json");

                if (!response.ok) {
                    throw new Error("students.json not found");
                }

                students = await response.json();

                displayStudents();

            } catch (error) {

                studentsContainer.innerHTML = `
                    <div class="message">
                        <h3>Unable to Load Students</h3>
                        <p>
                            Please make sure that students.json
                            is in the same folder as dashboard.html.
                        </p>
                    </div>
                `;

                pagination.innerHTML = "";

                console.error(error);
            }
        }


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


            if (searchText !== "") {

                result = result.filter(function (student) {

                    return (
                        student.name.toLowerCase().includes(searchText) ||
                        student.email.toLowerCase().includes(searchText) ||
                        student.city.toLowerCase().includes(searchText)
                    );

                });
            }


            if (department !== "all") {

                result = result.filter(function (student) {

                    return student.department === department;

                });
            }


            if (year !== "all") {

                result = result.filter(function (student) {

                    return String(student.year) === String(year);

                });
            }


            if (sortValue === "nameAsc") {

                result.sort(function (a, b) {

                    return a.name.localeCompare(b.name);

                });
            }


            if (sortValue === "nameDesc") {

                result.sort(function (a, b) {

                    return b.name.localeCompare(a.name);

                });
            }


            return result;
        }


        function displayStudents() {

            const result = getFilteredStudents();


            if (result.length === 0) {

                studentsContainer.innerHTML = `
                    <div class="message">
                        <h3>No students found</h3>
                        <p>Try another search or filter.</p>
                    </div>
                `;

                pagination.innerHTML = "";

                return;
            }


            const totalPages =
                Math.ceil(result.length / studentsPerPage);


            if (currentPage > totalPages) {
                currentPage = totalPages;
            }


            const start =
                (currentPage - 1) * studentsPerPage;

            const end =
                start + studentsPerPage;


            const pageStudents =
                result.slice(start, end);


            studentsContainer.innerHTML =
                pageStudents.map(function (student) {

                    return `
                        <div class="student-card">

                            <div class="student-card-header">

                                <div class="student-avatar">
                                    ${student.name
                                        .split(" ")
                                        .map(function (word) {
                                            return word.charAt(0);
                                        })
                                        .join("")
                                        .substring(0, 2)
                                        .toUpperCase()}
                                </div>

                                <div>

                                    <h3>${student.name}</h3>

                                    <span class="department">
                                        ${student.department}
                                    </span>

                                </div>

                            </div>

                            <div class="student-details">

                                <p>
                                    <b>Enrollment:</b>
                                    ${student.enrollment || "N/A"}
                                </p>

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

                        </div>
                    `;

                }).join("");


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

                displayStudents();

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

                    displayStudents();

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

                displayStudents();

            });


            pagination.appendChild(next);
        }


        studentSearch.addEventListener("input", function () {

            currentPage = 1;

            displayStudents();

        });


        departmentFilter.addEventListener("change", function () {

            currentPage = 1;

            displayStudents();

        });


        yearFilter.addEventListener("change", function () {

            currentPage = 1;

            displayStudents();

        });


        sortSelect.addEventListener("change", function () {

            currentPage = 1;

            displayStudents();

        });


        loadStudents();

    });

})();