<?php 

require 'ResultData.php';
$students=new ResultData();
$classresult=$students->generateReport('reports',$_POST);
 header('Location:report.php');
?>