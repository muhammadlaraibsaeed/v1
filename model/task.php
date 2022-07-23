<?php 
// Task Model Object

// empty TaskException class so we can catch task errors
class TaskException extends Exception { }

class Task {
	// define private variables
	// define variable to store task id number
	private $_id;
	// define variable to store task name
	private $_name;
	// define variable to store task fname
	private $_fname;
	// define variable to store task dob date
	private $_dob;
	// define variable to store task age
	private $_age;
	// define variable to store class classname
	private $_classname;

	// define variable to store section name
	private $_sname;

  
  
  // constructor to create the task object with the instance variables already set
	public function __construct($id, $name, $fname, $dob, $age,$classname,$section_name) {
		$this->setID($id);
		$this->setname($name);
		$this->setfname($fname);
		$this->setdob($dob);
		$this->setage($age);
		$this->setclass($classname);
		$this->setsection($section_name);
	}
  
  // function to return task ID
	public function getID() {
		return $this->_id;
	}
  
  // function to return task name
	public function getname() {
		return $this->_name;
	}
  
  // function to return task fname
	public function getfname() {
		return $this->_fname;
	}
  
  // function to return task dob
	public function getdob() {
		return $this->_dob;
	}
  
  // function to return task age
	public function getage() {
		return $this->_age;
	}

	// function to return 
	public function getclass() {
		return $this->_classname;
	}

	public function getsection() {
		return $this->_sname;
	}
  
	// function to set the private task ID
	public function setID($id) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(($id !== null) && (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this->_id !== null)) {
			throw new TaskException("Task ID error");
		}
		$this->_id = $id;
	}
  
  // function to set the private task name
	
  	public function setname($name) {
		// if passed in name is not between 1 and 255 characters
		if(strlen($name) < 1 || strlen($name) > 255) {
			throw new TaskException("Name error");
		}
		$this->_name = $name;
	}
  
  // function to set the private task fname
	
  public function setfname($fname) {
	// if passed in name is not between 1 and 255 characters
		if(strlen($fname) < 1 || strlen($fname) > 255) {
			throw new TaskException("Father Name error");
		}
		$this->_fname = $fname;
	}
  
  // public function to set the private task dob date and time 

  public function setdob($dob) {
		// make sure the value is null OR if not null validate date and time passed in, must create date time ok and still match the same string passed (e.g. prevent 31/02/2018)
		if(($dob !== null) &&   date_format(date_create_from_format('d/m/Y', $dob), 'd/m/Y') != $dob) {
			throw new TaskException("task dob date");
	  }
	  $this->_dob = $dob;
	}
	
	// function to set the private task age
	
	public function setclass($classname) {
	// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
		// over nine quintillion rows
		if(strlen($classname) < 1 || strlen($classname) > 255) {
			throw new TaskException("class name error");		
		}
		$this->_classname = $classname;
	}

	public function setsection($section_name) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
			// over nine quintillion rows
			if($section_name===null){
				$section_name = "mixed section";
			}
			if( strlen($section_name<1) || strlen($section_name) > 255 ) {
				throw new TaskException("section name error");		
			}
			$this->_sname = $section_name;
		}
  
	// function to set private variable 

	public function setage($age) {
		// if passed in task ID is not null or not numeric, is not between 0 and 9223372036854775807 (signed bigint max val - 64bit)
			// over nine quintillion rows
			if(($age !== null) && (!is_numeric($age) || $age <= 0 || $age > 9223372036854775807 || $age==null )) {
				throw new TaskException("Age error");
			}
			$this->_age = $age;
		}
  
  // function to return task object as an array for json
	public function returnTaskAsArray() {
		$task = array();
		$task['id'] = $this->getID();
		$task['Name'] = $this->getname();
		$task['Father name'] = $this->getfname();
		$task['Date Of Birth'] = $this->getdob();
		$task['Age'] = $this->getage();
		$task['class name'] = $this->getclass();
		$task['section name'] = $this->getsection();
		return $task;
	}
  
}