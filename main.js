const apiUrl = 'http://localhost/detyra web/main.php'; // Adjust this path to your file location

    export let cars = [];
    let currentPage = 1;
    const carsPerPage = 15; // Show 3 cars per page
    let totalPages = 0;


    // Function to fetch car details
    export async function getCarDetails() {
      try {
        const response = await fetch(apiUrl);  // Fetch the data from the API
        const data = await response.json();    // Parse the JSON response

        if (data.success) {
          cars = data.cars;  // Store the cars in the global variable
          // totalPages = Math.ceil(cars.length / carsPerPage); // Calculate total pages
          return cars;  // Return the car data
        } else {
          console.log("Error:", data.message);
          return [];  // Return an empty array if no success
        }
      } catch (error) {
        console.error("Error fetching car details:", error);  // Handle any fetch errors
        return [];  // Return an empty array if there's a fetch error
      }
    }

    export function setCars(t){
      cars=t;
    }

    export function restorePagination(){
      currentPage=1;
    }

    export function displayCars(carsToDisplay) {
      const doc = document.querySelector('main');
      let html = '';
      carsToDisplay.forEach(car => {
        html += `
          <div class="container" id=${car.id} onclick="showCarDetails()">
            <div class="relative">
              <div class="img-container">
                <img src=" images/cars/${car.image_path}" alt="${car.name}" />
                
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
        `;
      });
      doc.innerHTML = html;
    }

    export function updatePagination() {
      const prevButton = document.getElementById("prev-btn");
      const nextButton = document.getElementById("next-btn");
      const pageLinksContainer = document.getElementById("page-links");
      totalPages = Math.ceil(cars.length / carsPerPage); // Calculate total pages

      // Clear previous page links
      pageLinksContainer.innerHTML = '';

      // Generate page links
      for (let i = 1; i <= totalPages; i++) {
        const pageLink = document.createElement('a');
        pageLink.href = `#page${i}`;
        pageLink.textContent = i;
        pageLink.classList.toggle('active', i === currentPage);

        // Add click event to update the page
        pageLink.addEventListener('click', (event) => {
          event.preventDefault();
          currentPage = i;
          paginateCars(cars);
        });

        pageLinksContainer.appendChild(pageLink);
      }

      // Disable previous button if we're on the first page
      prevButton.disabled = currentPage === 1;
      prevButton.classList.toggle("disabled", currentPage === 1);

      // Disable next button if we're on the last page
      nextButton.disabled = currentPage === totalPages;
      nextButton.classList.toggle("disabled", currentPage === totalPages);
    }

    export function paginateCars(cars) {
      // Slice the cars array based on the current page and display the corresponding cars
      const start = (currentPage - 1) * carsPerPage;
      const end = currentPage * carsPerPage;
      // console.log(cars)
      const carsToDisplay = cars.slice(start, end);

      displayCars(carsToDisplay);
      updatePagination();
    }

    // Event listeners for pagination buttons
    document.getElementById("prev-btn").addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        paginateCars(cars);
      }
    });

    document.getElementById("next-btn").addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        paginateCars(cars);
        console.log("cars:",cars)
      }
      // console.log(filters)
      // console.log(filterCars(cars))


    });

    // Call the function to fetch and display car details
    export async function fetchAndDisplayCars() {
      await getCarDetails();  // Fetch car details from the API
      paginateCars(cars);  // Display the first page of cars
    }

    fetchAndDisplayCars();  // Initial call to fetch and display cars

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

    
