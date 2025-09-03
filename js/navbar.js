document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.querySelector(".menu-toggle");
    const dropdown = document.querySelector(".drop-down");

    toggleBtn.addEventListener("click", function () {
        dropdown.classList.toggle("show");
    });
});