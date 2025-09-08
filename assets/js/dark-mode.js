// Function to set dark mode state
function setDarkMode(isDark) {
    if (isDark) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        document.documentElement.removeAttribute('data-bs-theme');
        localStorage.setItem('darkMode', 'disabled');
    }
    updateDarkModeButton();
}

// Function to toggle dark mode
function toggleDarkMode() {
    const isDark = localStorage.getItem('darkMode') !== 'enabled';
    setDarkMode(isDark);
}

// Function to update the dark mode button icon
function updateDarkModeButton() {
    const darkModeBtn = document.getElementById('darkModeToggle');
    if (darkModeBtn) {
        const isDark = localStorage.getItem('darkMode') === 'enabled';
        darkModeBtn.innerHTML = isDark ? '☀️' : '🌙';
        darkModeBtn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    }
}

// Initialize dark mode based on saved preference or system preference
document.addEventListener('DOMContentLoaded', function() {
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const savedMode = localStorage.getItem('darkMode');
    
    if (savedMode === 'enabled' || (savedMode === null && prefersDarkScheme.matches)) {
        setDarkMode(true);
    } else {
        setDarkMode(false);
    }

    // Update button state after a short delay to ensure it's in the DOM
    setTimeout(updateDarkModeButton, 100);
});
