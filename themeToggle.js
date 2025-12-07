document.addEventListener("DOMContentLoaded", () => {
    const themeToggle = document.getElementById("themeToggle");

    const savedTheme = getCookie("site_theme");
    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
        themeToggle.textContent = "Темный режим";
    }

    themeToggle.addEventListener("click", () => {
        document.body.classList.toggle("dark-theme");
        const isDarkMode = document.body.classList.contains("dark-theme");

        themeToggle.textContent = isDarkMode ? "Темный режим" : "Светлый режим";

        document.cookie = `site_theme=${isDarkMode ? "dark" : "light"}; path=/; max-age=31536000`;
    });
});

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
}