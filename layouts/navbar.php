<?php
$page = basename($_SERVER['PHP_SELF']);
$name = explode('.', $page)[0]; 
require_once 'helpers.php';
?>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Expert Solutions Task</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="<?php echo ($name == 'index') ? 'active' : ''; ?>"><a href="/task">Home</a></li>
      <li class="<?php echo ($name == 'teacherlist') ? 'active' : ''; ?>"><a href="teacherlist.php">Teacher Management</a></li>
      <li class="<?php echo ($name == 'classlist') ? 'active' : ''; ?>"><a href="classlist.php">Class Management</a></li>
      <li class="<?php echo ($name == 'classubjects') ? 'active' : ''; ?>"><a href="classubjects.php">Assign Class Subjects</a></li>
      <li class="<?php echo ($name == 'teacherassign') ? 'active' : ''; ?>"><a href="teacherassign.php">Teachers Assign</a></li>
      <li class="<?php echo ($name == 'subjects') ? 'active' : ''; ?>"><a href="subjects.php">Subject Management</a></li>
      <li class="<?php echo ($name == 'report') ? 'active' : ''; ?>"><a href="report.php">Report Management</a></li>
    </ul>
  </div>
</nav>
