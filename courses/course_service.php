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
    case POST_TEST = "POST_TEST";
    case GET_COURSES = "GET_COURSES";
    case GET_PREV_TESTS = "GET_PREV_TESTS";
    case GET_QUESTIONS = "GET_QUESTIONS";
    case GET_RESULTS = "GET_RESULTS";
    case POST_RESULT = "POST_RESULT";
    case POST_ANSWERS = "POST_ANSWERS";
    case GET_ANSWERS = "GET_ANSWERS";
    case GET_MESSAGES = "GET_MESSAGES";
    case POST_MESSAGE = "POST_MESSAGE";
    case MARK_SEEN = "MARK_SEEN";
    case GET_ALL_COURSES = "GET_ALL_COURSES";

    case GET_ALL_RESULTS = "GET_ALL_RESULTS";


}

//##################################################

if (isset($_SERVER['CONTENT_TYPE'])) $contentType = $_SERVER['CONTENT_TYPE'];

if (str_contains($contentType, 'application/json')) {
    try {
        $contents = json_decode(file_get_contents('php://input'), true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $contents['action'];
            if ($action === Actions::POST_TEST->value) {
                $course = Course::getInstance($contents['data']);
                $response = Course::createTest($course);
                echo json_encode($response);
            } elseif ($action == Actions::MARK_SEEN->value) {
                $mId = $contents['data']['m_id'];
                $teacherId = $contents['data']['teacher_id'];
                $response = Course::markSeenMessages($mId, $teacherId);
                echo json_encode($response);
            } elseif ($action === Actions::POST_ANSWERS->value) {
                $answers = $contents['data'];
                $response = Course::postAnswers($answers);
                echo json_encode($response);
            } elseif ($action === Actions::POST_RESULT->value) {
                $result = $contents['data'];
                $response = Course::postResult($result);
                echo json_encode($response);
            } elseif ($action === Actions::POST_MESSAGE->value) {
                $message = $contents["data"];
                $response = Course::postMessage($message);
                echo json_encode($response);
            } elseif ($action === Actions::GET_COURSES->value) {
                if (isset($contents['data']['owner_id'])) {
                    $ownerId = $contents['data']['owner_id'];
                }
                $response = Course::getAvailableCourses($ownerId);
                echo json_encode($response);
            } elseif ($action === Actions::GET_ALL_COURSES->value) {
                echo json_encode(Course::getAllCourses());
            } elseif ($action === Actions::GET_ALL_RESULTS->value) {
                $ownerId = $contents['data']['student_id'];
                $response = Course::getAllResults($ownerId);
                echo json_encode($response);
            }elseif ($action === Actions::GET_QUESTIONS->value) {
                $testId = $contents['data']['test_id'];
                if (isset($ownerId)) $ownerId = $contents['data']['owner_id'];
                $response = Course::fetchQuestions($testId, null);
                echo json_encode($response);
            } elseif ($action === Actions::GET_PREV_TESTS->value) {
                if (isset($contents['data']['student_id'])) {
                    $student_id = $contents['data']['student_id'];
                }
                $response = Course::getPrevTests($student_id);
                echo json_encode($response);
            } elseif ($action === Actions::GET_ANSWERS->value) {
                $testId = $contents['data']['test_id'];
                $ownerId = $contents['data']['owner_id'];
                $response = Course::getAnswers($testId, $ownerId);
                echo json_encode($response);
            } elseif ($action === Actions::GET_RESULTS->value) {
                $testId = $contents['data']['test_id'];
                $response = Course::getResults($testId);
                echo json_encode($response);
            } elseif ($action === Actions::GET_MESSAGES->value) {
                $teacherId = $contents['data']['teacher_id'];
                $response = Course::getMessages($teacherId);
                echo json_encode($response);

            } else {
                echo json_encode(new CourseServiceResponse(false, "invalid request", null));
            }


        }
    } catch (Exception $exception) {
        echo json_encode(new CourseServiceResponse(false, "error happened: " . $exception->getMessage(), null));
    }
} else {
    echo json_encode(new CourseServiceResponse(false, "invalid content", null));
}


