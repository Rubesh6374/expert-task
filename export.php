<?php
require 'vendor/autoload.php';

require 'db.php';

$db = new Database();
$conn = $db->conn;
 $stmt = $conn->prepare("SELECT * FROM students");
            $data = [];
             $stmt->execute();
            $result = $stmt->get_result(); 
           
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Firstname');
$sheet->setCellValue('C1', 'Lastname');
$sheet->setCellValue('D1', 'Email');
$sheet->setCellValue('E1', 'Dob');
$sheet->setCellValue('F1', 'Phone');
$sheet->setCellValue('G1', 'Address');
$row = 2;
   while ($rows = $result->fetch_assoc()) {
    $sheet->setCellValue("A$row", $rows['id']);
    $sheet->setCellValue("B$row", $rows['first_name']);
    $sheet->setCellValue("C$row", $rows['last_name']);
    $sheet->setCellValue("D$row", $rows['email']);
    $sheet->setCellValue("E$row", $rows['dob']);
    $sheet->setCellValue("F$row", $rows['phone']);
    $sheet->setCellValue("G$row", $rows['addressinfo']);
    $row++;
}
$writer = new Xlsx($spreadsheet);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Export.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
