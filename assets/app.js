// Load Symfony UX bootstrap for Stimulus and Turbo integration.
import './stimulus_bootstrap.js';
// Load the global front-end stylesheet.
import './styles/app.css';

// Mark the document as JavaScript-enabled for progressive enhancement.
document.documentElement.classList.add('has-js');

// Define available theme modes used by the front-office.
const THEME_MODES = {
    // Define light mode key.
    LIGHT: 'light',
    // Define dark mode key.
    DARK: 'dark',
};

// Return the browser-preferred theme mode.
const getSystemTheme = () => {
    // Check if dark mode is preferred by the OS.
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    // Return the resolved mode.
    return prefersDark ? THEME_MODES.DARK : THEME_MODES.LIGHT;
};

// Apply a theme mode to the document.
const applyTheme = (themeMode) => {
    // Set theme attribute on root element.
    document.documentElement.setAttribute('data-theme', themeMode);
    // Persist theme in localStorage.
    localStorage.setItem('atlas-theme', themeMode);
};

// Update theme toggle button label and state.
const updateThemeToggleButton = () => {
    // Grab the toggle button element.
    const toggleButton = document.getElementById('themeToggleButton');
    // Grab the toggle label element.
    const toggleLabel = document.getElementById('themeToggleLabel');

    // Exit when toggle elements are not present.
    if (!toggleButton || !toggleLabel) {
        // Stop function safely.
        return;
    }

    // Read active theme from root element.
    const activeTheme = document.documentElement.getAttribute('data-theme') || THEME_MODES.LIGHT;

    // Handle dark mode active state.
    if (activeTheme === THEME_MODES.DARK) {
        // Render label for dark mode state.
        toggleLabel.textContent = 'Mode sombre';
        // Mark button as pressed for accessibility.
        toggleButton.setAttribute('aria-pressed', 'true');
    } else {
        // Render label for light mode state.
        toggleLabel.textContent = 'Mode clair';
        // Mark button as not pressed for accessibility.
        toggleButton.setAttribute('aria-pressed', 'false');
    }
};

// Register a callback once the DOM is fully loaded.
document.addEventListener('DOMContentLoaded', () => {
    // Grab sticky header element when available.
    const header = document.getElementById('siteHeader');
    // Grab all reveal elements used for progressive entrance.
    const revealElements = document.querySelectorAll('.reveal');
    // Grab the theme toggle button.
    const toggleButton = document.getElementById('themeToggleButton');

    // Read persisted theme from localStorage.
    const storedTheme = localStorage.getItem('atlas-theme');
    // Resolve initial theme from storage or system preference.
    const initialTheme = storedTheme || getSystemTheme();
    // Apply resolved theme.
    applyTheme(initialTheme);
    // Synchronize toggle button UI.
    updateThemeToggleButton();

    // Attach click handler when toggle button exists.
    if (toggleButton) {
        // Register click listener for theme switch.
        toggleButton.addEventListener('click', () => {
            // Read current theme mode.
            const currentTheme = document.documentElement.getAttribute('data-theme') || THEME_MODES.LIGHT;
            // Compute next theme mode.
            const nextTheme = currentTheme === THEME_MODES.DARK ? THEME_MODES.LIGHT : THEME_MODES.DARK;
            // Apply next theme mode.
            applyTheme(nextTheme);
            // Update button UI after mode switch.
            updateThemeToggleButton();
        });
    }

    // Define callback to toggle compact header state on scroll.
    const onScroll = () => {
        // Exit when header is not present.
        if (!header) {
            // Stop function safely.
            return;
        }

        // Check if page is scrolled beyond threshold.
        if (window.scrollY > 12) {
            // Add compact class when scrolled.
            header.classList.add('is-scrolled');
        } else {
            // Remove compact class near top.
            header.classList.remove('is-scrolled');
        }
    };

    // Attach scroll listener for header state.
    window.addEventListener('scroll', onScroll, { passive: true });
    // Run scroll handler once at load.
    onScroll();

    // Check if IntersectionObserver is supported.
    if ('IntersectionObserver' in window) {
        // Create reveal observer instance.
        const revealObserver = new IntersectionObserver((entries, observer) => {
            // Iterate entries coming from observer.
            entries.forEach((entry) => {
                // Continue only for intersecting entries.
                if (entry.isIntersecting) {
                    // Mark element as visible.
                    entry.target.classList.add('is-visible');
                    // Stop observing current element.
                    observer.unobserve(entry.target);
                }
            });
        }, {
            // Trigger reveal slightly before center.
            rootMargin: '0px 0px -10% 0px',
            // Use low threshold for soft reveal.
            threshold: 0.08,
        });

        // Observe each reveal element.
        revealElements.forEach((element) => {
            // Start observation for current element.
            revealObserver.observe(element);
        });
    } else {
        // Fallback for old browsers without observer support.
        revealElements.forEach((element) => {
            // Force visible state immediately.
            element.classList.add('is-visible');
        });
    }
});
