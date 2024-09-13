<?php
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/Anonymous_e_assessment/';
require_once $basePath . 'db-connect.php';
$GLOBALS['longAnswerQuestionTable'] = "longAnswerQuestions";
$GLOBALS['multipleChoiceQuestionTable'] = "multipleChoiceQuestions";
$GLOBALS['longAnswerTable'] = "longAnswerAnswers";
$GLOBALS['multipleChoiceAnswerTable'] = "multipleChoiceAnswers";
$GLOBALS['resultTable'] = "results";
$GLOBALS['testsTable'] = "tests";
$GLOBALS['messagesTable'] = "understandingMessages";
$GLOBALS['studentsTable'] = "students";
$GLOBALS['dbConnect'] = new DbConnect();

class Course implements JsonSerializable
{
    private $questions = [];
    private $t_id;
    private $owner_id;
    private $description;
    public static function getAllCourses(){
        $db = $GLOBALS['dbConnect']->getDb();
        $query = "SELECT * FROM ".$GLOBALS['testsTable'];
        $fetched = mysqli_query($db, $query);
        if ($fetched) {
            $courses = [];
            while ($row = $fetched->fetch_assoc()) {
                $courses[] = array(
                    "t_id" => $row['t_id'],
                    "owner_id"=> $row['owner_id'],
                    "description"=>$row['description']
                );
            }
            return new CourseServiceResponse(true, "fetched all courses", $courses);

        } else {
            return new CourseServiceResponse(false, "No courses Available", null);
        }
    }
    public static function markSeenMessages($lastMessageId, $teacherId)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $query = "UPDATE " . $GLOBALS['messagesTable'] . " SET seen = 1 WHERE m_id <= " .$lastMessageId." AND teacher_id = ".$teacherId;
        $updated = mysqli_query($db, $query);
        if ($updated) {
           return new CourseServiceResponse(true,"messages will not appear here in the next time",null);
        }else{
            return new CourseServiceResponse(false, "error in updating field seen", null);
        }

    }

    public static function getPrevTests($studentId)
    {
        $retrieveCoursesQuery = "SELECT " . $GLOBALS['testsTable'] . ".* " .
            "FROM " . $GLOBALS['resultTable'] . " " .
            "INNER JOIN " . $GLOBALS['testsTable'] . " " .
            "ON " . $GLOBALS['resultTable'] . ".t_id = " . $GLOBALS['testsTable'] . ".t_id " .
            "WHERE " . $GLOBALS['resultTable'] . ".owner_id = " . $studentId." ORDER BY r_id DESC ";

        return self::fetchCoursesFromDataBase($retrieveCoursesQuery);

    }

    /**
     * @param string $retrieveCoursesQuery
     * @return CourseServiceResponse
     */
    public static function fetchCoursesFromDataBase(string $retrieveCoursesQuery): CourseServiceResponse
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $result = mysqli_query($db, $retrieveCoursesQuery);
        mysqli_close($db);
        if ($result->num_rows > 0) {
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = array('t_id' => $row['t_id'], 'owner_id' => $row['owner_id'], 'description' => $row['description']);
            }
            return new CourseServiceResponse(true, "fetched courses successfully", $courses);
        } else {
            return new CourseServiceResponse(false, "No previous courses have fetched", null);
        }
    }

    public static function postMessage($message)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $studentId = $message['student_id'];
        $teacherId = $message['teacher_id'];
        $messageText = $message['message'];
        $query = "INSERT INTO " . $GLOBALS['messagesTable'] . " (student_id, teacher_id, message) VALUES ('$studentId','$teacherId',\"'$messageText'\")";
        $inserted = mysqli_query($db, $query);
        mysqli_close($db);
        if ($inserted) {
            return new CourseServiceResponse(true, "The message has sent successfully", null);
        } else {
            return new CourseServiceResponse(false, "unable to post message", null);
        }
    }

    public static function getMessages($teacherId)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $query = "SELECT " . $GLOBALS['messagesTable'] . ".*, " . $GLOBALS['studentsTable'] . ".sign " . " FROM " . $GLOBALS['studentsTable'] . " INNER JOIN " . $GLOBALS['messagesTable'] . " ON " . $GLOBALS['studentsTable'] . ".id = " . $GLOBALS['messagesTable'] . ".student_id WHERE teacher_id = " . $teacherId . " AND seen = 0";
        $result = mysqli_query($db, $query);

        if ($result->num_rows > 0) {
            $messages = [];
            while ($row = $result->fetch_assoc()) {

                $message = array("m_id" => $row['m_id'], "student_id" => $row['student_id'], "teacher_id" => $row['teacher_id'], "message" => $row['message'], "seen" => $row['seen'], "student_name" => $row['sign'], "fetched" => $row['fetched']);
                $messages[] = $message;
                $mId = $row['m_id'];
                $teacherId = $row['teacher_id'];
            }
            $updateQuery = "UPDATE " . $GLOBALS['messagesTable'] . " SET fetched = 1 WHERE m_id <= " . $mId . " AND teacher_id = " . $teacherId;
            $updated = mysqli_query($db, $updateQuery);

            return new CourseServiceResponse(true, "success", $messages);
        } else {
            return new CourseServiceResponse(false, "no messages available", null);
        }
    }

    public static function postResult($result)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $testId = $result['t_id'];
        $ownerId = $result['owner_id'];
        $score = $result['score'];
        $ownerName = $result['owner_name'];
        $query = "INSERT INTO " . $GLOBALS['resultTable'] . " (owner_id, t_id,owner_name, score) VALUES ('$ownerId','$testId','$ownerName','$score')";
        $inserted = mysqli_query($db, $query);
        if ($inserted) {
            return new CourseServiceResponse(true, "successfully posted a result", null);
        } else {
            return new CourseServiceResponse(false, "error while posting the result", null);
        }

    }

    public static function getResults($testId)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $query = "SELECT * FROM " . $GLOBALS['resultTable'] . " WHERE t_id = " . $testId;
        $result = mysqli_query($db, $query);
        mysqli_close($db);
        if ($result->num_rows > 0) {
            $results = [];
            while ($row = $result->fetch_assoc()) {
                $results[] = array('r_id' => $row['r_id'], 'owner_id' => $row['owner_id'], 'owner_name' => $row['owner_name'], 't_id' => $row['t_id'], 'score' => $row['score']);
            }
            return new CourseServiceResponse(true, "fetched results", $results);
        } else {
            return new CourseServiceResponse(false, "no results available", null);
        }
    }
    public static function getAllResults($ownerId)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $query = "SELECT * FROM " . $GLOBALS['resultTable'] . " WHERE owner_id = " . $ownerId." ORDER BY r_id DESC";
        $result = mysqli_query($db, $query);
        mysqli_close($db);
        if ($result->num_rows > 0) {
            $results = [];
            while ($row = $result->fetch_assoc()) {
                $results[] = array('r_id' => $row['r_id'], 'owner_id' => $row['owner_id'], 'owner_name' => $row['owner_name'], 't_id' => $row['t_id'], 'score' => $row['score']);
            }
            return new CourseServiceResponse(true, "fetched results", $results);
        } else {
            return new CourseServiceResponse(false, "no results available", null);
        }
    }

    public static function postAnswers($answers)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        foreach ($answers as $answer) {
            $ownerId = $answer['owner_id'];
            $description = $answer['description'];
            $aOrder = $answer['a_order'];
            $qId = $answer['q_id'];
            $tId = $answer['t_id'];
            if ($answer['a_type'] === "LONG") {
                $query = "INSERT INTO " . $GLOBALS['longAnswerTable'] . " (owner_id, t_id, q_id, description, a_order) VALUES ( '$ownerId', '$tId', '$qId', '$description', '$aOrder')";
            } else {
                $query = "INSERT INTO " . $GLOBALS['multipleChoiceAnswerTable'] . " (owner_id, t_id, q_id, description, a_order) VALUES ( '$ownerId', '$tId', '$qId', '$description', '$aOrder')";
            }
            mysqli_query($db, $query);
        }
        mysqli_close($db);
        return new CourseServiceResponse(true, "successfully posted answers", null);

    }

    public static function getAnswers($testId, $ownerId)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $queryL = "SELECT * FROM " . $GLOBALS['longAnswerTable'] . " WHERE t_id = " . $testId . " AND owner_id = " . $ownerId." ORDER BY a_id DESC";
        $queryM = "SELECT * FROM " . $GLOBALS['multipleChoiceAnswerTable'] . " WHERE t_id = " . $testId . " AND owner_id = " . $ownerId." ORDER BY a_id DESC";
        $multiResult = mysqli_query($db, $queryM);
        $longResult = mysqli_query($db, $queryL);
        mysqli_close($db);
        $answers = [];
        if ($longResult->num_rows > 0) {
            while ($row = $longResult->fetch_assoc()) {
                $answers[] = array('a_id' => $row['a_id'], 'description' => $row['description'], 'owner_id' => $row['owner_id'], 'a_order' => $row['a_order'], 'a_type' => "LONG", 'q_id' => $row['q_id'], 't_id' => $row['t_id']);
            }
        }
        if ($multiResult->num_rows > 0) {
            while ($row = $multiResult->fetch_assoc()) {
                $answers[] = array('a_id' => $row['a_id'], 'description' => $row['description'], 'owner_id' => $row['owner_id'], 'a_order' => $row['a_order'], 'a_type' => "MULTI", 'q_id' => $row['q_id'], 't_id' => $row['t_id']);
            }
        }
        if (isset($answers)) {
            usort($answers, function ($a, $b) {
                return $a['a_order'] <=> $b['a_order'];
            });
            return new CourseServiceResponse(true, "successfully fetched answers", $answers);
        } else {
            return new CourseServiceResponse(false, "error while fetching answers", null);
        }
    }

    public static function fetchQuestions($testId, $ownerId): CourseServiceResponse
    {
        $db = $GLOBALS['dbConnect']->getDb();
        if (isset($testId) and !isset($ownerId)) {
            $longQuestionsQuery = "SELECT * FROM  " . $GLOBALS['longAnswerQuestionTable'] . " WHERE t_id = " . $testId;
            $multiChoiceQuestionsQuery = "SELECT * FROM  " . $GLOBALS['multipleChoiceQuestionTable'] . " WHERE t_id = " . $testId;
        } elseif (isset($ownerId)) {
            $longQuestionsQuery = "SELECT * FROM  " . $GLOBALS['longAnswerQuestionTable'] . " WHERE owner_id = " . $ownerId;
            $multiChoiceQuestionsQuery = "SELECT * FROM  " . $GLOBALS['multipleChoiceQuestionTable'] . " WHERE owner_id = " . $ownerId;
        }
        $longResult = mysqli_query($db, $longQuestionsQuery);
        $multiResult = mysqli_query($db, $multiChoiceQuestionsQuery);
        mysqli_close($db);
        $questions = [];
        if ($longResult->num_rows > 0) {
            while ($row = $longResult->fetch_assoc()) {
                $temp = array(
                   "t_id"=>$row['t_id'] ,
                    "q_id"=>$row['q_id'],
                    "description"=>$row['description'],
                    "q_order"=>$row['q_order'],
                    "type"=>"LONG",
                    "solution"=>$row['solution']
                );
                $questions[] = $temp;
            }
        }
        if ($multiResult->num_rows > 0) {
            while ($row = $multiResult->fetch_assoc()) {
                $temp = array(
                    "t_id"=>$row['t_id'] ,
                    "q_id"=>$row['q_id'],
                    "description"=>$row['description'],
                    "q_order"=>$row['q_order'],
                    "choices"=>array(
                        $row['choice1'],
                        $row['choice2'],
                        $row['choice3']
                    ),
                    "type"=>"MULTI",
                    "solution"=>$row['solution']
                );
                $questions[] = $temp;
            }
        }
        usort($questions, function ($a, $b) {
            return $a['q_order'] <=> $b['q_order'];
        });
        if (isset($questions)) {
            return new CourseServiceResponse(true, "successfully fetched questions", $questions);
        } else {
            return new CourseServiceResponse(false, "error happened while fetching questions", null);
        }
    }

    public static function getAvailableCourses($ownerId)
    {
        if (isset($ownerId)) {
            $retrieveCoursesQuery = "SELECT * FROM " . $GLOBALS['testsTable'] . " WHERE owner_id = " . $ownerId;
        } else {
            $retrieveCoursesQuery = "SELECT * FROM " . $GLOBALS['testsTable'];
        }

        return self::fetchCoursesFromDataBase($retrieveCoursesQuery);
    }

    public static function createTest(Course $course)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $_owner_id = $course->getOwnerId();
        $_description = $course->getDescription();
        $testQuery = "insert into " . $GLOBALS['testsTable'] . " (owner_id, description) values ('$_owner_id','$_description')";
        $testCreated = mysqli_query($db, $testQuery);
        $tIdQuery = "SELECT * FROM " . $GLOBALS['testsTable'] . " WHERE description = '" . $course->getDescription() . "' LIMIT 1";
        $result = mysqli_query($db, $tIdQuery);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $tId = $row['t_id'];
            $course->setTId($tId);
        }
        foreach ($course->getQuestions() as $question) {
            $__t_id = $question->getTId();
            $__description = $question->getDescription();
            $__q_order = $question->getQOrder();
            $__solution = $question->getSolution();
            if ($question->isLongQuestion()) {
                $longQuestionQuery = "insert into " . $GLOBALS['longAnswerQuestionTable'] . " ( t_id, description, solution, q_order) values ('$__t_id','$__description','$__solution','$__q_order')";
                $longQuestionInserted = mysqli_query($db, $longQuestionQuery);

            } else {
                $__choices = $question->getChoices();
                $__choice1 = $__choices[0];
                $__choice2 = $__choices[1];
                $__choice3 = $__choices[2];
                $mutliChoiceQuery = "insert into " . $GLOBALS['multipleChoiceQuestionTable'] . " ( t_id, description, solution, q_order, choice1, choice2, choice3) values ('$__t_id','$__description','$__solution','$__q_order','$__choice1','$__choice2','$__choice3')";
                $multiChoiceQuestionInserted = mysqli_query($db, $mutliChoiceQuery);

            }

        }
        mysqli_close($db);
        if ($testCreated) {
            return new CourseServiceResponse(true, "course created", null);
        } else {
            return new CourseServiceResponse(false, "unable to create course", null);
        }

    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @param mixed $owner_id
     */
    public function setOwnerId($owner_id)
    {
        $this->owner_id = $owner_id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param array $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @return mixed
     */
    public function getTId()
    {
        return $this->t_id;
    }

    /**
     * @param mixed $t_id
     */
    public function setTId($t_id)
    {
        $this->t_id = $t_id;
        foreach ($this->questions as $question) {
            $question->setTId($this->getTId());
        }
    }

    public static function getInstance($data): Course
    {
        $course = new Course();
        if (isset($data['t_id'])) $course->setTId($data['t_id']);
        if (isset($data['owner_id'])) $course->setOwnerId($data['owner_id']);

        if (isset($data['description'])) $course->setDescription($data['description']);
        if (isset($data['questions'])) {
            foreach ($data['questions'] as $questionData) {
                $question = new Question();
                if (isset($questionData['q_id'])) $question->setQId($questionData['q_id']);
                if (isset($questionData['t_id'])) $question->setTId($questionData['t_id']);
                if (isset($questionData['solution'])) $question->setSolution($questionData['solution']);
                if (isset($questionData['description'])) $question->setDescription($questionData['description']);
                if (isset($questionData['q_order'])) $question->setQOrder($questionData['q_order']);
                if (isset($questionData['choices'])) $question->setChoices($questionData['choices']);
                $course->questions[] = $question;
            }
        }

        return $course;

    }

    public function jsonSerialize(): array
    {
        return ['t_id' => $this->t_id, 'owner_id' => $this->owner_id, 'description' => $this->description, 'questions' => $this->questions,];
    }
}

