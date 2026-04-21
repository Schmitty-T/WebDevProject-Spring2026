const checkboxes = document.querySelectorAll("input[type='checkbox']");

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
    let saved = JSON.parse(localStorage.getItem("exerciseProgress")) || [];

    saved[index] = box.checked;
    localStorage.setItem("exerciseProgress", JSON.stringify(saved));

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
  localStorage.removeItem("exerciseProgress");

  checkboxes.forEach((box, index) => {
    box.checked = false;
    markComplete(index, false);
  });
}