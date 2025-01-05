$(document).ready(function() {
    // Hide all profile sections initially
    toggleProfileSections({ user: false, list: false, admin: false });
    // Show only simulation
    $('#simulation').show();
});



function populateTable(tbodySelector, dataArray, fields) {
    const tbody = document.querySelector(tbodySelector);
    tbody.innerHTML = ""; // Clear previous rows
    dataArray.forEach(item => {
        const row = document.createElement('tr');
        fields.forEach(field => {
            const cell = document.createElement('td');
            cell.textContent = item[field] || '';
            row.appendChild(cell);
        });
        row.addEventListener('click', () => goDETAILS(item.id)); //Conect the click with the function
        tbody.appendChild(row);
    });
     // Add the "Add User" button at the end of the table
    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td colspan="3">
                <button onclick="showAddUserForm()">Add User</button>
            </td>
        </tr>
    `);
}

function get_user_main() {
    const userId = $('[name="simulate"]').val();
    if (!userId) {
        return;
    }
    $.ajax({
        url: 'get_user.php',
        method: 'GET',
        data: { id: userId }, 
        dataType: 'json',
        success: (data) => {
            if (!data || data.length === 0) {
                alert("User not found.");
                return;
            }
            const user = data[0];
            const { id, role_id } = user;

            if (role_id === 1) {
                alert('Get user list ad admnin' );
                get_list();
                toggleProfileSections({ user: false, list: true, admin: true });
            } else {
                alert('Get datas for regular user: '+userId);
                get_user(id);
                toggleProfileSections({ user: true, list: false, admin: false });
            }
        },
        error: (err) => {
            console.error("Error:", err);
            alert("An error occurred while fetching user data.");
        }
    });
}

function toggleProfileSections({ user, list, admin, addUser }) {
    $('#profile_user').toggle(user);
    $('#profile_list').toggle(list);
    $('#profile_admin').toggle(admin);
    $('#add_user_form').toggle(addUser);
    $('#simulation').toggle(false); // Hide simulation by default
}
function showAddUserForm() {
    // Hide all sections and show the add user form
    toggleProfileSections({ user: false, list: false, admin: false, addUser: true });

    // Add the form for adding a new user
    const formContainer = document.createElement('div');
    formContainer.id = 'add_user_form';
    formContainer.innerHTML = `
        <h2>Add New User</h2>
        <table>
            <tr>
                <td><input type="text" id="user_name_new" placeholder="Name"></td>
                <td><input type="text" id="user_email_new" placeholder="Email"></td>
                <td><input type="password" id="user_password_new" placeholder="Password"></td>
                <td><input type="number" id="user_role_new" placeholder="Role ID"></td>
                <td><input type="checkbox" id="user_verified_new"> Verified</td>
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
function cancelAddUser() {
    // Show the client list table
    toggleProfileSections({ user: false, list: true, admin: false, addUser: false });

    // Remove the add user form
    document.querySelector('#add_user_form').remove();
}
function submitNewUser() {
    const nameElement = document.querySelector('#user_name_new');
    const emailElement = document.querySelector('#user_email_new');
    const passwordElement = document.querySelector('#user_password_new');
    const roleElement = document.querySelector('#user_role_new');
    const verifiedElement = document.querySelector('#user_verified_new');
    const fileInput = document.querySelector('#user_profile_image_new');

    // Check if all required fields are filled
    if (!nameElement.value.trim() || !emailElement.value.trim() || !passwordElement.value.trim() || !roleElement.value.trim()) {
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
                const newName = nameElement.value;
                const newPassword = passwordElement.value;
                const newRole = roleElement.value;
                const newIsVerified = verifiedElement.checked ? 1 : 0;

                const formData = new FormData();
                formData.append('name', newName);
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
                        // Show the client list table again
                        toggleProfileSections({ user: false, list: true, admin: false, addUser: false });
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
function get_list() {
    $.ajax({
        url: 'admin_get_client_list.php',
        method: 'GET',
        dataType: 'json',   // jQuery will parse JSON for you
        success: response => {
            // Check if status is "success"
            if (!response || response.status !== "success") {
                alert("Error: " + (response ? response.message : "Unknown error"));
                return;
            }

            // response.data is already a JavaScript array
            const data = response.data;

            if (!data || data.length === 0) {
                alert("No clients available.");
                return;
            }
            
            // No need to JSON.parse(data), it’s already an array
            populateTable('#body-profile-list', data, ['id', 'name', 'email']);
        },
        error: err => {
            console.error("Error fetching client list:", err);
            alert("An error occurred while fetching the client list.");
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
function exit_admin() {
    // Fsheh seksionin e profilit të administratorit
    $('#profile_admin').hide();
    
    // Opsionalisht, mund të fshehësh edhe seksionet e tjera ose të bësh ndonjë veprim tjetër
    $('#profile_user').hide();
    $('#profile_list').show(); // Trego listën e klientëve

    // Mund të shtosh ndonjë logjikë tjetër që dëshiron këtu
    console.log("Exited admin profile view.");
}

function populateDetails(tbodySelector, user) {
    const tbody = document.querySelector(tbodySelector);
    tbody.innerHTML = ""; // Clear previous content

    let rowId = document.createElement('tr');
    
    let thId = document.createElement('td');
    thId.textContent = 'ID';
    
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

    // =========
    // NAME
    // =========
    let rowName = document.createElement('tr');
    
    let thName = document.createElement('td');
    thName.textContent = 'Name';
    
    let tdNameValue = document.createElement('td');
    let inputName = document.createElement('input');
    inputName.type = 'text';
    inputName.value = user.name;
    inputName.id = 'user_name_' + user.id; // or some unique ID
    
    tdNameValue.appendChild(inputName);
    rowName.appendChild(thName);
    rowName.appendChild(tdNameValue);
    tbody.appendChild(rowName);

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
    let rowPassword = document.createElement('tr');
    
    let thPassword = document.createElement('td');
    thPassword.textContent = 'Password';
    
    let tdPasswordValue = document.createElement('td');
    let inputPassword = document.createElement('input');
    inputPassword.type = user.pasword;
    inputPassword.id = 'user_password_' + user.id; // or some unique ID
    
    tdPasswordValue.appendChild(inputPassword);
    rowPassword.appendChild(thPassword);
    rowPassword.appendChild(tdPasswordValue);
    tbody.appendChild(rowPassword);

    // ==========================
    // PROFILE IMAGE (DISPLAY + INPUT)
    // ==========================
    let rowProfile = document.createElement('tr');
    
    let thProfile = document.createElement('td');
    thProfile.textContent = 'Profile Image';
    
    let tdProfileValue = document.createElement('td');

 	// 1) Display the existing image
     let imgElement = document.createElement('img');
     let imagePath = user.profile_image; // Path-i i plotë tashmë përfshin direktorinë 'images/'
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

	tbody.insertAdjacentHTML( 'beforeend', `
	    <tr>
	        <td colspan="2">
	            <button id="deleteUserButton"  onclick="exit_admin()">Exit </button>
	        </td>
	         <td colspan="2">
	            <button onclick="updateDETAILS(${user.id})">Update Profile</button>
	        </td>
	         
	         <td colspan="2">
	            <button onclick="deleteUser(${user.id})">Delete User</button>
	        </td>
	    </tr>
        
	`);
   
}


function updateDETAILS(userId) {
    console.log('userId:', userId);
    const nameElement = document.querySelector('#user_name_' + userId);
    const emailElement = document.querySelector('#user_email_' + userId);
    const passwordElement = document.querySelector('#user_password_' + userId);
    const roleElement = document.querySelector('#user_role_' + userId);
    const verifiedElement = document.querySelector('#user_verified_' + userId);
    const fileInput = document.querySelector('#user_profile_image_' + userId);

    if (!nameElement || !emailElement || !passwordElement || !roleElement || !verifiedElement || !fileInput) {
        console.error("One or more elements are missing.");
        alert("An error occurred: One or more elements are missing.");
        return;
    }

    const updatedName = nameElement.value;
    const updatedEmail = emailElement.value;
    const updatedPassword = passwordElement.value;
    const updatedRole = roleElement.value;
    const updatedIsVerified = verifiedElement.checked ? 1 : 0;

    const formData = new FormData();
    formData.append('id', userId);
    formData.append('name', updatedName);
    formData.append('email', updatedEmail);
    formData.append('password', updatedPassword);
    formData.append('role_id', updatedRole);
    formData.append('is_verified', updatedIsVerified);

    if (fileInput.files.length > 0) {
        formData.append('profile_picture', fileInput.files[0]);
    }

    $.ajax({
        url: 'admin_update_profile.php',
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
function addUser() {
    const nameElement = document.querySelector('#user_name_new');
    const emailElement = document.querySelector('#user_email_new');
    const passwordElement = document.querySelector('#user_password_new');
    const roleElement = document.querySelector('#user_role_new');
    const verifiedElement = document.querySelector('#user_verified_new');
    const fileInput = document.querySelector('#user_profile_image_new');

    if (!nameElement || !emailElement || !passwordElement || !roleElement || !verifiedElement || !fileInput) {
        console.error("One or more elements are missing.");
        alert("An error occurred: One or more elements are missing.");
        return;
    }

    const newName = nameElement.value;
    const newEmail = emailElement.value;
    const newPassword = passwordElement.value;
    const newRole = roleElement.value;
    const newIsVerified = verifiedElement.checked ? 1 : 0;

    const formData = new FormData();
    formData.append('name', newName);
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
if (!confirm("Are you sure you want to delete user with ID: " + userId + "?")) {
    return;
}

    $.ajax({
        url: 'delete.php',
        method: 'POST',
        data: { id: userId },
        success: (response) => {
            if (response.status === 'success') {
                alert('User deleted successfully.');
                // Refresh the user list or hide the user details
                get_list();
                toggleProfileSections({ user: false, list: true, admin: false });
            } else {
                alert('Failed to delete user: ' + response.message);
            }
        },
        error: (err) => {
            console.error("Error deleting user:", err);
            alert("An error occurred while deleting the user.");
        }
    });
}
