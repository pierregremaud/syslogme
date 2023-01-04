<?php
		
class Request {

	/* -------------------------------------------------------------------------- */
	/*                                 postGeneric                                */
	/* -------------------------------------------------------------------------- */
	
	//generic post fucntion
	public function postGeneric($action, $params) {
		
		//creates a response object
		$response = new responseBean();		
				
		//executes the query
		$dbquery = new DBQuery;
		switch ($action) {

			case 'createUser':
				$result = $dbquery->createUser($params["email"], $params["password"]); //email, password
				break;             				                           
		}
		
        //if query failed
		if (!$result) {

			//response failure
			$response->setSuccess(false);
			$response->setMessage("Query " . $action . " failure");
            return $response;        				
		}

        //if query successful
		else {
		
			//response successfull
			$response->setSuccess(true);
			$response->setMessage("Query " . $action . " successfull");
            return $response;            
		}
	} 

	/* -------------------------------------------------------------------------- */
	/*                                    login                                   */
	/* -------------------------------------------------------------------------- */
	
	//checks if username matches password - creates session if user validated
	public function login($email, $password, $rememberMe) {
		
		//creates a response object
		$response = new responseBean();

        //creates a dbquery object
        $dbquery = new DBQuery;

        //looks in database for a user with this email
        //a user is valid if email exist, verified_at is not null and deleted_at is null
        $arrRows = $dbquery->getVerifiedUserDetails($email);

        //if the query returned no rows
        if (count($arrRows) == 0) {

            //response failure
            $response->setSuccess(false);
            $response->setMessage("No valid user found in database for this email");
            return $response;
        }        

        //if query returned at least one row
        else {
            
            //if the password does not match with the hash in database
            if (!password_verify($password, $arrRows[0]['password'])) {

                //response failure
                $response->setSuccess(false);
                $response->setMessage("Email and password do not match");
                return $response;
            }            

            //if the password match with the hash in database
            else {
            
                //updates last_login_at
                $result = $dbquery->setUserLastLogin($email);

                //if the last_login_at could not be updated
                if (!$result) {

                    //response failure
                    $response->setSuccess(false);
                    $response->setMessage("Unable to update field 'last_login_at'");
                    return $response;
                } 
                
                //if the last_login_at could be updated
                else {
 
                    //sets remember_me cookie if needed
                    if ($rememberMe == 'true') {

                        $selector = bin2hex(random_bytes(8));
                        $validator = random_bytes(32);
                        $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
                        $email = $arrRows[0]['email'];
                        $expires = time() + TOKEN_VALIDITY_REMEMBER_ME; //10 days
                        $expiresSQL = date('Y-m-d\TH:i:s', $expires); //$expires in SQL format
                        
                        //deletes 'remember_me' user token from database for this user
                        $result = $dbquery->deleteUserToken($email, TOKEN_TYPE_REMEMBER_ME);

                        //if delete 'remember_me' user token failed
                        if (!$result) {

                            //response failure
                            $response->setSuccess(false);
                            $response->setMessage("Unable to delete 'remember_me' user token");
                            return $response;
                        }

                        //if delete 'remember_me' user token successfull
                        else {

                            //creates a new remember_me cookie
                            setcookie(
                                REMEMBER_ME_COOKIE,
                                $selector.':'.bin2hex($validator),
                                $expires, //10 days
                                '/',
                                NULL,
                                false, // TLS-only
                                false  // http-only
                            );

                            //creates 'remember_me' user token in database for this user
                            $result = $dbquery->createUserToken($email, TOKEN_TYPE_REMEMBER_ME, $selector, $hashedValidator, $expiresSQL);

                            //if create 'remember_me' user token failed
                            if (!$result) {

                                //response failure
                                $response->setSuccess(false);
                                $response->setMessage("Unable to create 'remember_me' user token");
                                return $response;
                            }
                        }
                    }

                    //creates session variables
                    session::getInstance()->setPKUser($arrRows[0]['pk_user']);
                    session::getInstance()->setFKUserRole($arrRows[0]['fk_user_role']);                            
                    session::getInstance()->setEmail($arrRows[0]['email']);

                    //creates a csrf token https://portswigger.net/web-security/csrf/tokens
                    session::getInstance()->setToken(bin2hex(random_bytes(32)));  

                    //response success
                    $response->setSuccess(true);
                    $response->setMessage("User successfully logged in");
                    //creates a user object
                    $objUser = new userBean(
                        Session::getInstance()->getPKUser(),
                        Session::getInstance()->getFKUserRole(),
                        Session::getInstance()->getEmail(),
                        Session::getInstance()->getToken()
                    );
                    $response->setBody($objUser);
                    return $response;
                }
            }
        }	
	}

