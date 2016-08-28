[![Amazon EC2](https://codeclimate.com/repos/57b56dabbd7db80dbe00344e/badges/5865eeec766a9fb5d895/gpa.svg)](https://codeclimate.com/repos/57b56dabbd7db80dbe00344e/feed)

# GUI ORM - A Web Interface for Doctrine : A Symfony ORM

This project is an use case of existing Doctrine ORM of symfony. It gives user a full control over "connected" database to the symfony app. This interface basically provides a GUI access for "connected" database over CLI. 

## Tech 
  - Symfony v3.1 (Stable) - PHP web framwork
  - PostgreSQL - A relational database
  - HTML, CSS3, Jquery, BootStrap - FrontEnd Technologies
  - Doctrine ORM

## Setting up the environment

### Setting up symfony
   Please refer to offcial symfony website. 
   
### Cloning the repository

You need git to clone this repository. You can get git from
[https://github.com/ssgaur/DBAsService.git](https://github.com/ssgaur/DBAsService.git).

### Clone gitdash

Clone the DBAsService git Repository [git]:

```
git clone https://github.com/ssgaur/DBAsService.git
cd DBAsService
```

### Install Dependencies

* Symfony depends upon composer to build dependencies. Run composer to gather the application dependencies

```
composer install 
```

### Change the parameters.yml file for database configuration

### Run the Application

We have preconfigured the project with a simple development web server.  The simplest way to start
this server is:

```
php bin/console server:run
```

Now browse to the app at `http://localhost:8000/`.



## Directory Layout

```

├── app
│   ├── config
│   ├── Resources
│   ├── AppCache.php
│   ├── AppKernel.php
│   └── autoload.php
├── web
│   ├── assets
│   ├── app.php
│   └── bundles
│   ├── app.php
│   └── ....
|	
|---src
├    ├──AppBundle
|   		├── Controller
├─  		├── Entity
|   		├── .....
├ ......
|---tests
├    ├──AppBundle
|   		├── Controller
|
├── README.md

```

## Testing

Two test case has been written in tests/AppBundle/Controller/DefaultController.php 


### Running Unit Tests


You can perform unit testing by using the command.

```
phpunit

```


