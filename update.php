<!DOCTYPE html>
<html lang="en">

<head>
	<title>Student List</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<?php
// require 'db.php';
require 'ResultData.php';

$students = new ResultData();
$classresult = $students->getTableDataList('classes');
$db = new Database();
$conn = $db->conn;
$id = base64_decode($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM students where id=?");
$stmt->bind_param("i", $id);
$data = [];
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM reports where user_id=?");
$stmt->bind_param("i", $id);
$data = [];
$stmt->execute();
$reportresult = $stmt->get_result();

$stmt = $conn->prepare("SELECT * FROM education_history where user_id=?");
$stmt->bind_param("i", $id);
$data = [];
$stmt->execute();
$instituteresult = $stmt->get_result();

if (isset($_POST['user_id'])) {
    // print_R($_POST);exit;

    $input = $_POST;
    $user_id = intval($input['user_id']);
    $first_name = $conn->real_escape_string($input['first_name']);
    $last_name = $conn->real_escape_string($input['last_name']);
    $email =  $conn->real_escape_string($input['email']);
    validateInputs(["type" => "email","email" => $email]);
    $dob =  $conn->real_escape_string($input['dob']);
    validateInputs(["type" => "dob","dob" => $dob]);
    $phone =  $conn->real_escape_string($input['phone']);
    validateInputs(["type" => "phone","phone" => $phone]);
    $address = $conn->real_escape_string($input['address']);
    $stmt =  $conn->prepare("UPDATE students SET first_name= ?, last_name= ?,email= ?,dob= ?,phone= ? ,addressinfo= ? WHERE id= ?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $dob, $phone, $address, $user_id);
    if ($stmt->execute()) {
    } else {
        echo "Error: " . $stmt->error;
    }


    $new_institute_name = isset($_POST['new_institute_name']) ? $_POST['new_institute_name'] : '';
    $new_batch = isset($_POST['new_batch']) ? $_POST['new_batch'] : '';
    $new_edu_percentage = isset($_POST['new_edu_percentage']) ? $_POST['new_edu_percentage'] : '';
    if (is_array($new_institute_name)) {
        foreach ($new_institute_name as $i_key => $ninsvalue) {
            $stmt = $conn->prepare("INSERT INTO education_history (user_id ,institute,batch,percentage) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                echo  "Prepare failed: " . $conn->error;
            }
            $stmt->bind_param("isss", $user_id, $ninsvalue, $new_batch[$i_key], $new_edu_percentage[$i_key]);
            if ($stmt->execute()) {
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    }
    $institute_ids = $_POST['institute_ids'];
    $institute_name = $_POST['institute_name'];
    $batch = $_POST['batch'];
    $edu_percentage = $_POST['edu_percentage'];
    foreach ($institute_ids as $key => $insvalue) {
        $stmt =  $conn->prepare("UPDATE education_history SET institute= ?,batch= ?,percentage= ? WHERE id= ?");
        $stmt->bind_param("sssi", $institute_name[$key], $batch[$key], $edu_percentage[$key], $insvalue);

        if ($stmt->execute()) {
            header("Location: update.php?id=".base64_encode($user_id));
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

function validateInputs($inputs)
{
    if ($inputs['type'] == 'email') {
        if (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
            responceJSON(["message" => "Invalid email format.","data" => []]);
            die();
        }
    } elseif ($inputs['type'] == 'dob') {
        $dob = $inputs['dob'];
        $dob_date = DateTime::createFromFormat('Y-m-d', $dob);

        if (!$dob_date || $dob_date->format('Y-m-d') !== $dob) {
            responceJSON(["message" => "Invalid date format. Use YYYY-MM-DD.","data" => []]);
            die();
        }
    } elseif ($inputs['type'] == 'phone') {
        if (!preg_match('/^\+?[0-9]{7,15}$/', $inputs['phone'])) {
            responceJSON(["message" => "Invalid phone number format.","data" => []]);
            die();
        }
    } elseif ($inputs['type'] == 'text') {
        if (!preg_match('/^[a-zA-Z ]{3,50}$/', $inputs['text'])) {
            throw new Exception("Name must be alphabetic and between 3-50 characters.");
        }
    } elseif ($inputs['type'] == 'textarea') {
        if (!preg_match('/^[a-zA-Z ]{3,50}$/', $inputs['textarea'])) {
            throw new Exception("Name must be alphabetic and between 3-50 characters.");
        }
    }
}
?>

<body>

	<!-- Modal -->
	<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="false">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Update Student Information</h4>
				</div>
				<div class="modal-body">
					<form action="update.php" method="post">
						<div class="form-group">
							<label for="fname">Firstname:</label>
							<input type="hidden"
								value="<?php echo  $row['id'];?>"
								name="user_id" id="id">
							<input type="text" class="form-control"
								value="<?php echo  $row['first_name'];?>"
								name="first_name" id="fname">
						</div>
						<div class="form-group">
							<label for="lname">Lastname:</label>
							<input type="text" name="last_name"
								value="<?php echo  $row['last_name'];?>"
								class="form-control" id="last_name">
						</div>
						<div class="form-group">
							<label for="email">Email:</label>
							<input type="email" name="email"
								value="<?php echo  $row['email'];?>"
								class="form-control" id="email">
						</div>
						<div class="form-group">
							<label for="sub_name">Select Class:</label>
							<select class="form-control" name="class_id">
								<?php
    $selected = '';
while ($class = $classresult->fetch_assoc()) {
    if ($class['id'] == $row['class_id']) {
        $selected = 'selected';
    }
    ?>

								<option
									value="<?php echo $class['id'];?>"
									<?php echo $selected;?>><?php echo $class['class_name'];?>
								</option>
								<?php } ?>
							</select>
						</div>
						<div class="form-group">
							<label for="dob">DOB:</label>
							<input type="date" name="dob" class="form-control"
								value="<?php echo  $row['dob'];?>"
								id="dob">
						</div>
						<div class="form-group">
							<label for="phone">Phone:</label>
							<input type="text" name="phone"
								value="<?php echo  $row['phone'];?>"
								class="form-control" id="phone">
						</div>
						<div class="form-group">
							<label for="address">Address:</label>
							<textarea class="form-control" name="address"
								id="address"><?php echo  $row['addressinfo'];?></textarea>
						</div>

						<div class="appendreport"></div>
						<h4>Educational History</h4>
						<?php
 $i = 0;
while ($row = $instituteresult->fetch_assoc()) {
    ?>
						<div class="row">
							<div class="col-md-3">
								<label for="fname">Institute:</label>
								<input type="hidden"
									value="<?php echo $row['id'] ?>"
									name="institute_ids[]" id="institute_ids">
								<input type="text" class="form-control"
									value="<?php echo $row['institute'] ?>"
									name="institute_name[]" id="institute_name">
							</div>
							<div class="col-md-3">
								<label for="lname">Batch:</label>
								<input type="text" name="batch[]"
									value="<?php echo $row['batch'] ?>"
									class="form-control" id="batch">
							</div>
							<div class="col-md-3">
								<label for="lname">Percentage:</label>
								<input type="text" name="edu_percentage[]"
									value="<?php echo $row['percentage'] ?>"
									class="form-control" id="edu_percentage">
							</div>
							<div class="col-md-3">
								<?php if ($i == 0) { ?>
								<button type="button" class="btn btn-danger" id="addinstitutebutton">Add</button>
								<?php } ?>
								<a href="removeReport.php?ins_id=<?php echo $row['id'];?>.&&user_id=<?php echo $row['user_id'];?>"
									class="btn btn-primary removeReportRow">X</a>
							</div>
						</div>
						<?php $i++ ;
} ?>
						<div class="appendinstitute"></div>
						<input type="submit" class="btn btn-primary" value="Submit">
					</form>
				</div>

			</div>

		</div>
	</div>
	<script type="text/javascript">
		$(window).on('load', function() {
			$('#myModal').modal('show');
		});
		$('.addReportButton').click(function() {
			var html = '';
			html += '<div class="row">';
			html +=
				'<div class="col-md-3"><label for="fname">Exam Name:</label><input type="text" class="form-control"  name="newexam_name[]" id="exam_name"></div>';
			html +=
				'<div class="col-md-3"> <label for="lname">Marks:</label><input type="text" name="newmarks[]"  class="form-control" id="marks"></div>';
			html +=
				'<div class="col-md-3"><label for="lname">Percentage:</label><input type="text" name="newpercentage[]"  class="form-control" id="percentage"> </div>';
			html +=
				'<div class="col-md-3"><button type="button" class="btn btn-danger remove_row" >X</button></div></div>';
			// alert(html);
			$('.appendreport').append(html);
		});
		$(document).on('click', '.remove_row', function() {
			$(this).closest('.row').remove();
		})

		$('#addinstitutebutton').click(function() {
			var html = '';
			html += '<div class="row">';
			html +=
				'<div class="col-md-3"><label for="fname">Institute:</label><input type="text" class="form-control"  name="new_institute_name[]" id="new_institute_name"></div>';
			html +=
				'<div class="col-md-3"> <label for="lname">Batch:</label><input type="text" name="new_batch[]"  class="form-control" id="marks"></div>';
			html +=
				'<div class="col-md-3"><label for="lname">Percentage:</label><input type="text" name="new_edu_percentage[]"  class="form-control" id="percentage"> </div>';
			html +=
				'<div class="col-md-3"><button type="button" class="btn btn-danger removeinstitute_row" >X</button></div></div>';
			// alert(html);
			$('.appendinstitute').append(html);
		});
		$(document).on('click', '.removeinstitute_row', function() {
			$(this).closest('.row').remove();
		})
	</script>
</body>

</html>