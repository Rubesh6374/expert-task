<?php
  include('./layouts/header.php');
  include('./layouts/navbar.php');
  require 'ResultData.php';
  require_once 'helpers.php';
  $students=new ResultData();
  $classresult=$students->getTableDataList('classes');
  $subjectlist=$students->getTableDataList('subject_list');
  $subjectlistmodal=$students->getTableDataList('subject_list');
  // $subjectlist=$students->getClassSubjects();

  $db = new Database();
  $conn= $db->conn;
  $table='subject_list';
  $columns = getColumns($conn,$table);
  
  
  $id = $_GET['delete'] ?? 0; 
  if($id){
    $deleterecord = $students->deleteRecord($table, $id);
    if($deleterecord){
        header('Location:subjects.php');
    }
  }
  if (isset($_GET['edit'])) {
    $id=$_GET['edit'];  
    $editdata=$students->editData('subject_list',$id);
  }

  if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    
        $set = [];
      $values = [];
      $valuesnew = [];
      $types = '';

      foreach ($columns as $col) {
          if ($col['Field'] == 'id') continue;
          $set[] = $col['Field'];
          $values[] = $_POST[$col['Field']];
          $valuesnew[]= "? ";
          $types .= 's';
      }
     
      $insert=$students->insertDataNew('subject_list',$set,$values,$types,$valuesnew);
      header('Location: subjects.php');
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $set = [];
        $values = [];
        $types = '';

        foreach ($columns as $col) {
            if ($col['Field'] == 'id') continue;
            $set[] = "{$col['Field']} = ?";
            $values[] = $_POST[$col['Field']];
            $types .= 's';
        }
        $values[] = $_POST['id'];
        $types .= 'i';
      
         $update=$students->updateData('subject_list',$set,$values,$types);
         header("Location: subjects.php");
    
    }
?>

<body>
  
  <div class="container">
    <form method="post">   
      <div class="form-inline">
        <label><strong>Subject name:</strong></label>
        <input type="text" name="subject_name" class="form-control" value="<?= $editdata['subject_name'] ?? '' ?>" placeholder="Enter Subject name">
        <?php if (isset($editdata)): ?>
          <input type="hidden" name="id" value="<?= $editdata['id'] ?>">
          <input type="submit" name="update" class="btn btn-primary" value="Update Subject">
        <?php else: ?>
          <input type="submit" name="add" class="btn btn-primary" value="Add Subject">
        <?php endif; ?>  
      </div>              
    </form>
        <table class="table" id="reportData">
            <thead>
                <th>ID</th>
                <th> Subject Name</th>     
                <th>Action</th>
            </thead>
            <tbody>
              <?php
              generateTable($columns,$subjectlist);
              ?>
            </tbody>
        </table>      
    </div>
<?php include('./layouts/footer.php');?>