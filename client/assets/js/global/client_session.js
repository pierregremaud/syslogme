clientSession = (function() {
	
    // Constante concernant les roles
    const INFO_USER = "syslogme";
    const PK_USER_ROLE_USER = 1; //has to match t_user_role SQL table
    const PK_USER_ROLE_ADMIN = 2; //has to match t_user_role SQL table

    //infoUser object containing user session variables
    var objInfoUser = JSON.parse(ls.get(INFO_USER, { decrypt: true }));

    /**
     * @ insert string received (beanUser) in local storage
     */
    function _setInfoUser(data) {

        //remove local storage info
        ls.remove(INFO_USER);
        //creates local storage info
        ls.set(INFO_USER, data, { encrypt: true });
    }
	
    /**
     * @ removes beanUser from local storage
     */
    function _removeInfoUser() {
        if (objInfoUser != null) {
            //remove local storage info
            ls.remove(INFO_USER);
        }
    }

    /**
     * @returns email or null
     */
     function _getEmail() {
        if (objInfoUser != null) {
            if (objInfoUser.email != null) {
                return objInfoUser.email;
            }
        }
        else {
            return null;
        }
    }
    
    /**
     * @returns token or null
     */
     function _getToken() {
        if (objInfoUser != null) {
            if (objInfoUser.token != null) {
                return objInfoUser.token;
            }
        }
        else {
            return null;
        }        
    }    

    /**
	 * returns true if user is admin
     */
    function _isAdmin() {
        if (objInfoUser != null) {
            if (objInfoUser.fk_user_role != null) {
                return (objInfoUser.fk_user_role == PK_USER_ROLE_ADMIN);
            }
        }
        else {
            return false;
        }            
    }

    return {
		setInfoUser: _setInfoUser,
		removeInfoUser: _removeInfoUser,
        getToken: _getToken,
        getEmail: _getEmail,
		isAdmin: _isAdmin
    };
})();