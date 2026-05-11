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
  const searchInput = document.getElementById("ExerciseSearch");
const tableBody = document.querySelector(".ExerciseContainer tbody");

searchInput.addEventListener("input", ()=> {
    const query = searchInput.value;
    fetch(`exercisesearch.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = "";
        
                data.forEach(exercise => {
                    const row = document.createElement("tr");
                    
                    row.innerHTML =`
                        <td class = "titlecell">
                        ${exercise.Exercise}<br>
                        <div class="buttoncontainer"> <p class="musclegroupplaceholder">${exercise.MuscleGroup}</p>
                            <a class="tutorialvideobutton"
                            target="_blank"
                            href="${exercise.TutorialVideo}">
                            Tutorial
                            </a>
                        </div>
                        </td>`;
            
                    tableBody.appendChild(row);    
                });
            });    
   
});
});
