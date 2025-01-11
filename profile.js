$(document).ready(function() {
    // Hide all profile sections initially
    toggleProfileSections({ user: false, list: false, admin: false,addUser: false, menueadmin: false, car: false,addCar:false });
    get_user_main();
});

function toggleProfileSections({ user, list, admin, addUser, menueadmin, car,addCar}) {
    $('#profile_user').toggle(user);
    $('#profile_list').toggle(list);
    $('#profile_admin').toggle(admin);
    $('#add_user_form').toggle(addUser);
    $('#menue_admin').toggle(menueadmin);
    $('#car_list_form').toggle(car);
    $('#car_details').toggle(addCar);
    // $('#carDetailsModal').toggle(foto);
    // $('#detailsContainer').toggle(pershkrimi);
}

function populateTable(tbodySelector, dataArray, fields) {
    const tbody = document.querySelector(tbodySelector);
   
    tbody.innerHTML = ""; // Clear previous rows
    dataArray.forEach(item => {
        const row = document.createElement('tr');

        row.id=item.id;
        row.classList.add("container");
        fields.forEach(field => {
            const cell = document.createElement('td');
            cell.textContent = item[field] || '';
            row.appendChild(cell);
        });
        
        if (currentTableType === 'user') {//type eshte parametri qe i kalohet ne goDetails ose gocarDetails
            row.addEventListener('click', () => goDETAILS(item.id)); // Connect the click with the function
        } else if (currentTableType === 'car') {
            row.addEventListener('click', () => showCarDetails()); // Connect the click with the function
        }
    

        tbody.appendChild(row);
    });
    // Add the "Add User" button at the end of the table
    if (currentTableType === 'user') {
    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td colspan="1">
                <button onclick="showAddUserForm()">Add User</button>
            </td>
            <td colspan="1">
                            <button onclick="signOut()">Sign Out</button>  
                 </td>
               <td colspan="1">
                <button onclick="exit_list()">Exit</button>
            </td>
        </tr>
    `);
}
else if (currentTableType === 'car') {
    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td colspan="3">
                <button onclick="showAddCarForm()">Add Car</button>
            </td>
            <td colspan="3">
                            <button onclick="signOut()">Sign Out</button>  
                 </td>
             <td colspan="3">
                <button onclick="exit_list()">Exit</button>
            </td>
        </tr>
    `);
}
}

function get_user_main() {
    $.ajax({
        url: 'get_user.php',
        method: 'GET',
        dataType: 'json',
        success: (data) => {
            if (!data || data.length === 0) {
                alert("User not found.");
                return;
            }

             role_id = data['role_id'];
             user_id = data['id'];
            if (role_id === 1) {
                alert('Get user list as admin');
                toggleProfileSections({ user: false, list: false, admin: false, menueadmin: true, car: false,addCar:false });
            } else {
                get_user(user_id);
                toggleProfileSections({ user: true, list: false, admin: false, menueadmin: false,car:false,addCar:false});
            }
        },
        error: (err) => {
            console.error("Error:", err);
            alert("An error occurred while fetching user data.");
        }
    });
}

function get_list() {
    currentTableType = 'user';
    $.ajax({
        url: 'admin_get_client_list.php',
        method: 'GET',
        dataType: 'json',
        success: response => {
            if (!response || response.status !== "success") {
                alert("Error: " + (response ? response.message : "Unknown error"));
                return;
            }

            const data = response.data;

            if (!data || data.length === 0) {
                alert("No clients available.");
                return;
            }

            populateTable('#body-profile-list', data, ['id', 'username', 'email']);
            toggleProfileSections({ user: false, list: true, admin: false, menueadmin: false, car: false,addCar:false }); // Show the list
        },
        error: err => {
            console.error("Error fetching client list:", err);
            alert("An error occurred while fetching the client list.");
        }
    });
}

function get_car_list() {
    currentTableType = 'car';
    $.ajax({
        url: 'admin_get_carlist.php',
        method: 'GET',
        dataType: 'json',
        success: response => {
            if (!response || response.status !== "success") {
                alert("Error: " + (response ? response.message : "Unknown error"));
                return;
            }

            const data = response.data;

            if (!data || data.length === 0) {
                alert("No cars available.");
                return;
            }

            populateTable('#body-profile-details-cars-list', data, ['name', 'price_per_day', 'type']);
            toggleProfileSections({ user: false, list: false, admin: false, menueadmin: false, car: true,addCar:false}); // Show the car list
        },
        error: err => {
            console.error("Error fetching car list:", err);
            alert("An error occurred while fetching the car list.");
        }
    });
}

