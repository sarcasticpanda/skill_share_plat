// Example: Auto-hide success messages after 3 seconds
document.addEventListener("DOMContentLoaded", function () {
    const flash = document.querySelector(".flash-message");
    if (flash) {
        setTimeout(() => {
            flash.style.display = "none";
        }, 3000);
    }
});

// Example: Confirmation before deleting skill (optional)
function confirmDelete() {
    return confirm("Are you sure you want to delete this skill?");
}