<?php
include('./layouts/header.php');
include('./layouts/navbar.php');
require 'ResultData.php';
$students = new ResultData();  // $subjectlist=$students->getClassSubjects();
$classresult = $students->getTableDataList('classes');

$db = new Database();
$conn = $db->conn;
$table = 'subject_list';
$columns = getColumns($conn, $table);
$tableone = 'class_subjects';
$columnsclsu = getColumns($conn, $tableone);
// print_r($columnsclsu);exit;
$classresultmodel = $students->getTableDataList('classes');
$subjectlist = $students->getTableDataList('subject_list');
$result = [];
if (isset($_GET['class_id'])) {
    $result = $students->getClassSubjects($_GET['class_id']);

}
$id = $_GET['delete'] ?? 0; // Or pass this from somewhere
if ($id) {
    $deleterecord = $students->deleteRecord('class_subjects', $id);
    if ($deleterecord) {
        header('Location: classubjects.php');
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {

    $set = [];
    $values = [];
    $valuesnew = [];
    $types = '';

    foreach ($columnsclsu as $col) {
        if ($col['Field'] == 'id') {
            continue;
        }
        $set[] = $col['Field'];
        $values[] = $_POST[$col['Field']];
        $valuesnew[] = "? ";
        $types .= 's';
    }

    $insert = $students->insertDataNew('class_subjects', $set, $values, $types, $valuesnew);
    //   if($insert){
    header('Location: classubjects.php');
    //   }
}
?>

<body>

	<div class="container">
		<form action="" method="get">
			<div class="form-inline">
				<label for="sub_name">Select Class:</label>
				<select class="form-control" name="class_id">
					<?php while ($row = $classresult->fetch_assoc()) { ?>
					<option
						value="<?php echo $row['id'];?>">
						<?php echo $row['class_name'];?>
					</option>
					<?php } ?>
				</select>
				<input type="submit" name="submit" class="btn btn-primary" value="Get Subject List">
				<button type="button" class="btn btn-info btn-md" data-toggle="modal" data-target="#myModal">Assign
					Subjects To Class</button>
			</div>
		</form>

		<table class="table">
			<thead>
				<?php foreach ($columns as $col): ?>
				<th><?= ucfirst(str_replace('_', ' ', $col['Field'])) ?>
				</th>
				<?php endforeach; ?>
				<th>Action</th>
			</thead>
			<tbody>
				<?php
     if (!$_GET) {
         echo "<tr><td>Select The Class To Get The Subject List</td></tr>";
     } else {
         generateTable($columns, $result, 'class_subjects');
     }
?>


			</tbody>
		</table>
	</div>
	<div id="myModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Map subjects to Classes</h4>
				</div>
				<div class="modal-body">
					<form method="post">
						<div class="col-md-6">
							<label for="sub_name">Select Class:</label>
							<select class="form-control" name="class_id">
								<?php while ($row = $classresultmodel->fetch_assoc()) {

								    $selected = 'selected';
								    if ($row['id'] == $_GET['class_id']) {
								        $selected = 'selected';
								    }
								    ?>
								<option
									value="<?= $row['id'];?>"
									<?= $selected;?>><?php echo $row['class_name'];?>
								</option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label for="sub_name">Select Subject:</label>
							<select class="form-control" name="subject_id">
								<?php while ($row = $subjectlist->fetch_assoc()) { ?>
								<option
									value="<?php echo $row['id'];?>">
									<?php echo $row['subject_name'];?>
								</option>
								<?php } ?>
							</select>
						</div>
						<input type="submit" class="btn btn-primary" name="add" value="Assign">
					</form>
				</div>
			</div>
		</div>
	</div>
</body>

</html>