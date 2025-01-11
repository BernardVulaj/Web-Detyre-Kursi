let filters = {
  fuelType: [],
  price:'',
  transmission:''
};

// Select all checkboxes within the container
const checkBoxes = document.querySelectorAll('.checkbox-container input[type="checkbox"]');
const numberInput= document.getElementById('numberInput')
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
  console.log({filters})
  modal.style.display = "none";
}
removeBtn.onclick=()=>{
  removeFilters();
  console.log(filters);
  modal.style.display='none';
}



function removeFilters(){
  checkBoxes.forEach(check=>{
    if(check.checked) check.checked=false;
    // console.log(check.checked)
    // if(check.target.checked) check.target.checked=false
  });
  numberInput.value='';
  mode.value='';
   filters = {
    fuelType: [],
    price: '',
    transmission: ''
  };

}