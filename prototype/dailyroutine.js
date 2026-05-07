const checkboxes = document.querySelectorAll("input[type='checkbox']");
const todayKey = "exerciseProgress_" + new Date().toDateString();
// LOAD SAVED DATA
window.onload = function () {
  const saved = JSON.parse(localStorage.getItem("exerciseProgress")) || [];

  checkboxes.forEach((box, index) => {
    if (saved[index]) {
      box.checked = true;
      markComplete(index, true);
    }
  });
};

// SAVE ON CHANGE
checkboxes.forEach(box => {
  box.addEventListener("change", () => {
    const index = box.dataset.index;
    let saved = JSON.parse(localStorage.getItem(todayKey)) || [];

    saved[index] = box.checked;
    localStorage.setItem(todayKey, JSON.stringify(saved));

    markComplete(index, box.checked);
  });
});

// MARK ROW COMPLETE
function markComplete(index, completed) {
  const row = document.getElementById("row" + index);
  if (!row) return;

  if (completed) {
    row.classList.add("completed");
  } else {
    row.classList.remove("completed");
  }
}

// CLEAR ALL
function clearProgress() {
  localStorage.removeItem(todayKey);

  checkboxes.forEach((box, index) => {
    box.checked = false;
    markComplete(index, false);
  });
}