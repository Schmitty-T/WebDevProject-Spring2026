document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("themeToggle");

  // --- 1. DARK MODE LOGIC ---
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
  }

  if (toggle) {
    toggle.addEventListener("click", () => {
      document.body.classList.toggle("dark");
      localStorage.setItem(
        "theme",
        document.body.classList.contains("dark") ? "dark" : "light",
      );
    });
  }
});
