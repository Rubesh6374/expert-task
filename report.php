<?php
include('./layouts/header.php');
include('./layouts/navbar.php');
require 'ResultData.php';
$students = new ResultData();
$classresult = $students->getTableDataList('classes');
$examresult = $students->getTableDataList('exams');
if (isset($_GET['filter_students'])) {
    $classId = $_GET['class_id'];
    $studentslist = $students->getTableDataListWhere('students', $classId);
    $subjectlist = $students->getClassSubjectsreport($classId);
    $subject_ids = [];
}
?>

<body>

	<div class="container">
		<form action="" method="GET">
			<div class="row">


				<div class="form-inline">
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
					<input type="submit" name="filter_students" value="Generate Report" class="btn btn-primary">
				</div>
				<div class="col-md-3">

				</div>
			</div>
		</form>
		<div class="row">
			<div class="col text-end">
				<a href="getreportlist.php" class="btn btn-success">View Reports Lists</a>
			</div>
		</div>

		<?php  if ($_GET) { ?>

		<div class="col-md-12">
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

				</thead>
				<tbody>
					<form action="generate_report.php" method="post">
						<input type="hidden" name="exam_id"
							value="<?php echo $_GET['exam_id']; ?>">
						<input type="hidden" name="class_id"
							value="<?php echo $_GET['class_id']; ?>">
						<?php $i = 1;
		    $studentIds = [];
		    while ($row = $studentslist->fetch_assoc()) {
		        $studentIds[] = $row['id'];
		        ?>
						<tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $row['first_name']. " ".$row['last_name'];?>
							</td>


							<?php foreach ($subjectlist as $subject) { ?>
							<td>
								<input type="text" class="form-control"
									name="marks[<?php echo $row['id']; ?>][<?php echo $subject['id']; ?>]"
									placeholder="Enter marks" required>
							</td>

							<?php  } ?>

						</tr>
						<?php $i++;
		    } ?>
				</tbody>
			</table>
			<input type="submit" class="btn btn-primary" name="submit" value="Generate Report">
			</form>
		</div>


		<?php } ?>

	</div>

	<!-- Trigger the modal with a button -->

</body>

</html>