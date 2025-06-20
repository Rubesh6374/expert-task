<?php
include('./layouts/header.php');
include('./layouts/navbar.php');
require 'ResultData.php';
$students = new ResultData();
$classresult = $students->getTableDataList('classes');
$examresult = $students->getTableDataList('exams');
if (isset($_GET['class_id']) && isset($_GET['exam_id']) && !isset($_GET['edit'])) {
    $classId = $_GET['class_id'];
    $examId = $_GET['exam_id'];
    $reportlist = $students->getReports($classId, $examId);
    $subjectlist = $students->getClassSubjectsreport($classId);
    $subject_ids = [];
    // echo "<pre>";
    $grouped = [];

    foreach ($reportlist as $record) {
        $userId = $record['user_id'];
        $grouped[$userId]['name'] = $record['first_name']." ".$record['last_name'];
        $grouped[$userId]['marks'][] = $record['marks'];
    }
}

if (isset($_GET['edit'])) {
    $user_id = $_GET['edit'];
    $exam_id = $_GET['exam_id'];
    $classId = $_GET['class_id'];
    $editdata = $students->editReport($user_id, $exam_id);
    $subjectlist = $students->getClassSubjectsreport($classId);
    // echo "<pre>";
    // print_r($editdata);
    // print_r($subjectlist);
    $subjectids = array_column($subjectlist, 'id');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // echo "<pre>";
    // print_r($_POST);

    $class_id = $_POST['class_id'];
    $exam_id = $_POST['exam_id'];
    $updatedata = $students->updateReport($_POST);
    // if($updatedata){
    header("Location: getreportlist.php?class_id=".$class_id."&exam_id=".$exam_id);
    // }
}
?>

<body>

	<div class="container">
		<form action="" method="GET">
			<div class="row">

				<div class="col-md-3">
					<label for="sub_name">Select Class:</label>
					<select class="form-control" name="class_id">
						<?php while ($row = $classresult->fetch_assoc()) {
						    $selected = '';
						    if (isset($_GET['class_id']) && $_GET['class_id'] == $row['id']) {
						        $selected = 'selected';
						    }
						    ?>
						<option
							value="<?php echo $row['id'];?>"
							<?php echo $selected; ?>><?php echo $row['class_name'];?>
						</option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-3">
					<label for="sub_name">Select Exam:</label>
					<select class="form-control" name="exam_id">
						<?php while ($row = $examresult->fetch_assoc()) {
						    $selected = '';
						    if (isset($_GET['exam_id']) && $_GET['exam_id'] == $row['id']) {
						        $selected = 'selected';
						    }
						    ?>
						<option
							value="<?php echo $row['id'];?>"
							<?php echo $selected;?>><?php echo $row['exam_name'];?>
						</option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-3">
					<input type="submit" name="get_report" value="Get Report" class="btn btn-primary">
				</div>
			</div>
		</form>
		<?php  if (isset($_GET['class_id']) && isset($_GET['exam_id']) && !isset($_GET['edit'])) { ?>

		<div class="col-md-6">
			<h3>Student Lists</h3>
			<table class="table">
				<thead>
					<th>ID</th>
					<th> Student</th>
					<?php

						            foreach ($subjectlist as $subject) {
						                ?>
					<th> <?= $subject['subject_name'];?>
					</th>
					<?php } ?>
					<th>Action</th>
				</thead>
				<tbody>
					<?php foreach ($grouped as $key => $value) { ?>
					<tr>
						<td><?= $key + 1 ; ?></td>
						<td><?= $value['name'];?>
						</td>
						<?php foreach ($value['marks'] as $marks) { ?>
						<td><?= $marks;?></td>
						<?php   } ?>
						<td><a class="btn btn-success"
								href="?edit=<?= $key;?>&&exam_id=<?= $_GET['exam_id'];?>&&class_id=<?= $_GET['class_id'];?>">Edit</a>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

		</div>


		<?php } ?>
		<?php if (isset($_GET['edit'])) { ?>
		<h3>Update Report</h3>

		<form method="post">
			<div class="row">
				<?php foreach ($subjectlist as $key => $value) { ?>

				<div class="col-md-3">
					<label><?= ucfirst($value['subject_name']) ?>:</label>
					<input type="text"
						name="mark[<?= $value['id'] ?>]"
						value="<?= $editdata[$key]['marks'];?>"
						class="form-control">
				</div>
				<?php } ?>
				<?php if (isset($editdata)): ?>
				<input type="hidden" name="subject_ids"
					value="<?= json_encode($subjectids); ?>">
				<input type="hidden" name="exam_id"
					value="<?= $exam_id; ?>">
				<input type="hidden" name="class_id"
					value="<?= $classId; ?>">
				<input type="hidden" name="user_id"
					value="<?= $user_id; ?>">
				<input type="submit" name="update" class="btn btn-primary" value="Update Report">
				<?php endif; ?>
		</form>
		<?php } ?>
	</div>

</body>

</html>