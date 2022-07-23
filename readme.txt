Add student data into database
http://localhost/v1/addinfo

use these variables to post data in databsae
classname
section
name
fname
age
dob

Read all students in specific class
http://localhost/v1/search/classname

FOR GET , delete and patch single data from databaase 
http://localhost/v1/student/studentid

use following variable for put or patch data
classname
sectionname
name
fname
dob
age

FOR  delete  class from databaase 
Note:
if you delete sppecific class from 
the database in class table all student and section related to this class also 
deleted
http://localhost/v1/class/classid

for UPDATED , DELETED SESSION IN section table
Note:
class name and section is necessary for updated section

http://localhost/v1/section/sectionid

use following variable for Patch and put data
classname
sectionname



