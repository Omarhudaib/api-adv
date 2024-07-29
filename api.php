<?php
header("Content-Type: application/json");

// Database connection
$call = mysqli_connect("localhost", "Omar", "Ai@ktv7L9_Cj4re7", "new_schol");

if (!$call) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

// Determine the request method
$request = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

switch ($request) {
    case 'GET':
        if (isset($path[1])) {
            switch ($path[0]) {
                case 'students':
                    getStudent($call, $path[1]);
                    break;
                case 'teachers':
                    getTeacher($call, $path[1]);
                    break;
                case 'subjects':
                    getSubject($call, $path[1]);
                    break;
                case 'exams':
                    getExam($call, $path[1]);
                    break;
            }
        } else {
            switch ($path[0]) {
                case 'students':
                    getAllStudents($call);
                    break;
                case 'teachers':
                    getAllTeachers($call);
                    break;
                case 'subjects':
                    getAllSubjects($call);
                    break;
                case 'exams':
                    getAllExams($call);
                    break;
            }
        }
        break;
    case 'POST':
        switch ($path[0]) {
            case 'students':
                createStudent($call);
                break;
            case 'teachers':
                createTeacher($call);
                break;
            case 'subjects':
                createSubject($call);
                break;
            case 'exams':
                createExam($call);
                break;
        }
        break;
    case 'PUT':
        if (isset($path[1])) {
            switch ($path[0]) {
                case 'students':
                    updateStudent($call, $path[1]);
                    break;
                case 'teachers':
                    updateTeacher($call, $path[1]);
                    break;
                case 'subjects':
                    updateSubject($call, $path[1]);
                    break;
                case 'exams':
                    updateExam($call, $path[1]);
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing ID"]);
        }
        break;
    case 'DELETE':
        if (isset($path[1])) {
            switch ($path[0]) {
                case 'students':
                    deleteStudent($call, $path[1]);
                    break;
                case 'teachers':
                    deleteTeacher($call, $path[1]);
                    break;
                case 'subjects':
                    deleteSubject($call, $path[1]);
                    break;
                case 'exams':
                    deleteExam($call, $path[1]);
                    break;
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Bad Request: Missing ID"]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

// Close the database connection
mysqli_close($call);

// CRUD Functions for Students

function createStudent($call) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['Name']) || empty($indata['Class']) || empty($indata['BirthDate']) || empty($indata['Address']) || empty($indata['ContactInfo'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("INSERT INTO students (Name, Class, BirthDate, Address, ContactInfo) VALUES (?, ?, ?, ?, ?)");
    $con->bind_param("sssss", $indata['Name'], $indata['Class'], $indata['BirthDate'], $indata['Address'], $indata['ContactInfo']);

    if ($con->execute()) {
        http_response_code(201);
        $indata['StudentID'] = $con->insert_id;
        echo json_encode($indata);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Student creation failed"]);
    }
    $con->close();
}

function getAllStudents($call) {
    $result = mysqli_query($call, "SELECT * FROM students");
    if (!$result) {
        http_response_code(500);
        echo json_encode(["message" => "Query failed"]);
        return;
    }

    $students = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($students);
}

function getStudent($call, $studentId) {
    $con = $call->prepare("SELECT * FROM students WHERE StudentID = ?");
    $con->bind_param("i", $studentId);
    $con->execute();
    $result = $con->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Student not found"]);
    } else {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    }
    $con->close();
}

function updateStudent($call, $studentId) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['Name']) || empty($indata['Class']) || empty($indata['BirthDate']) || empty($indata['Address']) || empty($indata['ContactInfo'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("UPDATE students SET Name = ?, Class = ?, BirthDate = ?, Address = ?, ContactInfo = ? WHERE StudentID = ?");
    $con->bind_param("sssssi", $indata['Name'], $indata['Class'], $indata['BirthDate'], $indata['Address'], $indata['ContactInfo'], $studentId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Student updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Student update failed"]);
    }
    $con->close();
}

function deleteStudent($call, $studentId) {
    $con = $call->prepare("DELETE FROM students WHERE StudentID = ?");
    $con->bind_param("i", $studentId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Student deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Student deletion failed"]);
    }
    $con->close();
}

// CRUD Functions for Teachers

function createTeacher($call) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['Name']) || empty($indata['ContactInfo'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("INSERT INTO teacher (Name, ContactInfo, subject_id) VALUES (?, ?, ?)");
    $con->bind_param("ssi", $indata['Name'], $indata['ContactInfo'], $indata['SubjectID']);

    if ($con->execute()) {
        http_response_code(201);
        $indata['TeacherID'] = $con->insert_id;
        echo json_encode($indata);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Teacher creation failed"]);
    }
    $con->close();
}

function getAllTeachers($call) {
    $result = mysqli_query($call, "SELECT * FROM teacher");
    if (!$result) {
        http_response_code(500);
        echo json_encode(["message" => "Query failed"]);
        return;
    }

    $teachers = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($teachers);
}

function getTeacher($call, $teacherId) {
    $con = $call->prepare("SELECT * FROM teacher WHERE TeacherID = ?");
    $con->bind_param("i", $teacherId);
    $con->execute();
    $result = $con->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Teacher not found"]);
    } else {
        $teacher = $result->fetch_assoc();
        echo json_encode($teacher);
    }
    $con->close();
}

function updateTeacher($call, $teacherId) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['Name']) || empty($indata['ContactInfo']) || empty($indata['SubjectID'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("UPDATE teacher SET Name = ?, ContactInfo = ?, subject_id = ? WHERE TeacherID = ?");
    $con->bind_param("ssii", $indata['Name'], $indata['ContactInfo'], $indata['SubjectID'], $teacherId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Teacher updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Teacher update failed"]);
    }
    $con->close();
}

function deleteTeacher($call, $teacherId) {
    $con = $call->prepare("DELETE FROM teacher WHERE TeacherID = ?");
    $con->bind_param("i", $teacherId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Teacher deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Teacher deletion failed"]);
    }
    $con->close();
}

// CRUD Functions for Subjects

function createSubject($call) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['Name']) || empty($indata['Description'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("INSERT INTO subject (Name, Description) VALUES (?, ?)");
    $con->bind_param("ss", $indata['Name'], $indata['Description']);

    if ($con->execute()) {
        http_response_code(201);
        $indata['SubjectID'] = $con->insert_id;
        echo json_encode($indata);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Subject creation failed"]);
    }
    $con->close();
}

function getAllSubjects($call) {
    $result = mysqli_query($call, "SELECT * FROM subject");
    if (!$result) {
        http_response_code(500);
        echo json_encode(["message" => "Query failed"]);
        return;
    }

    $subjects = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($subjects);
}

function getSubject($call, $subjectId) {
    $con = $call->prepare("SELECT * FROM subject WHERE SubjectID = ?");
    $con->bind_param("i", $subjectId);
    $con->execute();
    $result = $con->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Subject not found"]);
    } else {
        $subject = $result->fetch_assoc();
        echo json_encode($subject);
    }
    $con->close();
}

function updateSubject($call, $subjectId) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['Name']) || empty($indata['Description'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("UPDATE subject SET Name = ?, Description = ? WHERE SubjectID = ?");
    $con->bind_param("ssi", $indata['Name'], $indata['Description'], $subjectId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Subject updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Subject update failed"]);
    }
    $con->close();
}

function deleteSubject($call, $subjectId) {
    $con = $call->prepare("DELETE FROM subject WHERE SubjectID = ?");
    $con->bind_param("i", $subjectId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Subject deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Subject deletion failed"]);
    }
    $con->close();
}

// CRUD Functions for Exams

function createExam($call) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['SubjectID']) || empty($indata['Date']) || empty($indata['MaxScore'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("INSERT INTO exam (SubjectID, Date, MaxScore) VALUES (?, ?, ?)");
    $con->bind_param("isi", $indata['SubjectID'], $indata['Date'], $indata['MaxScore']);

    if ($con->execute()) {
        http_response_code(201);
        $indata['ExamID'] = $con->insert_id;
        echo json_encode($indata);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Exam creation failed"]);
    }
    $con->close();
}

function getAllExams($call) {
    $result = mysqli_query($call, "SELECT * FROM exam");
    if (!$result) {
        http_response_code(500);
        echo json_encode(["message" => "Query failed"]);
        return;
    }

    $exams = mysqli_fetch_all($result, MYSQLI_ASSOC);
    echo json_encode($exams);
}

function getExam($call, $examId) {
    $con = $call->prepare("SELECT * FROM exam WHERE ExamID = ?");
    $con->bind_param("i", $examId);
    $con->execute();
    $result = $con->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Exam not found"]);
    } else {
        $exam = $result->fetch_assoc();
        echo json_encode($exam);
    }
    $con->close();
}

function updateExam($call, $examId) {
    $indata = json_decode(file_get_contents("php://input"), true);

    if (empty($indata['SubjectID']) || empty($indata['Date']) || empty($indata['MaxScore'])) {
        http_response_code(400);
        echo json_encode(["message" => "Bad Request: Missing required fields"]);
        return;
    }

    $con = $call->prepare("UPDATE exam SET SubjectID = ?, Date = ?, MaxScore = ? WHERE ExamID = ?");
    $con->bind_param("isii", $indata['SubjectID'], $indata['Date'], $indata['MaxScore'], $examId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Exam updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Exam update failed"]);
    }
    $con->close();
}

function deleteExam($call, $examId) {
    $con = $call->prepare("DELETE FROM exam WHERE ExamID = ?");
    $con->bind_param("i", $examId);

    if ($con->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Exam deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Exam deletion failed"]);
    }
    $con->close();
}
?>
