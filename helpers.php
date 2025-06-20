<?php

function getColumns($conn, $table)
{
    $columns = [];
    $result = $conn->query("SHOW FULL COLUMNS FROM `$table`");
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    return $columns;
}

function getForeignKeys($conn, $table, $db)
{
    $fks = [];
    $sql = "SELECT 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = '$table' 
              AND TABLE_SCHEMA = '$db' 
              AND REFERENCED_TABLE_NAME IS NOT NULL";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $fks[$row['COLUMN_NAME']] = [
            'table' => $row['REFERENCED_TABLE_NAME'],
            'column' => $row['REFERENCED_COLUMN_NAME']
        ];
    }
    return $fks;
}


function generateTable($columns, $result, $table = '')
{
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    if ($table == 'students') {
        foreach ($data as &$item) {
            unset($item['class_id']);
            unset($item['addressinfo']);
        }
    }

    $columnvalues = [];
    foreach ($columns as $key => $value) {
        $columnvalues[] = $value['Field'];
    }
    $reIndexResult = [];
    foreach ($data as $fkey => $list) {
        $subArray = [];
        foreach ($list as $key => $value) {
            $subArray[] = [$key => $value];
        }
        $reIndexResult[] = $subArray;
    }

    $i = 0;
    foreach ($reIndexResult as $key => $value) {
        $i++;
        echo "<tr>";
        $rowid = '';

        foreach ($value as $vkey => $vvalue) {
            if (isset($vvalue['id'])) {
                $rowid = $vvalue['id'];
                echo "<td>".$i."</td>";
            }

            if (!isset($vvalue['id'])) {
                echo "<td>".$vvalue[$columnvalues[$vkey]]."</td>";
            }
        }
        echo "<td><a class='btn btn-primary btn-sm' href='?delete=" . base64_encode($rowid) . "'>Delete</a>";
        if ($table == 'students') {
            echo "<a class='btn btn-danger btn-sm' href='view.php?id=" . base64_encode($rowid) ."'>View</a>";
            echo "<a class='btn btn-success btn-sm' href='update.php?id=" . base64_encode($rowid) ."'>Edit</a></td>";
        } else {
            if ($table != 'class_subjects') {
                echo "<a class='btn btn-success btn-sm' href='?edit=" . base64_encode($rowid) ."'>Edit</a></td>";
            }
        }
        echo "</tr>";
    }
}


function validation($type, $min = '', $max = '', $required = '')
{

}
