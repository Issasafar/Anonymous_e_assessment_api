<?php
include_once 'db-connect.php';
require_once 'user.php';
class Student extends User
{
    public function __construct()
    {
        $this->db = new DbConnect();
        $this->db_table = "students";
    }
    public function registerNewStudent($name, $email, $password, $sign)
    {
        $isExisting = $this->isEmailExist($email);
        if ($isExisting) {
            $json['success'] = false;
            $json['message'] = "Error in registering. Probably the username/email already exists";
        } else {
            $isValid = $this->isValidEmail($email);
            if ($isValid) {
                $query = "insert into " . $this->db_table . " (name, email, password, sign) values ('$name','$email','$password','$sign')";
                $inserted = mysqli_query($this->db->getDb(), $query);
                if ($inserted == 1) {

                    $json['success'] = true;
                    $json['message'] = "Successfully registered the user";
                } else {
                    $json['success'] = false;
                    $json['message'] = "Error in registering. Probably the username/email already exists";
                }
                mysqli_close($this->db->getDb());
            } else {
                $json['success'] = false;
                $json['message'] = "Error in registering. Email Address is not valid";
            }
        }
        return $json;
    }


}