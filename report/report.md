[TOC]

## Intro

In order to containerize our application and make it easy to develope and deploy, we would use docker to prepare the enrionment in this project.

We omit the trival docker install part in this report due to my docker already installed in my local in the time I start this project.

[Dockerized php apache and mysql container](https://www.section.io/engineering-education/dockerized-php-apache-and-mysql-container-development-environment/)

# Setting up environment

## Basic PHP with docker

By define `docker-compose.yml`, we can use docker-compose to start docker container and bind the volumes in container to our disk.
We export the apache running port 80 to our host port 8000.

```yaml
version: '3.8'
services:
  php-apache-environment:
    container_name: php-apache
    image: php:8.0-apache
    volumes:
      - ./php/src:/var/www/html/
    ports:
      - 8000:80
```

After execute `docker-compose up`, we can host up the php+Apache environmnet.

We use this basic php file to test the funcionality of environment.

```html
<!DOCTYPE html>
<html>
<body>

<?php
echo "Hello World!";
?>

</body>
</html>
```

we would get a helloworld page like this.
![](https://i.imgur.com/CLAZVox.png)

## Build our mysql environment through docker-expose

we add following yml configuration in the `docker-compose.yml`

```yml
  db:
      container_name: db
      image: mysql
      restart: always
      environment:
          MYSQL_ROOT_PASSWORD: MYSQL_ROOT_PASSWORD
          MYSQL_DATABASE: MY_DATABASE
          MYSQL_USER: MYSQL_USER
          MYSQL_PASSWORD: MYSQL_PASSWORD
      ports:
          - "9906:3306"
```

Also we add a `./docker/Dockerfile` in the folder with `docker-compose.yml` to provide a custom image build.
In this dockerfile we build a image with `mysqli` extention installed on a `webdevops/php-apache` image on docker hub and upgrade the os system by the way.

```
FROM php:8.0-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get update && apt-get upgrade -y
```

In order to support the custom image with dockerfile, we add `build:` in  `php-apache-environment`  section.
Docker-compose would start the container on this image build by the dockerfile now.

```yml
    php-apache-environment:
        container_name: php-apache
        build:
            context: ./php
            dockerfile: Dockerfile
        depends_on:
            - db
        volumes:
            - ./php/src:/var/www/html/
        ports:
            - 8000:80
```

After built the environment, we can use `mysqli` in php to conect to mysql.

```php
//These are the defined authentication environment in the db service

// The MySQL service named in the docker-compose.yml.
$host = 'db';

// Database use name
$user = 'MYSQL_USER';

//database user password
$pass = 'MYSQL_PASSWORD';

// check the MySQL connection status
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected to MySQL server successfully!";
}
```

We can get a result on our `index.php` page like this to confirm the connection from php to mysql is successful.
![](https://i.imgur.com/FyV91nz.png)

## Add `phpmyadmin` to the environment and testing SQL queries

```yml
  phpmyadmin:
      image: phpmyadmin/phpmyadmin
      ports:
          - '9907:80'
      restart: always
      environment:
          PMA_HOST: db
      depends_on:
          - db
```

We can connect to phpmyadmin through [127.0.0.1:9907](127.0.0.1:9907)

The username of phpmyadmin is `root` and the password is `MYSQL_ROOT_PASSWORD` variable we set in `docker-compose.yml`.

We can type following SQL command in the SQL query section on site to test the functionality of mysql.

1. Drop the 'users' table first if exists any.
2. Create a 'users' table with auto incremental primary key `id` and `username`, `password` field in text.
3. Add some rows in the table.

```sql
drop table if exists `users`;
create table `users` (
    id int not null auto_increment,
    username text not null,
    password text not null,
    primary key (id)
);
insert into `users` (username, password) values
    ("admin","password"),
    ("Alice","this is my password"),
    ("Job","12345678");
```

![](https://i.imgur.com/UlgusTa.png)

We add extra database name in `mysqli` statement. And simply use `$result = $conn->query($sql)` to execute the SQL query storing result in `$result`. After that use `foreach` and `echo` to print out the result.

```php
//These are the defined authentication environment in the db service

// The MySQL service named in the docker-compose.yml.
$host = 'db';
// Database use name
$user = 'MYSQL_USER';
//database user password
$pass = 'MYSQL_PASSWORD';
// database name
$mydatabase = 'MY_DATABASE';
// check the mysql connection status

$conn = new mysqli($host, $user, $pass, $mydatabase);

// select query
$sql = 'SELECT * FROM users';

if ($result = $conn->query($sql)) {
    while ($data = $result->fetch_object()) {
        $users[] = $data;
    }
}

foreach ($users as $user) {
    echo "<br>";
    echo $user->username . " " . $user->password;
    echo "<br>";
}
```

![](https://i.imgur.com/hs8vOPS.png)

# Constructing PHP part

## Auth : Database

### Database with user data

![](https://i.imgur.com/QzeZ2wC.png)


To store the data of this web app, we create a database call `dinner_picker` in mysql.

The first table of this database is `user` table with following attributes:

* User id : unsigned int with auto increament which is also a **primary key**
* Username : varchar with max length 30 and **cannot be null**
* Password : varchar with max length 30 and **cannot be null**
* Email : varchar with max length 50 and **cannot be null**
* Register date : with CURRENT_TIMESTAMP when create the account.

The SQL query to create the table is this

```sql
CREATE TABLE users (
  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL,
  password VARCHAR(90) NOT NULL,
  email VARCHAR(50) NOT NULL,
  reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

Before we interact with database, we should create a account for this web app to access the db data.

We add a user named "web_basic_client" to operate the login and register function in phpmyadmin interface.
This user have permission with `SELECT` and `INSERT` to data table. We only grant very limit permissions for this user to prevent potential high privileges operation  by SQL injection like change admin password or exploit the vulnerability

of database itself.

![](https://i.imgur.com/RodV2Gg.png)

## Auth : Register

### Basic form and POST

We create a php file `/auth/register.php` to handle registering.
We write a simple form to get register data.

```html
<h1>This is the Register page</h1>

<form  action="register.php" method="post">
  Username: <input type="text" name="username"> <br>
  Password: <input type="password" name="password"> <br>
  Repeat Password: <input type="password" name="repeat_password"> <br>
  Email: <input type="text" name="email"> <br>
  <input type="submit">
</form>

<h1>Your data</h1>
```

use `$_POST` to get the POST request from the user filled form .

```php
echo "username:".($_POST["username"]                ?? "" )."<br>";
echo "password:".($_POST["password"]                ?? "" )."<br>";
echo "repeat_password:".($POST["repeat_password"]   ?? "" )."<br>";
echo "email:".($_POST["email"]                      ?? "" )."<br>";

```

So our register page is look like this currently.
![](https://i.imgur.com/yHG4Uu1.png)

### Check with field data

We do the following check to our field data before send the data to database.

* Check the field `username` is empty or not
* Check the field `password_1` and `password_2` is empty or not
* Check the field `email` is empty or not
* Check the `password_1` and `password_2` is equal(the same) or not.

```php
function register_data_static_check($username, $password_1, $password_2, $email){
  
    $define_error_msg["PASSWORD_NOT_MATCH"] = "Password and Repeat Password didn't match.";
    $define_error_msg["PASSWORD_EMPTY"] = "Password field is empty !";
    $define_error_msg["USERNAME_EMPTY"] = "Username field is empty !";
    $define_error_msg["EMAIL_EMPTY"] = "Email field is mepty !";

    $error_msg = "";

    // check empty
    if (empty($username)){
        $error_msg = $define_error_msg["USERNAME_EMPTY"];
    }
    if (empty($password_1) || empty($password_2)){
        $error_msg = $define_error_msg["PASSWORD_EMPTY"];
    }
    if (empty($email)){
        $error_msg = $define_error_msg["EMAIL_EMPTY"];
    }

    // check password match
    if ($password_1 != $password_2){
        $error_msg = $define_error_msg["PASSWORD_NOT_MATCH"];
    }

    return $error_msg;
}
```

A sample of error message might look like this.
![](https://i.imgur.com/0ILtWBs.png)

### Check exsitance of data in database

We do following check before insert the user data into database.

* Check the username is exists or not.
* Check the email is exists or not.

After these checks are passed, we can finally insert the user data into the database.

```php
Class Register{

    private $conn;
    private $define_error_msg;

    // return 1 if the check passed.
    private function _checker_username_exists($username){

        $sql = sprintf("SELECT username FROM users WHERE username = '%s'" , $username);

        if ($result = $this->conn->query($sql)) {

            if ($result->num_rows > 0) {

                return $this->define_error_msg["USERNAME_EXIST"];
            }
        }
        return "";
    }

    private function _checker_email_exists($email){

        $sql = sprintf("SELECT username FROM users WHERE email = '%s'" , $email);

        if ($result = $this->conn->query($sql)) {

            if ($result->num_rows > 0) {

                return $this->define_error_msg["EMAIL_EXIST"];
            }
        }
        return $this->failed_msg;
    }
  
    private function _register($username, $password, $email){
        $sql = sprintf("INSERT INTO users(username, password, email) VALUES('%s', '%s', '%s')",
                    $username, $password, $email);
  
        if ($this->conn->query($sql) === TRUE){
            echo $this->sucessful_msg;
        }
        return "";
    }
    function __construct() {
        $this->check_state = 0;
        $this->conn = connect_to_db();
  
        $this->define_error_msg["USERNAME_EXIST"] = "Username is already exists.";
        $this->define_error_msg["EMAIL_EXIST"] = "Email is aleardy exists..";
        $this->sucessful_msg = "Register Sucessfully !";
        $this->failed_msg = "Register Failed by unknowed reason !"; 
    }
```

A sample of error message may look like this.
![](https://i.imgur.com/INsDKu6.png)

### Provide basic security of password with hash

To prevent our databased get hacked and hacker can directly get all users' password.
We can hashed the password before we sent the register request. The password in database is also store in hashed form,
so that even hacker can get the data in database. The hacker still have to crack the hashed password to get the clear password.

However, Man In The Middle (MITM) attack can still get the password in the request client sent. We would add TLS support after we publish the website later to pathc out this vulnerability.

We can change the `_register` function a little bit by adding the `password_hash()` function

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = sprintf("INSERT INTO users(username, password, email) VALUES('%s', '%s', '%s')",
            $username, $hashed_password, $email);
```

## Auth : Login

### A basic Login page

This class is similar to the `Register` class but without check function.
We select the password field by username in table and check the password is equal to the password user submited.

In these page, we only show the login is success or not without provide the detail error message.

Note: This exmaple still use unhashed password in database.

```php
class Login{

    private $conn;

    function __construct(){
        $this->conn  = connect_to_db();
    }

    private function _login($username, $password){

        $sql = sprintf("SELECT password FROM users WHERE username = '%s'" , $username);

        if ($result = $this->conn->query($sql)) {

            if ($result->num_rows == 1) {

                $fetched_password = $result->fetch_assoc()["password"];
                if ($password === $fetched_password){
                    return 1;
                }
      
            }
        }

        return 0;
    }

    function login($username, $password){
        if($this->_login($username, $password) === 1){
            return "Logined!";
        }else{
            return "Login failed!";
        }
  
    }
}
```

### Verify with hashed password

with a little change, we can use `password_verify` instead of put password into where clause in SQL query.

```php
$result_arr = $result->fetch_array( MYSQLI_ASSOC);

if(password_verify($password, $result_arr["password"])){
  
    return $result_arr["id"];
}
```

### Sessions to stay login

By create  Login_Session object in first part of our php code, we can `session_start();` and use member function to get user info in session.

```php
class Login_Session{

    function __construct()
    {
        session_start();
    }

    function set_to_login($user, $id){

        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $id;
    }
    function get_user(){
        if (isset($_SESSION['user'])){
  
            return $_SESSION['user'];
        }
        return "";
    }
    function get_user_id(){
        if (isset($_SESSION['user_id'])){
  
            return $_SESSION['user_id'];
        }
        return "";
    }

    function set_to_logout(){
        unset($_SESSION['user']);
        unset($_SESSION['user_id']);
    }
}
```

### Redirect page by login session

we define 2 function to do the page redirection.
First one is adding `Location` in header before any render & output on the page.
Second one is using meta-header with page refresh to do redirection which is a little bit slower than the first one.

```php
function redirect($url, $statusCode = 303)
{
    header('Location: ' . $url, true, $statusCode);
    die();
}

function meta_redirect($URL= "/index.php"){
    echo sprintf("<meta http-equiv='refresh' content='0; URL=%s'>", $URL);
}
```

After define our redirect function, we can check if the user has been record in session.
Redirecting the page if a logined user is trying to visit login or register page.

Exmaple: in `login.php` or `register.php`

```php
$login_session = new Login_Session();
if(!empty($login_session->get_user())){
    redirect("/index.php");
}
```

### To prevent SQL injections

#### potential vulnerabilities from SQL injection

This is the code we validate the user credential in our login php file.

```php
$sql = sprintf("SELECT username FROM users WHERE username = '%s'" , $username);
```

We use a very common injection payload `' or 'x'='x` to bypass the authentication to get a logined sessionID to exploit other authenticated information.
![](https://i.imgur.com/rawsr2f.png)
![](https://i.imgur.com/ORxleFr.png)

With logined sessionID we can further dump out the system info and even the whole data table without credential of admin.

This is the system info we got from sql injection to our login page.
![](https://i.imgur.com/qNAEt9X.png)

We dump the whole `users` table by sql injection.
![](https://i.imgur.com/IuCma8j.png)

#### Protection to the authentication system

A SQL query like this can easily operate auth-by-pass injection like `' or ''-'` or `' or 'x'='x`

```php
$sql = sprintf("SELECT id FROM users WHERE username = '%s', password = 'password'" , $username, $password);
```

However, the modified version like this can be prevent the common auth-by-pass injection but not for any type of injections.

```php
$sql = sprintf("SELECT id,password FROM users WHERE username = '%s'" , ( $username));

if ($result = $this->conn->query($sql)) {

    if ($result->num_rows == 1) {

            $result_arr = $result->fetch_array( MYSQLI_ASSOC);
    
            if(password_verify($password, $result_arr["password"])){
        
                return $result_arr["id"];
            }
            ;
            // return $result_arr[0];
    
        }
  
  
}
```

`mysqli_real_escape_string`

## Dinner CRUD

### Dinner & Tag Schema

![](https://i.imgur.com/iMlXiON.png)


~~~sql
CREATE TABLE dinner (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_zh_0900_as_cs NOT NULL,
author INT(6) ,
CONSTRAINT DINNER_AUTHOR_ID_FK FOREIGN KEY (author) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE, 
create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
~~~

~~~sql
CREATE TABLE tag (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_zh_0900_as_cs NOT NULL
)
~~~

~~~sql
CREATE TABLE dinner_tag (
id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
dinner INT(6) UNSIGNED NOT NULL,
tag INT(6) UNSIGNED NOT NULL,
FOREIGN KEY (dinner) REFERENCES dinner(id) ON DELETE CASCADE ON UPDATE CASCADE, 
FOREIGN KEY (tag) REFERENCES tag(id) ON DELETE CASCADE ON UPDATE CASCADE
)
~~~

### A basic Dinner Object

Connect to MySQL in Dinner class constructor.
```php
class Dinner{

	private $conn;
	private $dinner_array;
	
	function __construct(){
		$this->conn = connect_to_db();
	
	}

}
```

### Create

Here comes the first function, we insert the dinner name & author into dinner table.
```php
// return the inserted dinner id
function _insert_dinner($dinner, $user){
	
	$sql = sprintf("INSERT INTO dinner (name, author) VALUES ('%s', '%s')", $dinner, $user);
	$this->conn->query($sql);

	return $this->conn->insert_id;
}
```

Later we insert tag into tag table. To prevent we insert the dulplicated rows we can check the tag if exists in tag table or not.
```php
// this function always return a tag id
// if the tag is not exist, this function would insert that tag into database.
function check_and_insert_tag($tag){

	$sql = sprintf("SELECT id FROM tag WHERE name='%s'", $tag );
	$result = $this->conn->query($sql);

	if($result->num_rows > 0){
	
		return $result->fetch_array(MYSQLI_NUM)[0];
	}

	$sql =sprintf("INSERT INTO tag (name) VALUES ('%s')", $tag );
	$this->conn->query($sql);

	return $this->conn->insert_id;
}
```

But after I finished this function, I found we can use `INSERT IGNORE` to prevent dulplicates.

And we can build the dinner-tag relation to implement many-to-many relation. 
```php
function _insert_dinner_tag($dinner, $tag){

	$sql = sprintf("INSERT INTO dinner_tag (dinner, tag) VALUES ('%s', '%s')", $dinner, $tag);

	$this->conn->query($sql);
}
```


Finally we can compose these sub-function to final insert_dinner function.
```php
function insert_dinner($data){

	// insert dinner with user and name
	$dinner_id = $this->_insert_dinner($data["name"], $data["user"]);

	// insert dinner-tag with dinner id and tag id
	foreach ($data["tags"] as &$tag){

		$tag_id = $this->check_and_insert_tag($tag);
		$this->_insert_dinner_tag($dinner_id, $tag_id);
	}
}
```

And we can use the `insert_dinner` function in `/dinner/insert.php`.
User can submit the insert form first and the PHP page gets POST request to start using `explode` and `implode` to process the tags string to tag array.
```php
<?php

include 'dinner.php';
include '../auth/session.php';

$login_session = new Login_Session();
if(empty($login_session->get_user())){
	redirect("/auth/login.php");
}
?>

  

<!DOCTYPE html>
<html>
<head>
	<title>Create Dinner</title>
</head>
<body>
<h1>Create!</h1>
<form action="insert.php" method="post">
	<div>
		<span>Dinner name </span><input type="text" name="name" required>
	</div>
	<div>
		<span>Tages </span><input type="text" name="tags">
	</div>
		<button type="submit" name="submit">Submit</button>
</form>

<?php

	$user = $login_session->get_user();
	$user_id = $login_session->get_user_id();

	if(empty($user)){
		redirect("/auth/login.php");
	}

	echo "<h1>Hi! User : ". $user. " with id : ". $user_id ."</h1>";

	if (!empty($_POST)){
  
		$tags = explode(",", $_POST["tags"]);
		$tags = array_map('trim', $tags);
		echo implode(' | ', $tags);

		$data = array(

			'name' => $_POST["name"],
			'tags' => $tags,
			'user' => $user_id
		);

		$dinner = new Dinner();
		$dinner->insert_dinner($data);
		meta_redirect("/dinner/index.php");
	}
?>

</body>
</html>
```

![](https://i.imgur.com/8JL77GH.png)

### Read

We can get a array contains the name of tags queued by dinner id. 
```php
// return a array with strings 
function get_tags($dinner_id){

	$sql = sprintf("SELECT * FROM dinner_tag INNER JOIN tag ON dinner_tag.tag=tag.id WHERE dinner='%s' ", $dinner_id );
	$result = $this->conn->query($sql);
	
	$tags = array();
	if($result->num_rows > 0){

		while($row = $result->fetch_assoc()){

			array_push($tags, $row["name"]);
		}
	}

	return $tags;
}
```

After that, we can use `JOIN` to combine dinner and users table to get username. Note the `LEFT JOIN` here to prevet there is null on dinner row so that we won't miss some rows.

Instead return the array, we encode the dictionary to json to make the response more RESTful and decode it later. 
```php
function get_dinners(){

	$sql = sprintf("SELECT dinner.id, name, users.username, users.id as uid FROM dinner LEFT JOIN users ON dinner.author=users.id" );
	$result = $this->conn->query($sql);

	$result_dic = array();

	if($result->num_rows > 0){

		$result_array = array();
		while($row = $result->fetch_assoc()){

			$tags = $this->get_tags($row["id"]);
			$row_result["id"] = $row["id"];
			$row_result["username"] = $row["username"];
			$row_result["dinner"] = $row["name"];
			$row_result["tags"] = $tags;

			array_push($result_array, $row_result);
		}
		$result_dic['status'] = "successful";
		$result_dic['response'] = $result_array;
		$dinner_array = $result_array;

	}else{

		$result_dic['status'] = "fail or no result";
		$result_dic['response'] = "";
	}

	// encode array to json with utf8 support
	$response_json = json_encode($result_dic, JSON_UNESCAPED_UNICODE);
	return $response_json;
}
```

Using the `foreach` to render each `<tr>` in table to dinner information.
```php
<?php

	include 'dinner.php';
	include '../auth/session.php';

	$login_session = new Login_Session();
	
	$dinner = new Dinner();
	$response = $dinner->get_dinners();

	$decoded = json_decode($response);
	$status = $decoded->status;
	$data = $decoded->response;
?>

<!DOCTYPE html>
<html>
<head>

<title>Dinner</title>

<style>
table, th, td {
	border: 1px solid black;
}
</style>
</head>

<body>

	<h1>Dinner!</h1>

<p><a href="/dinner/insert.php">Insert a new dinner</a></p>

  
<?php


	if(!empty($data)){
		echo "
		<table>
		<tr>
		<th>Dinner</th>
		<th>Author</th>
		<th>Tags</th>
		</tr>
		";

		foreach($data as &$d){

			echo "<tr>";
			echo sprintf(
			"
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s %s</td>
			",
			$d->dinner,
			$d->username,
			implode(", ", $d->tags));
			echo "</tr>";
		}
		echo "<table>";
	}else{
	
		echo "NO RESULT";
	}

	$user = $login_session->get_user();

	if(!empty($user)){

		echo "USER: ".$user, "<br/>";
		echo "<br><a href=\"/auth/logout.php\">Logout</a><br/>";
	}
	else{
		echo "<a href=\"/auth/login.php\">Login</a><br/>";
	}
?>
</body>
</html>
```

![](https://i.imgur.com/3tpjtZs.png)

### Update

Update the dinner table is simple. We can directly update the name column by id.
```php
function _update_dinner($id, $name){
	
	$sql = sprintf("UPDATE dinner SET name = '%s' WHERE id = %s ", $name, $id);
	$this->conn->query($sql);
}
```

To rebulit the many-to-many relation without recreate every tag. We delete all dinner-tag dependency by first and and the dependency  one by one later. 
```php
function _delete_dinner_tag_with_dinnerID($dinner){

	$sql = sprintf("DELETE FROM dinner_tag WHERE dinner= $dinner");
	$this->conn->query($sql);
}
```

Here we use `foreach` in tags array to create dinner-tag relation. 
```php
function update_dinner($data){
	
	$id = $data["id"];
	$tags = $data["tags"];
	$dinner_name = $data["name"];
	
	$this->_update_dinner($id, $dinner_name);
	$this->_delete_dinner_tag_with_dinnerID($id);
	
	foreach($tags as &$tag){
	
		$tag_id = $this->check_and_insert_tag($tag);		
		$this->_insert_dinner_tag($id, $tag_id);
	}
}
```

we can add update & delete button in `dinner/index.php`
```php
$delete_button = sprintf('<a href="/dinner/delete.php?id=%s">Delete</a>', $d->id);
$update_button = sprintf('<a href="/dinner/update.php?id=%s">Update</a>', $d->id);
```

To update the dinner data, we need the user id match the author's id to authenticate this operation.
If `$dinner_uid != $user_id` we return `NOT AUTHENTICATED` message.
```php
<?php

	include 'dinner.php';
	include '../auth/session.php';
	include_once '../utils/utils.php';
	
	$login_session = new Login_Session();
	if(empty($login_session->get_user())){
	
		redirect("/auth/login.php");
	}
	
	$user = $login_session->get_user();
	$user_id = $login_session->get_user_id();
	$dinner = new Dinner(); 
		
	$id = $_GET['id'] ?? null;
	$valid_dinner_id = False;
	$status_msg = "";
	
	  

	if(isset($id) and isInteger($id) ){

		$dinner_by_id = $dinner->_get_dinner($id);
		if(empty($dinner_by_id)){
	
			$status_msg = "DINNER NOT FOUND";
		}else{

			$dinner_uid = $dinner_by_id["uid"];
			if($dinner_uid != $user_id){

				$status_msg = "NOT AUTHENTICATED";
			}else{

				$dinner_uname = $dinner_by_id["username"];
				$dinner_name = $dinner_by_id["name"];
				$dinner_tags = $dinner->get_tags($id);

				$dinner_tags_string = implode(", ", $dinner_tags);
				echo $dinner_uid, " ", $dinner_uname;
			}
		}
	}else{
		$status_msg = "NOT VALID";
	}
		echo $status_msg;
?>

  

<!DOCTYPE html>
<html>
<head>
<title>Update Dinner</title>
</head>
<body>
<h1>UPDATE!</h1>
	<div>
		<a href="/dinner/index.php">BACK</a>
	</div>
	<form action="update.php" method="post">
		<input type="hidden" name="id" value="<?php echo $id ?? "";?>">
		<div>
			<span>Dinner name </span><input type="text" name="name" value="<?php echo $dinner_name ?? "";?>" required>
		</div>

		<div>
			<span>Tages </span><input type="text" name="tags" value="<?php echo $dinner_tags_string ?? "";?>">
		</div>
		<div>
			<span>Author </span>
		</div>
		<button type="submit" name="submit">Submit</button>

	</form>

<?php

	echo "<h1>Hi! User : ". $user. " with id : ". $user_id ."</h1>";
	if (!empty($_POST)){

		$tags = explode(",", $_POST["tags"]);
		$tags = array_map('trim', $tags);

		$data = array(
			'id' => $_POST['id'],
			'name' => $_POST["name"],
			'tags' => $tags,
		);

		$dinner->update_dinner($data);
		meta_redirect("/dinner/index.php");

	}

?>
</body>
</html>
```

![](https://i.imgur.com/dxh6GZd.png)

### Delete

To delete a dinner row is as simple as update. We can just delete the row by id and deal with the relation later.
```php
// INSERT INTO dinner (name, author) VALUES ("TO_BE_DELETED", 3) ;
// $dinner is dinner id
function _delete_dinner($dinner){

	$sql = sprintf("DELETE FROM dinner WHERE id = $dinner");
	$this->conn->query($sql);
}
```


```php
function delete_dinner($data){

	$user = $data["user"];
	$dinner = $data["dinner"];

	$result_dic = array();
	$dinner_by_id = $this->_get_dinner($dinner);
	$dinner_uid = $dinner_by_id["uid"] ?? null;

	if(empty($dinner_by_id)){

		$result_dic["status"] = "DINNER not found";
	}else{

		if ($dinner_uid == $user){
	
			$this->_delete_dinner($dinner);
			$result_dic["status"] = "successful";
		}else{

			$result_dic["status"] = "permission deny";
		}
	}

	$response_json = json_encode($result_dic, JSON_UNESCAPED_UNICODE);
	return $response_json;
}
```

```php
<?php

	include_once '../utils/utils.php';
	include_once '../auth/session.php';
	include_once 'dinner.php';

	$login_session = new Login_Session();
	$dinner = new Dinner();

	if(isset($_GET['id']) and isInteger($_GET['id']) ){
		$id = $_GET['id'];
		$user_id = $login_session->get_user_id();  

		$data = array(

			'dinner' => $id,
			'user' => $user_id
		);
		$decoded_response = json_decode($dinner->delete_dinner($data));
		$response = $decoded_response->status;
	}else{
	
		$response = "Invalid ID";
	}
	$uri_with_parameter = sprintf("/dinner/index.php?response=%s", $response);
	meta_redirect($uri_with_parameter);
?>
```

Because we redirect to dinner index page for all response. So we pass the response message as parameter, for exmaple, `/dinner/index.php?response="permission deny"`

We still need to check the current user is match to author's user id or not to confirm this user has the permission to delete.
![](https://i.imgur.com/D7s6oFL.png)