function get_user(userId) {
    $.ajax({
        url: 'admin_get_client_select.php',
        method: 'GET',
        data: { id: userId },
        dataType: 'json',
        success: client => {
            populateDetails('#body-profile-details-user', client);
            $('#profile_user').show();
        },
        error: err => {
            console.error("Error fetching user details:", err);
            alert("An error occurred while fetching user details.");
        }
    });
}
function goDETAILS(userId) {
    $.ajax({
        url: 'admin_get_client_select.php',
        data: { id: userId },
        method: 'GET',
        dataType: 'json',
	success: user => {
    		populateDetails('#body-profile-details', user);
    		$('#profile_admin').show();
        },
        error: function (err) {
            console.error("Error fetching client details:", err);
            alert("An error occurred while fetching client details.");
        }
    });
    
}
function go_carsDETAILS(carId) {
    $.ajax({
        url: 'admin_get_car_select.php',
        data: { id: carId },
        method: 'GET',
        dataType: 'json',
	success: car => {
    	window.location.href = `car_details.php?id=${carId}`;
        },
        error: function (err) {
            console.error("Error fetching client details:", err);
            alert("An error occurred while fetching client details.");
        }
    });
    
}
function showAddUserForm() {
    // Hide all sections and show the add user form
    toggleProfileSections({ user: false, list: false, admin: false,car:false,menueadmin:false, addUser: true,addCar:false});

    // Add the form for adding a new user
    const formContainer = document.createElement('div');
    formContainer.id = 'add_user_form';
    formContainer.innerHTML = `
        <h2>Add New User</h2>
<table>
    <tr>
        <td><input type="text" id="user_name_new" placeholder="Username"></td>
    </tr>
    <tr>
        <td><input type="text" id="user_fullname_new" placeholder="Emri i Plote"></td>
    </tr>
    <tr>
        <td><input type="text" id="user_address_new" placeholder="Adresa"></td>
    </tr>
    <tr>
        <td><input type="tel" id="user_phone_number_new" placeholder="Nr.Telefoni"></td>
    </tr>
    <tr>
        <td><input type="text" id="user_email_new" placeholder="Email"></td>
    </tr>
    <tr>
        <td><input type="password" id="user_password_new" placeholder="Password"></td>
    </tr>
    <tr>
        <td><input type="number" id="user_role_new" placeholder="Role ID"></td>
    </tr>
    <tr>
        <td><input type="checkbox" id="user_verified_new"> Verified</td>
    </tr>
    <tr>
        <td><input type="file" id="user_profile_image_new" accept="image/*"></td>
    </tr>
    <tr>
        <td>
            <button onclick="submitNewUser()">Submit</button>
            <button onclick="cancelAddUser()">Cancel</button>
        </td>
    </tr>
</table>
    `;
    document.body.appendChild(formContainer);
}
function showAddCarForm(){
    toggleProfileSections({ user: false, list: false, admin: false,addUser: false, menueadmin: false, car: false,addCar:true});

}
function cancelAddUser() {
    // Show the client list table
    toggleProfileSections({ user: false, list: true, admin: false, addUser: false,addCar:false,menueadmin:false,car:false });

    // Remove the add user form
    document.querySelector('#add_user_form').remove();
}
function cancelAddCar() {
    // Show the client list table
        toggleProfileSections({ user: false, list: false, admin: false,addUser: false, menueadmin: false, car: true,addCar:false });

}
function submitNewUser() {
    const usernameElement = document.querySelector('#user_name_new');
    const emailElement = document.querySelector('#user_email_new');
    const fullnameElement= document.querySelector('#user_fullname_new');
    const addressElement= document.querySelector('#user_address_new');
    const telephoneElement= document.querySelector('#user_phone_number_new');
    const passwordElement = document.querySelector('#user_password_new');
    const roleElement = document.querySelector('#user_role_new');
    const verifiedElement = document.querySelector('#user_verified_new');
    const fileInput = document.querySelector('#user_profile_image_new');

    // Check if all required fields are filled
    if (!usernameElement.value.trim() || !fullnameElement.value.trim() || !addressElement.value.trim() || !telephoneElement.value.trim() ||
     !emailElement.value.trim() || !passwordElement.value.trim() || !roleElement.value.trim()) {
        alert("Please fill in all required fields.");
        return;
    }

    // Validate email format
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(emailElement.value.trim())) {
        alert("Please enter a valid email address.");
        return;
    }

    // Check if profile image is provided
    if (fileInput.files.length === 0) {
        alert("Please upload a profile image.");
        return;
    }

    const newEmail = emailElement.value.trim();

    // Check if email is unique
    $.ajax({
        url: 'check_email.php',
        method: 'POST',
        data: { email: newEmail },
        dataType: 'json',
        success: response => {
            if (response.exists) {
                alert("This email is already in use. Please use a different email.");
            } else {
                // Proceed with form submission
                const newName = usernameElement.value;
                const newPassword = passwordElement.value;
                const newRole = roleElement.value;
                const newIsVerified = verifiedElement.checked ? 1 : 0;
                const newFullname= fullnameElement.value;
                const newAddress= addressElement.value;
                const newTelephone= telephoneElement.value;

                const formData = new FormData();
                formData.append('username', newName);
                formData.append('email', newEmail);
                formData.append('password', newPassword);
                formData.append('role_id', newRole);
                formData.append('is_verified', newIsVerified);
                formData.append('fullname', newFullname);
                formData.append('address', newAddress);
                formData.append('telephone', newTelephone);
               

                if (fileInput.files.length > 0) {
                    formData.append('profile_picture', fileInput.files[0]);
                }

                $.ajax({
                    url: 'admin_addUser.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: response => {
                        console.log(response);
                        alert('User added successfully!');
                        // Show the client list table again
                        toggleProfileSections({ user: false, list: true, admin: false, addUser: false,menueadmin:false,addCar:false });
                        // Remove the add user form
                        document.querySelector('#add_user_form').remove();
                        // Refresh the client list
                        get_list();
                    },
                    error: err => {
                        console.error("Error adding user:", err);
                        alert("An error occurred while adding the user.");
                    }
                });
            }
        },
        error: err => {
            console.error("Error checking email:", err);
            alert("An error occurred while checking the email.");
        }
    });
}
function submitNewCar() {
    const nameElement = document.querySelector('#name_new');
    const priceElement = document.querySelector('#price_per_day_new');
    const fuelElement = document.querySelector('#fuel_new');
    const seatsElement = document.querySelector('#seating_capacity_new');
    const engineElement = document.querySelector('#engine_new');
    const transmissionElement = document.querySelector('#transmission_new');
    const yearElement = document.querySelector('#year_new');
    const bluetoothElement = document.querySelector('#bluetooth_new');
    const gpsElement = document.querySelector('#gps_new');
    const colorElement = document.querySelector('#color_new');
    const typeElement = document.querySelector('#type_new');
    const fileInput = document.querySelector('#profile_pictures_new');

    if (!nameElement.value.trim() || !priceElement.value.trim() || !fuelElement.value.trim() || !seatsElement.value.trim() ||
        !engineElement.value.trim() || !transmissionElement.value.trim() || !yearElement.value.trim() || !colorElement.value.trim() || !typeElement.value.trim()) {
        alert("Please fill in all required fields.");
        return;
    }

    const formData = new FormData();
    formData.append('name', nameElement.value);
    formData.append('price_per_day', priceElement.value);
    formData.append('fuel', fuelElement.value);
    formData.append('seating_capacity', seatsElement.value);
    formData.append('engine', engineElement.value);
    formData.append('transmission', transmissionElement.value);
    formData.append('year', yearElement.value);
    formData.append('bluetooth', bluetoothElement.checked ? 1 : 0);
    formData.append('gps', gpsElement.checked ? 1 : 0);
    formData.append('color', colorElement.value);
    formData.append('type', typeElement.value);

    if (fileInput.files.length > 0) {
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('profile_pictures[]', fileInput.files[i]);
        }
    }
    $.ajax({
        url: 'admin_addCar.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: response => {
            console.log(response);
            alert('Car added successfully!');
            toggleProfileSections({ user: false, list: false, admin: false, addUser: false, menueadmin: false, car: true, addCar: false });
            get_car_list();
        },
        error: err => {
            console.error("Error adding car:", err);
            alert("An error occurred while adding the car.");
        }
    });
}
function exit_admin() {
    // Fsheh seksionin e profilit të administratorit
    $('#profile_admin').hide();
    
    // Opsionalisht, mund të fshehësh edhe seksionet e tjera ose të bësh ndonjë veprim tjetër
    $('#profile_user').hide();
    $('#profile_list').show(); // Trego listën e klientëve

    // Mund të shtosh ndonjë logjikë tjetër që dëshiron këtu
    console.log("Exited admin profile view.");
}
function exit_list(){
    toggleProfileSections({user: false, list: false, admin: false, addUser: false, menueadmin: true, car: false, addCar: false});
}

