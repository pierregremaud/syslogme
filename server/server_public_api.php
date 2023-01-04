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

//if a REQUEST_METHOD was found
else {

    //performs actions allowed to everyone (with or without user session)
    $response = actionEveryone($request, $response);

    //if no response was returned
    if (is_null($response)) {

        //creates a response object
        $response = new responseBean();

        //response failure
        $response->setSuccess(false);
        $response->setMessage("Request did not return any response");
    }
}

//returns the response
//echo 'glouglou';
echo json_encode($response);
exit();


/* -------------------------------------------------------------------------- */
/*                   api calls that do not need user session                  */
/* -------------------------------------------------------------------------- */

function actionEveryone($request, $response) {

    switch ($_SERVER['REQUEST_METHOD']) {

		//handles HTTP_GET requests
        case 'GET':
            if (isset($_GET['action'])) {	
                switch ($_GET['action']) {                        

                    case 'getPHPSession':
                        $response = $request->getPHPSession();
                        break;                     

					default:
						break;
                }
            }
            break;
		
		//handles HTTP_POST requests
        case 'POST':
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {

                    case 'login':
						if (isset($_POST['email'], $_POST['password'], $_POST['rememberMe'])) {
							$response = $request->login($_POST['email'], $_POST['password'], $_POST['rememberMe']);
						}
                        break;
                        
                    case 'sendRegistrationMail':
                        if (isset($_POST['email'], $_POST['password'])) {
                            $response = $request->sendRegistrationMail($_POST['email'], $_POST['password']);
                        }
                        break;

                    case 'completeUserRegistration':
                        if (isset($_POST['selector'], $_POST['validator'])) {
                            $response = $request->completeUserRegistration($_POST['selector'], $_POST['validator']);
                        }
                        break;                        

                    case 'sendResetPasswordMail':
                        if (isset($_POST['email'])) {
                            $response = $request->sendResetPasswordMail($_POST['email']);
                        }
                        break; 
                        
                    case 'completePasswordReset':
                        if (isset($_POST['selector'], $_POST['validator'])) {
                            $response = $request->completePasswordReset($_POST['selector'], $_POST['validator']);
                        }
                        break;                            

					case 'logout':
                        $response = $request->logout();
                        break;
                        
                    case 'processPublicMail':
                        if (isset($_POST['senderName'], $_POST['senderEmail'], $_POST['content'])) {
                            $response = $request->processPublicMail($_POST['senderName'], $_POST['senderEmail'], $_POST['content']);
                        }
                        break; 

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