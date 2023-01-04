redir = {

    /* -------------------------------------------------------------------------- */
    /*                                  initPage                                  */
    /* -------------------------------------------------------------------------- */

    initPage: function() {

        //gets params
        //WARNING : only ascii characters allowed
        const title = commonFunction.findGetParameter("title");
        const subTitle = commonFunction.findGetParameter("sub-title");
        const message = commonFunction.findGetParameter("message");
        const btnURL = commonFunction.findGetParameter("btn-url");
        const btnText = commonFunction.findGetParameter("btn-text");
        const footer = commonFunction.findGetParameter("footer");

        //if all params are null then display default html values
        if (!title && !subTitle && !message && !btnURL && !btnText && !footer) {

            //do nothing
        }

        else {

            //adds decoded params to page - if param is null then do not display
            document.getElementById('title').innerHTML = title ?  atob(title) : null;
            document.getElementById('sub-title').innerHTML = subTitle ?  atob(subTitle) : null;
            document.getElementById('message').innerHTML = message ?  atob(message) : null;
            document.getElementById('button').href = btnURL ?  atob(btnURL) : "#";
            document.getElementById('button').innerHTML = btnText ?  atob(btnText) : "btn-text";
            document.getElementById('footer').innerHTML = footer ?  atob(footer) : null;
        }
    },

    //test
    test: function() {

        commonFunction.redirClient({

            title: "myTitle",
            subTitle: "mySubTitle",
            message:"Error message",
            btnURL: "/client/login/login.html",
            btnText: "Go to Login",
            footer: ""
        });



        commonFunction.redirClient(title, subTitle, message, redirPage, redirText, footer);

    }
}