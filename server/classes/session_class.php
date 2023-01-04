<?php

class Session {	

    private static $_instance = null;
    
    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return Singleton de la Session
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Session();
        }
        return self::$_instance;
    }

    /**
     * Fonction permettant d'ouvrir la gestion de la session
     */
    private function __construct()
    {
        if (session_status() == PHP_SESSION_DISABLED) {

            // server should keep session data for AT LEAST 1 hour
            ini_set('session.gc_maxlifetime', PHP_SESSION_VALIDITY);

            // each client should remember their session id for EXACTLY 1 hour
            session_set_cookie_params(PHP_SESSION_VALIDITY);
        }
        session_start();
    }
	
	/**
     * @param stores pk_user in session variable
     */
    public function setPKUser($pkUser)
    {
        $_SESSION['pk_user'] = $pkUser;
    }
	
	/**
     * @param stores username in session variable
     */
    public function setUserName($username)
    {
        $_SESSION['username'] = $username;
    }

	/**
     * @param stores email in session variable
     */
    public function setEmail($email)
    {
        $_SESSION['email'] = $email;
    }    
	
	/**
     * @param stores fk_user_role in session variable
     */
    public function setFKUserRole($fkUserRole)
    {
        $_SESSION['fk_user_role'] = $fkUserRole;
    }
    
    /**
     * @param stores csrf token in session variable
     */
    public function setToken($token)
    {
        $_SESSION['token'] = $token;
    }     
	
	/**
     * @returns pk_user or null if session variable does not exist
     */
    public function getPKUser()
    {
        $res = null;
        if (isset($_SESSION['pk_user'])) {
            $res = $_SESSION['pk_user'];
        }
        return $res;
    }		

	/**
     * @returns fk_user_role or null if session variable does not exist
     */
    public function getFKUserRole()
    {
        $res = null;
        if (isset($_SESSION['fk_user_role'])) {
            $res = $_SESSION['fk_user_role'];
        }
        return $res;
    }

	/**
     * @returns fk_user_role or null if session variable does not exist
     */
    public function getEmail()
    {
        $res = null;
        if (isset($_SESSION['email'])) {
            $res = $_SESSION['email'];
        }
        return $res;
    }	    

    /**
     * @return csrf token if user has a session on the server
     * @return null if user has no session
     */
    public function getToken()
    {
        $res = null;
        if (isset($_SESSION['token'])) {
            $res = $_SESSION['token'];
        }
        return $res;
    }

    /**
     * @return returns true if session exists on the server
     */
    public function sessionExist()
    {
        $res = false;
        if (isset($_SESSION['fk_user_role'])) {
			$res = true;
		}
        return $res;
    }

    /**
     * @return returns true if logged on user is an admin
     */
    public function isAdmin()
    {
        $res = false;
        if (isset($_SESSION['fk_user_role'])) {
			if ( ($_SESSION['fk_user_role'] == PK_USER_ROLE_ADMIN) ) {
				$res = true;
			} 
		}
        return $res;
    } 
 
    /**
     * @return returns true if logged on user is at least a user
     */
    public function isAtLeastUser()
    {
        $res = false;
        if (isset($_SESSION['fk_user_role'])) {
			if ( ($_SESSION['fk_user_role'] >= PK_USER_ROLE_USER) ) {
				$res = true;
			} 
		}
        return $res;
    }
    
    /**
     * Destroys the session.
     */
    public function destroySession()
    {
		//unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(), '', time() - 3600,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}

		//unsets $_SESSION variable for the run-time
        $result = session_unset();
        
        //destroy session
		$result = session_destroy();		
		
        self::$_instance = null;
    }
}