class Question implements JsonSerializable
{


    private $q_id;
    private $t_id;
    private $q_order;
    private $longQuestion = true;
    private $description;
    private $solution;
    private $choices = [];

    /**
     * @return mixed
     */
    public function getQOrder()
    {
        return $this->q_order;
    }

    /**
     * @param mixed $q_order
     */
    public function setQOrder($q_order)
    {
        $this->q_order = $q_order;
    }

    /**
     * @return bool
     */
    public function isLongQuestion(): bool
    {
        return $this->longQuestion;
    }

    /**
     * @param bool $longQuestion
     */
    public function setLongQuestion(bool $longQuestion)
    {
        $this->longQuestion = $longQuestion;
    }

    /**
     * @return mixed
     */
    public function getQId()
    {
        return $this->q_id;
    }

    /**
     * @param mixed $q_id
     */
    public function setQId($q_id)
    {
        $this->q_id = $q_id;
    }

    /**
     * @return mixed
     */
    public function getTId()
    {
        return $this->t_id;
    }

    /**
     * @param mixed $t_id
     */
    public function setTId($t_id)
    {
        $this->t_id = $t_id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getSolution()
    {
        return $this->solution;
    }

    /**
     * @param mixed $solution
     */
    public function setSolution($solution)
    {
        $this->solution = $solution;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @param array $choices
     */
    public function setChoices(array $choices)
    {
        $this->choices = $choices;
        $this->longQuestion = false;
    }

    public function jsonSerialize(): array
    {
        return ['t_id' => $this->t_id, 'q_id' => $this->q_id, 'q_order' => $this->q_order, 'description' => $this->description, 'solution' => $this->solution, 'choices' => $this->choices];
    }
}

