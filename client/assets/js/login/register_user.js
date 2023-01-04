registerUser = {

    /* -------------------------------------------------------------------------- */
    /*                                  initPage                                  */
    /* -------------------------------------------------------------------------- */

    initPage: function() {

        const selector = commonFunction.findGetParameter('selector');
        const validator = commonFunction.findGetParameter('validator');
        const button = document.getElementById('button');
        const cardTitle = document.getElementById('card-title');
        const form = document.getElementById('form');        

        //if selector and validator are present
        if (selector && validator) {

            //change the card title
            cardTitle.innerHTML = "Complete Registration";

            //disable all fields except captcha and register-button
            form.style.display = 'none';
            
            //change the button text
            button.innerHTML = "Complete Registration";

            //change the button function
            button.addEventListener('click', function handleClick() {registerUser.completeUserRegistration(selector, validator)});
        }

        //else if selector and validator are NOT present
        else {

            //change the card title
            cardTitle.innerHTML = "User Registration";

            //enable all fields except captcha and register-button
            form.style.display = 'block';

            //change the button text
            button.innerHTML = "Send Registration";            

            //change the button function
            button.addEventListener('click', function handleClick() {registerUser.sendRegistrationMail()});
        }
    },

    /* -------------------------------------------------------------------------- */
    /*                           completeUserRegistration                         */
    /* -------------------------------------------------------------------------- */

    completeUserRegistration: function(selector, validator) {

        //complete user registration
        serverRequest.postData({
            
            method: 'POST',
            api: 'server_public_api.php', 
            params: {
                action: 'completeUserRegistration',
                selector: selector, 
                validator: validator
            }, 
            successCallback: function(objResponse) { 

                if (objResponse.success == true) {

                    //redirect to login.html redir page
                    commonFunction.redirClient({

                        title: "Your account has been <b>successfully activated</b>",
                        subTitle: "Welcome on board!",
                        message: "Your account email is : <span style='color:blue; font-weight: bold;'>" + objResponse.body.email + "</span>",
                        btnURL: "/client/login/login.html",
                        btnText: "Go to Login page"
                    });                          
                }

                else {
                    
                    commonFunction.showNotification('warning',objResponse.message);	
                }                
            }
        });
    },

    /* -------------------------------------------------------------------------- */
    /*                            sendRegistrationMail                            */
    /* -------------------------------------------------------------------------- */
	
    sendRegistrationMail: function() {

        //password validation is done by HTML checkValidity
        const email = document.getElementById('email').value;
        const password = document.getElementById('new-password').value;
        const form = document.getElementById("form");

        //adds the :invalid and :valid styles to parent .was-validated class of the form
        //https://getbootstrap.com/docs/5.0/forms/validation/
        form.classList.add('was-validated');

        if (form.checkValidity() === false) {

            //recent browser report nicely
            form.reportValidity();
        }
        else {
        
            //send registration mail to user 
            serverRequest.postData({
                
                method: 'POST',
                api: 'server_public_api.php', 
                params: {
                    action: 'sendRegistrationMail',
                    email: email, 
                    password: password
                }, 
                successCallback: function(objResponse) {
                
                    if (objResponse.success == true) {

                        //redirect to login page
                        commonFunction.redirClient({

                            title: "An email has been sent to the following address with a verification link",
                            subTitle: email,
                            message:"Please check your mailbox and <b>click on the button in the email message</b> to complete your registration",
                            btnURL: "/client/login/login.html",
                            btnText: "Go to Login page",
                            footer: "If you did not receive the email, please follow" +
                                    "<span style='color:blue;'><a href='./register_user.html'> this link </a></span>and try to register again" +
                                    "</br>Please notice that the link is only valid 1 hour and that you can only use it once"
                        });                                 
                    }
                    else {
                        
                        commonFunction.showNotification('warning',objResponse.message);	
                    }
                }
            });
        }
    }
}  