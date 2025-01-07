const apiUrl = 'http://localhost/Web-Detyre-Kursi/main.php'; // Adjust this path to your file location

// Function to fetch car details
async function getCarDetails() {
    try {
        const response = await fetch(apiUrl);  // Fetch the data from the API
        const data = await response.json();    // Parse the JSON response

        if (data.success) {
            // console.log("Car Details:");
            // console.log(data.cars);  // Log the cars data
            return data.cars;  // Return the car data
        } else {
            console.log("Error:", data.message);
            return [];  // Return an empty array if no success
        }
    } catch (error) {
        console.error("Error fetching car details:", error);  // Handle any fetch errors
        return [];  // Return an empty array if there's a fetch error
    }
}

// Call the function to fetch car details
async function fetchAndDisplayCars() {
    const cars = await getCarDetails();  // Wait for car details to be fetched
    console.log(cars);  // Now you can log the cars data
    const doc = document.querySelector('main');
    cars.forEach(car=>{
      doc.innerHTML+=` <div class="container">
              <div id=${car.id} class="relative">
                <div class='img-container'><img src='../Images/cars/${car.image_path}' alt='${car.name}'/>
                </div>
                <p class="year">${car.year} / $${car.price_per_day}</p>
              </div>
              <div class="description">
                <p class="car-name">${car.name}</p>
                <div class="car-details-container">
                  <!-- <div class="car-details"> -->
                    <i class="bi bi-lightning-charge-fill">
                      <span class="text">2.${car.engine}</span>
                    </i>
                    <i class="bi bi-fuel-pump-fill">
                      <span class="text">${car.fuel}</span>
                    </i>
                    <i class="bi bi-hdd-network-fill">
                      <span class="text">${car.transmission}</span>
                    </i>
                  <!-- </div> -->
                </div>
              </div>
            </div>
          `
      })
      
  }

fetchAndDisplayCars();  // Call the function to fetch and display car details
