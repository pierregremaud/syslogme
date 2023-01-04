login = {

    /* -------------------------------------------------------------------------- */
    /*                                    login                                   */
    /* -------------------------------------------------------------------------- */

    //sends login request to server	
    login: function() {

        //password validation is done by HTML checkValidity
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('remember').checked;
        const form = document.getElementById("form");

        //first validate all form fields except captcha
        //https://getbootstrap.com/docs/5.0/forms/validation/
        form[0].classList.add('was-validated');        

        if (form[0].checkValidity() === false) {

            //recent browser report nicely
            form[0].reportValidity();
        }
        else {                      

            //try to log the user 
            serverRequest.postData({
            
                method: 'POST',
                api: 'server_public_api.php',
                params: {
                    action: 'login',
                    email: email,
                    password: password,
                    rememberMe: rememberMe
                },
                successCallback: function(objResponse) {
                
                    //login successful
                    if (objResponse.success == true) {
        
                        //updates the local storage with session info
                        clientSession.setInfoUser(JSON.stringify(objResponse.body));

                        //redirect to dashboard
                        window.location.href = window.location.protocol + "//" + window.location.hostname + "/client/private/dashboard.html"
                    }

                    //login failed
                    else {
        
                        commonFunction.showNotification('warning',JSON.stringify(objResponse.message))
                    }
                }
            });
        }                    
    }
} 