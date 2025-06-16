<?php
include('./layouts/header.php');
include('./layouts/navbar.php');
require 'ResultData.php';

  $students=new ResultData();
 
  $db = new Database();
  $conn= $db->conn;
  $table='classes';
  $columns = getColumns($conn,$table);
  $columnsupdate = $columns;
  $data['Field']='teacher_name';
  $columns=array_filter($columns,function($list){
    return $list['Field'] !='class_teacher_id';
  });
  array_push($columns,$data);
  $teacherresult=$students->getTableDataList('teachers_list');
  $result=$students->getDoubleTableDataList('classes');
 
  $id = $_GET['delete'] ?? 0; 
  if($id){
    $deleterecord = $students->deleteRecord($table, $id);
    if($deleterecord){
        header('Location:classlist.php');
    }
  }
  if (isset($_GET['edit'])) {
      $id=$_GET['edit'];
      $editdata=$students->editData($table,$id);
      // print_r($editdata);
  }
  // $joindatas=$students->joinQueries('classes','teachers_list','id','class_teacher_id');
  //   echo "<pre>";
  // print_r($joindatas);exit;
  if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    // print_r($_POST);exit;
      $set = [];
      $values = [];
      $valuesnew = [];
      $types = '';

      foreach ($columnsupdate as $col) {
          if ($col['Field'] == 'id') continue;
          $set[] = $col['Field'];
          $values[] = $_POST[$col['Field']];
          $valuesnew[]= "? ";
          $types .= 's';
      }
     
      $insert=$students->insertDataNew('classes',$set,$values,$types,$valuesnew);
      header('Location: classlist.php');
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $set = [];
        $values = [];
        $types = '';

        foreach ($columnsupdate as $col) {
            if ($col['Field'] == 'id') continue;
            $set[] =$col['Field'] ." = ?";
            $values[] = $_POST[$col['Field']];
            $types .= 's';
        }

        $values[] = $_POST['id'];
        $types .= 'i';
        $update=$students->updateData($table,$set,$values,$types);
        header("Location: classlist.php");
  }
?>

<body>
  
  <div class="container">
        <form action="" method="post">
        <div class="form-inline">
            <label><strong>Class name:</strong></label>
            <input type="text" name="class_name"  value="<?= $editdata['class_name'] ?? '' ?>" class="form-control" placeholder="Enter class name">
            <select class="form-control" name="class_teacher_id">
              <?php  while ($row = $teacherresult->fetch_assoc()) { 
                  $selected="";
                  if($row['id'] == $editdata['class_teacher_id']){
                      $selected="selected";
                  }    
              ?>
                <option value="<?php echo $row['id'];?>" <?=$selected;?>><?php echo $row['teacher_name'];?></option>
                <?php } ?>
            </select>
             <?php if (isset($editdata)): ?>
        <input type="hidden" name="id" value="<?= $editdata['id'] ?>">
        <input type="submit" name="update" class="btn btn-primary" value="Update">
        <?php else: ?>
            <input type="submit" name="add" class="btn btn-primary" value="Add Class">
        <?php endif; ?>
           
        </div>
        </form>

        <table class="table">
            <thead>
                <?php foreach ($columns as $col): ?>
                  <th><?= ucfirst(str_replace('_', ' ',$col['Field'])) ?></th>
                <?php endforeach; ?>       
                <th>Action</th>
            </thead>
            <tbody>           
            <?php generateTable($columns,$result);?>
            </tbody>
        </table>
    </div>
<?php include('./layouts/footer.php');?>