
<?php
// require 'db.php';
require 'ResultData.php';
 include('./layouts/header.php');
   include('./layouts/navbar.php');
  $students=new ResultData();
  $classresult=$students->getTableDataList('classes');
  $db = new Database();
  $conn = $db->conn;
  $db = new Database();
  $id=$_GET['id'];
  $id=base64_decode($id);
  $conn = $db->conn;

  $stmt = $conn->prepare("SELECT students.* ,classes.class_name FROM students LEFT JOIN classes ON classes.id = students.class_id WHERE students.id = ?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $student = null;

  while ($row = $result->fetch_assoc()) {
      if (!$student) {
          $student = [
              'id' => $row['id'],
              'name' => $row['first_name']." ".$row['last_name'],
              'email' =>$row['email'],
              'dob' =>$row['dob'],
              'phone' =>$row['phone'],
              'addressinfo' =>$row['addressinfo'],
              'class_name' =>$row['class_name']
          ];
      }
  }
  $stmt3 = $conn->prepare("SELECT  re.exam_id,ex.exam_name,ex.id as id ,SUM(re.marks) AS total_marks FROM reports re LEFT JOIN exams ex ON ex.id=re.exam_id  WHERE user_id = ? GROUP BY re.exam_id");
       
          $stmt3->bind_param("i", $id);
           if (!$stmt3) {
            echo "Prepare failed: " . $conn->error; 
        }
          $stmt3->execute();
          $result3 = $stmt3->get_result();
           while ($row = $result3->fetch_assoc()) {
              $report_data[] = $row;
          }
 
  $stmt2 = $conn->prepare("SELECT id AS edu_id,institute,batch,percentage FROM education_history WHERE user_id = ?");

          $stmt2->bind_param("i", $id);
          $stmt2->execute();
          $result2 = $stmt2->get_result();

          $education_data = [];
          while ($row = $result2->fetch_assoc()) {
              $education_data[] = $row;
          }
          $student['reports']=$report_data;
          $student['education_history']=$education_data;

           ?>
<body>
  
    <div class="container">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Student Information</h4>
      </div>
      
            <div class="col-md-4">
              <strong>First Name:</strong> <?php echo $student['name']; ?>
            </div>
             <div class="col-md-4">
              <strong>Class Name:</strong> <?php echo $student['class_name']; ?>
            </div>
            <div class="col-md-4">
              <strong>Email:</strong> <?php echo $student['email']; ?>
            </div><hr>
            <div class="col-md-4">
              <strong>Mobile:</strong> <?php echo $student['phone']; ?>
            </div>
            <div class="col-md-4">
              <strong>DOB:</strong> <?php echo $student['dob']; ?>
            </div>
            <hr>
          <hr>

          <h5 class="mt-4">Reports</h5>
          <table class="table table-bordered table-striped mt-3">
            <thead class="table-secondary">
              <tr>
                <th>Exam Name</th>
                <th>Mark</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($student['reports'] as $key => $value) {
                echo "<tr><td>".$value['exam_name']."</td>";
                echo "<td>".$value['total_marks']."</td>";
                echo '<td><button type="button" data-toggle="modal" data-target="#myModal" class="btn btn-info btn-sm view_report" data-user_id='.$id.' data-exam_id='.$value['id'].'>View</button></td></tr>';
                }
              ?>
            </tbody>
          </table>

           <h5 class="mt-4">Education History</h5>
          <table class="table table-bordered table-striped mt-3">
            <thead class="table-secondary">
              <tr>
                <th>Institute</th>
                <th>Batch</th>
              </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($student['education_history'] as $key => $value) {
                  echo "<tr><td>".$value['institute']."</td>";
                  echo "<td>".$value['batch']."</td></tr>";
                }
              ?>
            </tbody>
          </table>
      
    </div>


    <!-- MODAL PART -->
      
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
       <div class="reportDetails"></div>
      </div>

    </div>

  </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#myModal').modal('show');
    });
      $('.view_report').click(function(){
         $('#myModal').modal('show');
    var exam_id = $(this).data('exam_id');
    var user_id = $(this).data('user_id');

    $.ajax({
      url: 'viewreport.php',      // PHP file to receive data
      type: 'POST',
      dataType: 'html',
      data: { exam_id: exam_id , user_id:user_id },    // data to send
      success: function(response) {
        $('#myModal').show();
       $('.reportDetails').html(response);
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', error);
      }
    });
  });
</script>
</body>
</html>