<?php
require_once 'student.php';
require_once 'teacher.php';
header('Content-Type: application/json; charset=utf-8');
$contentType = $_SERVER['CONTENT_TYPE'];
$user = new Student();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($contentType, 'application/json') !== false) {
        $json_data = file_get_contents('php://input');

        $data = json_decode($json_data, true);
        $data = json_decode($data, true);


    } else {
        $data = $_POST;
    }
    $email = isset($data['email']) ? $data['email'] : "";
    $name = isset($data['userName']) ? $data['userName'] : "";

    if (!empty($data['newPassword'])) {
        // if the post method contain new password
        // reset the user's password
        // *********************************************/
        $newPassword = $data['newPassword'];
        $account_type = isset($data['accountType']) ? $data['accountType'] : "student";
        if (strtolower($account_type) == "teacher") {
            $user = new Teacher();
        }
        $user->resetPassword($email, $newPassword);
    } else {
        // before resetting the password the client should send the email and username
        // to make sure the email exists and the username is valid to that particular email
        // check for entries of $email in the database

        $user = new Student();
        $isStudent = $user->checkForEntry($email, $name);
        if ($isStudent) {
            echo json_encode(array(
                'success' => true,
                'message' => 'student'
            ));
        } elseif (!$isStudent ) {

             $user = new Teacher();
            if($user->checkForEntry($email, $name)){
            echo json_encode(array(
                'success' => true,
                'message' => 'teacher'
            ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => "Email doesn't exist."
                ));
            }

        } else {
            // email not exist
            // so return an error
            echo json_encode(array(
                'success' => false,
                'message' => 'Error'
            ));
        }
    }
} else {
    echo json_encode(array('success'=> false, 'message' => 'Invalid request method'));
}
