<?php

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
define('OAUTH_USER_EMAIL', 'yourmail@gmail.com');  //the email you used on point 4 to authenticate to google developpers console
define('OAUTH_CLIENT_ID', '012345678901-abcdefghijklmnopqrstuvwxyzabcdef.apps.googleusercontent.com'); //found in google developpers console
define('OAUTH_SECRET_KEY', 'ABCDEF-1abcdefghijklmnopqrstuvwxyza');  //found in google developpers console
define('OAUTH_REFRESH_TOKEN', '1//09AbcdefghijKlmnopqrstuvwxyz-L9AbcdefghijKlmnopqrstuvwxyzAbcdefghijKlm-4-6AbcdefghijKlmnopqrstuvwxyz'); //generated on point 5

// PHP
define('PHP_SESSION_VALIDITY', 3600); //1 HOUR
 
// LOGIN SYSTEM
define("TOKEN_TYPE_REMEMBER_ME", "remember_me"); //has to match t_token_type SQL table
define("TOKEN_TYPE_VERIFY_ACCOUNT", "verify_account"); //has to match t_token_type SQL table
define("TOKEN_TYPE_RESET_PASSWORD", "reset_password"); //has to match t_token_type SQL table
define('TOKEN_VALIDITY_REMEMBER_ME', 864000); //10 DAYS
define('TOKEN_VALIDITY_ACCOUNT_VERIFY', 3600); //1 HOUR
define('TOKEN_VALIDITY_RESET_PASSWORD', 3600); //1 HOUR
define("PK_USER_ROLE_USER", 1); //has to match t_user_role SQL table
define("PK_USER_ROLE_ADMIN", 2); //has to match t_user_role SQL table
define("REMEMBER_ME_COOKIE", 'remember_me'); //name of the cookie, can be anything

define('APP_NAME', 'SysLogMe Web Site');        //change this value if needed
define('APP_ORGANIZATION', 'SYSLOGME.CH');      //change this value if needed
define('APP_OWNER', 'Your Name');               //change this value if needed
define('APP_OWNER_EMAIL', 'your.email@your.domain');        //change this value if needed
define('APP_DESCRIPTION', 'Embeddable PHP Login System');   //change this value if needed

// URL
define('URL_REGISTER_USER', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/client/login/register_user.html');
define('URL_RESET_PASSWORD', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/client/login/reset_password.html');