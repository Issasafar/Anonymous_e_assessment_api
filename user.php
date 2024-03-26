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

    public function checkForEntry($email, $name)
    {

            $query = "SELECT * FROM " . $this->db_table . " WHERE email = '$email' AND name = '$name'";
            $result = mysqli_query($this->db->getDb(), $query);
            mysqli_close($this->db->getDb());
            if (mysqli_num_rows($result) > 0) {
                return true;
            }
            return false;

   }
    public function isLoginExist($email, $password)
    {
        $query = "SELECT * FROM " . $this->db_table . " WHERE email = '$email' AND password = '$password' LIMIT 1";
        $result = mysqli_query($this->db->getDb(), $query);
        if (mysqli_num_rows($result) > 0) {

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
            $this->loginResult['success'] = true;
            $this->loginResult['message'] = "Successfully logged in";

        } else {
            $this->loginResult['success'] = false;
            $this->loginResult['message'] = "Incorrect details ";
        }

        return $this->loginResult;
    }

    public function resetPassword($email, $newPassword)
    {

        $hashedNewPassword = md5($newPassword);
        $query = "UPDATE $this->db_table SET `password` = '$hashedNewPassword' WHERE `email` = '$email'";

        try{

        $updated = mysqli_query($this->db->getDb(),$query);

        }
        catch (Exception $e ){

            echo json_encode(array(
                'success'=>false,
                'message'=>$e
            ));

    }
        if ($updated) {
            echo json_encode(array(
                'success' =>true,
                'message' => 'password has been reset'
            ));
        } else {
            echo json_encode(array(
                'success' => false,
                'message'=>'Error in resetting password'
            ));
        }
    }
}