let imagePaths;
            let imagePosition = 0;
            let imagePathLength = 0;
            // function getCookie(name) {
            //     const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            //     return match ? match[2] : null;
            // }
            // // Check if "remember_token" cookie exists and is valid
            // const carId = getCookie('car_id');
            
            function showCarDetails(){

                const clickedElement = event.target.closest('.container');
                const carId = clickedElement.id;
                console.log(carId);
                imagePosition = 0;
                console.log("!");
                document.getElementById("carDetailsModal").style.display = 'block';  // Display the modal
                if(carId){
                  console.log(carId);
                  // Create an object to hold the user data
                  const data = {
                      carId: carId,
                      action: "getCarDetails"
                  };
              
                  // Send the data to the PHP backend using fetch()
                  fetch("carDetails.php",{
                      method: "POST",
                      headers: {
                          "Content-Type": "application/json"
                      },
                      body: JSON.stringify(data)
                  })
                  .then(response => response.json())
                  .then(data => {
                      if(data.success){
                          document.getElementById("carName").innerText = data.name;
                          document.getElementById("carPrice").innerText = data.pricePerDay;
                          // document.getElementById("carDescription").innerText = data.description;
                          document.getElementById("carSeatingCapacity").innerText = data.seatingCapacity;
                          document.getElementById("carEngine").innerText = data.engine;
                          document.getElementById("carTransmission").innerText = data.transmission;
                          if(data.bluetooth == 1){
                              document.getElementById("carBluetooth").innerText = "Po";
                          }
                          else{
                              document.getElementById("carBluetooth").innerText = "Jo";
                          }
                          if(data.gps == 1){
                              document.getElementById("carGPS").innerText = "Po";
                          }
                          else{
                              document.getElementById("carGPS").innerText = "Jo";
                          }
                          document.getElementById("carColor").innerText = data.color;
                          document.getElementById("carType").innerText = data.type;
                          document.getElementById("carYear").innerText = data.year;
                          imagePaths = data.images;
                          console.log('Image paths:', imagePaths);
                          imagePathLength = imagePaths.length;
                          console.log(imagePathLength);
                          console.log(imagePaths[imagePosition]);
                          document.getElementById("carImage").src = "images/cars/"+imagePaths[0];
                          ;
              
              
                      } else {
                          alert(data.message);  // Display error message
                      }
                  })
                  .catch(error => {
                      console.error("Error:", error);
                  });
                }
                else{
                    alert("Nuk eshte gjetur carId ne cookies");
                }
              
              
                const date = new Date();
                document.getElementById("startDate").min = date.toISOString().split('T')[0];
                date.setDate(date.getDate() + 1);
                document.getElementById("endDate").min = date.toISOString().split('T')[0];
              
              }

              window.onclick = function(event) {
                if (event.target == document.getElementById("carDetailsModal")) {
                  document.getElementById("carDetailsModal").style.display = "none";
                }
              }
              document.getElementById("dilButton").addEventListener("click", function(){
                document.getElementById("carDetailsModal").style.display = "none";

              })

            //e vendosa limitin e rentimit 30 dit mund ta ndryshojm
            document.getElementById("startDate").addEventListener("change", function(){
                const date = new Date(document.getElementById("startDate").value);
                if(document.getElementById("startDate").value == ""){
                    document.getElementById("endDate").max = "";

                    const date = new Date();
                    date.setDate(date.getDate() + 1);
                    document.getElementById("endDate").min = date.toISOString().split('T')[0];
                }
                else{
                    date.setDate(date.getDate() + 1);
                    document.getElementById("endDate").min = date.toISOString().split('T')[0];

                    date.setDate(date.getDate() + 29);
                    document.getElementById("endDate").max = date.toISOString().split('T')[0];
                }
                document.getElementById("endDate").value = '';  // Reset the end date if it's invalid

                
            });

            function calculateRentalDays() {
                let startDate = new Date(document.getElementById("startDate").value);
                let endDate = new Date(document.getElementById("endDate").value);
                if(document.getElementById("startDate").value == "" || document.getElementById("endDate").value == ""){
                    document.getElementById("diteTotale").textContent = "";
                }
                else{
                    const diferenca = (endDate - startDate)/(1000 * 3600 * 24);
                    document.getElementById("diteTotale").textContent = diferenca;
                }

            }
            document.getElementById("startDate").addEventListener('change', calculateRentalDays);
            document.getElementById("endDate").addEventListener('change', calculateRentalDays);

            
            document.getElementById("leftArrow").addEventListener('click', function(){
                if(imagePosition - 1 >= 0){
                    imagePosition--;
                    // console.log(imagePosition);

                    document.getElementById("carImage").src = "images/cars/"+imagePaths[imagePosition];

                }
            });

            document.getElementById("rightArrow").addEventListener('click', function(){
                if(imagePosition + 1 < imagePathLength){
                    imagePosition++;
                    // console.log(imagePosition);

                    document.getElementById("carImage").src = "images/cars/"+imagePaths[imagePosition];

                }
            });

            document.getElementById("rezervoButton").addEventListener("click", function(){

                if(document.getElementById("startDate").value == "" || document.getElementById("endDate").value == ""){
                    alert("Duhet te plotesoni daten per rezervim");
                    return;
                }
                console.log(document.getElementById("diteTotale").textContent);
                console.log(document.getElementById("startDate").value); 
                console.log(document.getElementById("endDate").value); 
                console.log(document.getElementById("carPrice").value);

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
                        } else {
                            alert(data.message);  // Display error message
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });

            });
