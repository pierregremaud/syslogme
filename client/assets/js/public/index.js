index = {

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

        //first validate all form fields
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
                api: 'server_public_api.php',
                params: {
                    action: 'processPublicMail',
                    senderName: senderName,
                    senderEmail: senderEmail,
                    content: content
                },
                successCallback : function(objResponse) {

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

    /* -------------------------------------------------------------------------- */
    /*                                   initMap                                  */
    /* -------------------------------------------------------------------------- */

    initMap: function() { 

        // The location of riaz
        const riaz = { lat: 46.6404401, lng: 7.0685642 };

        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 9,
            maxZoom: 17,
            minZoom: 2,
            center: riaz,
            mapTypeId: "terrain",
        });

        //sends the request to the server
        serverRequest.getData({
            
            method: 'GET',
            api: 'server_public_api.php',
            params: {action: 'getLocalizedDevices'},
            successCallback: function(objResponse) {

                if (objResponse.success == true) {	

                    const markers = [];
                    
                    //creates a marker for all devices in array objResponse.body
                    for (const device of objResponse.body) {

                        const marker = new google.maps.Marker({
                            position: {lat: parseFloat(device.latitude), lng: parseFloat(device.longitude)},
                            label: device.name,
                        });

                        //open wind chart when marker is clicked
                        marker.addListener("click", () => {

                            let selectedDevice = device.ttn_device_id;
                            window.location = 'wind_chart.html?device=' + selectedDevice;
                        });

                        //creates an InfoWindow - for mouseover
                        const infowindow = new google.maps.InfoWindow();

                        // add content to your InfoWindow
                        infowindow.setContent('<div class="scrollFix">' + device.name + '</div>');
    
                        //adds mouseover event
                        marker.addListener('mouseover', function() {

                            infowindow.open(map, this);
                        });
                        
                        // assuming you also want to hide the infowindow when user mouses-out
                        marker.addListener('mouseout', function() {
                            infowindow.close();
                        });                    
            
                        markers.push(marker);
                    }

                    // Add a marker clusterer to the map
                    let algorithm = new markerClusterer.GridAlgorithm({ maxZoom: 12 });

                    var markerCluster = new markerClusterer.MarkerClusterer({ map, markers, algorithm });
                }
                else {
                    
                    commonFunction.showNotification('warning',objResponse.message)
                }
            }
        });      
    }
}