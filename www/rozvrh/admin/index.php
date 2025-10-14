<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rozvrh</title>
    <link rel="stylesheet" href="./../style.css">
    <link rel="shortcut icon" href="./../favicon.ico" type="image/x-icon">
    <style>
        .lessons-cell {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <img src="./../logo.png" alt="Trakaláři logo">

    <div id="lessonOverlay" class="overlay" style="display: none;">
        <div class="overlay-content">
            <div class="overlay-header">
                <h2 id="overlayTitle">Manage Lessons</h2>
                <button class="close-btn" onclick="closeOverlay()">&times;</button>
            </div>
            <div class="overlay-body">
                <div id="lessons-list"></div>
                <div class="add-lesson-form">
                    <h3>Add New Lesson</h3>
                    <input type="text" id="newSubject" placeholder="Subject" required>
                    <input type="text" id="newTeacher" placeholder="Teacher" required>
                    <input type="text" id="newRoom" placeholder="Room" required>
                    <button onclick="addLesson()">Add Lesson</button>
                </div>
            </div>
        </div>
    </div>

    <header>

        <form method="GET" action="" id="classForm">
            <select id="class-select" name="class" onchange="document.getElementById('classForm').submit()">
                <option value="">Select a class</option>
                <?php
                $classesDir = './../classes';
                if (is_dir($classesDir)) {
                    $files = scandir($classesDir);
                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                            // <AI>
                            // Remove .json extension
                            $className = pathinfo($file, PATHINFO_FILENAME);
                            $displayName = str_replace('_', ' ', $className);
                            $displayName = ucwords($displayName);
                            // </AI>

                            $selected = (isset($_GET['class']) && $_GET['class'] === $className) ? 'selected' : '';
                            echo "<option value=\"{$className}\" {$selected}>{$displayName}</option>";
                        }
                    }
                }
                ?>
            </select>
        </form>
        <button onclick="deleteClass()">Delete Class</button>
        <input type="text" name="newclass" id="newclass" placeholder="New Class Name" onkeypress="createNewClass(event)">
        <button onclick="location.href='./../'">Back</button>
    </header>
    <script src="./script.js"></script>


    <?php
    // Display selected class info
    if (isset($_GET['class']) && !empty($_GET['class'])) {
        $selectedClass = $_GET['class'];
        $filePath = "./../classes/{$selectedClass}.json";

        if (file_exists($filePath)) {
            require_once './../Lesson.php';
            $classDataRaw = json_decode(file_get_contents($filePath), true);
            $classData = [];

            $displayName = str_replace('_', ' ', ucwords($selectedClass));

            // Time slots for the timetable
            $timeSlots = [
                '7:00 - 7:45',
                '8:00 - 8:45',
                '8:55 - 9:40',
                '9:50 - 10:35',
                '10:55 - 11:40',
                '11:50 - 12:35',
                '12:45 - 13:30',
                '13:40 - 14:25',
                '14:35 - 15:20',
                '15:30 - 16:15',
                '16:25 - 17:10'
            ];

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $timetableData = [];

            foreach ($classDataRaw as $dayIndex => $dayLessons) {
                if ($dayIndex < count($days)) {
                    foreach ($dayLessons as $lessonRaw) {
                        $slotIndex = intval($lessonRaw['nth']);
                        if ($slotIndex >= 0 && $slotIndex < count($timeSlots)) {
                            if (!isset($timetableData[$dayIndex])) $timetableData[$dayIndex] = [];
                            if (!isset($timetableData[$dayIndex][$slotIndex])) $timetableData[$dayIndex][$slotIndex] = [];
                            $timetableData[$dayIndex][$slotIndex][] = new Lesson(
                                $lessonRaw['subject'] ?? 'Unknown Subject',
                                $lessonRaw['teacher'] ?? 'Unknown Teacher',
                                $lessonRaw['room'] ?? 'Unknown'
                            );
                        }
                    }
                }
            }
    ?>

            <div id="timetable" onclick="timetableClick(event)">
                <div></div> <!-- Empty top-left cell -->
                <?php foreach ($timeSlots as $timeSlot): ?>
                    <p class="time-slot">
                        <?= htmlspecialchars($timeSlot) ?>
                    </p>
                <?php endforeach; ?>

                <?php foreach ($days as $dayIndex => $day): ?>
                    <p class="day-label">
                        <?= htmlspecialchars($day) ?>
                    </p>

                    <?php foreach ($timeSlots as $slotIndex => $timeSlot): ?>
                        <div data-row="<?= $dayIndex ?>" data-col="<?= $slotIndex ?>" class="lessons-cell">
                            <?php
                            $lessons = $timetableData[$dayIndex][$slotIndex] ?? [];
                            foreach ($lessons as $lesson) echo $lesson->getHtml();
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
    <?php
        } else {
            echo "<p style='color: red;'>Error: Class file not found!</p>";
        }
    }
    ?>
</body>

</html>