# converter

## Deployment

The app can be deployed locally for testing and on AWS Cloud

### Local

#### Prerequisites

1. Pull the repository and navigate in terminal to its derictory

2. Deploy a mysql container

```bash
docker network create converter-net
docker run -p 3306:3306 --name=mysql1 --net converter-net -d mysql/mysql-server
```

Execute docker ps to monitor the STATUS. Wait untill it is (healthy) (This may take up to 5 minutes)

3. In terminal retrieve the generated password

```bash
docker logs mysql1 2>&1 | grep GENERATED
```

or

```cmd
docker logs mysql1
```

and search for GENERATED ROOT PASSWORD:

4. Allow the host's root user to have priveleges for the database

```bash
docker exec -it mysql1 mysql -uroot -p
ALTER USER 'root'@'localhost' IDENTIFIED BY 'password';
CREATE USER 'root'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
exit
```

5. Edit config.php, setting the values to these variables

```php
public static $DB_HOST = "mysql1";
public static $DB_PORT = 3306;
public static $DB_NAME = "converter";
public static $DB_USER = "root";
public static $DB_PASS = "password";
```

#### Build & deploy

1. build the container image:

```bash
docker build -t converter-app .
```

2. Deploy the container and execute the database setup script

```bash
docker run -tid -p 80:80 --name converter -v <path_to_converter>\converter:/var/www/html --net converter-net converter-app
docker exec converter php ./db_scripts/setup_database.php
```

3. If all is successful the application is available at http://localhost/converter.php

### On AWS cloud

#### Prerequisites

1. Create a VPC and allow DNS assignments for instances
2. Create 4 subnets - two public and two private
3. Create an Internet gateway and associate it with the VPC
4. Create two Security groups - one for the app, the other for the database
5. Create two route configurations associated with the public and private subnets
6. Deploy an RDS isntances in the VPC and take note of the password and host dns
7. Create an S3 bucket
8. Create a IAM role with S3FullAccess permission

#### Deploy EC2 instance

1. Set the public subnet and enable public ip assignment
2. Attach the create IAM role for S3
3. Paste the user data located in user-data file:

 * Set the proper database credentials and host, and set the bucket name. This script will also install the AWS PHP SDK in the container and initialize the database.
 * When the deployment is complete, it can be accessed in http://<ec2_host>/converter.php