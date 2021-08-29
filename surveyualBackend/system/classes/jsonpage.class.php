<?php

/**
 * Creates a JSON page based on the parameters
 *
 * @author Rajan Makh
 */
class JSONpage
{
    private $page;
    private $recordset;

    /**
     * @param array $pathArr - an array containing the route information
     * @param $recordset
     */
    public function __construct($pathArr, $recordset)
    {

        $this->recordset = $recordset;
        $path = (empty($pathArr[1])) ? "api" : $pathArr[1];

        switch ($path) {

            // API Welcome Endpoint
            case 'api':
                $this->page = $this->json_welcome();
                break;

            // Login Endpoint
            case 'login':
                $this->page = $this->json_login();
                break;

            // Registration Endpoint
            case 'registration':
                $this->page = $this->json_registration();
                break;

            //Submit Survey Endpoint
            case 'submitSurvey':
                $this->page = $this->json_submitSurvey();
                break;

            //Survey Link Endpoint
            case 'surveyLink':
                $this->page = $this->json_surveyLink();
                break;

            //Retrieve Questions Endpoint
            case 'retrieveQuestions':
                $this->page = $this->json_retrieveQuestions();
                break;

            //Submit Answers Endpoint
            case 'submitAnswers':
                $this->page = $this->json_submitAnswers();
                break;

            //Retrieve Response Endpoint
            case 'retrieveResponses':
                $this->page = $this->json_retrieveResponses();
                break;

            //All Survey Endpoint
            case 'allSurveys':
                $this->page = $this->json_allSurveys();
                break;

            //Stats - Total Surveys Endpoint
            case 'totalSurveys':
                $this->page = $this->json_totalSurveys();
                break;

//          //Test
//          case 'test':
//              $this->page = $this->json_test();
//              break;

            //Error
            default:
                $this->page = $this->json_error();
                break;
        }
    }

    /**
     * An arbitrary max length of 20 is set
     *
     * @param string $x
     *
     * @return string sanitised data
     */
    private function sanitiseString($x)
    {
        return substr(trim(filter_var($x, FILTER_SANITIZE_STRING)), 0, 20);
    }

