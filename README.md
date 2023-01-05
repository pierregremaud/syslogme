# SysLogMe

PHP Authentication System 

## Description

A Web Site Template with Login, Signup, Account Verification via Google Gmail, Password Reset System, Remember Me Feature, CSRF Token and more.
<br />
All client/server communications made through REST API.
<br />
* Bootstrap latest version (5.3.x)
* Plain vanilla Javascript (no JQuery)
* PHP (8.1.0) 

## Installation

 1. Clone the repository to local drive

 2. Configure the MySQL database

 3. Verify PHP version

 4. Create a Client ID for Web application in Google Developpers Console

 5. Obtain a refresh token from Google for your application 

 6. Modify config.php to include all parameters

 7. You are done !!!

### 1. Clone the repository to local drive

Make sure that a local web server is installed (for example xampp on windows)
<br />
Try to access http://localhost/client/public/index.html with a web browser (for example chrome) 

### 2. Configure the MySQL database

Make sure that a mysql database is installed
<br />
Create an empty database called `syslogme`
<br />
Import the file `/server/config/database_backup.sql` into mysql database `syslogme`

### 3. Verify PHP version

Make sure that a PHP is installed (PHP version ">= 8.1.0")

### 4. Create a Client ID for Web application in Google Developpers Console

Follow instructions provided at point 3 of : https://www.w3jar.com/php-send-emails-using-phpmailer/
<br />
Do not forget to specify [http://localhost/server/get_oauth_token.php] as Authorized redirect URI

### 5. Obtain a refresh token from Google for your application 

Open a web browser and navigate to http://localhost/server/get_oauth_token.php
<br />
Follow instructions provided at point 4 of : https://www.w3jar.com/php-send-emails-using-phpmailer/
<br />
You should receive a refresh token from Google

### 6. Modify config.php to include all parameters

Complete all settings in config.php, especially the settings bellow given as example:

```
// MYSQL 
define('DB_TYPE', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'syslogme');      //change this value if needed
define('DB_CHARSET', 'utf8');
define('DB_USER', 'root');          //change this value if needed
define('DB_PASS', 'password');      //change this value if needed

// MAIL SYSTEM
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('OAUTH_USER_EMAIL', 'yourmail@gmail.com');  //change this value with the email you used on point 4 to authenticate to google developpers console
define('OAUTH_CLIENT_ID', '012345678901-abcdefghijklmnopqrstuvwxyzabcdef.apps.googleusercontent.com'); //change this value with the one found in google developpers console
define('OAUTH_SECRET_KEY', 'ABCDEF-1abcdefghijklmnopqrstuvwxyza');  //change this value with the one found in google developpers console
define('OAUTH_REFRESH_TOKEN', '1//09AbcdefghijKlmnopqrstuvwxyz-L9AbcdefghijKlmnopqrstuvwxyzAbcdefghijKlm-4-6AbcdefghijKlmnopqrstuvwxyz'); //change this value with the one  generated on point 5
```

## Optional : Update PHP dependencies using composer

Make sure that composer is installed
<br />
Open a command prompt on the local server and navigate to `/server` directory
<br />
Verify that composer.json exist and that it contains:

```
{
    "require": {
        "google/apiclient": "^2.0",
        "phpmailer/phpmailer": "^6.0",
        "league/oauth2-google": "^4.0"
    },
	"scripts": {
        "pre-autoload-dump": "Google\\Task\\Composer::cleanup"
    },
    "extra": {
        "google/apiclient-services": [
            "Gmail"
        ]
    }
}
```

run `composer update` from the `/server` directory
