document.addEventListener('click', function() {
    // Send the data using fetch to sessionTimeout.php
    fetch('sessionTimeout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json' // Specify the content type as JSON
        },
        body: JSON.stringify({updateActivity: true}) // Convert the JavaScript object to a JSON string
    })
    .then(response => response.json()) // Parse the JSON response from the server
    .then(result => {
        if(result.active){
            console.log("Sessioni eshte aktiv");
        }
    })
    .catch(error => {
        console.error('Timeout Error:', error); // Handle any errors
    });

    
});


setInterval(checkSessionTimeout, 1000); // Kontrollo çdo 1 sekond (mund të ndryshohet)

function checkSessionTimeout() {
    // Send the data using fetch to sessionTimeout.php
    fetch('sessionTimeout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json' // Specify the content type as JSON
        },
        body: JSON.stringify({updateActivity: false}) // Convert the JavaScript object to a JSON string
    })
    .then(response => response.json()) // Parse the JSON response from the server
    .then(result => {
        if(result.success){
            window.location.href = "login.html";
        }
    })
    .catch(error => {
        console.error('Timeout Error:', error); // Handle any errors
    });
}

