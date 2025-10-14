<?php
header('Content-Type: application/json');

function readClassData($className): ?array {
    $filePath = "./../classes/{$className}.json";
    if (!file_exists($filePath)) {
        return null;
    }
    $content = file_get_contents($filePath);
    return json_decode($content, true);
}

function writeClassData($className, $data): bool {
    $filePath = "./../classes/{$className}.json";
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $jsonContent);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        switch ($_GET['action'] ?? '') {
            case 'get_lessons':
                get_lessons();
                break;
            default:
                echo json_encode(['success' => false, 'code' => 400, 'error' => 'Invalid action']);
                exit;
        }
        break;
    case 'POST':
        switch ($_POST['action'] ?? '') {
            case 'add_lesson':
                add_lesson();
                break;
            case 'delete_lesson':
                delete_lesson();
                break;
            case 'create_class':
                create_class();
                break;
            case 'delete_class':
                delete_class();
                break;
            default:
                echo json_encode(['success' => false, 'code' => 400, 'error' => 'Invalid action']);
                exit;
        }
        break;
    default:
        echo json_encode(['success' => false, 'code' => 405, 'error' => 'Invalid request method']);
        exit;
}

function get_lessons() {
    $className = $_GET['class'] ?? '';
    $day = intval($_GET['day'] ?? -1);
    $slot = intval($_GET['slot'] ?? -1);

    if (empty($className) || $day < 0 || $slot < 0) {
        echo json_encode(['success' => false, 'code' => 400, 'error' => 'Invalid parameters']);
        exit;
    }

    $classData = readClassData($className);
    if ($classData === null) {
        echo json_encode(['success' => false, 'code' => 404, 'error' => 'Class not found']);
        exit;
    }

    $lessons = [];
    if (isset($classData[$day]) && is_array($classData[$day])) {
        foreach ($classData[$day] as $lesson) {
            if (intval($lesson['nth']) === $slot) {
                $lessons[] = [
                    'subject' => $lesson['subject'] ?? 'Unknown',
                    'teacher' => $lesson['teacher'] ?? 'Unknown',
                    'room' => $lesson['room'] ?? 'Unknown'
                ];
            }
        }
    }

    echo json_encode(['success' => true, 'code' => 200, 'lessons' => $lessons]);
}

function delete_lesson() {
    $className = $_POST['class'] ?? '';
    $day = intval($_POST['day'] ?? -1);
    $slot = intval($_POST['slot'] ?? -1);
    $index = intval($_POST['index'] ?? -1);

    if (empty($className) || $day < 0 || $slot < 0 || $index < 0) {
        echo json_encode(['success' => false, 'code' => 400, 'error' => 'Invalid parameters']);
        exit;
    }

    $classData = readClassData($className);
    if ($classData == null) {
        echo json_encode(['success' => false, 'code' => 404, 'error' => 'Class not found']);
        exit;
    }
    
    if (!isset($classData[$day]) || !is_array($classData[$day])) {
        echo json_encode(['success' => false, 'code' => 404, 'error' => 'No lessons found for this day']);
        exit;
    }
    
    $lessonsInSlot = [];
    $otherLessons = [];

    // <AI>
    // Split all lessons
    foreach ($classData[$day] as $lesson) {
        if (intval($lesson['nth']) === $slot) {
            $lessonsInSlot[] = $lesson;
        } else {
            $otherLessons[] = $lesson;
        }
    }

    // Remove the lesson at the specified index
    if (isset($lessonsInSlot[$index])) {

        array_splice($lessonsInSlot, $index, 1); // Slice element out

        // Merge back all lessons
        $classData[$day] = array_merge($otherLessons, $lessonsInSlot);
    // </AI>
        if (writeClassData($className, $classData)) {
            echo json_encode(['success' => true, 'code' => 200]);
        } else {
            echo json_encode(['success' => false, 'code' => 500, 'error' => 'Failed to save data']);
        }
    } else {
        echo json_encode(['success' => false, 'code' => 404, 'error' => 'Lesson not found']);
    }
}

function add_lesson() {
    $className = $_POST['class'] ?? '';
    $day = intval($_POST['day'] ?? -1);
    $slot = intval($_POST['slot'] ?? -1);
    $subject = $_POST['subject'] ?? '';
    $teacher = $_POST['teacher'] ?? '';
    $room = $_POST['room'] ?? '';

    if (empty($className) || $day < 0 || $slot < 0 || empty($subject) || empty($teacher) || empty($room)) {
        echo json_encode(['success' => false, 'code' => 400, 'error' => 'Invalid parameters']);
        exit;
    }

    $classData = readClassData($className);
    if ($classData === null) {
        echo json_encode(['success' => false, 'code' => 404, 'error' => 'Class not found']);
        exit;
    }

    if (!isset($classData[$day])) {
        $classData[$day] = [];
    }

    // Add the new lesson
    $classData[$day][] = [
        'nth' => intval($slot),
        'subject' => $subject,
        'teacher' => $teacher,
        'room' => $room
    ];

    if (writeClassData($className, $classData)) {
        echo json_encode(['success' => true, 'code' => 200]);
    } else {
        echo json_encode(['success' => false, 'code' => 500, 'error' => 'Failed to save data']);
    }
}

function create_class(){
    $className = $_POST['class'] ?? '';
    $filePath = "./../classes/{$className}.json";
    if (file_exists($filePath)) {
        echo json_encode(['success' => false, 'code' => 409, 'error' => 'Class already exists']);
        exit;
    }
    $initialData = [];
    if (writeClassData($className, $initialData)) {
        echo json_encode(['success' => true, 'code' => 201]);
    } else {
        echo json_encode(['success' => false, 'code' => 500, 'error' => 'Failed to create class']);
    }
}

function delete_class() {
    $className = $_POST['class'] ?? '';
    $filePath = "./../classes/{$className}.json";
    if (!file_exists($filePath)) {
        echo json_encode(["success"=> false, "code"=> 404, "error"=> "Class not found"]);
        exit;
    }
    if (unlink($filePath)) {
        echo json_encode(['success' => true, 'code' => 200]);
    } else {
        echo json_encode(['success' => false, 'code' => 500, 'error' => 'Failed to delete class']);
    }
}
