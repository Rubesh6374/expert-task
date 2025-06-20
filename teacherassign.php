<?php
include('./layouts/header.php');
include('./layouts/navbar.php');
require 'ResultData.php';
$students = new ResultData();
$classresult = $students->getTableDataList('classes');
$subjectresult = $students->getTableDataList('subject_list');
$teacherresult = $students->getTableDataList('teachers_list');
$assigneddetails = $students->getAssignedTeachersDetails();
$columns = [['Field' => 'id'],['Field' => 'subject_name'],['Field' => 'class_name'],['Field' => 'teacher_name']];
$db = new Database();
$conn = $db->conn;
$table = 'teacher_subject_class';
$columnsnew = getColumns($conn, $table);


if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $editData = $students->editData('teacher_subject_class', $id);
}

$id = $_GET['delete'] ?? 0;
if ($id) {
    $deleterecord = $students->deleteRecord($table, $id);
    if ($deleterecord) {
        header('Location:teacherassign.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {

    $set = [];
    $values = [];
    $valuesnew = [];
    $types = '';

    foreach ($columnsnew as $col) {
        if ($col['Field'] == 'id') {
            continue;
        }
        $set[] = $col['Field'];
        $values[] = $_POST[$col['Field']];
        $valuesnew[] = "? ";
        $types .= 's';
    }

    $insert = $students->insertDataNew('teacher_subject_class', $set, $values, $types, $valuesnew);
    header('Location: teacherassign.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $set = [];
    $values = [];
    $types = '';

    foreach ($columnsnew as $col) {
        if ($col['Field'] == 'id') {
            continue;
        }
        $set[] = "{$col['Field']} = ?";
        $values[] = $_POST[$col['Field']];
        $types .= 'i';
    }
    $values[] = $_POST['id'];
    $types .= 'i';

    $update = $students->updateData('teacher_subject_class', $set, $values, $types);
    header("Location: teacherassign.php");

}
?>


<body>

	<div class="container">
		<form method="post">
			<div class="row">


				<div class="form-inline">
					<label for="sub_name">Select Class:</label>
					<select class="form-control" name="class_id" id="getClassStudents">
						<?php while ($row = $classresult->fetch_assoc()) {
						    $selected = "";
						    if ($row['id'] == $editData['class_id']) {
						        $selected = "selected";
						    }
						    ?>
						<option
							value="<?php echo $row['id'];?>"
							<?=$selected;?>><?php echo $row['class_name'];?>
						</option>
						<?php } ?>
					</select>
					<label for="sub_name">Select Subject:</label>
					<select class="form-control" name="subject_id" id="getClassStudents">
						<?php while ($row = $subjectresult->fetch_assoc()) {
						    $selected = "";
						    if ($row['id'] == $editData['subject_id']) {
						        $selected = "selected";
						    }
						    ?>
						<option
							value="<?php echo $row['id'];?>"
							<?=$selected;?>><?php echo $row['subject_name'];?>
						</option>
						<?php } ?>
					</select>
					<label for="sub_name">Select Teacher:</label>
					<select class="form-control" name="teacher_id" id="getClassStudents">
						<?php while ($row = $teacherresult->fetch_assoc()) {
						    $selected = "";
						    if ($row['id'] == $editData['teacher_id']) {
						        $selected = "selected";
						    }
						    ?>
						<option
							value="<?php echo $row['id'];?>"
							<?=$selected;?>><?php echo $row['teacher_name'];?>
						</option>
						<?php } ?>
					</select>
					<?php if (isset($editData)): ?>
					<input type="hidden" name="id"
						value="<?= $editData['id'] ?>">
					<input type="submit" name="update" class="btn btn-primary" value="Update">
					<?php else: ?>
					<input type="submit" name="add" class="btn btn-primary" value="Assign">
					<?php endif; ?>
				</div>

			</div>
		</form>
		<br>
		<table class="table" id="reportData">
			<thead>
				<?php foreach ($columns as $col): ?>
				<th><?= ucfirst(str_replace('_', ' ', $col['Field'])) ?>
				</th>
				<?php endforeach; ?>
				<th>Action</th>
			</thead>
			<tbody>
				<?php generateTable($columns, $assigneddetails); ?>
			</tbody>
		</table>
	</div>
	<?php include('./layouts/footer.php');?>