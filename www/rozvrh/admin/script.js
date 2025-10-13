let currentRow = null;
let currentCol = null;
let currentClass = null;

function timetableClick(event) {
    const clickedCell = event.target.closest(".lessons-cell");
    if (!clickedCell) return;

    const row = clickedCell.dataset.row;
    const col = clickedCell.dataset.col;
    if (row === undefined || col === undefined) return;

    currentRow = row;
    currentCol = col;
    currentClass = document.getElementById("class-select").value;

    if (!currentClass) {
        alert("Please select a class first");
        return;
    }

    openOverlay(row, col);
}

function openOverlay(row, col) {
    const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
    const timeSlots = [
        "7:00 - 7:45",
        "8:00 - 8:45",
        "8:55 - 9:40",
        "9:50 - 10:35",
        "10:55 - 11:40",
        "11:50 - 12:35",
        "12:45 - 13:30",
        "13:40 - 14:25",
        "14:35 - 15:20",
        "15:30 - 16:15",
        "16:25 - 17:10",
    ];

    const dayName = days[row] || "Unknown";
    const timeSlot = timeSlots[col] || "Unknown";

    document.getElementById(
        "overlayTitle"
    ).textContent = `${dayName}: ${timeSlot}`;
    document.getElementById("lessonOverlay").style.display = "flex";

    loadLessons();
}

function closeOverlay() {
    document.getElementById("lessonOverlay").style.display = "none";
    document.getElementById("newSubject").value = "";
    document.getElementById("newTeacher").value = "";
    document.getElementById("newRoom").value = "";
}

async function loadLessons() {
    try {
        const data = await fetch(
            `api.php?action=get_lessons&class=${currentClass}&day=${currentRow}&slot=${currentCol}`
        ).then((res) => res.json());
        if (data.success) {
            displayLessons(data.lessons);
        } else {
            alert("Error loading lessons: " + (data.error || "Unknown error"));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to load lessons");
    }
}

function displayLessons(lessons) {
    const lessonsList = document.getElementById("lessons-list");

    if (lessons.length === 0) {
        lessonsList.innerHTML =
            '<p class="no-lessons">No lessons in this time slot</p>';
        return;
    }

    let html = '';
    lessons.forEach((lesson, index) => {
        html += `
                    <div class="lesson-item">
                        <div class="lesson-info">
                            <strong>${lesson.subject}</strong>
                            <span>${lesson.teacher}</span>
                            <span>Room: ${lesson.room}</span>
                        </div>
                        <button class="delete-btn" onclick="deleteLesson(${index})">Delete</button>
                    </div>
                `;
    });

    lessonsList.innerHTML = html;
}

async function addLesson() {
    const subject = document.getElementById("newSubject").value.trim();
    const teacher = document.getElementById("newTeacher").value.trim();
    const room = document.getElementById("newRoom").value.trim();

    if (!subject || !teacher || !room) {
        alert("Please fill in all fields");
        return;
    }

    try {
        const response = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `action=add_lesson&class=${currentClass}&day=${currentRow}&slot=${currentCol}&subject=${encodeURIComponent(
                subject
            )}&teacher=${encodeURIComponent(teacher)}&room=${encodeURIComponent(
                room
            )}`,
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById("newSubject").value = "";
            document.getElementById("newTeacher").value = "";
            document.getElementById("newRoom").value = "";
            loadLessons();

            // Reload the page to update the timetable
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert("Error adding lesson: " + (data.error || "Unknown error"));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to add lesson");
    }
}

async function deleteLesson(index) {
    if (!confirm("Are you sure you want to delete this lesson?")) {
        return;
    }

    try {
        const response = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `action=delete_lesson&class=${currentClass}&day=${currentRow}&slot=${currentCol}&index=${index}`,
        });

        const data = await response.json();

        if (data.success) {
            loadLessons();

            // Reload the page to update the timetable
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert("Error deleting lesson: " + (data.error || "Unknown error"));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to delete lesson");
    }
}

async function createNewClass(event) {
    if (event.key !== "Enter") return;

    event.preventDefault();
    const input = document.getElementById("newclass");
    const className = input.value.trim();

    if (!className) {
        alert("Please enter a class name");
        return;
    }

    // Redirect to create the new class
    //window.location.href = `?action=create_class&class_name=${encodeURIComponent(className)}`;
    try {
        const response = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `action=create_class&class=${encodeURIComponent(className)}`,
        });
        const data = await response.json();
        if (data.success) {
            const url = new URL(window.location.href);
            url.searchParams.set("class", className);
            window.location.href = url.toString();
        } else {
            alert("Error creating class: " + (data.error || "Unknown error"));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to create class");
    }
}

async function deleteClass() {
    const className = new URLSearchParams(window.location.search).get("class");

    if (!className) {
        alert("You need to select class first!");
        return;
    }

    if (!confirm("Are you sure you want to delete this class?")) {
        return;
    }

    try {
        const response = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `action=delete_class&class=${encodeURIComponent(className)}`,
        });
        const data = await response.json();
        if (data.success) {
            const url = new URL(window.location.href);
            url.searchParams.delete("class");
            window.location.href = url.toString();
        } else {
            alert("Error creating class: " + (data.error || "Unknown error"));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to create class");
    }
}