    /**
     * An arbitrary max range of 1000 is set
     *
     * @param int $x
     *
     * @return int sanitised data
     */
    private function sanitiseNum($x)
    {
        return filter_var($x, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0, "max_range" => 1000000)));
    }

    /**
     * Returns the available endpoints on the api
     *
     * @return string available endpoints
     */
    private function json_welcome()
    {
        $msg = array(
            "message" => "Welcome to the API System for Surveyual",
            "developer" => "Rajan Makh",
            "endpoints" => "available endpoints are listed down below:",
            "api" => "/api",
            "login" => "/api/login",
            "registration" => "/api/registration",
            "submitSurvey" => "/api/submitSurvey",
            "surveyLink" => "/api/surveyLink",
            "retrieveQuestions" => "/api/retrieveQuestions",
            "submitAnswers" => "/api/submitAnswers",
            "retrieveResponses" => "/api/retrieveResponses",
            "allSurveys" => "/api/allSurveys",
            "totalSurveys" => "/api/totalSurveys",
            "error" => "/api/error");

        return json_encode($msg);
    }

    /**
     * Returns an error
     *
     * @return string error message
     */
    private function json_error()
    {
        $msg = array("message" => "An Error Occurred, Please Try Again.");
        return json_encode($msg);
    }

    public function get_page()
    {
        return $this->page;
    }

    /**
     * json_login
     *
     * Verifies and authenticates a user using JWT token || an message is displayed if there is an error with verification
     */
    private function json_login()
    {
        $msg = "Invalid Request. Email and Password Required";
        $status = 400;
        $token = null;
        $input = json_decode(file_get_contents("php://input"));

        if ($input) {

            if (isset($input->email) && isset($input->password)) {
                $query = "SELECT userID, username, email, password FROM Users WHERE email LIKE :email";
                $params = ["email" => $input->email];
                $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);
                $password = ($res['count']) ? $res['data'][0]['password'] : null;
                if (password_verify($input->password, $password)) {
                    $msg = "User Authorised. Welcome " . $res['data'][0]['email'];
                    $status = 200;

                    $token = array();
                    $token['email'] = $input->email;
                    $token['email'] = $res['data'][0]['email'];
                    $token['userID'] = $res['data'][0]['userID'];
                    $token['username'] = $res['data'][0]['username'];
                    $token['iat'] = time();
                    $token['exp'] = time() + (60 + 60);

                    $jwtkey = JWTKEY;
                    $token = \Firebase\JWT\JWT::encode($token, $jwtkey);

                } else {
                    $msg = "Email or Password is Invalid";
                    $status = 401;
                }
            }
        }

        return json_encode(array("status" => $status, "message" => $msg, "token" => $token));
    }

    /**
     * json_registration
     *
     * Registers a user based on the params
     *
     * @param string username
     * @param string email
     * @param string password
     *
     * @return string $res
     */
    private function json_registration()
    {

        $input = json_decode(file_get_contents("php://input"));

        $username = $input->username;
        $email = $input->email;
        $password = $input->password;

        // Check if the email exists in the database
        $emailCheckQuery = "SELECT * FROM Users WHERE email = :email;";
        $checkQueryParams = [":email" => $email];

        $resEmail = json_decode($this->recordset->getJSONRecordSet($emailCheckQuery, $checkQueryParams), true);

        if (isset($resEmail['data'][0]['email'])) {
            $res['status'] = 200;
            $res['message'] = "User Already Exists";

        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO Users (username, email, password ) VALUES (:username, :email, :password); ";
            $params = [":username" => $username, ":email" => $email, ":password" => $password_hash];
            $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);
        }

        $res['status'] = 200;
        $res['message'] = "Something Went Wrong";

        return json_encode($res);

    }

    /**
     * json_submitSurvey
     *
     * Submits survey based on the params
     *
     * @param string title
     * @param string description
     * @param string question
     * @param string file
     *
     * @return string $res
     */
    private function json_submitSurvey()
    {

        // Gets the input data entered in the Front-End
        $input = json_decode(file_get_contents("php://input"));
        $title = $_POST['title'];
        $description = $_POST['description'];
        $question = $_POST['question'];
        $file = $_FILES['file']['name'];

        // Gets the last SurveyID
        $query = "SELECT surveyID FROM Surveys ORDER BY surveyID DESC LIMIT 1;";
        $params = [];
        $resLastSurveyID = json_decode($this->recordset->getJSONRecordSet($query, $params), true);
        $currentSurveyID = $resLastSurveyID['data']['0']['surveyID'] + 1;

        // Gets the last QuestionID
        $query = "SELECT questionID FROM Questions ORDER BY questionID DESC LIMIT 1;";
        $params = [];
        $resLastQuestionID = json_decode($this->recordset->getJSONRecordSet($query, $params), true);
        $currentQuestionID = $resLastQuestionID['data']['0']['questionID'] + 1;

        // Create the Uploads Directory
        $uploadsDirectory = dirname(__FILE__, 2) . "/uploads";
        $surveyDirectory = $uploadsDirectory . "/" . $currentSurveyID;
        $questionDirectory = $surveyDirectory . "/" . $currentQuestionID;
//        $questionDirectory = $questionDirectory . "/";

        // File Permissions
        if (!file_exists($questionDirectory)) {
            mkdir($questionDirectory, 0777, true);
        }

        // File Checks
        $path = pathinfo($file);
        $filename = $path['filename'];
        $ext = $path['extension'];
        $temp_name = $_FILES['file']['tmp_name'];
        $path_filename_ext = $questionDirectory . "/" . $filename . "." . $ext;
        if (file_exists($path_filename_ext)) {
            // echo "Sorry, File Already Exists.";
        } else {
            move_uploaded_file($temp_name, $path_filename_ext);
            // echo "Congratulations! File Uploaded Successfully.";
        }
        $media_url = "uploads/" . $currentSurveyID . "/" . $currentQuestionID . "/" . $_FILES['file']['name'];

        // Insert survey data into the Survey table
        $createSurveyQuery = "INSERT INTO Surveys (surveyTitle, surveyDescription) VALUES (:surveyTitle, :surveyDescription);";

        $createSurveyParams =
            [
                ":surveyTitle" => $title,
                ":surveyDescription" => $description,
            ];

        $resCreateSurvey = json_decode($this->recordset->getJSONRecordSet($createSurveyQuery, $createSurveyParams), true);

        print_r($resCreateSurvey);

        // Insert question data into the Questions table
        $createQuestionQuery = "INSERT INTO Questions (question, mediaPath, surveyID) VALUES (:question, :media_url, :surveyID);";

        $createQuestionParams =
            [
                ":question" => $question,
                ":media_url" => $media_url,
                ":surveyID" => $currentSurveyID,
            ];

        $resCreateQuestion = json_decode($this->recordset->getJSONRecordSet($createQuestionQuery, $createQuestionParams), true);

        $res['status'] = 200;
        $res['filename'] = $file;
        $res['dirnametest'] = dirname(__FILE__, 2);
        $res['lastSurveyID'] = $resLastSurveyID;
        $res['lastQuestionID'] = $resLastQuestionID;
        $res['questionDirectory'] = $questionDirectory;

        return json_encode($res);
    }

    /**
     * json_surveyLink
     *
     * Gets the latest surveyID for the shareable link
     */
    private function json_surveyLink()
    {
        $query = "SELECT surveyID FROM Surveys ORDER BY surveyID DESC LIMIT 1";
        $params = [];

        $nextpage = null;

        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);

        $res['status'] = 200;
        $res['message'] = "ok";
        $res['next_page'] = $nextpage;
        return json_encode($res);
    }

    /**
     * json_retrieveQuestions
     *
     * Retrieves questions from Surveyual db using params
     *
     * @param string surveyID
     *
     * @return string $res
     */
    private function json_retrieveQuestions()
    {
        // Gets the input data from the Front-End
        $input = json_decode(file_get_contents("php://input"));
//      $surveyID = $_POST['surveyID'];
        $surveyID= $input->surveyID;

        // Gets Survey Details
        $surveyQuery = "SELECT surveyTitle, surveyDescription FROM Surveys WHERE surveyID = :surveyID;";
        $surveyParams = [
            ":surveyID" => $surveyID,
        ];
        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $resSurveyDetails = json_decode($this->recordset->getJSONRecordSet($surveyQuery, $surveyParams), true);
        $surveyTitle = $resSurveyDetails['data']['0']['surveyTitle'];
        $surveyDescription = $resSurveyDetails['data']['0']['surveyDescription'];

        // Gets Questions Details
        $questionsQuery = "SELECT questionID, question, mediaPath FROM Questions WHERE surveyID = :surveyID;";
        $questionsParams = [
            ":surveyID" => $surveyID,
        ];

        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $resQuestions = json_decode($this->recordset->getJSONRecordSet($questionsQuery, $questionsParams), true);
        $questionID = $resQuestions['data']['0']['questionID'];
        $question = $resQuestions['data']['0']['question'];
        $mediaPath = $resQuestions['data']['0']['mediaPath'];

        $nextpage = null;

        $res['status'] = 200;
        $res['message'] = "ok";
        $res['next_page'] = $nextpage;
        $res['surveyTitle'] = $surveyTitle;
        $res['surveyDescription'] = $surveyDescription;
        $res['questionID'] = $questionID;
        $res['question'] = $question;
        $res['mediaPath'] = $mediaPath;

        return json_encode($res);
    }

    /**
     * json_submitAnswers
     *
     * Submits survey answers based on the surveyID and questionID and stores it on Surveyual DB using params
     *
     * @param string surveyID
     * @param string questionID
     * @param string answer
     *
     * @return string $res
     */
    private function json_submitAnswers()
    {
        $input = json_decode(file_get_contents("php://input"));
        $surveyID= $input->surveyID;
        $questionID= $input->questionID;
        $answer = $input->answer;

        $query = "INSERT INTO Answers (surveyID, questionID, answer, userID) VALUES (:surveyID, :questionID, :answer, :userID);";

        $queryParams =
            [
                ":surveyID" => $surveyID,
                ":questionID" => $questionID,
                ":answer" => $answer,
                ":userID" => 1,
            ];

        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $resSubmitAnswers = json_decode($this->recordset->getJSONRecordSet($query, $queryParams), true);

        $nextpage = null;

        $res['status'] = 200;
        $res['message'] = "ok";
        $res['next_page'] = $nextpage;
        $res['resSubmitAnswers'] = $resSubmitAnswers;
        return json_encode($res);
    }

    /**
     * json_retrieveResponses
     *
     * Retrieves surveys, questions and answers
     *
     * @return string $res
     */
    private function json_retrieveResponses()
    {
        $query = "SELECT Surveys.surveyID, Surveys.surveyTitle, Surveys.surveyDescription, Questions.questionID, Questions.question, Answers.answerID, Answers.answer
          FROM Answers 
          JOIN Questions on (Answers.questionID = Questions.questionID) 
          JOIN Surveys on (Answers.surveyID = Surveys.surveyID)
          ORDER BY Surveys.surveyID DESC LIMIT 1";
        $params = [];
        $where = " WHERE ";
        $doneWhere = FALSE;

        // Retrieves questions and answers based on surveyID
        if (isset($_REQUEST['surveyID'])) {
            $where .= " Answers.surveyID = :surveyID ";
            $doneWhere = TRUE;
            $term = $this->sanitiseNum($_REQUEST['surveyID']);
            $params["surveyID"] = $term;
        }

        $query .= $doneWhere ? $where : "";

        $nextpage = null;

        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);

        $res['status'] = 200;
        $res['message'] = "ok";
        $res['next_page'] = $nextpage;
        return json_encode($res);
    }

    /**
     * json_allSurveys
     *
     * Displays all surveys
     *
     * @return string $res
     */
    private function json_allSurveys()
    {
        $query = "SELECT * FROM Surveys ORDER BY surveyID DESC LIMIT 1;";
        $params = [];

        $nextpage = null;

        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);

        $res['status'] = 200;
        $res['message'] = "ok";
        $res['next_page'] = $nextpage;
        return json_encode($res);
    }

    /**
     * json_totalSurveys
     *
     * Displays total number of surveys
     *
     * @return string $res
     */
    private function json_totalSurveys()
    {
        $query = "SELECT COUNT(surveyID) as totalSurveys FROM Surveys;";
        $params = [];

        $nextpage = null;

        // This decodes the JSON encoded by getJSONRecordSet() from an associative array
        $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);

        $res['status'] = 200;
        $res['message'] = "ok";
        $res['next_page'] = $nextpage;
        return json_encode($res);
    }

