<?php

require_once('db.php');
require_once('../model/task.php');
require_once('../model/response.php');

// attempt to set up connections to read and write db connections
try {
  $writeDB = DB::connectWriteDB();
  $readDB = DB::connectReadDB();
}
catch(PDOException $ex) {
  // log connection error for troubleshooting and return a json error response
  error_log("Connection Error: ".$ex, 0);
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("Database connection error");
  $response->send();
  exit;
}

// within this if/elseif statement, it is important to get the correct order (if query string GET param is used in multiple routes)
// check if taskid is in the url e.g. /tasks/1

// check if taskid is in the url e.g. /tasks/1
if (array_key_exists("studentid",$_GET)) {
  $studentid = $_GET['studentid'];
  // get task id from query string
  //check to see if task id in query string is not empty and is number, if not return json error
  if($studentid == '' || !is_numeric($studentid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Task ID cannot be blank or must be numeric");
    $response->send();
    exit;
  }
  
  // if request is a GET, e.g. get task
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    // attempt to query the database
    try {
      // create db query
      
      $query = $readDB->prepare('SELECT s.id AS id , s.NAME AS name , s.fname AS fname, DATE_FORMAT(s.birthday, "%d/%m/%Y") AS dob, s.age AS age,c.classname AS classname ,se.sname AS section_name FROM student AS s INNER JOIN class AS c ON  s.classid=c.id INNER JOIN section as se ON se.id=s.sectionid  where s.id = :stduentid');
      $query->bindParam(':stduentid', $studentid, PDO::PARAM_INT);
  		$query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // create task array to store returned task
      $taskArray = array();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Such id student is not found");
        $response->send();
        exit;
      }
      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new task object for each row
        $task = new Task($row['id'], $row['name'], $row['fname'], $row['dob'], $row['age'],$row['classname'],$row["section_name"]);
        // create task and store in array for return in json data
  	    $taskArray[] = $task->returnTaskAsArray();
      }

      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['student detail'] = $taskArray;
      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(TaskException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to get task");
      $response->send();
      exit;
    }
  }
  // else if request if a DELETE e.g. delete task
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // attempt to query the database
    try {
      // create db query
      $query = $writeDB->prepare('delete from student where id = :stduentid');
      $query->bindParam(':stduentid', $studentid, PDO::PARAM_INT);
      $query->execute();
      // get row count
      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Student is not found");
        $response->send();
        exit;
      }
      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Student at id". $studentid." deleted successfully");
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to delete task");
      $response->send();
      exit;
    }
  }
  // handle updating task
  elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'||$_SERVER['REQUEST_METHOD'] === 'PUT') {
    // update task
    try {
      // check request's content type header is JSON
      if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        // set up response for unsuccessful request
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content Type header not set to JSON");
        $response->send();
        exit;
      }
      
      // get PATCH request body as the PATCHed data will be JSON format
      $rawPatchData = file_get_contents('php://input');
      
      if(!$jsonData = json_decode($rawPatchData)) {
        // set up response for unsuccessful request
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
      }
      
      // set task field updated to false initially
      $name_updated = false;
      $fname_updated = false;
      $dob_updated = false;
      $age_updated = false;
      
      // create blank query fields string to append each field to
      $queryFields = "";
      
      // check if name exists in PATCH
      if(isset($jsonData->name)) {
        // set name field updated to true
        $name_updated = true;
        // add name field to query field string
        $queryFields .= "name = :name, ";
      }
      
      // check if fname exists in PATCH
      if(isset($jsonData->fname)) {
        // set fname field updated to true
        $fname_updated = true;
        // add fname field to query field string
        $queryFields .= "fname = :fname, ";
      }
      
      // check if dob exists in PATCH
      if(isset($jsonData->dob)) {
        // set dob field updated to true
        $dob_updated = true;
        // add dob field to query field string
        $queryFields .= "dob = STR_TO_DATE(:dob, '%d/%m/%Y %H:%i'), ";
      }
      
      // check if age exists in PATCH
      if(isset($jsonData->age)) {
        // set age field updated to true
        $age_updated = true;
        // add age field to query field string
        $queryFields .= "age = :age, ";
      }
      
      // remove the right hand comma and trailing space
      $queryFields = rtrim($queryFields, ", ");
      
      // check if any task fields supplied in JSON
      if($name_updated === false && $fname_updated === false && $dob_updated === false && $age_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No task fields provided");
        $response->send();
        exit;
      }
      
      // create db query to get task from database to update - use master db
      $query = $writeDB->prepare('SELECT id, name, fname, DATE_FORMAT(dob, "%d/%m/%Y %H:%i") as dob, age from tbltasks where id = :taskid');
      $query->bindParam(':taskid', $taskid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // make sure that the task exists for a given task id
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No task found to update");
        $response->send();
        exit;
      }
      
      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new task object
        $task = new Task($row['id'], $row['name'], $row['fname'], $row['dob'], $row['age'],$row['class_name'],$row["section_name"]);
      }
      
      // create the query string including any query fields
      $queryString = "update tbltasks set ".$queryFields." where id = :taskid";
      // prepare the query
      $query = $writeDB->prepare($queryString);
      
      // if name has been provided
      if($name_updated === true) {
        // set task object name to given value (checks for valid input)
        $task->setname($jsonData->name);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_name = $task->getname();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':name', $up_name, PDO::PARAM_STR);
      }
      
      // if fname has been provided
      if($fname_updated === true) {
        // set task object fname to given value (checks for valid input)
        $task->setfname($jsonData->fname);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_fname = $task->getfname();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':fname', $up_fname, PDO::PARAM_STR);
      }
      
      // if dob has been provided
      if($dob_updated === true) {
        // set task object dob to given value (checks for valid input)
        $task->setdob($jsonData->dob);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_dob = $task->getdob();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':dob', $up_dob, PDO::PARAM_STR);
      }
      
      // if age has been provided
      if($age_updated === true) {
        // set task object age to given value (checks for valid input)
        $task->setage($jsonData->age);
        // get the value back as the object could be handling the return of the value differently to
        // what was provided
        $up_age= $task->getage();
        // bind the parameter of the new value from the object to the query (prevents SQL injection)
        $query->bindParam(':age', $up_age, PDO::PARAM_STR);
      }
      
      // bind the task id provided in the query string
      $query->bindParam(':taskid', $taskid, PDO::PARAM_INT);
      // run the query
    	$query->execute();
      
      // get affected row count
      $rowCount = $query->rowCount();

      // check if row was actually updated, could be that the given values are the same as the stored values
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Task not updated - given values may be the same as the stored values");
        $response->send();
        exit;
      }
      
      // create db query to return the newly edited task - connect to master database
      $query = $writeDB->prepare('SELECT id, name, fname, DATE_FORMAT(dob, "%d/%m/%Y %H:%i") as dob, age from tbltasks where id = :taskid');
      $query->bindParam(':taskid', $taskid, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();

      // check if task was found
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No task found");
        $response->send();
        exit;
      }
      // create task array to store returned tasks
      $taskArray = array();

      // for each row returned
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new task object for each row returned
        $task = new Task($row['id'], $row['name'], $row['fname'], $row['dob'], $row['age'],$row['class_name'],$row["section_name"]);

        // create task and store in array for return in json data
        $taskArray[] = $task->returnTaskAsArray();
      }
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['tasks'] = $taskArray;

      // set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Task updated");
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(TaskException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to update task - check your data for errors");
      $response->send();
      exit;
    }
  }
  // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  } 
}

