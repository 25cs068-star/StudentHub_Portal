document.addEventListener("DOMContentLoaded", function () {

    const assignmentSearch =
        document.querySelector(".assignment-search");

    const assignmentFilter =
        document.querySelector(".assignment-filter");

    const assignments =
        document.querySelectorAll(".assignment");

    function filterAssignments() {

        const searchText =
            assignmentSearch.value.toLowerCase().trim();

        const filterValue =
            assignmentFilter.value;

        assignments.forEach(function (assignment) {

            const assignmentText =
                assignment.textContent.toLowerCase();

            const matchesSearch =
                assignmentText.includes(searchText);

            let matchesFilter = true;

            if (filterValue === "Completed") {
                matchesFilter =
                    assignment.querySelector(".completed") !== null;
            } else if (filterValue === "Pending") {
                matchesFilter =
                    assignment.querySelector(".pending") !== null;
            } else if (filterValue === "In Progress") {
                matchesFilter =
                    assignment.querySelector(".in-progress") !== null;
            }

            if (matchesSearch && matchesFilter) {
                assignment.style.display = "grid";
            } else {
                assignment.style.display = "none";
            }

        });

    }

    if (assignmentSearch && assignmentFilter) {

        assignmentSearch.addEventListener(
            "input",
            filterAssignments
        );

        assignmentFilter.addEventListener(
            "change",
            filterAssignments
        );

    }

});