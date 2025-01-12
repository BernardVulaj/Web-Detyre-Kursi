import { cars, fetchAndDisplayCars, paginateCars, setCars, restorePagination, getCarDetails } from "./main.js";

export let filters = {
  fuelType: [],
  price: '',
  transmission: ''
};

// Select all checkboxes within the container
const checkBoxes = document.querySelectorAll('.checkbox-container input[type="checkbox"]');
const numberInput = document.getElementById('numberInput');
const modal = document.querySelector('.modal');

checkBoxes.forEach((check) => {
  check.addEventListener('change', (event) => {
    const value = event.target.value;

    if (event.target.checked) {
      if (!filters.fuelType.includes(value)) {
        filters.fuelType.push(value);
      }
    } else {
      filters.fuelType = filters.fuelType.filter((fuel) => fuel !== value);
    }
  });
});

numberInput.addEventListener('input', (event) => {
  filters.price = event.target.value;
});

const modeSelect = document.getElementById('mode');

modeSelect.addEventListener('change', (event) => {
  filters.transmission = event.target.value;
});

var applyBtn = document.getElementById("applyBtn");
var removeBtn = document.getElementById("removeBtn");

applyBtn.onclick = function () {
  const temp = filterCars(cars, filters);
  setCars(temp);
  restorePagination(); // Reset pagination to the first page
  paginateCars(temp);
  modal.style.display = "none";
};

removeBtn.onclick = () => {
  removeFilters();
  modal.style.display = 'none';
};

function removeFilters() {
  checkBoxes.forEach((check) => {
    if (check.checked) check.checked = false;
  });
  numberInput.value = '';
  mode.value = '';
  
  filters.fuelType = [];
  filters.price = '';
  filters.transmission = '';
  fetchAndDisplayCars();
}

function filterCars(cars, filters) {
  getCarDetails();
  return cars.filter(car => {
    const matchesFuelType =
      !filters.fuelType.length || 
      filters.fuelType.some(fuel => fuel.trim().toLowerCase() === car.fuel.trim().toLowerCase());

    const matchesPrice =
      !filters.price || car.price_per_day <= parseFloat(filters.price);

    const matchesTransmission =
      !filters.transmission ||
      filters.transmission.toLowerCase() === car.transmission.toLowerCase();

    return matchesFuelType && matchesPrice && matchesTransmission;
  });
}
