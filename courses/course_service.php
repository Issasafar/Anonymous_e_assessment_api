<?php
header('Content-Type: application/json; charset=utf-8');
// Enable Logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
// Required files
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/Anonymous_e_assessment/';
require_once $basePath . 'config.php';
require_once $basePath . 'db-connect.php';
require_once $basePath . 'courses/course.php';
require_once 'course_service_response.php';

//#####################__Actions__###########################//
enum Actions: string
{
    case CREATE_TEST = "CREATE_TEST";
    case GET_COURSES = "GET_COURSES";
    case GET_QUESTIONS = "GET_QUESTIONS";

}

//##################################################

if (isset($_SERVER['CONTENT_TYPE']))
    $contentType = $_SERVER['CONTENT_TYPE'];

if (str_contains($contentType, 'application/json')) {
    $contents = json_decode(file_get_contents('php://input'), true);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $contents['action'];
        if ($action === Actions::CREATE_TEST->value) {
            $course = Course::getInstance($contents['data']);
            echo json_encode($course);
            $response = Course::createTest($course);
            echo json_encode($response);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET'){
        $action = $contents['action'];
        if($action === Actions::GET_COURSES->value){
            $response = Course::getAvailableCourses();
            echo json_encode($response);
        }elseif ($action == Actions::GET_QUESTIONS->value){
            $testId = $contents['data']['testId'];
            $response = Course::fetchQuestions($testId);
            echo json_encode($response);
        }


    }
} else {
    // show error
}


