<?php
require_once 'student.php';
require_once 'teacher.php';
$name = "";
$email = "";
$password = "";
$sign = "";
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

}else{
if (isset($_POST['name'])) {
    $name = $_POST['name'];
}
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
if (isset($_POST['password'])) {
    $password = $_POST['password'];
}
if (isset($_POST['sign'])) {
    $sign = $_POST['sign'];
}}
//echo "<h1>$name, $email, $password, $sign</h1>";
if (!empty($email) && !empty($name) && !empty($password)) {

    $hashed = md5($password);
    if (empty($sign)) {
      $user = new Teacher();
        $json_result = $user->registerNewTeacher($name, $email, $password);
    }else
    {
        $user = new Student();
        $json_result = $user->registerNewStudent($name, $email, $password, $sign);
    }
    echo json_encode($json_result);
}else if (empty($name) && empty($sign) && !empty($email) && !empty($password)) {
    $hashed = md5($password);
  //TODO() implement the login functionality (done)SSS
    $user = new Student();
    $json_student_result = $user->login($email, $password);
    if ($json_student_result['success'] === 1) {
        echo json_encode($json_student_result);
    } elseif ($json_student_result['success'] === 0) {
        $user = new Teacher();
        $json_teacher_result = $user->login($email, $password);
       echo json_encode($json_teacher_result);
    }
} else {

}