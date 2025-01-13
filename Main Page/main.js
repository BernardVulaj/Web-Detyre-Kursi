let apiUrl = 'http://localhost/Web-Detyre-Kursi/main.php'; // Adjust this path to your file location
import { filters } from "./filters.js";
export let cars = [];
let filteredCars = []; // To store filtered results
let currentPage = 1;
const carsPerPage = 3; // Show 3 cars per page
let totalPages = 0;
let searchText;


function setSearchURL(searchText){
  apiUrl=`http://localhost/Web-Detyre-Kursi/${searchText?'search':'main'}.php`
}

// Function to fetch car details with filters and pagination
export async function getCarDetails(filters = {}, page = 1) {
  try {
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ 
        ...filters,  // Send filters to the backend
        page, 
        carsPerPage 
      }),
    });

    const data = await response.json();  // Parse the JSON response

    if (data.success) {
      cars = data.cars; // Store the cars in the global variable
      totalPages = data.totalPages; // Store the total pages for pagination
      return cars; // Return the filtered car data
    } else {
      console.log("Error:", data.message);
      return []; // Return an empty array if no success
    }
  } catch (error) {
    console.error("Error fetching car details:", error); // Handle any fetch errors
    return []; // Return an empty array if there's a fetch error
  }
}

export function setCars(t) {
  cars = t;
}

export function restorePagination() {
  currentPage = 1;
}

export function displayCars(carsToDisplay) {
  const doc = document.querySelector('main');
  let html = '';
  carsToDisplay.forEach(car => {
    html += `
      <div class="container">
        <div id="${car.id}" class="relative">
          <div class="img-container">
            <img src="../Images/cars/${car.image_path}" alt="${car.name}" />
          </div>
          <p class="year">${car.year} / $${car.price_per_day}</p>
        </div>
        <div class="description">
          <p class="car-name">${car.name}</p>
          <div class="car-details-container">
            <i class="bi bi-lightning-charge-fill">
              <span class="text">${car.engine}</span>
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

  const data = filteredCars.length > 0 ? filteredCars : cars; // Use filtered cars if available
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
      paginateCars(data);
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

export async function paginateCars(data) {
  // const start = (currentPage - 1) * carsPerPage;
  // const end = currentPage * carsPerPage;
  // const carsToDisplay = data.slice(start, end); // Slice the passed data
  // displayCars(carsToDisplay);
  await getCarDetails(filters,currentPage);
  console.log(totalPages)
  updatePagination();
  displayCars(cars);
}

// Event listeners for pagination buttons
document.getElementById("prev-btn").addEventListener("click", async () => {
  if (currentPage > 1) {
    currentPage--;
    await paginateCars(filteredCars.length > 0 ? filteredCars : cars); // Use filteredCars if available
  }
});

document.getElementById("next-btn").addEventListener("click", async () => {
  if (currentPage < totalPages) {
    currentPage++;
    await paginateCars(filteredCars.length > 0 ? filteredCars : cars); // Use filteredCars if available
  }
});

// Call the function to fetch and display car details
export async function fetchAndDisplayCars(filters = {}) {
  await getCarDetails(filters, currentPage);  // Fetch car details from the API with optional filters
  filteredCars = [];      // Clear filtered results
  paginateCars(cars);     // Display the first page of cars
}

fetchAndDisplayCars();  // Initial call to fetch and display cars

// Search functionality
document.querySelector('.search-icon').addEventListener('click', () => {
  const searchText = document.getElementById('search-input').value;
  const dataToSearch = filteredCars.length > 0 ? filteredCars : cars; // Use filteredCars if available
  const filteredData = dataToSearch.filter(car =>
    car.name.toLowerCase().includes(searchText.toLowerCase())
  );
  displayCars(filteredData);
});

document.getElementById('search-input').addEventListener('keydown', (event) => {
  if (event.key === 'Enter') {
    const searchText = document.getElementById('search-input').value;
    const dataToSearch = filteredCars.length > 0 ? filteredCars : cars; // Use filteredCars if available
    const filteredData = dataToSearch.filter(car =>
      car.name.toLowerCase().includes(searchText.toLowerCase())
    );
    displayCars(filteredData);
    // restorePagination();
    // paginateCars(filteredData);
    console.log(filteredData);
  }
});

document.querySelector('.search-field').addEventListener('input', (event) => {
  if (event.target.value === '') {
    paginateCars(filteredCars.length > 0 ? filteredCars : cars);
  }
});
