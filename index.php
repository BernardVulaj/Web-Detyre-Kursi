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
    <body style="
        font-family: 'Poppins', sans-serif;
        background-image: url('images/wallpaper.jpg');
        background-repeat: no-repeat;
        background-position: center center;
        background-attachment: fixed;
        background-size: cover; 
        color: #333; 
        line-height: 1.6">
        
        <div id="header">
            <div id="navigationBar">
                <a href="index.php">Home</a>
                <a id="makinat">Makinat</a>
                <a>Rreth Nesh</a>
                <a>Kontakto</a>
            </div>
            <div id="imageContainer">
                <img id="profileImage" src="<?php echo $userImage; ?>" alt="Profile Image">
            </div>
        </div>

        <div id="mesazhiContainer">
            <label id="mesazhi">
                Makina te reja dhe te sigurta, te pershtatshme per cdo lloj udhetimi, me sherbim te shpejte dhe te profesional.
            </label>
        </div>

        <a id="makinatTona">Makinat Tona</a>

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

                if (!userId) {
                    window.location.href = "login.html";  // Redirect to login page
                } else {
                    window.location.href = "profile.html";  // Redirect to profile page
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
