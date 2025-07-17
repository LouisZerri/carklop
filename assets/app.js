import "./styles/app.css";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

/**
 * Affichage/masquage du mot de passe
 */
function initPasswordToggle() {
    const toggleBtn = document.querySelector("[data-toggle-password]");
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.querySelector("[data-eye-icon]");

    if (!toggleBtn || !passwordInput || !eyeIcon) return;

    toggleBtn.addEventListener("click", (e) => {
        e.preventDefault();
        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";
        eyeIcon.classList.toggle("fa-eye");
        eyeIcon.classList.toggle("fa-eye-slash");
        toggleBtn.setAttribute(
            "aria-label",
            isHidden ? "Masquer le mot de passe" : "Afficher le mot de passe"
        );
    });
}

/**
 * Affichage des messages flash en toast
 */
function initFlashMessages() {
    const flashMessages = document.querySelectorAll("[data-flash-message]");
    flashMessages.forEach((element) => {
        const type = element.dataset.type || "info";
        const message = element.dataset.flashMessage;
        if (!message) return;

        Toastify({
            text: message,
            duration: 5000,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
            style: {
                borderRadius: "0.5rem",
                background:
                    type === "success"
                        ? "#16a34a"
                        : type === "error"
                        ? "#dc2626"
                        : "#3b82f6",
                color: "#fff",
                padding: "0.75rem 1rem",
                fontSize: "0.875rem",
            },
        }).showToast();
    });
}

/**
 * Menu déroulant (dropdown)
 */
function initDropdownMenu() {
    const menu = document.getElementById("dropdownMenu");
    if (!menu) return;

    // Fonction pour basculer le menu (accessible globalement)
    window.toggleDropdown = function() {
        menu.classList.toggle("hidden");
    };

    // Fermer le menu en cliquant ailleurs
    document.addEventListener("click", (e) => {
        const button = e.target.closest('button');
        const isClickInside = menu.contains(e.target);
        
        // Si ce n'est pas un clic sur le bouton de toggle et pas à l'intérieur du menu
        if (!button || !button.getAttribute('onclick')?.includes('toggleDropdown')) {
            if (!isClickInside) {
                menu.classList.add("hidden");
            }
        }
    });

    // Fermer avec la touche Échap
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && !menu.classList.contains("hidden")) {
            menu.classList.add("hidden");
        }
    });
}

/**
 * Initialisation de l'application
 */
function initApp() {
    initPasswordToggle();
    initFlashMessages();
    initDropdownMenu();
}

document.addEventListener("DOMContentLoaded", initApp);