<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rozvrh</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
</head>

<body>
    <img src="./logo.png" alt="Trakaláři logo">
    <header>

        <form method="GET" action="" id="classForm">
            <select id="class-select" name="class" onchange="document.getElementById('classForm').submit()">
                <option value="">Select a class</option>
                <?php
                $classesDir = './classes';
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
        <button onclick="location.href='./admin/'">Admin</button>
    </header>

    <?php
    // Display selected class info
    if (isset($_GET['class']) && !empty($_GET['class'])) {
        $selectedClass = $_GET['class'];
        $filePath = "./classes/{$selectedClass}.json";

        if (file_exists($filePath)) {
            require_once './Lesson.php';
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

            <div id="timetable">
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
                        <div class="lessons-cell">
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