import { cars,displayCars,fetchAndDisplayCars,paginateCars,setCars } from "./main.js";

export let filters = {
  fuelType: [],
  price:'',
  transmission:''
};

// Select all checkboxes within the container
const checkBoxes = document.querySelectorAll('.checkbox-container input[type="checkbox"]');
const numberInput= document.getElementById('numberInput')
const modal=document.querySelector('.modal');
checkBoxes.forEach((check) => {
  check.addEventListener('change', (event) => {
    const value = event.target.value;

    if (event.target.checked) {
      // Add the value to the array if it's checked
      if (!filters.fuelType.includes(value)) {
        filters.fuelType.push(value);
      }
    } else {
      // Remove the value from the array if it's unchecked
      filters.fuelType = filters.fuelType.filter((fuel) => fuel !== value);
    }

    // console.log('Selected Fuel Types:', filters.fuelType);
  });
});

numberInput.addEventListener('input',(event)=>{
  console.log(event.target.value)
  filters.price=event.target.value;
  // console.log({filters})
})

const modeSelect = document.getElementById('mode');

// Add an event listener to update the filters object when the selection changes
modeSelect.addEventListener('change', (event) => {
  // Update the filters object with the selected mode
  filters.transmission = event.target.value;
  // console.log('Selected Mode:', filters.transmission);
});

var applyBtn = document.getElementById("applyBtn");
var removeBtn = document.getElementById("removeBtn");

applyBtn.onclick = function() {
  // console.log({filters})
  modal.style.display = "none";
  // console.log(cars)
  // console.log(filterCars(cars,filters));
  setCars(filterCars(cars,filters));
  paginateCars(cars)
}
removeBtn.onclick=()=>{
  removeFilters();
  // console.log(filters);
  modal.style.display='none';
}



function removeFilters() {
  checkBoxes.forEach((check) => {
    if (check.checked) check.checked = false;
  });
  numberInput.value = '';
  mode.value = '';
  
  // Reset the properties without reassigning
  filters.fuelType = [];
  filters.price = '';
  filters.transmission = '';
  fetchAndDisplayCars();
}

function filterCars(cars, filters) {
  return cars.filter(car => {
    // Match fuel type (case-insensitive)
    const matchesFuelType =
      !filters.fuelType ||
      filters.fuelType.length === 0 ||
      filters.fuelType.some(fuel => fuel.toLowerCase() === car.fuel.toLowerCase());

    // Match price
    const matchesPrice =
      !filters.price || filters.price === '' || car.price_per_day <= parseFloat(filters.price);

    // Match transmission (case-insensitive)
    const matchesTransmission =
      !filters.transmission ||
      filters.transmission === '' ||
      filters.transmission.toLowerCase() === car.transmission.toLowerCase();

    // Return true if all conditions match
    return matchesFuelType && matchesPrice && matchesTransmission;
  });
}



