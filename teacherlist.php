<?php
include('./layouts/header.php');
include('./layouts/navbar.php');

require 'ResultData.php';


  $students=new ResultData();
//   $result=$students->getDoubleTableDataList('teachers_list');
  $db = new Database();
  $conn= $db->conn;
  $table='teachers_list';
  $columns = getColumns($conn,$table);
  $result=$students->getTableDataList('teachers_list');

  $id = $_GET['delete'] ?? 0; // Or pass this from somewhere
  if($id){
    $deleterecord = $students->deleteRecord('teachers_list', $id);
    if($deleterecord){
        header('Location: teacherlist.php');
    }
  }
  
  if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
      // $insertRecord=$students->insertData('teachers_list',$_POST);
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
     
      $insert=$students->insertDataNew('teachers_list',$set,$values,$types,$valuesnew);
      if($insert){
      // header('Location: teacherlist.php');
      }
  }

  if (isset($_GET['edit'])) {
      $id=$_GET['edit'];
      $editdata=$students->editData('teachers_list',$id);
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
      $update=$students->updateData('teachers_list',$set,$values,$types);
      header("Location: teacherlist.php");
    
  }
?>

<body>
  
    <div class="container">
      <form method="post">
        <div class="row">
          <?php foreach ($columns as $col){ 
            $label = str_replace('_', ' ', $col['Field']);
            ?>
        <?php if ($col['Field'] == 'id') continue; ?>
        <div class="col-md-3">
             <label><?= ucfirst($label) ?>:</label>
            <input type="text" name="<?= $col['Field'] ?>" value="<?= $editdata[$col['Field']] ?? '' ?>" class="form-control" placeholder="<?= ucfirst($label) ?>">
        </div>
        <?php } ?>
        <?php if (isset($editdata)): ?>
          <input type="hidden" name="id" value="<?= $editdata['id'] ?>">
          <input type="submit" name="update" class="btn btn-primary" value="Update Teacher">
        <?php else: ?>
        <input type="submit" name="add" class="btn btn-primary" value="Add Teacher">
         <?php endif; ?>
      </form>
      <br>
      <br>
      <br>
        <table class="table" id="reportData">
            <thead>
                <?php foreach ($columns as $col): ?>
                    <th><?= ucfirst(str_replace('_', ' ',$col['Field'])) ?></th>
                <?php endforeach; ?>       
                <th>Action</th>
            </thead>
            <tbody>           
    <?php
      generateTable($columns,$result);
    ?>
            </tbody>
        </table>
    </div>
<?php include('./layouts/footer.php');?>