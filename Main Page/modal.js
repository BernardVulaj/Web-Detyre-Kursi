
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
}