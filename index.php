<?php
// Start the session at the beginning of the file
session_start();

// Check if there is an ID in the session
$userImage = 'images/profileImage.jpg';  // Default profile image

if (isset($_SESSION['id'])) {
    // Connect to the database (adjust your database connection parameters)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "car_rental"; // Replace with your actual database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the user ID from the session
    $userId = $_SESSION['id'];

    // Prepare and execute the query to get the user's profile image
    $sql = "SELECT profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId); // Bind the user ID to the query
    $stmt->execute();
    $stmt->bind_result($profileImage); // Bind the result (image path)

    // Check if the image is found
    if ($stmt->fetch()) {
        // If a profile image is found, update the profile image path
        $userImage = "images/" . $profileImage;    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Home Page</title>
        <link rel="stylesheet" href="css/index.css">
    </head>
    <body>

            <div id="header">
                <div id="navigationBar">
                    <a href="index.php">
                        Home
                    </a>
                    <a>
                        Makinat
                    </a>
                    <a>
                        Rreth Nesh
                    </a>
                    <a>
                        Kontakto
                    </a>
    
                </div>
                <a id="profileLink" >
                    <img id="profileImage" src="Images/profileImage.jpg">
                </a>
            </div>

            <div id=mesazhiContainer>
                <label id="mesazhi">
                    Makina te reja dhe te sigurta, te pershtatshme per cdo lloj udhetimi, me sherbim te shpejte dhe te profesional.                
                </label>
            </div>

            <a id="makinatTona">
                Makinat Tona
            </a>
            <script>
                // Check if the 'id' exists in the PHP session and store it in a JavaScript variable
                <?php if (isset($_SESSION['id'])): ?>
                    var userId = '<?php echo $_SESSION['id']; ?>';
                <?php else: ?>
                    var userId = null;
                <?php endif; ?>
    
                // Profile link click event
                document.getElementById("profileLink").addEventListener("click", function(event) {
                    event.preventDefault();  // Prevent the default action
    
                    if (userId) {
                        // If the 'id' exists in the session, redirect to profile.html
                        window.location.href = "profile.html";
                    } else {
                        // If the 'id' does not exist, redirect to login.html
                        window.location.href = "login.html";
                    }
                });
    
                // Example: Handling 'Makinat Tona' click event
                document.getElementById("makinatTona").addEventListener("click", function(){
                    // For testing purposes (you can change this later)
                    document.cookie = "car_id=1; path=/";
                    window.location.href = "carDetails.html"; 
                });
    
            </script>
        <script>
            // Check if the 'id' exists in the PHP session and store it in a JavaScript variable
            var userId;
            <?php if (isset($_SESSION['id'])): ?>
                userId = '<?php echo $_SESSION['id']; ?>';
            <?php else: ?>
                var userId = null;
            <?php endif; ?>

            // Profile link click event
            document.getElementById("profileImage").addEventListener("click", function() {
                event.preventDefault();  // Prevent the default action
                console.log(userId);

                if (userId) {
                    // If the 'id' exists in the session, redirect to profile.html
                    window.location.href = "profile.html";
                } else {
                    // If the 'id' does not exist, redirect to login.html
                    window.location.href = "login.html";
                }
            });

            document.getElementById("makinatTona").addEventListener("click", function(){
                if (!userId) {
                    window.location.href = "login.html";
                } else {
                    window.location.href = "main.html"; 
                }
            });
            document.getElementById("makinat").addEventListener("click", function(){
                if (!userId) {
                    window.location.href = "login.html";
                } else {
                    window.location.href = "main.html"; 
                }
            });
        </script>
        <script src="sessionTimeout.js"></script>

    </body>
</html>
