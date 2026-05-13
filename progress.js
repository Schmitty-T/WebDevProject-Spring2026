// Dark mode button
const toggle = document.getElementById("themeToggle");

// Keep dark mode on if it was selected before
if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark");
}

// Turn dark mode on or off and save the user's choice
toggle.addEventListener("click", () => {
  document.body.classList.toggle("dark");

  if (document.body.classList.contains("dark")) {
    localStorage.setItem("theme", "dark");
  } else {
    localStorage.setItem("theme", "light");
  }
});

// Chart data is provided by PHP through window.progressChartData
// Convert it to the format our drawChart function expects
const weeklyData = window.progressChartData.weekly.labels.map(function (label, i) {
  return { label: label, value: window.progressChartData.weekly.data[i] };
});

const monthlyData = window.progressChartData.monthly.labels.map(function (label, i) {
  return { label: label, value: window.progressChartData.monthly.data[i] };
});

// Chart area and buttons
const chartContainer = document.getElementById("chart-container");
const weeklyBtn = document.getElementById("weekly-btn");
const monthlyBtn = document.getElementById("monthly-btn");

// This function creates the chart bars based on the data passed in
function drawChart(data) {
  // Find the biggest number so the bar heights scale correctly
  let maxValue = 0;
  for (let i = 0; i < data.length; i++) {
    if (data[i].value > maxValue) {
      maxValue = data[i].value;
    }
  }

  if (maxValue === 0) maxValue = 1; // avoid divide-by-zero when chart is empty

  // Remove the old chart before drawing a new one
  chartContainer.innerHTML = "";

  // Make one bar for each item in the data
  for (let i = 0; i < data.length; i++) {
    const bar = document.createElement("div");
    bar.className = "chart-bar";
    bar.setAttribute("data-value", data[i].value);

    // Add the label under each bar
    const label = document.createElement("span");
    label.textContent = data[i].label;
    bar.appendChild(label);

    chartContainer.appendChild(bar);

    // Slight delay so the bar animation shows smoothly
    const heightPercent = (data[i].value / maxValue) * 100;
    setTimeout(() => {
      bar.style.height = heightPercent + "%";
    }, 100);
  }
}

// Show the weekly chart first when the page opens
drawChart(weeklyData);

// Show weekly data when Weekly is clicked
weeklyBtn.addEventListener("click", () => {
  weeklyBtn.classList.add("active");
  monthlyBtn.classList.remove("active");
  drawChart(weeklyData);
});

// Show monthly data when Monthly is clicked
monthlyBtn.addEventListener("click", () => {
  monthlyBtn.classList.add("active");
  weeklyBtn.classList.remove("active");
  drawChart(monthlyData);
});

// Fill the exercise progress bars once the page has loaded
window.addEventListener("load", () => {
  const fills = document.querySelectorAll(".progress-fill");
  fills.forEach((fill) => {
    const percent = fill.getAttribute("data-progress");
    fill.style.width = percent + "%";
  });
});

// Custom confirmation modal (handles both delete and clear actions)
let pendingForm = null;

function showDeleteConfirm(form) {
  pendingForm = form;
  document.getElementById("modalTitle").textContent = "Delete this goal?";
  document.getElementById("modalMessage").textContent = "This cannot be undone.";
  document.getElementById("modalConfirm").textContent = "Delete";
  document.getElementById("confirmModal").style.display = "flex";
  return false;
}

function showClearConfirm(form) {
  pendingForm = form;
  document.getElementById("modalTitle").textContent = "Clear all progress?";
  document.getElementById("modalMessage").textContent = "This will erase all your logged entries. This cannot be undone.";
  document.getElementById("modalConfirm").textContent = "Clear All";
  document.getElementById("confirmModal").style.display = "flex";
  return false;
}

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("confirmModal");
  const cancelBtn = document.getElementById("modalCancel");
  const confirmBtn = document.getElementById("modalConfirm");

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      modal.style.display = "none";
      pendingForm = null;
    });
  }

  if (confirmBtn) {
    confirmBtn.addEventListener("click", () => {
      modal.style.display = "none";
      if (pendingForm) {
        pendingForm.submit();
      }
    });
  }

  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
        pendingForm = null;
      }
    });
  }
});

// Remove the "msg" parameter from the URL after showing the success banner
if (window.location.search.includes("msg=")) {
  const cleanUrl = window.location.pathname;
  window.history.replaceState({}, "", cleanUrl);
}