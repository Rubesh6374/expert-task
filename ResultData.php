<?php

require 'db.php';
class ResultData
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->conn; // Assign to class property
    }
    public function getTableDataList($tablename)
    {
        $stmt = $this->conn->prepare("SELECT * FROM `$tablename` ORDER BY id desc");
        if (!$stmt) {
            echo "Prepare failed: " . $this->conn->error;
        }
        $stmt->execute();
        return   $result = $stmt->get_result();
    }

    public function editData($tablename, $id)
    {
        $id = base64_decode($id);
        $stmt = $this->conn->prepare("SELECT * FROM $tablename WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateData($table, $set, $values, $types)
    {
        $sql = "UPDATE $table SET " . implode(',', $set) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            echo "Prepare failed: " . $this->conn->error;
        }
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
    }

    public function insertDataNew($table, $set, $values, $types, $valuesnew, $institutes = '')
    {
        $columns = implode(',', $set);
        $placeholders = implode(',', $valuesnew);
        $stmt = $this->conn->prepare("INSERT INTO `$table`  ($columns) VALUES ($placeholders)");
        if (!$stmt) {
            echo "Prepare failed: " . $this->conn->error;
        }

        $posts = array_map([$this, 'cleanXSS'], $values);
        $stmt->bind_param($types, ...$posts);
        if ($stmt->execute() && $table != 'students') {
            return true;
        } else {
            echo "Error: " . $stmt->error;
        }

        $last_id = $this->conn->insert_id;
        if ($table == 'students') {
            $institute_name = $institutes['institute_name'];
            $batch = $institutes['batch'];
            $percentage = $institutes['percentage'];

            foreach ($institute_name as $key => $value) {
                $stmt = $this->conn->prepare("INSERT INTO education_history (user_id ,institute,batch,percentage) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $last_id, $value, $batch[$key], $percentage[$key]);
                $stmt->execute();
            }
        }
    }

    public function cleanXSS($data)
    {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

    public function getTableDataListWhere($tablename, $id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM `$tablename` WHERE class_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $result = $stmt->get_result();
    }

    public function joinQueries($table, $jointable, $id, $foreign_key)
    {
        $query = " SELECT `$table`.*, `$table`.id as row_id,`$jointable`.* FROM `$table`";
        $query .= " LEFT JOIN `$jointable` ON `$jointable`.`$id`=`$table`.`$foreign_key`";
        //   echo $query;exit;
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ["status" => "error", "message" => "Prepare failed: " . $this->conn->error];
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC); // ← convert to array set
        return $data;
    }
    public function getDoubleTableDataList($tableOne)
    {
        $query = " SELECT classes.id as id, class_name,teacher_name
        FROM `$tableOne`";
        if ($tableOne == 'classes') {
            $query .= " LEFT JOIN teachers_list ON classes.class_teacher_id=teachers_list.id";
        } else {
            $query .= " LEFT JOIN classes ON classes.class_teacher_id=teachers_list.id";
        }
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ["status" => "error", "message" => "Prepare failed: " . $this->conn->error];
        }
        $stmt->execute();
        return $result = $stmt->get_result();
    }


    public function deleteRecord($tablename, $id)
    {
        $stmt =  $this->conn->prepare("DELETE FROM ".$tablename." WHERE id= ?");
        $stmt->bind_param("i", base64_decode($id));
        if ($stmt->execute()) {
            return true;
        } else {
            return $stmt->error;
        }
    }


    public function getClassSubjects($id)
    {
        $stmt = $this->conn->prepare("SELECT cs.id, sl.subject_name FROM class_subjects cs
                LEFT JOIN subject_list sl ON sl.id = cs.subject_id WHERE class_id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt) {
            throw new Exception("SQL prepare failed: " . $this->conn->error);
        }
        $stmt->execute();
        return $result = $stmt->get_result();
    }

    public function getClassSubjectsreport($id)
    {
        $stmt = $this->conn->prepare("SELECT sl.id, sl.subject_name FROM class_subjects cs
                LEFT JOIN subject_list sl ON sl.id = cs.subject_id WHERE class_id= ?");
        $stmt->bind_param("i", $id);
        if (!$stmt) {
            throw new Exception("SQL prepare failed: " . $this->conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC); // ← convert to array set
        return $data;
    }



    public function getAssignedTeachersDetails()
    {
        $stmt = $this->conn->prepare(" SELECT tsc.id,subject_list.subject_name,classes.class_name,teachers_list.teacher_name
            FROM teacher_subject_class tsc
            LEFT JOIN subject_list ON subject_list.id = tsc.subject_id
            LEFT JOIN classes ON classes.id = tsc.class_id
            LEFT JOIN teachers_list ON teachers_list.id = tsc.teacher_id");
        if (!$stmt) {
            throw new Exception("SQL prepare failed: " . $this->conn->error);
        }
        $stmt->execute();
        return $result = $stmt->get_result();
    }


    public function getupdateData($id, $table)
    {
        $stmt = $this->conn->prepare("SELECT * FROM `$table` WHERE id= ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return   $result = $stmt->get_result();
    }



    public function generateReport($table, $insertData)
    {

        $student_ids = json_decode($insertData['student_ids']);
        $subject_ids = json_decode($insertData['subject_ids']);
        $marks = $insertData['marks'];

        foreach ($marks as $userKey => $mark_details) {
            foreach ($mark_details as $subjectKey => $mark) {
                $stmt = $this->conn->prepare("INSERT INTO `$table` (user_id,exam_id,class_id,marks,subject_id) VALUES (? , ?, ?, ?, ?)");
                if (!$stmt) {
                    echo "Prepare failed: " . $this->conn->error;
                }
                $stmt->bind_param("iiiii", $userKey, $insertData['exam_id'], $insertData['class_id'], $mark, $subjectKey);
                $stmt->execute();
            }
        }
    }

    public function editReport($user_id, $exam_id)
    {

        $stmt = $this->conn->prepare("SELECT * FROM reports WHERE user_id = ? AND exam_id= ?");
        $stmt->bind_param("ii", $user_id, $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC); // ← convert to array set
        return $data;
    }

    public function updateReport($insertData)
    {
        $subject_ids = json_decode($insertData['subject_ids']); // array of subject IDs
        $exam_id = $insertData['exam_id'];
        $user_id = $insertData['user_id'];
        $marks = $insertData['mark']; // associative array like ['1' => 50, '2' => 60]

        foreach ($subject_ids as $subject_id) {
            $sql = "UPDATE reports SET marks = ? WHERE exam_id = ? AND user_id = ? AND subject_id = ?";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                echo "Prepare failed: " . $this->conn->error;
                continue;
            }

            $stmt->bind_param("iiii", $marks[$subject_id], $exam_id, $user_id, $subject_id);
            $stmt->execute();
        }
    }


    public function getReports($class_id, $exam_id)
    {
        $stmt = $this->conn->prepare(" 
         SELECT re.id,re.marks,re.user_id,classes.class_name,subject_list.subject_name,students.first_name,students.last_name,exams.exam_name FROM reports re
            LEFT JOIN exams ON exams.id = re.exam_id
            LEFT JOIN classes ON classes.id = re.class_id
            LEFT JOIN students ON students.id = re.user_id
            LEFT JOIN subject_list ON subject_list.id = re.subject_id
             WHERE re.class_id= ? AND exam_id = ?");
        if (!$stmt) {
            throw new Exception("SQL prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("ii", $class_id, $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC); // ← convert to array set
        return $data;
    }

    public function getStudentsExamReport($user_id, $exam_id)
    {
        $stmt = $this->conn->prepare(" 
         SELECT re.id,re.marks,re.user_id,classes.class_name,subject_list.subject_name,students.first_name,students.last_name,exams.exam_name FROM reports re
            LEFT JOIN exams ON exams.id = re.exam_id
            LEFT JOIN classes ON classes.id = re.class_id
            LEFT JOIN students ON students.id = re.user_id
            LEFT JOIN subject_list ON subject_list.id = re.subject_id
             WHERE re.user_id= ? AND exam_id = ?");
        if (!$stmt) {
            throw new Exception("SQL prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("ii", $user_id, $exam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC); // ← convert to array set
        return $data;
    }
}
