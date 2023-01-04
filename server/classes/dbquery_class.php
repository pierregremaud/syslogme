<?php

class DBQuery {

    /* -------------------------------------------------------------------------- */
    /*                                    getVerifiedUserDetails                  */
    /* -------------------------------------------------------------------------- */
	
    //gets all data from a verified user to create session
    //a user is verified if email exist, verified_at is not null and deleted_at is null
    public function getVerifiedUserDetails($email) {
        $database = Database::getInstance();
        $query = "SELECT * from t_user\n"
				. "WHERE email = :email\n"
                . "AND (verified_at IS NOT NULL) AND (deleted_at IS NULL)";
        $params = ['email' => $email];
        return $database->selectQuery($query, $params);
		//return ['user1' => 'guigui', 'usr2' => 'hello'];
    } 

    /* -------------------------------------------------------------------------- */
    /*                              getUnverifiedUserDetails                      */
    /* -------------------------------------------------------------------------- */

    //gets all data from a non verified user to complete its registration 
    //a user is unverified if email exist, verified_at is null and deleted_at is null
    public function getUnverifiedUserDetails($email) {
        $database = Database::getInstance();
        $query = "SELECT * from t_user\n"
				. "WHERE email = :email\n"
                . "AND (verified_at IS NULL) AND (deleted_at IS NULL)";
        $params = ['email' => $email];
        return $database->selectQuery($query, $params);
		//return ['user1' => 'guigui', 'usr2' => 'hello'];
    } 

    /* -------------------------------------------------------------------------- */
    /*                              getUserPassword                               */
    /* -------------------------------------------------------------------------- */

    //gets the password hash of a given user
    public function getUserPassword($pkUser) {
        $database = Database::getInstance();
        $query = "SELECT password FROM t_user\n"
				. "WHERE pk_user = :pk_user";               
        $params = ['pk_user' => $pkUser];
        return $database->selectQuery($query, $params);
    }    

    /* -------------------------------------------------------------------------- */
    /*                             updateUserPassword                             */
    /* -------------------------------------------------------------------------- */

    //updates user password in database
    public function updateUserPassword($pkUser, $newPwd) {
        $database = Database::getInstance();
        $query = "UPDATE t_user\n"
                . "SET password = :password\n"        
                . "WHERE pk_user = :pk_user";
        $hashedPassword = password_hash($newPwd, PASSWORD_DEFAULT);                
        $params = ['pk_user' => $pkUser, 'password' => $hashedPassword];
        return $database->executeQuery($query, $params);
    }    

    /* -------------------------------------------------------------------------- */
    /*                              setUserLastLogin                              */
    /* -------------------------------------------------------------------------- */

    //updates the last_login_at
    public function setUserLastLogin($email) {
        $database = Database::getInstance();
        $query = "UPDATE t_user\n"
                . "SET last_login_at = NOW()\n"
				. "WHERE email = :email";
        $params = ['email' => $email];
        return $database->executeQuery($query, $params);
    } 

    /* -------------------------------------------------------------------------- */
    /*                              setUserVerifiedAt                             */
    /* -------------------------------------------------------------------------- */

    //updates the verified_at
    public function setUserVerifiedAt($tokenEmail) {
        $database = Database::getInstance();
        $query = "UPDATE t_user\n"
                . "SET verified_at = NOW()\n"
				. "WHERE email = :email";
        $params = ['email' => $tokenEmail];
        return $database->executeQuery($query, $params);
    }  
    
    /* -------------------------------------------------------------------------- */
    /*                                createUser                                  */
    /* -------------------------------------------------------------------------- */

    //adds a user to db in case of standard registration
    public function createUser($email, $password) {
        $database = Database::getInstance();
        $query = "INSERT INTO t_user(email, password, created_at)\n"
                . "VALUES( :email, :password, NOW() )";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $params = ['email' => $email, 'password' => $hashedPassword];
        return $database->executeQuery($query, $params);
    }

    /* -------------------------------------------------------------------------- */
    /*                               deleteUserToken                              */
    /* -------------------------------------------------------------------------- */
    
    //deletes user token
    public function deleteUserToken($email, $token_type) {
        $database = Database::getInstance();
        $query = "DELETE FROM t_user_token\n"
                . "WHERE fk_user_email = :fk_user_email AND fk_token_type = (SELECT pk_token_type from t_token_type WHERE token_type = :token_type)";
        $params = ['fk_user_email' => $email, 'token_type' => $token_type];
        return $database->executeQuery($query, $params);
    }    

    /* -------------------------------------------------------------------------- */
    /*                                getUserToken                                */
    /* -------------------------------------------------------------------------- */

    //gets user token corresponding to a given selector
    public function getUserToken($selector, $token_type) {
        $database = Database::getInstance();
        $query = "SELECT * FROM t_user_token\n"
                . "WHERE fk_token_type = (SELECT pk_token_type from t_token_type WHERE token_type = :token_type)\n"
                . "AND selector = :selector AND expires_at >= NOW()";
        $params = ['selector' => $selector, 'token_type' => $token_type];
        return $database->selectQuery($query, $params);
    }    

    /* -------------------------------------------------------------------------- */
    /*                               createUserToken                              */
    /* -------------------------------------------------------------------------- */

    //creates user token valid for one hour
    public function createUserToken($email, $token_type, $selector, $validator, $valid) {
        $database = Database::getInstance();
        $query = "INSERT INTO t_user_token (fk_user_email, fk_token_type, selector, validator, expires_at)\n"
                . "VALUES (:fk_user_email, (SELECT pk_token_type from t_token_type WHERE token_type = :token_type), :selector, :validator, :valid)";
        $params = ['fk_user_email' => $email, 'token_type' => $token_type, 'selector' => $selector, 'validator' => $validator, 'valid' => $valid];
        return $database->executeQuery($query, $params);
    }  
}
