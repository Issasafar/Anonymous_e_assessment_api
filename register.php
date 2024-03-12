<?php
require_once 'student.php';
require_once 'teacher.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$data = [];
    if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
        $json_data = file_get_contents('php://input');
        if (!$json_data) {
            echo json_encode(array('success'=>0 , 'message'=> 'Empty request body.'));
            exit;
        }
        $data = json_encode($json_data, true);
        if (!$data) {
            echo json_encode(array('success' => 0, 'message' => 'Invalid JSON data.'));
            exit;
        }

    } else {
        $data = $_POST;
    }
    $name = isset($data['name']) ? $data['name'] : "";
    $email = isset($data['email']) ? $data['email'] : "";
    $password = isset($data['password']) ? $data['password'] : "";
    $sign = isset($data['sign']) ? $data['sign'] : "";
}else{
    echo json_encode(array('success'=> 0, 'message' => 'Invalid request method'));
}
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
    echo json_encode(array('success' => 0, 'message' => 'Missing required fields.'));
    exit;
}