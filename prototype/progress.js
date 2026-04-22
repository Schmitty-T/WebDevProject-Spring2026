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

// Data shown in the weekly chart
const weeklyData = [
  { label: "Mon", value: 3 },
  { label: "Tue", value: 5 },
  { label: "Wed", value: 2 },
  { label: "Thu", value: 4 },
  { label: "Fri", value: 6 },
  { label: "Sat", value: 5 },
  { label: "Sun", value: 1 },
];

// Data shown in the monthly chart
const monthlyData = [
  { label: "Week 1", value: 12 },
  { label: "Week 2", value: 15 },
  { label: "Week 3", value: 10 },
  { label: "Week 4", value: 18 },
];

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