	/* -------------------------------------------------------------------------- */
	/*                                   logout                                   */
	/* -------------------------------------------------------------------------- */

	//logs user out
	public function logout() {

		//creates a response object
		$response = new responseBean();	

		//destroy remember_me cookie
		if (isset($_COOKIE[REMEMBER_ME_COOKIE])) {
		
			setcookie(REMEMBER_ME_COOKIE, '', time()-3600, '/');
		}

		//destroy 'remember_me' user_token
		if ( Session::getInstance()->getEmail() !== null ) {

			//delete user_token 'remember_me'
			$dbquery = new DBQuery;
			$email = Session::getInstance()->getEmail();
			$result = $dbquery->deleteUserToken($email, TOKEN_TYPE_REMEMBER_ME);	
		}	

		//destroy session and session cookies
		session::getInstance()->destroySession();
		
		//response success
		$response->setSuccess(true);
		$response->setMessage("Session destroyed");

		//returns the response
		return($response);
	}

    /* -------------------------------------------------------------------------- */
    /*                               changePassword                               */
    /* -------------------------------------------------------------------------- */

	//change user password
	public function changePassword($currentPwd, $newPwd) {

		//creates a response object
		$response = new responseBean();

        //checks in database if currentPwd is valid
        $dbquery = new DBQuery;
        $pkUser = Session::getInstance()->getPKUser();
        $arrRows = $dbquery->getUserPassword($pkUser);

		//if query returned no row
		if (count($arrRows) == 0) {   
            
            //response failure
            $response->setSuccess(false);
            $response->setMessage("Unable to find user in database");					
		}

        //if password was found for this user        
        else {

            //checks currentPassord validity
            $hashedPassword = $arrRows[0]['password'];
            $passwordCheck = password_verify($currentPwd, $hashedPassword);

            //if current password is invalid
            if (!$passwordCheck) {

                //response failure
                $response->setSuccess(false);
                $response->setMessage("Invalid current password");  
            }

            //if current password is valid
            else {

                //updates currentPwd with newPwd
                $result = $dbquery->updateUserPassword($pkUser, $newPwd);   
                
                //if update failure
                if (!$result) {

                    //response failure
                    $response->setSuccess(false);
                    $response->setMessage("Unable to change password in database");                
                }

                //if update success
                else {

                    //response success
                    $response->setSuccess(true);
                    $response->setMessage("Password successfully updated");                
                }
            }            
        }

		//returns the response
		return($response);
	}

    /* -------------------------------------------------------------------------- */
    /*                             processInternalMail                            */
    /* -------------------------------------------------------------------------- */

    public function processInternalMail($senderName, $senderEmail, $content) {

        //creates a response object
		$response = new responseBean();

        //creates a subject
        $subject = "Windball internal mail from " . $senderName;

        //sends the mail
        $sendContactMailResponse = $this->sendContactMail($senderName, $senderEmail, $content, $subject);

        //if sendMail failed
        if ( !$sendContactMailResponse->getSuccess() ) {

            //response failure
            $response = $sendMailResponse;
            return $response;
        }

        //if sendMail successful
        else {

            //response success
            $response->setSuccess(true);
            $response->setMessage("Mail sent successfully");
            return $response;
        }
    }     

    /* -------------------------------------------------------------------------- */
    /*                               processPublicMail                            */
    /* -------------------------------------------------------------------------- */
    
    public function processPublicMail($senderName, $senderEmail, $content) {

        //creates a response object
		$response = new responseBean();

        //creates a subject
        $subject = "Windball external mail from " . $senderName;

        //sends the mail
        $sendContactMailResponse = $this->sendContactMail($senderName, $senderEmail, $content, $subject);

        //if sendMail failed
        if ( !$sendContactMailResponse->getSuccess() ) {

            //response failure
            $response->$sendMailResponse;
            return $response;
        }

        //if sendMail successful
        else {

            //response success
            $response->setSuccess(true);
            $response->setMessage("Mail sent successfully");
            return $response;
        }
    } 

