
serverRequest = {

    /* -------------------------------------------------------------------------- */
    /*                                  getData                                   */
    /* -------------------------------------------------------------------------- */

    //GET method implementation

    // options = {
    //      method: 'GET',
    //      api: 'server_public_api.php' or 'server_user_api' or ' server-admin_api',
    //      params: {
    //          action: the action to be performed ba the api, for example 'getPHPSession',
    //          param1: the first parameter for example 'username',
    //          param2: the second parameter for example 'password',
    //          etc...
    //      }
    // }

    getData: function (options) {

        const url = new URL(window.location.protocol + "//" + window.location.hostname + "/server/" + options.api);

        if (options.method == 'GET') {

            //adding the params to url
            url.search = new URLSearchParams(options.params).toString();

            //creates a GET promise (Default options are marked with *)
            const rawResponse = fetch (url, {

                method: 'GET', // *GET
                mode: 'cors', // no-cors, *cors, same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    'Content-Type': 'application/json',
                    //'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': clientSession.getToken()
                },
                redirect: 'follow', // manual, *follow, error
                referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            })

            //if GET promise returns something
            .then ((response) => {

                //check for network error
                if (!response.ok) {
                    throw new Error(options.api + ' HTTP error code ' + response.status + ' ' + response.statusText);
                }

                //create NEW promise with response
                return response.text();
            })

            //if NEW promise returns something
            .then ((data) => {

                //if the response is a JSON, returns the response as object
                try {
                    return options.successCallback(JSON.parse(data));
                }

                //if the response is not a JSON, raises an error
                catch {
                    throw new Error(options.api + ' response ' + data);
                }
            })  

            //if any promise failed
            .catch ((error) => {
                console.log(error.stack);
                commonFunction.showNotification('error', error);
                return;
            })            
        }

        else {

            commonFunction.showNotification('error', 'serverRequest.getData can only use GET method');            
        }
    },

    /* -------------------------------------------------------------------------- */
    /*                                  postData                                  */
    /* -------------------------------------------------------------------------- */

    //POST method implementation
    postData: async function (options) {

    // options = {
    //      method: 'POST' or 'PUT' or 'DELETE',
    //      api: 'server_public_api.php' or 'server_user_api' or ' server-admin_api',
    //      params: {
    //          action: the action to be performed ba the api, for example 'login',
    //          param1: the first parameter for example 'userName',
    //          param2: the second parameter for example 'password',
    //          etc...
    //      }
    // }        

        //preparing url
        const url = new URL(window.location.protocol + "//" + window.location.hostname + "/server/" + options.api);

        if ( (options.method == 'POST') || (options.method == 'PUT') || (options.method == 'DELETE') ) {

            //creates a POST promise (Default options are marked with *)
            const rawResponse = await fetch (url, {

                method: options.method, // POST, PUT or DELETE
                mode: 'cors', // no-cors, *cors, same-origin
                cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
                credentials: 'same-origin', // include, *same-origin, omit
                headers: {
                    'Accept': 'application/json; charset=UTF-8',
                    //'Content-Type': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': clientSession.getToken()
                },
                redirect: 'follow', // manual, *follow, error
                referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                body: commonFunction.objectToQueryString(options.params)// body data type must match "Content-Type" header
            })

            //if POST promise returns something
            .then ((response) => {

                //check for network error
                if (!response.ok) {
                    throw new Error(options.api + ' HTTP error code ' + response.status + ' ' + response.statusText);
                }

                //create NEW promise with response and catches its own error if response is not a JSON
                return response.text();
            })

            //if NEW promise returns something
            .then ((data) => {

                //if the response is a JSON, returns the response as object
                try {
                    return options.successCallback(JSON.parse(data));
                }

                //if the response is not a JSON, raises an error
                catch {
                    throw new Error(options.api + ' response ' + data);
                }
            })  

            //if any promise failed
            .catch ((error) => {
                console.log(error.stack);
                commonFunction.showNotification('error', error);
                return;
            })    
        }

        else {

            commonFunction.showNotification('error', 'serverRequest.postData can only use POST, PUT or DELETE methods');            
        }
    }
}