changePassword = {

    /* -------------------------------------------------------------------------- */
    /*                               changePassword                               */
    /* -------------------------------------------------------------------------- */
	
    changePassword: function() {

        //password validation is done by HTML checkValidity
        const currentPwd = document.getElementById("current-password").value;
        const newPwd = document.getElementById("new-password").value;
        var form = document.getElementById("form"); // WARNING: has to be declared as 'var' to be retrieved by 'success' callback function

        //adds the :invalid and :valid styles to parent .was-validated class of the form
        //https://getbootstrap.com/docs/5.0/forms/validation/
        form.classList.add('was-validated');        

        if (form.checkValidity() === false) {

            //recent browser report nicely
            form.reportValidity();
        }
        else {

            //change password in database 
            serverRequest.postData({

                method: 'PUT',
                api: 'server_user_api.php',
                params: {             
                    action: 'changePassword',
                    currentPwd: currentPwd,
                    newPwd: newPwd
                },
                successCallback: function(objResponse) {	
                
                    if (objResponse.success == true){

                        //updates the title
                        document.getElementById("card-title").innerHTML = "Password successfully changed";

                        //updates the sub-title
                        document.getElementById("card-sub-title").innerHTML = "Please use this password next time you log in";

                        //clears all passwords and hide the form
                        form.reset();
                        form.style.display = 'none';
                    }
                    else {
                
                        commonFunction.showNotification('warning',objResponse.message);			
                    }									
                }
            });
        }
    }    
}