<?php
include_once 'db-connect.php';
class User
{
    protected $db;
    protected $db_table;
    protected $loginResult;

    public function __construct()
    {
        $this->db = new DbConnect();
    }
    public function isLoginExist($email, $password)
    {
        $query = "select * from " . $this->db_table . " where email = '$email' AND password = '$password' Limit 1";
        $result = mysqli_query($this->db->getDb(), $query);
        if (mysqli_num_rows($result) > 0) {
//            while($row = $result->fetch_array()) {
//                echo $row[0] . ":" . $row[1] . ":". $row[2] .":". $row[3] .":". $row[4] ."\n";
//            }
            $row = $result->fetch_array();
            $this->loginResult = array();
            $this->loginResult['userId'] = $row[0];
            $this->loginResult['userName'] = $row[1];
            $this->loginResult['email'] = $row[2];
            $this->loginResult['password'] = $row[3];
            $this->loginResult['sign'] = $row[4];
            mysqli_close($this->db->getDb());
            return true;
        }
        //TODO()
        mysqli_close($this->db->getDb());
        return false;
    }
    public function isEmailExist($email)
    {
        $query = "select * from " . $this->db_table . " where email = '$email'";
        $result = mysqli_query($this->db->getDb(), $query);
        if (mysqli_num_rows($result) > 0) {
            mysqli_close($this->db->getDb());
            return true;
        }
        return false;
    }

    public function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public function login($email, $password)
    {


        $canStudentLogin = $this->isLoginExist($email, $password);
        if ($canStudentLogin) {
            $this->loginResult['success'] = 1;
            $this->loginResult['message'] = "Successfully logged in";

        } else {
            $this->loginResult['success'] = 0;
            $this->loginResult['message'] = "Incorrect details ".$email.$password;
        }

        return $this->loginResult;
    }
}