resetPassword = {

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
            cardTitle.innerHTML = "Complete Password Reset";

            //disable all fields except register-button
            form.style.display = 'none';
            
            //change the button text
            button.innerHTML = "Complete Password Reset";

            //change the button function
            button.addEventListener('click', function handleClick() {resetPassword.completePasswordReset(selector, validator)});
        }

        //else if selector and validator are NOT present
        else {

            //change the card title
            cardTitle.innerHTML = "Password Reset";

            //enable all fields except register-button
            form.style.display = 'block';

            //change the button text
            button.innerHTML = "Reset Password";            

            //change the button function
            button.addEventListener('click', function handleClick() {resetPassword.sendResetPasswordMail()});
        }
    },
    
    /* -------------------------------------------------------------------------- */
    /*                           completePasswordReset                            */
    /* -------------------------------------------------------------------------- */

    completePasswordReset: function(selector, validator) {


        //complete password reset
        serverRequest.postData({
            
            method: 'POST',
            api: 'server_public_api.php', 
            params: {
                action: 'completePasswordReset',
                selector: selector, 
                validator: validator
            }, 
            successCallback: function(objResponse) { 

                if (objResponse.success == true) {

                    //redirect to login.html redir page
                    commonFunction.redirClient({

                        title: "You have been logged in with a temporary password",
                        message: "Your account email is : <span style='color:blue; font-weight: bold;'>" + objResponse.body.email + "</span>" +
                        "</br></br>Click on the bellow button to choose a new password",
                        btnURL: "/client/private/change_password.html?pwd=" + objResponse.body.password,
                        btnText: "Choose a new password"
                    });                          
                }

                else {
                    
                    commonFunction.showNotification('warning',objResponse.message);	
                }                
            }
        });
    },    

    /* -------------------------------------------------------------------------- */
    /*                               sendResetPasswordMail                        */
    /* -------------------------------------------------------------------------- */

    sendResetPasswordMail: function() {

        //password validation is done by HTML checkValidity
        const email = document.getElementById('email').value;
        const form = document.getElementById("form");

        //adds the :invalid and :valid styles to parent .was-validated class of the form
        //https://getbootstrap.com/docs/5.0/forms/validation/
        form.classList.add('was-validated');        

        if (form.checkValidity() === false) {

            //recent browser report nicely
            form.reportValidity();
        }
        else {

            //sends a email to reset the user password 
            serverRequest.postData({
                
                method: 'POST',
                api: 'server_public_api.php',
                params: {
                    action: 'sendResetPasswordMail', 
                    email: email
                },
                successCallback: function(objResponse) {

                    if (objResponse.success == true){

                        //redirect to login page
                        commonFunction.redirClient({

                            title: "An email has been sent to the following address with a verification link",
                            subTitle: email,
                            message:"Please check your mailbox and <b>click on the button in the email message</b> to change your password",
                            btnURL: "/client/login/login.html",
                            btnText: "Go to Login page",
                            footer: "If you did not receive the email, please follow" + 
                                    "<span style='color:blue;'><a href='./reset_password.html'> this link </a></span>and start the process over" +
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