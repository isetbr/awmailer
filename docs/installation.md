Installation
============

Overview
--------

1. Packages / Dependencies
2. Downloading and checkout
3. Database
4. Installing and Configuring
5. Tests
6. Web Server
7. Starting services
8. Documentation

## 1. Packages / Dependencies

Make sure that you solve all dependecies and requirements of AwMailer, check the document of [requirements](requirements.md) to help you with that you have to do before install AwMailer.

## 2. Downloading and checkout

To download AwMailer you have two options, you can clone this repository and checkout to the tag of version that you wish to install or download the zip file of project, in this guide we will clone this repository, see the codes below:

```shell
# Go to install dir *
cd /usr/local

# Clone the repository
git clone git@192.168.0.14:devsdmf/awmailer.git awmailer

# Go to awmailer directory
cd awmailer/

# Checkout to the version that you wish to install, e.g. v1.0.0-stable
git checkout tags/vX.Y.Z-release

# Check for dependecies **
make check
```

\* You can install in your prefer location, we use /usr/local as a commom location for installed applications in linux environments.

** If you get a error on aglio and you don't wish the documentation of API acessible in a browser, you can ignore this.

## 3. Database

You need to create the MySQL database and users before install the AwMailer, see below how you do this:

```shell
# Login to MySQL
mysql -u root -p

# Type the MySQL root password

# Create the user that will used by AwMailer (we recommend to not use the root user)
# do not type the 'mysql>', this is a part of the prompt
# change $password in the command below to a real password you pick
mysql> CREATE USER 'awmailer'@'localhost' IDENTIFIED BY '$password';

# Ensure you can use the InnoDB engine which is necessary to support long indexes
# If this fails, check your MySQL config files (e.g. `/etc/mysql/*.cnf`, `/etc/mysql/conf.d/*`) for the setting "innodb = off"
mysql> SET storage_engine=INNODB;

# Create a database that awmailer will use
mysql> CREATE DATABASE IF NOT EXISTS `awmailer` DEFAULT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci`;

# Grant the AwMailer user necessary permissions on the database
mysql> GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES ON `awmailer`.* TO 'awmailer'@'localhost';

# Quit the database session
mysql> \q

# Try connecting to the new database with the new user
mysql -u awmailer -p -D awmailer

# Type the password you replaced $password with earlier

# You should now see a 'mysql>' prompt

# Quit the database session
mysql> \q

# Done!
```

## 4. Installing and Configuring

The AwMailer after the first stable release (v1.0.0-stable) gets a Makefile that makes the installation a easy process, then, you only need run the following commands in awmailer install folder.

```shell
# Assuming that you are in /usr/local/awmailer/
# Create folders, set permissions, generate binaries and install package dependencies *
make

# Open the configuration file of application and configure the lines below **:
# - base_url
# - mysql.user
# - mysql.password
# - mysql.dbname
# - mongo.dsn
# - mongo.dbname
vim app/config/application.ini

# Save the file and exit

# The run the command below to configure and create databases and tables
make db

# Install binaries (you may need run this command as root)
make install
```

\* If you get a error of XSL library not found and you don't wish to generate the documentation of source-code, you need to remove the dependency of PHPdocumentor of `composer.json` file and delete the `composer.lock` file.
** Remember to use the database settings configure in the past section, on the mongodb, don't worry, the installer will create the database and configure it for you, you only need to update this fields if your connection params are different from default.

## 5. Tests

The command below will run the tests in source of application to grants that all is working.

```shell
# Assuming that you are in /usr/local/awmailer/
# Make sure that all tests has passed!!
make test
```

## 6. Web Server

Now you need to configure your web server to turn the API available to consume, the AwMailer carry with it a sample vhost file to you configure the instance of awmailer on a Apache Web Server, you can find it in the support folder at root folder of application. 

Don't forget to update the paths and the ServerName of vhost.

After update your vhost file and enable it in your Apache, restart the WebServer, the API is now accessible.

## 7. Starting Services

To start the AwMailer daemon, only type the following command on terminal

```shell
$ awd
```

## 8. Documentation

To generate the documentation of your AwMailer instance, you can do this of two ways, the first is to generate the API and Sourcecode documentation using the Makefile, the two way is manually generate on of each options.

To do this with the Makefile, make sure that you have solved all dependecies to generate both documentations, and type the following command on terminal:

```shell
## Assuming that you are in /usr/local/awmailer/
make docs
```

To generate one of each options you can type the commands below:

```shell
# To generate the documentation of API only
rm -Rf web/docs/api/*
aglio -t slate -i blueprint.apib -o web/docs/api/index.html > /dev/null 2>&1

# To generate the documentation of sourcecode only
rm -Rf web/docs/source/*
./vendor/bin/phpdoc.php --force > /dev/null 2>&1
```