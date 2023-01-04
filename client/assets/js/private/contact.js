contact = {

    /* -------------------------------------------------------------------------- */
    /*                                 submitMail                                 */
    /* -------------------------------------------------------------------------- */

        //sends contact request to server	
        submitMail: function() {

            //form validation is done by HTML checkValidity
            const senderName = document.getElementById('sender-name').value;
            const senderEmail = document.getElementById('sender-email').value;
            const content = document.getElementById('message-content').value;
            const form = document.getElementById("form");
    
            //first validate all form fields except captcha
            //https://getbootstrap.com/docs/5.0/forms/validation/
            form.classList.add('was-validated');        
    
            //if the form is not valid 
            if (form.checkValidity() === false) {
    
                //recent browser report nicely
                form.reportValidity();
            }
    
            //if the form is valid
            else {                     
    
                //send the mail 
                serverRequest.postData({
                    
                    method: 'POST',
                    api: 'server_user_api.php',
                    params:  {
                        action: 'processInternalMail', 
                        senderName: senderName, 
                        senderEmail: senderEmail, 
                        content: content
                    }, 
                    successCallback: function(objResponse) {
    
                        if (objResponse.success == true) {
        
                            commonFunction.showNotification('info',objResponse.message)
                        }
        
                        else {
                        
                            commonFunction.showNotification('warning',objResponse.message)
                        }
                    }               
                });
            }
        },
}