<?php
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/Anonymous_e_assessment/';
require_once $basePath . 'db-connect.php';
$GLOBALS['longAnswerQuestionTable'] = "longAnswerQuestions";
$GLOBALS['multipleChoiceQuestionTable'] = "multipleChoiceQuestions";
$GLOBALS['longAnswerTable'] = "longAnswerAnswers";
$GLOBALS['multipleChoiceAnswerTable'] = "multipleChoiceAnswers";
$GLOBALS['resultTable'] = "results";
$GLOBALS['testsTable'] = "tests";
$GLOBALS['dbConnect'] = new DbConnect();

class Course implements JsonSerializable
{
    public static function fetchQuestions($test_id)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $longQuestionsQuery = 'SELECT * FROM  ' . $GLOBALS['longAnswerQuestionTable'] . " WHERE t_id = " . $test_id;
        $multiChoiceQuestionsQuery = 'SELECT * FROM  ' . $GLOBALS['multipleChoiceQuestionTable'] . " WHERE t_id = " . $test_id;
        $longResult = mysqli_query($db, $longQuestionsQuery);
        $mutliResutl = mysqli_query($db, $multiChoiceQuestionsQuery);

        return $test_id;
    }

    public static function getAvailableCourses()
    {
        $retrieveCoursesQuery = "SELECT * FROM " . $GLOBALS['testsTable'];
        $db = $GLOBALS['dbConnect']->getDb();
        $result = mysqli_query($db, $retrieveCoursesQuery);
        mysqli_close($db);
        if ($result->num_rows > 0) {
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = array(
                    't_id' => $row['t_id'],
                    'owner_id' => $row['owner_id'],
                    'description' => $row['description']
                );
            }
            return new CourseServiceResponse(true, "fetched courses successfully", $courses);
        } else {


        }

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
     * @return array
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param array $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
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

    private $questions = [];
    private $answers = [];
    private $t_id;
    private $owner_id;
    private $description;

    public static function createTest(Course $course)
    {
        $db = $GLOBALS['dbConnect']->getDb();
        $_owner_id = $course->getOwnerId();
        $_description = $course->getDescription();
        $testQuery = "insert into " . $GLOBALS['testsTable'] . " (owner_id, description) values ('$_owner_id','$_description')";
        $testCreated = mysqli_query($db, $testQuery);
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

    public static function getInstance($data): Course
    {
        $course = new Course();
        if (isset($data['t_id']))
            $course->setTId($data['t_id']);
        if (isset($data['owner_id']))
            $course->setOwnerId($data['owner_id']);

        if (isset($data['description']))
            $course->setDescription($data['description']);
        if (isset($data['questions'])) {
            foreach ($data['questions'] as $questionData) {
                $question = new Question();
                if (isset($questionData['q_id']))
                    $question->setQId($questionData['q_id']);
                if (isset($questionData['t_id']))
                    $question->setTId($questionData['t_id']);
                if (isset($questionData['solution']))
                    $question->setSolution($questionData['solution']);
                if (isset($questionData['description']))
                    $question->setDescription($questionData['description']);
                if (isset($questionData['q_order']))
                    $question->setQOrder($questionData['q_order']);
                if (isset($questionData['choices']))
                    $question->setChoices($questionData['choices']);
                $course->questions[] = $question;
            }
        }
        if (isset($data['answers'])) {
            foreach ($data['answers'] as $answerData) {
                $answer = new Answer();
                $answer->setAId($answerData['a_id']);
                $answer->setDescription($answerData['description']);
                $answer->setOwnerId($answerData['owner_id']);
                $answer->setAOrder($answerData['a_order']);
                $course->answers[] = $answer;
            }
        }
        return $course;

    }

    public function jsonSerialize(): array
    {
       return [
           't_id'=>$this->t_id,
           'owner_id'=>$this->owner_id,
           'description'=>$this->description,
           'questions'=>$this->questions,
       ];
    }
}

class Question implements JsonSerializable
{


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

    private $q_id;
    private $t_id;
    private $q_order;
    private $longQuestion = true;
    private $description;
    private $solution;
    private $choices = [];

    public function jsonSerialize(): array
    {
        return [
            't_id' => $this->t_id,
            'q_id' => $this->q_id,
            'q_order' => $this->q_order,
            'description' => $this->description,
            'solution' => $this->solution,
            'choices' => $this->choices
        ];
    }
}

class Answer
{
    /**
     * @return mixed
     */
    public function getAId()
    {
        return $this->a_id;
    }

    /**
     * @param mixed $a_id
     */
    public function setAId($a_id)
    {
        $this->a_id = $a_id;
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
    public function getAOrder()
    {
        return $this->a_order;
    }

    /**
     * @param mixed $a_order
     */
    public function setAOrder($a_order)
    {
        $this->a_order = $a_order;
    }

    private $a_id;
    private $description;
    private $owner_id;
    private $a_order;

}