//    /**
//     * json_test
//     *
//     * Displays slots, questions, answers and authors information
//     * Displays survey, questions and answers
//     */
//    private function json_test()
//    {
//        $surveyQuery = "SELECT Surveys.surveyID, Surveys.surveyTitle, Surveys.surveyDescription
//        FROM Answers
//        JOIN Surveys ON (Answers.surveyID = Surveys.surveyID)";
//        $params = [];
//        $survey = $this->recordset->getJSONRecordSet($surveyQuery, $params);
//
//        $resultSurveyJsonDecoded = json_decode($survey, true);
//
//
//
//        foreach ($resultSurveyJsonDecoded["data"] as $surveyKey => $survey) {
//
//            $questionsQuery = "SELECT DISTINCT Questions.questionID, Questions.question
//            FROM Answers
//            JOIN Questions ON (Answers.questionID = Questions.questionID)
//            WHERE Answers.surveyID = :surveyID";
//            $params = ["surveyID" => $survey['surveyID']];
//            $questions = $this->recordset->getJSONRecordSet($questionsQuery, $params);
//
//            $resultQuestionsJsonDecoded = json_decode($questions, true);
//
//            //Join Data
//            $resultSurveyJsonDecoded["data"][$surveyKey]["questions"] = $resultQuestionsJsonDecoded["data"];
//
//        }
//
//        return json_encode(array("count" => $resultSurveyJsonDecoded["count"], "data" => $resultSurveyJsonDecoded["data"], "status" => 200, "message" => "ok"));
//    }

}
?>

