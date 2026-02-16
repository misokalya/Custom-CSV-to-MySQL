<?php
include 'db.php';

$first = $_POST['first_name'];
$last  = $_POST['last_name'];
$module = $_POST['module_code'];
$CA = $_POST['CA'];
$SE = $_POST['SE'];

$TOT = $CA + $SE;

/* Grade Logic */
if ($TOT >= 70) $GRD = "A";
elseif ($TOT >= 60) $GRD = "B";
elseif ($TOT >= 50) $GRD = "C";
elseif ($TOT >= 40) $GRD = "D";
else $GRD = "F";

/* Insert Student */
$sql1 = "INSERT INTO students (first_name, last_name)
         VALUES ('$first', '$last')";
$conn->query($sql1);

$student_id = $conn->insert_id;

/* Insert Result */
$sql2 = "INSERT INTO results (student_id, module_code, CA, SE, TOT, GRD)
         VALUES ('$student_id', '$module', '$CA', '$SE', '$TOT', '$GRD')";
$conn->query($sql2);

header("Location: view.php");