// handle getting all tasks or creating a new one
elseif(array_key_exists("classname",$_GET)){

      if(isset($_GET)) {
        // if request is a GET e.g. get tasks
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
          
          // attempt to query the database
          try {
            // create db query
          
            $search_class= $_GET['classname'];
            $query = $readDB->prepare('SELECT s.id AS id , s.NAME AS name , s.fname AS fname, DATE_FORMAT(s.birthday, "%d/%m/%Y") AS dob, s.age AS age,c.classname AS classname ,se.sname AS section_name FROM student AS s INNER JOIN class AS c ON  s.classid=c.id INNER JOIN section as se ON se.id=s.sectionid where c.classname=:search_classname');
            $query->bindParam(':search_classname', $search_class, PDO::PARAM_STR);
            $query->execute();

            // get row count
            $rowCount = $query->rowCount();

            // create task array to store returned tasks
            $taskArray = array();
            // for each row returned
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
              // create new task object for each row
              $task = new Task($row['id'], $row['name'], $row['fname'], $row['dob'], $row['age'],$row['classname'],$row["section_name"]);

              // create task and store in array for return in json data
              $taskArray[] = $task->returnTaskAsArray();
            }

            // bundle tasks and rows returned into an array to return in the json data
            $returnData = array();
            $returnData['rows_returned'] = $rowCount;
            $returnData['tasks'] = $taskArray;

            // set up response for successful return
            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnData);
            $response->send();
            exit;
          }
          // if error with sql query return a json error
          catch(TaskException $ex) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($ex->getMessage());
            $response->send();
            exit;
          }
          catch(PDOException $ex) {
            error_log("Database Query Error: ".$ex, 0);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to get tasks");
            $response->send();
            exit;
          }
  }
  // if any other request method apart from GET or POST is used then return 405 method not allowed
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit;
  } 
  }
} 
// else if request is a POST e.g. create task info post
elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    // create task
    try {
      // check request's content type header is JSON
      if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        // set up response for unsuccessful request
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content Type header not set to JSON");
        $response->send();
        exit;
      }
      
      // get POST request body as the POSTed data will be JSON format
      $rawPostData = file_get_contents('php://input');
      
      if(!$jsonData = json_decode($rawPostData)) {
        // set up response for unsuccessful request
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid JSON");
        $response->send();
        exit;
      }
    //  for getting id of class to insert in section and student
     if($jsonData->classname == "" ||strlen($jsonData->classname) < 1 || strlen($jsonData->classname) > 255){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("class name is manadatory");
        $response->send();
        return;
     } 

     $classname = strtolower($jsonData->classname); ;
      $query = $readDB->prepare("SELECT * from class WHERE classname = :classname");
      $query->bindParam(':classname', $classname, PDO::PARAM_STR);
      $query->execute();
      $row =$query->fetch(PDO::FETCH_ASSOC);
       if($row===false){
        $query = $readDB->prepare("INSERT INTO class(classname)VALUE(:classname)");
        $query->bindParam(':classname', $classname, PDO::PARAM_STR);
        $query->execute();
        $query = $readDB->prepare("SELECT * from class WHERE classname = :classname");
        $query->bindParam(':classname', $classname, PDO::PARAM_STR);
        $query->execute();
        $row =$query->fetch(PDO::FETCH_ASSOC);
     }

    //  for develop relation with student and section table
     $return_classname =$row['classname'];
     $return_classid =$row['id'];
     
     //  for getting id of section to insert in student
     $query = $readDB->prepare("SELECT * from section WHERE classid = :classid");
     $query->bindParam(':classid', $return_classid, PDO::PARAM_INT);
     $query->execute();
     $row =$query->fetch(PDO::FETCH_ASSOC);
     if($row===false){
       (empty($jsonData->section) ? $jsonData->section="mixed section":false);
          $sname=$jsonData->section;
          $query = $readDB->prepare("INSERT INTO section(sname,classid)VALUE(:sname,:classid)");
          $query->bindParam(':classid', $return_classid, PDO::PARAM_INT);
          $query->bindParam(':sname', $sname, PDO::PARAM_STR);
          $query->execute();
          $query = $readDB->prepare("SELECT * from section WHERE classid = :classid");
          $query->bindParam(':classid', $return_classid, PDO::PARAM_INT);
          $query->execute();
          $row =$query->fetch(PDO::FETCH_ASSOC);
     }
    //  for develop relation with student 
     $return_sectionid =$row['id'];
     $return_sectionname =$row['id'];

     if(!isset($jsonData->name) || !isset($jsonData->age)|| !isset($jsonData->classname) || !isset($jsonData->fname)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($jsonData->name) ? $response->addMessage("name field is mandatory and must be provided") : false);
        (!isset($jsonData->classname) ? $response->addMessage("classname field is mandatory and must be provided") : false);
        (!isset($jsonData->fname) ? $response->addMessage("father name (fname) field is mandatory and must be provided") : false);
        (!isset($jsonData->age) ? $response->addMessage("age field is mandatory and must be provided") : false);
        $response->send();
        exit;
      }
      
      // create new task with data, if non mandatory fields not provided then set to null
      $newTask = new Task(null, $jsonData->name, $jsonData->fname, (isset($jsonData->dob) ? $jsonData->dob : null), $jsonData->age,$return_classname,$return_sectionname);
      // get name, fname, dob, age and store them in variables
      $name = $newTask->getname();
      $fname = $newTask->getfname();
      $dob = $newTask->getdob();
      $age = $newTask->getage();
        // create db query
      $query = $writeDB->prepare('insert into student (name, fname,classid,age,sectionid) values (:name, :fname,:classid,:age,:sectionid)');
      $query->bindParam(':name', $name, PDO::PARAM_STR);
      $query->bindParam(':fname', $fname, PDO::PARAM_STR);
      $query->bindParam(':classid', $return_classid, PDO::PARAM_INT);
      $query->bindParam(':sectionid', $return_sectionid, PDO::PARAM_INT);
      $query->bindParam(':age', $age, PDO::PARAM_INT);
      $query->execute();
      $rowCount = $query->rowCount();
      // check if row was actually inserted, PDO exception should have caught it if not.
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to create task");
        $response->send();
        exit;
      }
      
      // get last task id so we can return the Task in the json
      $lastTaskID = $writeDB->lastInsertId();     
      // create db query to get newly created task - get from master db not read slave as replication may be too slow for successful read
      $query = $writeDB->prepare('SELECT s.id AS id , s.NAME AS name , s.fname AS fname, DATE_FORMAT(s.birthday, "%d/%m/%Y") AS dob, s.age AS age,c.classname AS classname ,se.sname AS section_name FROM student AS s INNER JOIN class AS c ON  s.classid=c.id INNER JOIN section as se ON se.id=s.sectionid where s.id=:taskid');
      $query->bindParam(':taskid', $lastTaskID, PDO::PARAM_INT);
      $query->execute();

      // get row count
      $rowCount = $query->rowCount();
      // make sure that the new task was returned
      if($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to retrieve task after creation");
        $response->send();
        exit;
      }
      
      // create empty array to store tasks
      $taskArray = array();

      // for each row returned - should be just one
      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new task object
        $task = new Task($row['id'], $row['name'], $row['fname'], $row['dob'], $row['age'],$row['classname'],$row["section_name"]);

        // create task and store in array for return in json data
        $taskArray[] = $task->returnTaskAsArray();
      }
      // bundle tasks and rows returned into an array to return in the json data
      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['tasks'] = $taskArray;

      //set up response for successful return
      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->addMessage("THAT STUDENT IS ADDED SUCCESSFULLY");
      $response->setData($returnData);
      $response->send();
      exit;      
 }
    // if task fails to create due to data types, missing fields or invalid data then send error json
    catch(TaskException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    // if error with sql query return a json error
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("line error".$ex->getLine()."message".$ex->getMessage());
      $response->send();
      exit;
    }
}

// return 404 error if endpoint not available
else {
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint not found  ".$_SERVER['REQUEST_METHOD']);
  $response->send();
  exit;
}