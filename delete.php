<?php
require 'db.php';

$db = new Database();
$conn = $db->conn;
        $id = intval($_GET['id']);
        $id=base64_decode($id);
        $stmtreport =  $conn->prepare("DELETE FROM reports WHERE user_id= ?");
        $stmtreport->bind_param("i",$id);
        $stmt =  $conn->prepare("DELETE FROM students WHERE id= ?");
        $stmt->bind_param("i",$id);
       
         if ($stmt->execute()) {
         header("Location: index.php");
        } else {
        echo "Error: " . $stmt->error;
    }