
document.addEventListener("DOMContentLoaded", () => {
function showAlert(message) {
    alert(message);
}
function clearURLParams() {
    history.replaceState(null, null, window.location.pathname);
}

window.addEventListener('DOMContentLoaded', (event) => {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        showAlert(decodeURIComponent(message));
        clearURLParams();
    }
});
});
