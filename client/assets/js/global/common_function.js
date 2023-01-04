commonFunction = {

	/* -------------------------------------------------------------------------- */
	/*                               checkServerSession                           */
	/* -------------------------------------------------------------------------- */

	//checks if the user has session on the server
	checkServerSession: function(redir) {

		//gets the PHP session parameters 
		serverRequest.getData({
            
            method: 'GET',
            api: 'server_public_api.php',
            params: {action: 'getPHPSession'},
            successCallback: function(objResponse) {	
	
                //if session exists on server
                if (objResponse.success == true) {

                    //updates the local storage with session info
                    clientSession.setInfoUser(JSON.stringify(objResponse.body));
        
                    //redirect to dashboard redir page if redir=true 
                    if (redir) {

                        commonFunction.redirClient({

                            title: "You have been redirected to :",
                            subTitle: "Windball Dashboard",
                            message:"You are currently logged in as : <span style='color:blue; font-weight: bold;'>" + objResponse.body.email + "</span>",
                            btnURL: "/client/private/dashboard.html",
                            btnText: "Go to Dashboard page",
                        });
                    }
    
                }

                //if no session exit on the server
                else {

                    //remove session info from local storage but do not redir
                    clientSession.removeInfoUser();
                }
            }
		});
	},

    /* -------------------------------------------------------------------------- */
    /*                             checkClientSession                             */
    /* -------------------------------------------------------------------------- */

	//checks if the user has session on the client
	checkClientSession: function() {
	
        //if the user does not have a client session
        if (!clientSession.getToken()) {				

            //redirect to login redir page
            commonFunction.redirClient({

                title: "You have been redirected to :",
                subTitle: "Windball Login",
                message: "You need to log in to access the requested page",
                btnURL: "/client/login/login.html",
                btnText: "Go to Login page"
            });
        }
	},

    /* -------------------------------------------------------------------------- */
    /*                                redirClient                                 */
    /* -------------------------------------------------------------------------- */

    //customizable redirect page (/client/login/redir.html)
    //  title = Title
    //  subTitle = SubTitle
    //  message = Message
    //  btnURL = The html page where to redirect user on button click (Example : "/client/login/register.html")
    //  btnText = The text on the button
    //  footer = The footer message


    redirClient: function (params) {

        //creates the base url
        let url = new URL(window.location.protocol + "//" + window.location.hostname + "/client/login/redir.html");

        //adds the params to the url if they exist
        params.title ?  url.searchParams.append("title", btoa(params.title)) : null;
        params.subTitle ?  url.searchParams.append("sub-title", btoa(params.subTitle)) : null;
        params.message ?  url.searchParams.append("message", btoa(params.message)) : null;
        params.btnURL ?  url.searchParams.append("btn-url", btoa(window.location.protocol + "//" + window.location.hostname + params.btnURL)) : null;
        params.btnText ?  url.searchParams.append("btn-text", btoa(params.btnText)) : null;
        params.footer ?  url.searchParams.append("footer", btoa(params.footer)) : null;

        //redirect the client browser to redir.html page with params
        window.location.href = url;
    },  

    /* -------------------------------------------------------------------------- */
    /*                                 loadPrivateNavBar                          */
    /* -------------------------------------------------------------------------- */

    //loads nav and side bar
    loadPrivateNavBar: function(pageName, activeNavItem) {

        //show customized menu depending on the user role
        document.getElementById('page-name').textContent = pageName;

        //display username in nav bar
        document.getElementById('navUserName').textContent = clientSession.getEmail();

        //activates the current Link
        if (activeNavItem != "") {
            document.getElementById(activeNavItem).classList.add('active');
        }                
        
        //populates side bar for admin
        if (clientSession.isAdmin()) {
            document.getElementById('dashboardNavItem').style.display = "block";;
            document.getElementById('changePwdNavItem').style.display = "block";;
            document.getElementById('logoutNavItem').style.display = "block";;
        }
        //populates side bar for user
        else {
            document.getElementById('dashboardNavItem').style.display = "block";;
            document.getElementById('changePwdNavItem').style.display = "block";;
            document.getElementById('contactNavItem').style.display = "block";;
            document.getElementById('logoutNavItem').style.display = "block";;
        }

        //adds event handler on element of class .sidebar-Collapse
        for (const item of document.getElementsByClassName('sidebar-collapse')) {
            item.addEventListener("click", () => { 
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            });
        }
    },

    /* -------------------------------------------------------------------------- */
    /*                                loadPublicNavBar                            */
    /* -------------------------------------------------------------------------- */

    loadPublicNavBar: function() {

        window.addEventListener('DOMContentLoaded', event => {

            // Navbar shrink function
            var navbarShrink = function () {
                const navbarCollapsible = document.body.querySelector('#main-nav');
                if (!navbarCollapsible) {
                    return;
                }
                if (window.scrollY === 0) {
                    navbarCollapsible.classList.remove('navbar-shrink');

                } else {
                    navbarCollapsible.classList.add('navbar-shrink');
                }

            };

            // Shrink the navbar 
            navbarShrink();

            // Shrink the navbar when page is scrolled
            document.addEventListener('scroll', navbarShrink);

            // Collapse responsive navbar when toggler is visible and a nav-link is clicked
            const navbarToggler = document.body.querySelector('.navbar-toggler');
            const responsiveNavItems = [].slice.call(
                document.querySelectorAll('#navbar-content .nav-link')
            );

            responsiveNavItems.map(function (responsiveNavItem) {
                responsiveNavItem.addEventListener('click', () => {
                    if (window.getComputedStyle(navbarToggler).display !== 'none') {
                        navbarToggler.click();
                    }
                });
            });
        });  
    },
 
    /* -------------------------------------------------------------------------- */
    /*                                   logout                                   */
    /* -------------------------------------------------------------------------- */

	logout: function() {
		
		//close the session
        serverRequest.postData({
            
            method: 'POST',
            api: 'server_public_api.php',
            params: {action:'logout'},
            successCallback : function(objResponse) {
       
                //if logout failed
                if (objResponse.success == false) {

                    commonFunction.showNotification('warning',objResponse.message);
                }
                
                //if logout successful
                else {

                    //removes user info from local storage
                    clientSession.removeInfoUser();
                    
                    //redirect to login
                    window.location.href = window.location.protocol + "//" + window.location.hostname + "/client/login/login.html"	
                }
            }
        });
	},
    
    /* -------------------------------------------------------------------------- */
    /*                              showNotification                              */
    /* -------------------------------------------------------------------------- */

	//display a notification on the current page
	showNotification: function(level, message) {

        let timer;
        let backgroundColor;

        switch (level) {
            case 'success': 
                backgroundColor = '#5cb85c';
                timer = 2000;
                break;
            case 'error': 
                backgroundColor = '#d9534f';
                timer = 0;
                break; 
            case 'warning': 
                backgroundColor = '#f0ad4e';
                timer = 4000;
                break;
            case 'info': 
                backgroundColor = '#5bc0de';
                timer = 2000;
                break;
            default: 
                backgroundColor = '#0275d8';
                timer = 0;
                break;                                                     
        }
		
        const Toast = Swal.mixin({
            toast: true,
            position: 'top',
            width: "50%",
            color: "#ffffff",
            background: backgroundColor,
            showConfirmButton: false,
            showCloseButton: true,
            timer: timer,
            timerProgressBar: false,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer)
              toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })
          
        Toast.fire({
            icon: level,
            title: message
        })
   
	},

    /* -------------------------------------------------------------------------- */
    /*                               togglePassword                               */
    /* -------------------------------------------------------------------------- */

    togglePassword: function(span) {  
        var input = span.parentNode.previousElementSibling;
        var showEye = span.firstElementChild;
        var hideEye = span.lastElementChild;
        hideEye.classList.remove("d-none");
        if (input.type === "password") {
            input.type = "text";
            showEye.style.display = "none";
            hideEye.style.display = "block";
        } else {
            input.type = "password";
            showEye.style.display = "block";
            hideEye.style.display = "none";
        }
    },

    /* -------------------------------------------------------------------------- */
    /*                              findGetParameter                              */
    /* -------------------------------------------------------------------------- */

    //get a parameter that was used to call the page
    findGetParameter: function(parameterName) {

        // Address of the current window
        address = window.location.search
    
        // Returns a URLSearchParams object instance
        parameterList = new URLSearchParams(address)
    
        // Returning the respected value associated
        // with the provided key
        return parameterList.get(parameterName)
    },      

    /* -------------------------------------------------------------------------- */
    /*                               comparePassword                              */
    /* -------------------------------------------------------------------------- */

    comparePassword: function(pwd1, pwd2) {

        if (pwd1.value !== pwd2.value) {
            pwd1.setCustomValidity("Duplicate passwords do not match");
            pwd2.setCustomValidity("Duplicate passwords do not match");
        } else {
            pwd1.setCustomValidity(""); // is valid
            pwd2.setCustomValidity("");  // is valid
        }
    },

    /* -------------------------------------------------------------------------- */
    /*                             objectToQueryString                            */
    /* -------------------------------------------------------------------------- */

    objectToQueryString: function (obj) {
        let str = [];
        for (let p in obj)
          if (obj.hasOwnProperty(p)) {
            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
          }
        return str.join("&");
    }
}	