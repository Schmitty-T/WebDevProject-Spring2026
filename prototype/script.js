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

  // --- 2. REGISTRATION LOGIC ---
  const regForm = document.getElementById("register-form");
  if (regForm) {
    regForm.addEventListener("submit", (e) => {
      e.preventDefault(); // THIS STOPS THE REFRESH

      const user = document.getElementById("reg-username").value;
      const pass = document.getElementById("reg-password").value;

      if (user && pass) {
        localStorage.setItem(
          "mockUser",
          JSON.stringify({ username: user, password: pass }),
        );
        alert("Registration successful! Redirecting to login...");
        window.location.href = "login.html";
      }
    });
  }

  // --- 3. LOGIN LOGIC ---
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault(); // THIS STOPS THE REFRESH

      const userInput = document.getElementById("login-username").value;
      const passInput = document.getElementById("login-password").value;
      const storedUser = JSON.parse(localStorage.getItem("mockUser"));

      if (
        storedUser &&
        storedUser.username === userInput &&
        storedUser.password === passInput
      ) {
        localStorage.setItem("currentUser", userInput);
        window.location.href = "homepage.html";
      } else {
        alert("Invalid credentials. Did you register first?");
      }
    });
  }

  // Handle Logout for the <div> element
  const logoutBtn = document.getElementById("logout-btn");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      // 1. Remove the current user from storage
      localStorage.removeItem("currentUser");

      // 2. Optional: Provide a quick alert
      alert("You have been logged out.");

      // 3. Refresh the page to reset the UI (hides welcome message, shows login/register)
      window.location.href = "homepage.html";
    });
  }

  // --- 4. AUTH UI UPDATE (For Homepage) ---
  const loggedOutView = document.getElementById("logged-out-view");
  const loggedInView = document.getElementById("logged-in-view");
  const currentUser = localStorage.getItem("currentUser");

  if (currentUser && loggedInView) {
    if (loggedOutView) loggedOutView.style.display = "none";
    loggedInView.style.display = "block";
    document.getElementById("welcome-message").textContent =
      `Welcome back, ${currentUser}!`;
  }
});
