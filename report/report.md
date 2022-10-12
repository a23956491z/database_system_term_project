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
  password VARCHAR(30) NOT NULL,
  email VARCHAR(50) NOT NULL,
  reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

Before we interact with database, we should create a account for this web app to access the db data.

We add a user named "web_basic_client" to operate the login and register function in phpmyadmin interface.
This user have permission with `SELECT` and `INSERT` to data table.

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

## Auth : Login

### A basic Login page

This class is similar to the `Register` class but without check function.
We select the password field by username in table and check the password is equal to the password user submited.

In these page, we only show the login is success or not without provide the detail error message.
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