function populateDetails(tbodySelector, user) {
    const tbody = document.querySelector(tbodySelector);
    tbody.innerHTML = ""; // Clear previous content

    let rowId = document.createElement('tr');
    
    let thId = document.createElement('td');
    thId.textContent = 'ID';
    
     if(role_id == 1){
        let tdIdValue = document.createElement('td');
        let inputId = document.createElement('input');
        inputId.type = 'text';
        inputId.value = user.id;
        inputId.readOnly = true; 
        // or inputId.disabled = true; if you prefer
        
        tdIdValue.appendChild(inputId);
        rowId.appendChild(thId);
        rowId.appendChild(tdIdValue);
        tbody.appendChild(rowId);

     }
    

    // =========
    // NAME
    // =========
    let rowName = document.createElement('tr');
    
    let thName = document.createElement('td');
    thName.textContent = 'Username';
    
    let tdNameValue = document.createElement('td');
    let inputName = document.createElement('input');
    inputName.type = 'text';
    inputName.value = user.username;
    inputName.id = 'user_name_' + user.id; // or some unique ID
    
    tdNameValue.appendChild(inputName);
    rowName.appendChild(thName);
    rowName.appendChild(tdNameValue);
    tbody.appendChild(rowName);

    // =========
    // Emri i Plote
    // =========
    let rowEmriPlote = document.createElement('tr');
    
    let thEmriPlote = document.createElement('td');
    thEmriPlote.textContent = 'Emri i Plote';
    
    let tdEmriPloteValue = document.createElement('td');
    let inputEmriPlote = document.createElement('input');
    inputEmriPlote.type = 'text';
    inputEmriPlote.value = user.full_name;
    inputEmriPlote.id = 'emri_plote_' + user.id; // or some unique ID
    
    tdEmriPloteValue.appendChild(inputEmriPlote);
    rowEmriPlote.appendChild(thEmriPlote);
    rowEmriPlote.appendChild(tdEmriPloteValue);
    tbody.appendChild(rowEmriPlote);

// =========
    // Adresa
    // =========
    let rowAdresa = document.createElement('tr');
    
    let thAdresa = document.createElement('td');
    thAdresa.textContent = 'Adresa';
    
    let tdAdresaValue = document.createElement('td');
    let inputAdresa = document.createElement('input');
    inputAdresa.type = 'text';
    inputAdresa.value = user.address;
    inputAdresa.id = 'adresa' + user.id; // or some unique ID
    
    tdAdresaValue.appendChild(inputAdresa);
    rowAdresa.appendChild(thAdresa);
    rowAdresa.appendChild(tdAdresaValue);
    tbody.appendChild(rowAdresa);

    
    // =========
    // Nr.teli
    // =========
    let rowtel = document.createElement('tr');
    
    let thtel = document.createElement('td');
    thtel.textContent = 'Nr.Telefonit';
    
    let tdtelValue = document.createElement('td');
    let inputtel = document.createElement('input');
    inputtel.type = 'tel';// e specifikuar nga html vtm per nr telefoni
    inputtel.value = user.phone_number;
    inputtel.id = 'tel' + user.id; // or some unique ID
    
    tdtelValue.appendChild(inputtel);
    rowtel.appendChild(thtel);
    rowtel.appendChild(tdtelValue);
    tbody.appendChild(rowtel);


    // =========
    // EMAIL
    // =========
    let rowEmail = document.createElement('tr');
    
    let thEmail = document.createElement('td');
    thEmail.textContent = 'Email';
    
    let tdEmailValue = document.createElement('td');
    let inputEmail = document.createElement('input');
    inputEmail.type = 'text'; // or "email"
    inputEmail.value = user.email;
    inputEmail.id = 'user_email_' + user.id;
    
    tdEmailValue.appendChild(inputEmail);
    rowEmail.appendChild(thEmail);
    rowEmail.appendChild(tdEmailValue);
    tbody.appendChild(rowEmail);

    
    // =========
    // PASSWORD
    // =========
    if(role_id==1){
    let rowPassword = document.createElement('tr');
    
    let thPassword = document.createElement('td');
    thPassword.textContent = 'Password';
    
    let tdPasswordValue = document.createElement('td');
    let inputPassword = document.createElement('input');
    inputPassword.type = 'password';
    inputPassword.id = 'user_password_' + user.id; // or some unique ID
    
    tdPasswordValue.appendChild(inputPassword);
    rowPassword.appendChild(thPassword);
    rowPassword.appendChild(tdPasswordValue);
    tbody.appendChild(rowPassword);
}
    // ==========================
    // PROFILE IMAGE (DISPLAY + INPUT)
    // ==========================
    let rowProfile = document.createElement('tr');
    
    let thProfile = document.createElement('td');
    thProfile.textContent = 'Foto Profili';
    
    let tdProfileValue = document.createElement('td');

 	// 1) Display the existing image
     let imgElement = document.createElement('img');
     let imagePath = 'images/' +user.profile_image; // Path-i i plotë tashmë përfshin direktorinë 'images/'
     imgElement.src = imagePath;
     imgElement.alt = "User Profile Image";
     imgElement.style.maxWidth = '100px';
     
     console.log("Image path:", imagePath);
     
     // Add error handler for image loading
     imgElement.onerror = () => {
         console.error("Image not found:", imagePath);
     };

	// 2) Create a file input (for uploading a new image)
	let fileInput = document.createElement('input');
	fileInput.type = 'file';
	fileInput.accept = 'image/*'; 
	fileInput.id = 'user_profile_image_' + user.id;

	// 3) When the user picks a file, show a preview (optional)
	fileInput.addEventListener('change', (event) => {
  	const file = event.target.files[0];
  	if (!file) return;
  
  	// Preview
  	const reader = new FileReader();
  	reader.onload = (e) => {
    	imgElement.src = e.target.result; // Update <img> to the new file data
	 };
	 reader.readAsDataURL(file);
	});

	// Then append them
	tdProfileValue.appendChild(imgElement);
	tdProfileValue.appendChild(document.createElement('br'));
	tdProfileValue.appendChild(fileInput);

	// Append profile row to tbody
	rowProfile.appendChild(thProfile);
	rowProfile.appendChild(tdProfileValue);
	tbody.appendChild(rowProfile);

	// ===================================
	// Example for Role, Verified, etc.
	// ===================================
	// role_id
     if(role_id == 1){
        let rowRole = document.createElement('tr');
        let thRole = document.createElement('td');
        thRole.textContent = 'Role ID';
        let tdRoleValue = document.createElement('td');
        let inputRole = document.createElement('input');
        inputRole.type = 'number';
        inputRole.value = user.role_id;
        inputRole.id = 'user_role_' + user.id;
        tdRoleValue.appendChild(inputRole);
        rowRole.appendChild(thRole);
        rowRole.appendChild(tdRoleValue);
        tbody.appendChild(rowRole);

    
	

	// is_verified
	let rowVerified = document.createElement('tr');
	let thVerified = document.createElement('td');
	thVerified.textContent = 'Is Verified';
	let tdVerifiedValue = document.createElement('td');
	let inputVerified = document.createElement('input');
	inputVerified.type= 'checkbox';
	// If user.is_verified is 1 (true) set checked
	inputVerified.checked= !!user.is_verified;
	inputVerified.id= 'user_verified_' + user.id;
    // if(user.role_id == 2){

        inputVerified.disabled = true;

    // }

	tdVerifiedValue.appendChild(inputVerified);
	rowVerified.appendChild(thVerified);
	rowVerified.appendChild(tdVerifiedValue);
	tbody.appendChild(rowVerified);

	// created_at
	let rowCreated= document.createElement('tr');
	let thCreated= document.createElement('td');
	thCreated.textContent= 'Created At';
	let tdCreatedValue= document.createElement('td');
	let inputCreated= document.createElement('input');
	inputCreated.type= 'text';
	inputCreated.value= user.created_at;
	inputCreated.readOnly= true; // or disabled
	tdCreatedValue.appendChild(inputCreated);
	rowCreated.appendChild(thCreated);
	rowCreated.appendChild(tdCreatedValue);
	tbody.appendChild(rowCreated);

	// updated_at
	let rowUpdated= document.createElement('tr');
	let thUpdated= document.createElement('td');
	thUpdated.textContent= 'Updated At';
	let tdUpdatedValue= document.createElement('td');
	let inputUpdated= document.createElement('input');
	inputUpdated.type= 'text';
	inputUpdated.value= user.updated_at;
	inputUpdated.readOnly= true; // or disabled
	tdUpdatedValue.appendChild(inputUpdated);
	rowUpdated.appendChild(thUpdated);
	rowUpdated.appendChild(tdUpdatedValue);
	tbody.appendChild(rowUpdated);
     }
     console.log(role_id);
     if(role_id==1){

     tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td colspan="2">
            <img id="picture3" src="images/exit.png" 
                 style="height:50px; width:50px; float:left;" 
                 onclick="exit_admin();">
                 </td>
                 
                 <td colspan="2">
            <img id="picture5" src="images/edit-user.png" 
                 style="height:50px; width:50px; float:right;" 
                 onclick="updateDETAILS(${user.id});">
                 </td>
                 <td colspan="2">
                  <img id="picture4" src="images/delete.png" 
                 style="height:50px; width:50px; float:right;" 
                 onclick="deleteUser(${user.id});">
                 </td>

        </tr>
      `);
    }else{
        tbody.insertAdjacentHTML('beforeend', `
            <tr>
              <td colspan="1">
                     <button onclick="signOut()">Sign Out</button>  
                     </td>
                     <td colspan="1">
                <button onclick="updateUser(${user.id})">Update User</button>
                     </td>
    
            </tr>
          `);

    }

   
}
// 2) Define the signOut function
function signOut() {
    // Make an AJAX request to logout.php
    $.ajax({
        url: 'logout.php',         // or '/api/logout'
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            console.log('Logout response:', response);
            // If successful, redirect the user to the login page
            window.location.href = 'index.php'; 
        },
        error: function(err) {
            console.error('Logout error:', err);
            alert("An error occurred while logging out.");
        }
    });
}




function updateDETAILS(userId) {
    console.log('userId:', userId);
    const usernameElement = document.querySelector('#user_name_' + userId);
    const emailElement = document.querySelector('#user_email_' + userId);
    const passwordElement = document.querySelector('#user_password_' + userId);
    const emriploteElement = document.querySelector('#emri_plote_'+ userId);
    const adresaElement=document.querySelector('#adresa'+ userId);
    const telElement= document.querySelector('#tel'+ userId);

    const roleElement = document.querySelector('#user_role_' + userId);
    const verifiedElement = document.querySelector('#user_verified_' + userId);
    const fileInput = document.querySelector('#user_profile_image_' + userId);

    if (!usernameElement || !emailElement || !passwordElement  || !emriploteElement  || !adresaElement  || !telElement  || !roleElement || !verifiedElement || !fileInput) {
        console.error("One or more elements are missing.");
        alert("An error occurred: One or more elements are missing.");
        return;
    }

    const updatedName = usernameElement.value;
    const updatedEmail = emailElement.value;
    const updatedPassword = passwordElement.value;
    const updatedEmriPlote= emriploteElement.value;
    const updatedAdresa= adresaElement.value;
    const updatedTel=telElement.value;

    const updatedRole = roleElement.value;
    const updatedIsVerified = verifiedElement.checked ? 1 : 0;

    const formData = new FormData();
    formData.append('id', userId);//celesat:id,username etj perdoren ne php per te marre vlerat
    formData.append('username', updatedName);
    formData.append('email', updatedEmail);
    formData.append('emriplote', updatedEmriPlote);
    formData.append('adresa', updatedAdresa);
    formData.append('nrtelefoni', updatedTel);
    
    formData.append('password', updatedPassword);
    formData.append('role_id', updatedRole);
    formData.append('is_verified', updatedIsVerified);

    if (fileInput.files.length > 0) {
        formData.append('profile_picture', fileInput.files[0]);
    }

    $.ajax({
        url: 'user_update_profile.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: response => {
            console.log(response);
            alert('Profile updated successfully!');
            get_list();
        },
        error: err => {
            console.error("Error updating profile:", err);
            alert("An error occurred while updating the profile.");
        }
    });
}


function updateUser(userId) {
    console.log('userId:', userId);
    const usernameElement = document.querySelector('#user_name_' + userId);
    const emailElement = document.querySelector('#user_email_' + userId);
    const passwordElement = document.querySelector('#user_password_' + userId);
    const emriploteElement = document.querySelector('#emri_plote_'+ userId);
    const adresaElement=document.querySelector('#adresa'+ userId);
    const telElement= document.querySelector('#tel'+ userId);

    const fileInput = document.querySelector('#user_profile_image_' + userId);

    if (!usernameElement || !emailElement || !emriploteElement  || !adresaElement  || !telElement || !fileInput) {
        console.error("One or more elements are missing.");
        alert("An error occurred: One or more elements are missing.");
        return;
    }

    const updatedName = usernameElement.value;
    const updatedEmail = emailElement.value;
    //const updatedPassword = passwordElement.value;
    const updatedEmriPlote= emriploteElement.value;
    const updatedAdresa= adresaElement.value;
    const updatedTel=telElement.value;

   
    const formData = new FormData();
    formData.append('id', userId);
    formData.append('username', updatedName);
    formData.append('email', updatedEmail);
   // formData.append('password', updatedPassword);
    formData.append('emriplote', updatedEmriPlote);
    formData.append('adresa', updatedAdresa);
    formData.append('nrtelefoni', updatedTel);
   
    
    if (fileInput.files.length > 0) {
        formData.append('profile_picture', fileInput.files[0]);
    }

    $.ajax({
        url: 'user_update_profile.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: response => {
            console.log(response);
            alert('User profile updated successfully!');
            
        },
        error: err => {
            console.error("Error updating profile:", err);
            alert("An error occurred while updating the profile.");
        }
    });
}

function addUser() {
    const usernameElement = document.querySelector('#user_name_new');
    const emailElement = document.querySelector('#user_email_new');
    const passwordElement = document.querySelector('#user_password_new');
    const roleElement = document.querySelector('#user_role_new');
    const verifiedElement = document.querySelector('#user_verified_new');
    const fileInput = document.querySelector('#user_profile_image_new');

    if (!usernameElement || !emailElement || !passwordElement || !roleElement || !verifiedElement || !fileInput) {
        console.error("One or more elements are missing.");
        alert("An error occurred: One or more elements are missing.");
        return;
    }

    const newName = usernameElement.value;
    const newEmail = emailElement.value;
    const newPassword = passwordElement.value;
    const newRole = roleElement.value;
    const newIsVerified = verifiedElement.checked ? 1 : 0;

    const formData = new FormData();
    formData.append('username', newName);
    formData.append('email', newEmail);
    formData.append('password', newPassword);
    formData.append('role_id', newRole);
    formData.append('is_verified', newIsVerified);

    if (fileInput.files.length > 0) {
        formData.append('profile_picture', fileInput.files[0]);
    }

    $.ajax({
        url: 'admin_addUser.php',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: response => {
            console.log(response);
            alert('User added successfully!');
        },
        error: err => {
            console.error("Error adding user:", err);
            alert("An error occurred while adding the user.");
        }
    });
}



function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}


function deleteUser(userId) {
    console.log("Deleting user with ID: ", userId); // debugging

    if (!confirm("Are you sure you want to delete user with ID: " + userId + "?")) {
        return;
    }

    fetch('delete.php', {
        method: 'POST',
        headers: {
            // Tells the server we are sending form data
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('User deleted successfully.');
            get_list();
            toggleProfileSections({ user: false, list: true, admin: false });
        } else {
            alert('Failed to delete user: ' + data.message);
        }
    })
    .catch(err => {
        console.error("Error deleting user:", err);
        alert("An error occurred while deleting the user.");
    });
}
