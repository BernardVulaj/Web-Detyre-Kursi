<?php
// Connect to the database
$conn = new mysqli('car_rent', 'root', '', 'profile_db');

// Fetch user data
$sql = "SELECT * FROM users WHERE id = 1"; // Assuming user ID = 1
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="profile-container">
        <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-picture">
        <h1><?php echo $user['name']; ?></h1>
        <p>Email: <?php echo $user['email']; ?></p>
        <p>Password: <?php echo $user['password']; ?></p>
        <button id="editButton">Edit Profile</button>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <form id="editForm" method="POST" action="update_profile.php">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>">
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>">
                
                <label for="password">Bio:</label>
                <textarea id="password" name="password"><?php echo $user['password']; ?></textarea>
                
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture">

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
