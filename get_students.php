<?php
require 'ResultData.php';
$students=new ResultData();
$classId=$_POST['class_id'];
 $studentResult=$students->getTableDataListWhere('students',$classId);
             $data = [];
            while ($row = $studentResult->fetch_assoc()) {
                // print_R($row);
                $data[] = $row;
            }
 echo json_encode($data);
?>
 