<?php
require_once 'user.php';
include_once 'db-connect.php';
class Teacher extends User
{
    public function __construct()
    {
        $this->db = new DbConnect();
        $this->db_table = "teachers";
    }
    public function registerNewTeacher($name, $email, $password)
    {
        $isExisting = $this->isEmailExist($email);
        if ($isExisting) {
            $json['success'] = 0;
            $json['message'] = "Error in registering. Probably the username/email already exists";
        } else {
            $isValid = $this->isValidEmail($email);
            if ($isValid) {
                $query = "insert into " . $this->db_table . " (name, email, password) values ('$name','$email','$password')";
                $inserted = mysqli_query($this->db->getDb(), $query);
                if ($inserted == 1) {

                    $json['success'] = 1;
                    $json['message'] = "Successfully registered the user";
                } else {
                    $json['success'] = 0;
                    $json['message'] = "Error in registering. Probably the username/email already exists";
                }
                mysqli_close($this->db->getDb());
            } else {
                $json['success'] = 0;
                $json['message'] = "Error in registering. Email Address is not valid";
            }
        }
        return $json;
    }
}
