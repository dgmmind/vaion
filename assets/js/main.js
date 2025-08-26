
  document.addEventListener('DOMContentLoaded', function() {
      // Código relacionado con el switch de pausa eliminado
  });

  class UIController {
    constructor() {
      this.actions = document.getElementById("actions");
      this.tabs = document.querySelectorAll(".tab");
      this.mobileMenu = document.querySelector(".mobile-menu");

        this.initEvents();
      }

      toggleDropdown(id) {
        document.querySelectorAll(".dropdown").forEach(d => {
          if (d.id !== id) d.classList.remove("show");
        });
        const dropdown = document.getElementById(id);
        dropdown.classList.toggle("show");
      }

      closeDropdowns() {
        document.querySelectorAll(".dropdown").forEach(d => d.classList.remove("show"));
      }

      toggleMobileMenu() { this.actions.classList.toggle("mobile-open"); }
      closeMobileMenu() { this.actions.classList.remove("mobile-open"); }

      initEvents() {
        document.querySelectorAll(".button[data-dropdown]").forEach(btn => {
          btn.addEventListener("click", e => {
            e.preventDefault();
            this.toggleDropdown(btn.dataset.dropdown);
          });
        });

        this.mobileMenu.addEventListener("click", () => this.toggleMobileMenu());

        document.addEventListener("click", e => {
          if(!e.target.closest(".button") && !e.target.closest(".dropdown")) this.closeDropdowns();
          if(!this.actions.contains(e.target) && !this.mobileMenu.contains(e.target)) this.closeMobileMenu();
        });
      }
    }

    document.addEventListener("DOMContentLoaded", () => new UIController());

    (function() {
      document.querySelectorAll('.checkbox-cell input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function () {
          let icon = this.nextElementSibling;
          icon.setAttribute('data-feather', this.checked ? 'check-square' : 'square');
          
          feather.replace(); // vuelve a renderizar
        });
      });
    })()