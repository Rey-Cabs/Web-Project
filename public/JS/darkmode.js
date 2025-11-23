/**
 * Dark Mode Toggle Script
 * Handles theme switching and persistence
 */

(function () {
  "use strict";

  const DARK_MODE_KEY = "darkModeEnabled";
  const darkModeToggle = document.getElementById("darkModeToggle");
  const htmlElement = document.documentElement;
  const bodyElement = document.body;

  /**
   * Initialize dark mode based on user preference or stored setting
   */
  function initializeDarkMode() {
    // Check if user has a saved preference
    const savedPreference = localStorage.getItem(DARK_MODE_KEY);
    let isDarkMode = false;

    if (savedPreference !== null) {
      isDarkMode = JSON.parse(savedPreference);
    } else {
      // Check system preference
      isDarkMode =
        window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: dark)").matches;
    }

    if (isDarkMode) {
      enableDarkMode();
    }
  }

  /**
   * Enable dark mode
   */
  function enableDarkMode() {
    bodyElement.classList.add("dark-mode");
    localStorage.setItem(DARK_MODE_KEY, JSON.stringify(true));
    updateToggleIcon();
  }

  /**
   * Disable dark mode
   */
  function disableDarkMode() {
    bodyElement.classList.remove("dark-mode");
    localStorage.setItem(DARK_MODE_KEY, JSON.stringify(false));
    updateToggleIcon();
  }

  /**
   * Toggle dark mode on and off
   */
  function toggleDarkMode() {
    if (bodyElement.classList.contains("dark-mode")) {
      disableDarkMode();
    } else {
      enableDarkMode();
    }
  }

  /**
   * Update the toggle button icon
   */
  function updateToggleIcon() {
    if (!darkModeToggle) return;
    const icon = darkModeToggle.querySelector(".dark-mode-icon");
    if (icon) {
      if (bodyElement.classList.contains("dark-mode")) {
        icon.textContent = "☀️";
        darkModeToggle.title = "Switch to light mode";
      } else {
        icon.textContent = "🌙";
        darkModeToggle.title = "Switch to dark mode";
      }
    }
  }

  /**
   * Listen for system theme changes
   */
  function watchSystemTheme() {
    if (!window.matchMedia) return;

    const darkModeQuery = window.matchMedia("(prefers-color-scheme: dark)");

    // Modern browsers
    if (darkModeQuery.addEventListener) {
      darkModeQuery.addEventListener("change", (e) => {
        if (localStorage.getItem(DARK_MODE_KEY) === null) {
          if (e.matches) {
            enableDarkMode();
          } else {
            disableDarkMode();
          }
        }
      });
    }
  }

  // Attach click event listener
  if (darkModeToggle) {
    darkModeToggle.addEventListener("click", toggleDarkMode);
  }

  // Initialize on page load
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeDarkMode);
  } else {
    initializeDarkMode();
  }

  // Watch for system theme changes
  watchSystemTheme();
})();
