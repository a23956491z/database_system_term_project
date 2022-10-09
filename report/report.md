[TOC]

## Intro

In order to containerize our application and make it easy to develope and deploy, we would use docker to prepare the enrionment in this project.

We omit the trival docker install part in this report due to my docker already installed in my local in the time I start this project.

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