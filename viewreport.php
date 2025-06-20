<?php

require 'ResultData.php';
$students = new ResultData();

$report = $students->getStudentsExamReport($_POST['user_id'], $_POST['exam_id']);
// print_r($report);
foreach ($report as $key => $value) {
    $subjects[] = $value['subject_name'];
}
foreach ($report as $key => $value) {
    $details['exam_name'] = $value['exam_name'];
    $details['marks'][] = $value['marks'];
}
// print_r($subjects);
// print_r($details);
$html = '<table class="table">';
$html .= '<thead><th> Exam Name</th>';
foreach ($subjects as $key => $value) {
    $html .= "<th>".$value."</th>";
}
$html .= '</thead><tbody>';
$html .= '<tr><td>'.$details['exam_name'].'</td>';
foreach ($details['marks'] as $marks) {
    $html .= '<td>'.$marks.'</td>';
}
$html .= '</tr></tbody></table>';
echo $html;
