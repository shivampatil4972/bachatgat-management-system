/**
 * Theme Switcher Logic
 * Handles toggling between Light and Dark mode, saving preference to localStorage.
 */

 document.addEventListener('DOMContentLoaded', () => {
    const htmlEl = document.documentElement;
    const themeToggleBtns = document.querySelectorAll('.theme-toggle-btn');
    
    // Check for saved theme preference or OS preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    let currentTheme = savedTheme || 'dark';
    
    // Apply initial theme
    applyTheme(currentTheme);
    
    // Add click listener to all toggle buttons (could be one in navbar, one in sidebar, etc.)
    themeToggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            currentTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(currentTheme);
        });
    });
    
    function applyTheme(theme) {
        htmlEl.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Update icons
        themeToggleBtns.forEach(btn => {
            if (theme === 'dark') {
                btn.innerHTML = '<i class="bi bi-moon-fill"></i>';
            } else {
                btn.innerHTML = '<i class="bi bi-sun-fill"></i>';
            }
        });
    }
});
