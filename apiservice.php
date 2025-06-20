<?php

header("Content-Type: application/json");
require 'db.php';

$db = new Database();
$conn = $db->conn;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':

        $input = json_decode(file_get_contents('php://input'), true);
        //  print_r($input);exit;
        if (isset($input['id'])) {
            $id = intval($input['id']);
            $stmt = $conn->prepare("
SELECT students.id AS student_id,students.first_name,students.last_name,reports.id as report_id,reports.exam_name,reports.marks,
  education_history.id AS edu_id,education_history.institute,education_history.batch,education_history.percentage
FROM students LEFT JOIN reports ON reports.user_id = students.id LEFT JOIN education_history ON education_history.user_id = students.id
WHERE students.id = ?");
            if (!$stmt) {
                throw new Exception("SQL prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = null;
            $result = structureData($result);
            responceJSON(["message" => "Student Information","data" => $result]);
        } else {
            $stmt = $conn->prepare("
SELECT students.id AS student_id,students.first_name,students.last_name,reports.id as report_id,reports.exam_name,reports.marks,
  education_history.id AS edu_id,education_history.institute,education_history.batch,education_history.percentage
FROM students LEFT JOIN reports ON reports.user_id = students.id LEFT JOIN education_history ON education_history.user_id = students.id
");
            if (!$stmt) {
                throw new Exception("SQL prepare failed: " . $conn->error);
            }
            $stmt->execute();
            $data = [];
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $resultdata = structureData($result);
            responceJSON(["message" => "Student List","data" => $data]);

        }
        break;

    case 'POST':

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $first_name = $conn->real_escape_string(strip_tags($input['first_name']));
            validateInputs(["type" => "text","text" => $first_name]);

            $last_name = $conn->real_escape_string(strip_tags($input['last_name']));
            validateInputs(["type" => "text","text" => $last_name]);
            $email =  $conn->real_escape_string(strip_tags($input['email']));
            validateInputs(["type" => "email","email" => $email]);
            $dob =  $conn->real_escape_string(strip_tags($input['dob']));
            validateInputs(["type" => "dob","dob" => $dob]);
            $phone =  $conn->real_escape_string(strip_tags($input['phone']));
            validateInputs(["type" => "phone","phone" => $phone]);
            $address = $conn->real_escape_string(strip_tags($input['address']));
            validateInputs(["type" => "textarea","textarea" => $phone]);
            $stmt = $conn->prepare("INSERT INTO students (first_name,last_name,email,dob,phone,addressinfo) VALUES (?, ?, ? ,?, ?, ? )");
            if (!$stmt) {
                responceJSON(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
            }
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $dob, $phone, $address);
            if ($stmt->execute()) {
                $last_id = $conn->insert_id;
                // Fetch full record
                $result = $conn->query("SELECT * FROM students WHERE id = $last_id");
                $newStudent = $result->fetch_assoc();
                responceJSON(["status" => "success", "message" => "Student created", "data" => $newStudent]);
            } else {
                responceJSON(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
            }
        } catch (Exception $e) {
            responceJSON([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id']);
        $first_name = $conn->real_escape_string($input['first_name']);
        $last_name = $conn->real_escape_string($input['last_name']);
        $email =  $conn->real_escape_string($input['email']);
        validateInputs(["type" => "email","email" => $email]);
        $dob =  $conn->real_escape_string($input['dob']);
        validateInputs(["type" => "dob","dob" => $dob]);
        $phone =  $conn->real_escape_string($input['phone']);
        validateInputs(["type" => "phone","phone" => $phone]);
        $address = $conn->real_escape_string($input['address']);
        $stmt =  $conn->prepare("UPDATE students SET first_name= ?, last_name= ?,email= ?,dob= ?,phone= ? ,addressinfo= ? WHERE id= ?");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $dob, $phone, $address, $id);
        $stmt->execute();
        responceJSON(["message" => "Student Updated Successfully","data" => $input]);
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id']);
        $stmt =  $conn->prepare("DELETE FROM students WHERE id= ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            responceJSON(["message" => "Student Deleted Successfully","data" => []]);
        } else {
            responceJSON(["message" => "Error Occured","data" => []]);
        }

        break;

    default:
        http_response_code(405);
        responceJSON(["message" => "Method not allowed","data" => []]);
        break;
}


function validateInputs($inputs)
{
    if ($inputs['type'] == 'email') {
        if (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
    } elseif ($inputs['type'] == 'dob') {
        $dob = $inputs['dob'];
        $dob_date = DateTime::createFromFormat('Y-m-d', $dob);

        if (!$dob_date || $dob_date->format('Y-m-d') !== $dob) {
            throw new Exception("Invalid date format. Use YYYY-MM-DD");
        }
    } elseif ($inputs['type'] == 'phone') {
        if (!preg_match('/^\+?[0-9]{7,15}$/', $inputs['phone'])) {
            throw new Exception("Invalid phone number format");
        }
    } elseif ($inputs['type'] == 'text') {
        if (!preg_match('/^[a-zA-Z ]{3,50}$/', $inputs['text'])) {
            throw new Exception("Name must be alphabetic and between 3-50 characters.");
        }
    } elseif ($inputs['type'] == 'textarea') {
        if (!preg_match('/^[a-zA-Z ]{3,50}$/', $inputs['textarea'])) {
            throw new Exception("Name must be alphabetic and between 3-50 characters.");
        }
    }

}
function responceJSON($response)
{
    $result = [
        "message" => $response['message'],
        "data" => isset($response['data']) ? $response['data'] : ''
    ];
    echo  json_encode($result);
}

function structureData($data)
{
    $student = null;
    while ($row = $data->fetch_assoc()) {
        if (!$student) {
            $student = [
                'id' => $row['student_id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'reports' => [],
                'education_history' => []
            ];
        }


        if ($row['report_id']) {

            $existingReportIds = array_column($student['reports'], 'report_id');
            if (!in_array($row['report_id'], $existingReportIds)) {
                $student['reports'][] = [
                    'report_id' => $row['report_id'],
                    'exam_name' => $row['exam_name'],
                    'mark' => $row['marks']
                ];
            }
        }
        if ($row['edu_id']) {

            $existingEduIds = array_column($student['education_history'], 'id');
            if (!in_array($row['edu_id'], $existingEduIds)) {
                $student['education_history'][] = [
                    'id' => $row['edu_id'],
                    'user_id' => $row['student_id'],
                    'institute' => $row['institute'],
                    'batch' => $row['batch'],
                    'percentage' => $row['percentage']
                ];
            }
        }
    }
    return $student;
}