    /* -------------------------------------------------------------------------- */
    /*                                sendRegistrationMail                        */
    /* -------------------------------------------------------------------------- */

    public function sendRegistrationMail($email, $password) {

		//creates a response object
		$response = new responseBean();

        //creates a db query object
        $dbquery = new DBQuery;

        //check if a valid user with this email exist in database
        $arrRows = $dbquery->getVerifiedUserDetails($email);

        //if a valid user allready exist in database
        if (count($arrRows) != 0) {

            //response failure
            $response->setSuccess(false);
            $response->setMessage("Email allready used by verified customer");
            return $response;
        } 

        //if no valid user exist in database with this email
        else {

            //creates the user in database
            $params = array (
                "email" => $email,
                "password" => $password
            );            
            $createUserResponse = $this->postGeneric('createUser', $params);

            //if the user could not be created
            if ( !$createUserResponse->getSuccess() ) {

                //response failure
                $response = $createUserResponse;
                return $response;                    
            }

            //if the user could be created
            else {

                //gets verification URL
                $verificationURLResponse = $this->getVerificationURL($email, TOKEN_TYPE_VERIFY_ACCOUNT);

                //if verification URL failure
                if ( !$verificationURLResponse->getSuccess() ) {

                    //response failure
                    $response = $sendMailResponse;
                    return $response;
                }
                    
                //if verification URL success
                else {

                    //prepares the URL for the verification email 
                    $objURL = $verificationURLResponse->getBody();

                    //sends a registration email to the user $recipientEmail, $redirectURL, $tokenType
                    $sendMailresponse = $this->sendVerificationMail($email, $objURL->getURL(), TOKEN_TYPE_VERIFY_ACCOUNT);

                    //response success or failure
                    $response = $sendMailresponse; 
                    return $response;
                }
            }
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                          completeUserRegistration                          */
    /* -------------------------------------------------------------------------- */

	//complete user registration
	public function completeUserRegistration($selector, $validator) {

		//creates a response object
		$response = new responseBean();

        //creates a db query object
        $dbquery = new DBQuery;        

        //looks in database for a user_token of type 'account_verify' with this selector
        $arrRows = $dbquery->getUserToken($selector, TOKEN_TYPE_VERIFY_ACCOUNT);

        //if no user token were found in database
        if (count($arrRows) == 0) { 
            
            //response failure
            $response->setSuccess(false);
            $response->setMessage("Non-existent or expired 'account_verify' token, please register again");
            return $response;
        }

        //if at least one user token was found
        else {

            //compares validator with token found in database
            $validatorBin = hex2bin($validator);
            $validatorCheck = password_verify($validatorBin, $arrRows[0]['validator']);

            //if token do not match
            if ($validatorCheck == false) {
                
                //response failure
                $response->setSuccess(false);
                $response->setMessage("Invalid 'account_verify' token, please register again");
                return $response;                    
            }

            //if token match
            else {

                //gets email stored in token database
                $tokenEmail = $arrRows[0]['fk_user_email'];

                //gets user with email address corresponding to token
                $arrRows = $dbquery->getUnverifiedUserDetails($tokenEmail);

                //if no user found in database for this email
                if (count($arrRows) == 0) {	

                    //response failure
                    $response->setSuccess(false);
                    $response->setMessage("Invalid email address, please register again");
                    return $response;                         
                }

                //if a user was found in database for this email
                else {

                    //updates field 'verified_at' in t_user table
                    $result = $dbquery->setUserVerifiedAt($tokenEmail);

                    //if update field failure
                    if ($result == false) {
                        
                        //response failure
                        $response->setSuccess(false);
                        $response->setMessage("Account update failure, please register again");
                        return $response;                                 
                    }
                    
                    //if update field success
                    else {

                        //deletes user token 'account_verify'
                        $result = $dbquery->deleteUserToken($tokenEmail, TOKEN_TYPE_VERIFY_ACCOUNT);

                        //if delete 'account_verify' token failure
                        if ($result == false) {		  
                            
                            //response failure
                            $response->setSuccess(false);
                            $response->setMessage("Token 'account_verify' delete failure, please register again");
                            return $response;                                  
                        }
                        
                        //if delete 'account_verify' token successful
                        else {

                            //response success
                            $response->setSuccess(true);
                            $response->setMessage("Registration successful");
                            $response->setBody($arrRows[0]);
                            return $response;                                
                        }
                    }
                }
            }
        }
	}    

    /* -------------------------------------------------------------------------- */
    /*                              sendResetPasswordMail                         */
    /* -------------------------------------------------------------------------- */

	//sends a email to reset the user password
	public function sendResetPasswordMail($email) {		

		//creates a response object
		$response = new responseBean();

        //creates a db query object
        $dbquery = new DBQuery;        

        //check if a valid user with this email exist in database
        $arrRows = $dbquery->getVerifiedUserDetails($email);

        //if query returned no rows -> email does not exist in database
        if (count($arrRows) == 0) {	            

            //response failure
            $response->setSuccess(false);
            $response->setMessage("Email not found in database");
            return $response;
        }
        
        //if a valid user exists in database
        else {

            //gets verification URL
            $verificationURLResponse = $this->getVerificationURL($email, TOKEN_TYPE_RESET_PASSWORD);

            //if verification URL failure
            if ( !$verificationURLResponse->getSuccess() ) {

                //response failure
                $response = $verificationURLResponse;
                return $response;
            }

            //if verification URL successful
            else {
                
                //prepares the URL for the verification email                  
                $objURL = $verificationURLResponse->getBody();

                //sends a forgotten password email to the user $recipientEmail, $redirectURL, $tokenType
                $sendMailresponse = $this->sendVerificationMail($email, $objURL->getURL(), TOKEN_TYPE_RESET_PASSWORD);

                //response success or failure
                $response = $sendMailresponse; 
                return $response;	                   
            }                    
        } 
    }

    /* -------------------------------------------------------------------------- */
    /*                            completePasswordReset                           */
    /* -------------------------------------------------------------------------- */

	//complete user forgotten password
	public function completePasswordReset($selector, $validator) {

		//creates a response object
		$response = new responseBean();

        //creates a db query object
        $dbquery = new DBQuery;  

        //looks in database for a user_token of type 'reset_password' with this selector
        $arrRows = $dbquery->getUserToken($selector, TOKEN_TYPE_RESET_PASSWORD);

        //if no user token was found
        if (count($arrRows) == 0) {

            //response failure
            $response->setSuccess(false);
            $response->setMessage("Non-existent or expired user token, please start over the reset password process");
            return $response;                
        }
    
        //if at least one user token was found
        else {

            //compares validator with token found in database
            $validatorBin = hex2bin($validator);
            $validatorCheck = password_verify($validatorBin, $arrRows[0]['validator']);

            //if user token do not match
            if ($validatorCheck == false) {

                //response failure
                $response->setSuccess(false);
                $response->setMessage("Invalid user token, please start over the reset password process");
                return $response;     
            }

            //if token match
            else {

                //gets email stored in token database
                $tokenEmail = $arrRows[0]['fk_user_email'];

                //looks in database for a user with this email
                //a user is valid if email exist, verified_at is not null and deleted_at is null
                $arrRows = $dbquery->getVerifiedUserDetails($tokenEmail); 

                //if query returned no row
                if (count($arrRows) == 0) {
                    
                    //response failure
                    $response->setSuccess(false);
                    $response->setMessage("Invalid email address, please start over the reset password process");
                    return $response;                                       
                }

                //if query returned at least one row
                else {

                    //deletes user token 'reset_password'
                    $result = $dbquery->deleteUserToken($tokenEmail, TOKEN_TYPE_RESET_PASSWORD);

                    //if delete user token failure
                    if ($result == false) {
                        
                        //response failure
                        $response->setSuccess(false);
                        $response->setMessage("Token delete failure, please start over the reset password process");
                        return $response;                              
                    } 	 
                
                    //if delete user token successful
                    else {

                        //generate new password
                        $newPwd = bin2hex(openssl_random_pseudo_bytes(4));

                        //updates currentPwd with newPwd
                        $result = $dbquery->updateUserPassword($arrRows[0]['pk_user'], $newPwd);   

                        //if update failure
                        if (!$result) {

                            //response failure
                            $response->setSuccess(false);
                            $response->setMessage("Account update failure, please start over the reset password process");
                            return $response;                                     
                        }

                        //if update success
                        else {

                            //logs user in and redirect to change passwd page with old password in post
                            session::getInstance()->setPKUser($arrRows[0]['pk_user']);
                            session::getInstance()->setFKUserRole($arrRows[0]['fk_user_role']);
                            session::getInstance()->setEmail($arrRows[0]['email']);
                            //creates a csrf token https://portswigger.net/web-security/csrf/tokens
                            session::getInstance()->setToken(bin2hex(random_bytes(32)));

                            //adds new password to arrRows
                            $arrRows[0]['password'] = $newPwd;

                            //response success
                            $response->setSuccess(true);
                            $response->setMessage("Password successfully reset");
                            $response->setBody($arrRows[0]);
                            return $response;       							
                        }
                    }
                }
            }
        }
	}	    

	/* -------------------------------------------------------------------------- */
	/*                                getPHPSession                               */
	/* -------------------------------------------------------------------------- */

	//gets the PHP session parameters
	public function getPHPSession() {

		//creates a response object
		$response = new responseBean();

        //creates a dbquery object
		$dbquery = new DBQuery;	

		//if session exits then gets the email of the logged user
		if ($email = Session::getInstance()->getEmail()) {

			//looks in database for a user with this email
            //a user is valid if email exist, verified_at is not null and deleted_at is null
			$arrRows = $dbquery->getVerifiedUserDetails($email); 
			
            //if no user in database with this email
			if (count($arrRows) == 0) {

				//deletes session
				Session::getInstance()->destroySession();

				//response failure
				$response->setSuccess(false);
				$response->setMessage("No valid user found in database for this PHP session");
                return ($response);              
            }
			
			//if query returned at least one row
			else {

                //response success
                $response->setSuccess(true);
                $response->setMessage("PHP session exist");

                //creates a user object
                $objUser = new userBean(
                    Session::getInstance()->getPKUser(),
                    Session::getInstance()->getFKUserRole(),
                    Session::getInstance()->getEmail(),
                    Session::getInstance()->getToken()
                );
                $response->setBody($objUser);
                return ($response); 
			}
		}

        //if session does not exist and there in no remember_me cookie
		else if ( empty($_COOKIE[REMEMBER_ME_COOKIE]) ) {

			//response failure
			$response->setSuccess(false);
			$response->setMessage("PHP session does not exit and no remember_me cookie was found");
            return ($response);
        }

        //if session does not exist but a remember_me cookie exit on this computer for this site
        else {
            
			list($selector, $validator) = explode(':', $_COOKIE[REMEMBER_ME_COOKIE]);

			//looks in database for a user token of type 'remember_me' with this selector
			$arrRows = $dbquery->getUserToken($selector, TOKEN_TYPE_REMEMBER_ME);

			//if query returned no rows
			if (count($arrRows) == 0) {

				//response failure
				$response->setSuccess(false);
				$response->setMessage("Invalid selector or expired remember_me cookie");
                return ($response);
            }

            //if query returned at leat one row
            else {

				//validates the user token with values from database
				$validatorBin = hex2bin($validator);
                $validatorCheck = password_verify($validatorBin, $arrRows[0]['validator']);

                //if invalid user token
				if ($validatorCheck == false) {

					//response failure
					$response->setSuccess(false);
					$response->setMessage("Invalid validator in remember_me cookie");
                    return ($response);
                } 
                
                //if valid user token
                else {

                    //gets the email corresponding to the user token
                    $email = $arrRows[0]['fk_user_email'];

					//looks in database for a user with this email
                    //a user is valid if email exist, verified_at is not null and deleted_at is null                    
					$arrRows = $dbquery->getVerifiedUserDetails($email);

					//if query returned no rows
					if (count($arrRows) == 0) {

						//response failure
						$response->setSuccess(false);
						$response->setMessage("No valid user found in database for this remember_me cookie");   
                        return ($response);
                    }
                    
                    //if query returned at least one row
                    else {

                        //creates session variables
                        session::getInstance()->setPKUser($arrRows[0]['pk_user']);
                        session::getInstance()->setFKUserRole($arrRows[0]['fk_user_role']);
                        session::getInstance()->setEmail($arrRows[0]['email']);
                        //creates a csrf token https://portswigger.net/web-security/csrf/tokens
                        session::getInstance()->setToken(bin2hex(random_bytes(32)));                        

                        //response success
                        $response->setSuccess(true);
                        $response->setMessage("User successfully logged with cached credentials");
                        //creates a user object
                        $objUser = new userBean(
                            Session::getInstance()->getPKUser(),
                            Session::getInstance()->getFKUserRole(),
                            Session::getInstance()->getEmail(),
                            Session::getInstance()->getToken()
                        );
                        $response->setBody($objUser);
                        return ($response);
                    }                      
				}
			}
		}
	}	

	/* -------------------------------------------------------------------------- */
	/*                             getVerificationURL                             */
	/* -------------------------------------------------------------------------- */

	//creates a verification URL 
	private function getVerificationURL($email, $tokenType) {

        //creates a response object
		$response = new responseBean();

        //$tokenType can be ('verify_account' or 'reset_password')
        if ($tokenType == TOKEN_TYPE_VERIFY_ACCOUNT) {
            $expires = time() + TOKEN_VALIDITY_ACCOUNT_VERIFY; //1 hour
            $url = URL_REGISTER_USER;   
        }
        if ($tokenType == TOKEN_TYPE_RESET_PASSWORD) {
            $expires = time() + TOKEN_VALIDITY_RESET_PASSWORD; //1 hour
            $url = URL_RESET_PASSWORD;         
        }

        $expiresSQL = date('Y-m-d\TH:i:s', $expires); //$expires in SQL format
		$selector = bin2hex(random_bytes(8));
		$validator = random_bytes(32);
		$hashedValidator = password_hash($validator, PASSWORD_DEFAULT);
		$url = $url . '?selector=' . $selector . '&validator=' . bin2hex($validator);
		
		//query the database
		$dbquery = new DBQuery;
		
		//deletes all tokens for this user
		$result = $dbquery->deleteUserToken($email, $tokenType);

		//if delete user token failure
		if (!$result) {

            //response failure
            $response->setSuccess(false);
            $response->setMessage("Unable to delete user token for this user");
            return $response;
        }

        //if delete user token success
		else {
					
			//creates a new token for this user
			$result = $dbquery->createUserToken($email, $tokenType, $selector, $hashedValidator, $expiresSQL);
			
			//if create user token failure
			if (!$result) {

                //response failure
                $response->setSuccess(false);
                $response->setMessage("Unable to create user token for this user");
                return $response;
            }

            //if create user token success
            else {

				//response success
				$response->setSuccess(true);
				$response->setMessage("Verification URL generated successfully");
				
				//creates a verificationURL object
				$objVerificationURL = new URLBean($url);
				$response->setBody($objVerificationURL); 
                return $response;              
            }
		}	
	}   

	/* -------------------------------------------------------------------------- */
	/*                            sendVerificationMail                            */
	/* -------------------------------------------------------------------------- */

	//sends a verification email to the user
	private function sendVerificationMail($recipientEmail, $redirectURL, $tokenType) {			

		//creates a response object
		$response = new responseBean();		

        //creates array of mail_variables
        $mail_variables = array();
        $mail_variables['APP_NAME'] = APP_NAME;  //'Windball Web Site'
        $mail_variables['RECIPIENT_EMAIL'] = $recipientEmail;
        $mail_variables['REDIRECT_URL'] = $redirectURL;

        //customize the template with the variables
        //$tokenType can be ('verify_account' or 'reset_password')
        if ($tokenType == TOKEN_TYPE_VERIFY_ACCOUNT) {
            $subject = "Complete Your Registration";
            $message = file_get_contents("./templates/template_registeremail.php");           
        }
        if ($tokenType == TOKEN_TYPE_RESET_PASSWORD) {
            $subject = "Reset Your Password";
            $message = file_get_contents("./templates/template_forgotpassemail.php");         
        }                    

        foreach($mail_variables as $key => $value) {
        
            $message = str_replace('{{ '.$key.' }}', $value, $message);
        } 
        
        //sends the mail
        $sendMailResponse = $this->sendMail($recipientEmail, $subject, $message);

        //if sendMail failed
        if ( !$sendMailResponse->getSuccess() ) {

            //response failure
            $response = $sendMailResponse;
            return $response;
        }

        //if sendMail successful
        else {

            //response success
            $response->setSuccess(true);
            $response->setMessage("Registration mail sent successfully");
            return $response;	
        }       
	}

    /* -------------------------------------------------------------------------- */
    /*                               sendContactMail                              */
    /* -------------------------------------------------------------------------- */

	private function sendContactMail($senderName, $senderEmail, $content, $subject) {
        
        //creates a response object
		$response = new responseBean();

        $recipientEmail = WINDBALL_CONTACT_EMAIL; //'contact@windball.ch'

        //creates array of mail_variables
        $mail_variables = array();
        $mail_variables['RECIPIENT_EMAIL'] = $recipientEmail;
        $mail_variables['SENDER_NAME'] = $senderName;
        $mail_variables['SENDER_EMAIL'] = $senderEmail;
        $mail_variables['APP_NAME'] = APP_NAME;
        $mail_variables['MESSAGE_CONTENT'] = $content;
        $message = file_get_contents("./templates/template_contactemail.php");         
                    
        //customize the template with the variables
        foreach($mail_variables as $key => $value) {
        
            $message = str_replace('{{ '.$key.' }}', $value, $message);
        }

        //sends the mail
        $sendMailResponse = $this->sendMail($recipientEmail, $subject, $message);

        //if sendMail failed
        if ( !$sendMailResponse->getSuccess() ) {

            //response failure
            $response = $sendMailResponse;
            return $response;
        }

        //if sendMail successful
        else {

            //response success
            $response->setSuccess(true);
            $response->setMessage("Contact mail sent successfully");
            return $response;
        }
    }
    
    /* -------------------------------------------------------------------------- */
    /*                                  sendMail                                  */
    /* -------------------------------------------------------------------------- */

	private function sendMail($recipientEmail, $subject, $message) {
        
		//creates a response object
		$response = new responseBean();

		//creates mailer object
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);        
        
        //try to send the mail
        try {
        
            $mail->isSMTP();            
            //SMTP::DEBUG_OFF;
            //SMTP::DEBUG_CLIENT;
            //SMTP::DEBUG_SERVER;
            $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $mail->Host = MAIL_HOST; //'smtp.gmail.com'
            $mail->SMTPAuth = true;
            $mail->SMTPSecure =  PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // or PHPMailer::ENCRYPTION_STARTTLS "tls"
            $mail->Port = MAIL_PORT;
            $mail->AuthType = 'XOAUTH2';

			//Fill in authentication details here
			//Either the gmail account owner, or the user that gave consent
			$oauthUserEmail = OAUTH_USER_EMAIL;
			$clientId = OAUTH_CLIENT_ID;
			$clientSecret = OAUTH_SECRET_KEY; 

			//Obtained by configuring and running get_oauth_token.php
			//after setting up an app in Google Developer Console.
			$refreshToken = OAUTH_REFRESH_TOKEN; 

			//Create a new OAuth2 provider instance
			$provider = new League\OAuth2\Client\Provider\Google(
				[
					'clientId' => $clientId,
					'clientSecret' => $clientSecret,
				]
			);

			//Pass the OAuth provider instance to PHPMailer
			$mail->setOAuth(
				new PHPMailer\PHPMailer\OAuth(
					[
						'provider' => $provider,
						'clientId' => $clientId,
						'clientSecret' => $clientSecret,
						'refreshToken' => $refreshToken,
						'userName' => $oauthUserEmail,
					]
				)
			);
                    
            //mail content is HTML
            $mail->isHTML(true);

            //mail from
            $mail->setFrom(OAUTH_USER_EMAIL, APP_NAME); //(windball.ch@gmail.com, SysLogMe Web Site)

            //recipient to
            $mail->addAddress($recipientEmail, APP_NAME); //($recipientEmail, SysLogMe Web Site)

            $mail->Subject = $subject;          
        
            $mail->CharSet = PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
            $mail->msgHTML($message);

            // Enable SMTP debug output.
            $mail->SMTPDebug = 0;

            $mail->send();

            //response success
            $response->setSuccess(true);
            $response->setMessage("Email sent successfully");
            return $response;	
        }
        
        //if mail could not be sent
        catch (PHPMailer\PHPMailer\Exception $e) {

            //response failure
            $response->setSuccess(false);
            $response->setMessage("Unable to send email. Error : " . $mail->ErrorInfo);
            return $response;
        }
    }
}

