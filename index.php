
<?php
require 'ResultData.php';

  $students=new ResultData();
  
  $result=$students->getTableDataList('students');
  // $joindatas=$students->joinQueries('students','classes','id','class_id');
  // echo "<pre>";
  // print_r($joindatas);exit;
  $classresult=$students->getTableDataList('classes');
   include('./layouts/header.php');
   include('./layouts/navbar.php');
   $db = new Database();
 $conn= $db->conn;
 $table='students';
$columns = getColumns($conn,$table);
$formcolumns=$columns;
$columns=array_filter($columns,function($list){
  return $list['Type'] !='text' && $list['Field'] !='class_id';
});
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
  $set = [];
      $values = [];
      $valuesnew = [];
      $types = '';
      foreach ($formcolumns as $col) {
          if ($col['Field'] == 'id') continue;
          $set[] = $col['Field'];
          $values[] = $_POST[$col['Field']];
        
          $valuesnew[]= "? ";
          $types .= 's';
      }
        $institutes['institute_name'] = $_POST['institute_name'];
          $institutes['batch'] = $_POST['batch'];
          $institutes['percentage'] = $_POST['percentage'];
          $insert=$students->insertDataNew('students',$set,$values,$types,$valuesnew,$institutes);
          header("Location: index.php");
}

?>
<body>
  
    <div class="container">
        <a href="export.php" class="btn btn-primary">Export</a>
       <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal">Add Student</button>

        <table class="table" id="reportData">
           <thead>
                <?php foreach ($columns as $col): ?>
                  <th><?= ucfirst(str_replace('_', ' ',$col['Field'])) ?></th>
                <?php endforeach; ?>       
                <th>Action</th>
            </thead>
            <tbody>                
            <?php generateTable($columns,$result,'students');?>
            </tbody>
        </table>
    </div>

    <!-- Trigger the modal with a button -->

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modal Header</h4>
      </div>
      <div class="modal-body">
       <form method="post">
       <div class="form-group">
    <label for="fname">Firstname:</label>
    <input type="fname" class="form-control" name="first_name" id="fname">
  </div>
   <div class="form-group">
    <label for="lname">Lastname:</label>
    <input type="text" name="last_name" class="form-control" id="lname">
  </div>
   <div class="form-group">
    <label for="email">Email:</label>
    <input type="email" name="email" class="form-control" id="email">
  </div>
  <div class="form-group">
    <label for="sub_name">Select Class:</label>
    <select class="form-control" name="class_id">
    <?php while ($row = $classresult->fetch_assoc()) { ?>
        <option value="<?php echo $row['id'];?>"><?php echo $row['class_name'];?></option>
        <?php } ?>
    </select>
  </div>
   <div class="form-group">
    <label for="dob">DOB:</label>
    <input type="date" name="dob" class="form-control" id="dob">
  </div>
   <div class="form-group">
    <label for="phone">Phone:</label>
    <input type="text" name="phone" class="form-control" id="phone">
  </div>
  <div class="form-group">
    <label for="address">Address:</label>
    <textarea class="form-control" name="addressinfo" id="address"></textarea>
  </div>
 <h4>Educational History</h4>
  <div class="row">
  <div class="col-md-3">
    <label for="institute_name">Institute:</label>
    
    <input type="text" class="form-control"  name="institute_name[]" id="institute_name">
  </div>
   <div class="col-md-3">
    <label for="batch">Batch:</label>
    <input type="text" name="batch[]"  class="form-control" id="batch">
  </div>
  <div class="col-md-3">
    <label for="percentage">Percentage:</label>
    <input type="text" name="percentage[]"  class="form-control" id="percentage">
  </div>
   <div class="col-md-3">
    
    <button type="button"   class="btn btn-danger" id="addinstitutebutton">Add</button>
  </div>
</div>
<div class="appendinstitute"></div>
  <input type="submit" name="add" class="btn btn-primary"value="Submit">
</form>
      </div>
     
    </div>

  </div>
</div>
<script type="text/javascript">


    $('#addinstitutebutton').click(function(){
       var html='';
       html +='<div class="row">';
  html +='<div class="col-md-3"><label for="fname">Institute:</label><input type="text" class="form-control"  name="institute_name[]" id="exam_name"></div>';
  html +='<div class="col-md-3"> <label for="lname">Batch:</label><input type="text" name="batch[]"  class="form-control" id="marks"></div>';
  html +='<div class="col-md-3"><label for="lname">Percentage:</label><input type="text" name="percentage[]"  class="form-control" id="percentage"> </div>';
html +='<div class="col-md-3"><button type="button" class="btn btn-danger removeinstitute_row" >X</button></div></div>';
// alert(html);
$('.appendinstitute').append(html);
    });
    $(document).on('click', '.removeinstitute_row', function() {
    $(this).closest('.row').remove();
})
</script>
<?php include('./layouts/footer.php');?>