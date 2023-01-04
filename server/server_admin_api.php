<?php

include_once 'beans/user_bean.php';
include_once 'beans/response_bean.php';
include_once 'beans/url_bean.php';
include_once 'classes/dbquery_class.php';
include_once 'classes/database_class.php';
include_once 'classes/session_class.php';
include_once 'classes/request_class.php';
include_once 'config/config.php';
include_once 'vendor/autoload.php';

$request = new Request();
$response = null;

//if no request_method exist
if (!isset($_SERVER['REQUEST_METHOD'])) {

    //creates a response object
    $response = new responseBean();

    //response failure
    $response->setSuccess(false);
    $response->setMessage("Server could not find any valid request");
}

//if there is a validated admin session
else if (Session::getInstance()->isAdmin() === true) {

    //if the crfs token is valid
    if (isTokenValid() === true) {

        //performs actions that need a validated admin session
        $response = actionValidatedAdmin($request, $response);

        //if no response was returned
        if (is_null($response)) {

            //creates a response object
            $response = new responseBean();

            //response failure
            $response->setSuccess(false);
            $response->setMessage("Request did not return any response");
        }
    }

    //if the crfs token is invalid
    else {

        //creates a response object
        $response = new responseBean();

        //response failure
        $response->setSuccess(false);
        $response->setMessage("CRFS token is invalid");
    }
}

//if there is no valid sesion
else {

    //creates a response object
    $response = new responseBean();

    //response failure
    $response->setSuccess(false);
    $response->setMessage("No session found or insufficient access");
}        

//returns the response
echo json_encode($response);
exit();

/* -------------------------------------------------------------------------- */
/*                                isTokenValid                                */
/* -------------------------------------------------------------------------- */

function isTokenValid() {

    $res = false;
    if ( isset($_SERVER['HTTP_X_CSRF_TOKEN']) && (Session::getInstance()->getToken() !== null) ) {
        $res = hash_equals(Session::getInstance()->getToken(), $_SERVER['HTTP_X_CSRF_TOKEN']);
    }
    return $res;
}

/* -------------------------------------------------------------------------- */
/*              api calls allowed for a validated admin                       */
/* -------------------------------------------------------------------------- */

function actionValidatedAdmin($request, $response) {

    switch ($_SERVER['REQUEST_METHOD']) {

        //handles HTTP_GET requests
        case 'GET':
            if (isset($_GET['action'])) {	
                switch ($_GET['action']) {  

                    default:
                        break;
                }
            }
            break;
        
        //handles HTTP_POST requests
        case 'POST':
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {

                    default:
                        break;					
                }
            }
            break;

        //handles HTTP_PUT requests
        case 'PUT':
            parse_str(file_get_contents('php://input'), $_PUT);
            if (isset($_PUT['action'])) {
                switch ($_PUT['action']) {            

                    default:
                        break;  
                }
            }                         
            break;
            
        //handles HTTP_DELETE requests
        case 'DELETE':
            parse_str(file_get_contents('php://input'), $_DELETE);
            if (isset($_DELETE['action'])) {
                switch ($_DELETE['action']) {            

                    default:
                        break;  
                }
            }                
            break;
            
        default:
            break;              
    }
    
    return $response;    
}
