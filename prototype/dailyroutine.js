const checkboxes = document.querySelectorAll("input[type='checkbox']");
const todayKey = "exerciseProgress_" + new Date().toDateString();

async function saveWorkoutProgress(count) {
  try {
    const response = await fetch('save_workout_progress.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        workouts_done: count,
        entry_date: new Date().toISOString().slice(0, 10)
      })
    });

    const result = await response.json();
    if (!result.success) {
      console.warn('Could not save workout progress:', result.error || 'Unknown error');
    }
  } catch (error) {
    console.warn('Error saving workout progress:', error);
  }
}

function getCompletedCount() {
  return Array.from(checkboxes).filter(box => box.checked).length;
}

// LOAD SAVED DATA
window.addEventListener('load', () => {
  const saved = JSON.parse(localStorage.getItem(todayKey)) || [];

  checkboxes.forEach((box, index) => {
    if (saved[index]) {
      box.checked = true;
      markComplete(index, true);
    }
  });

  const completedCount = getCompletedCount();
  if (completedCount > 0) {
    saveWorkoutProgress(completedCount);
  }
});

// SAVE ON CHANGE
checkboxes.forEach(box => {
  box.addEventListener('change', () => {
    const index = Number(box.dataset.index);
    const saved = JSON.parse(localStorage.getItem(todayKey)) || [];

    saved[index] = box.checked;
    localStorage.setItem(todayKey, JSON.stringify(saved));

    markComplete(index, box.checked);
    saveWorkoutProgress(getCompletedCount());
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

  saveWorkoutProgress(0);
}
