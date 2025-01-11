
var modal = document.getElementById("myModal");
var filterBtn = document.getElementById("filterBtn");
var closeBtn = document.getElementById("closeBtn");

// Open the modal when the "Filter" button is clicked
filterBtn.onclick = function() {
    modal.style.display = "block";
}

// Close the modal when the "X" (close) button is clicked
closeBtn.onclick = function() {
    modal.style.display = "none";
}

// Close the modal when the "Apply" button is clicked


// Close the modal if the user clicks anywhere outside the modal content
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
    if (event.target == document.getElementById("carDetailsModal")) {
        document.getElementById("carDetailsModal").style.display = "none";
      }
}

document.getElementById("rezervoButton").addEventListener("click", function(){

    if(document.getElementById("startDate").value == "" || document.getElementById("endDate").value == ""){
        alert("Duhet te plotesoni daten per rezervim");
        return;
    }
    console.log(document.getElementById("diteTotale").textContent);
    console.log(document.getElementById("startDate").value); 
    console.log(document.getElementById("endDate").value); 
    console.log(document.getElementById("carPrice").value);
    carId = getCookie('car_id');

    const data = {
            carId: carId,
            startDate: document.getElementById("startDate").value,
            endDate: document.getElementById("endDate").value,
            totalDays: document.getElementById("diteTotale").textContent,
            pricePerDay: document.getElementById("carPrice").innerText,
            action: "addPendingBook"
        };
    fetch("carDetails.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                alert(data.message);
                //vazhdo me transaction
            } else {
                alert(data.message);  // Display error message
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });

    });