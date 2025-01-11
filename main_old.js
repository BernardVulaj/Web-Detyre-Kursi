const apiUrl = 'http://localhost/detyra web/main.php'; // Adjust this path to your file location

export let cars = [];
    let currentPage = 1;
    const carsPerPage = 15; // Show 3 cars per page
    let totalPages = 0;

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

function displayCars(cars){
  const doc = document.querySelector('main');
  let html='';
  // for(let i=0; i<5; i++){

  //   html+=` <div class="container" id=${cars[i].id} onclick="showCarDetails()">
  //           <div  class="relative">
  //             <div class='img-container'><img src='../detyra web/images/cars/${cars[i].image_path}' alt='${cars[i].name}'/>
  //             </div>
  //             <p class="year">${cars[i].year} / $${cars[i].price_per_day}</p>
  //           </div>
  //           <div class="description">
  //             <p class="car-name">${cars[i].name}</p>
  //             <div class="car-details-container">
  //               <!-- <div class="car-details"> -->
  //                 <i class="bi bi-lightning-charge-fill">
  //                   <span class="text">2.${cars[i].engine}</span>
  //                 </i>
  //                 <i class="bi bi-fuel-pump-fill">
  //                   <span class="text">${cars[i].fuel}</span>
  //                 </i>
  //                 <i class="bi bi-hdd-network-fill">
  //                   <span class="text">${cars[i].transmission}</span>
  //                 </i>
  //               <!-- </div> -->
  //             </div>
  //           </div>
  //         </div>
  //       `
  // }
  
   cars.forEach(car=>{
    html+=` 
          <div class="container" id=${car.id} onclick="showCarDetails()">
            <div class="relative">
              <div class="img-container">
                <img src="../images/cars/${car.image_path}" alt="${car.name}" />
              </div>
              <p class="year">${car.year} / $${car.price_per_day}</p>
            </div>
            <div class="description">
              <p class="car-name">${car.name}</p>
              <div class="car-details-container">
                <i class="bi bi-lightning-charge-fill">
                  <span class="text">2.${car.engine}</span>
                </i>
                <i class="bi bi-fuel-pump-fill">
                  <span class="text">${car.fuel}</span>
                </i>
                <i class="bi bi-hdd-network-fill">
                  <span class="text">${car.transmission}</span>
                </i>
              </div>
            </div>
          </div>
        `
    })
    doc.innerHTML = html;
}

// Call the function to fetch car details
async function fetchAndDisplayCars() {
    cars = await getCarDetails();  // Wait for car details to be fetched
    // console.log(cars);  // Now you can log the cars data
    displayCars(cars);
  }

fetchAndDisplayCars();  // Call the function to fetch and display car details

// console.log(document.querySelector('.search-icon')) 
document.querySelector('.search-icon').addEventListener('click',()=>{
  const searchText=document.getElementById('search-input').value;
  // console.log(searchText);
  const keys=Object.keys(cars[0]).filter(filter=>filter!='id'&&filter!='image_path');
  const filteredData=cars.filter((car)=>
    car['name'].toLowerCase().includes(searchText.toLowerCase())
  )
  // console.log(filteredData)
  displayCars(filteredData)
})
document.getElementById('search-input').addEventListener('keydown', (event) => {
  if (event.key === 'Enter') {
    const searchText = event.target.value;
    const keys = Object.keys(cars[0]).filter(filter => filter !== 'id' && filter !== 'image_path');
    const filteredData = cars.filter((car) =>
      car['name'].toLowerCase().includes(searchText.toLowerCase())
    );
    displayCars(filteredData);
  }
});

document.querySelector('.search-field').addEventListener('input',(event)=>{
  if(event.target.value=='') fetchAndDisplayCars();
});





