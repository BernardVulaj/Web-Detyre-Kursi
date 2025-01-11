<?php
// Start the session at the beginning of the file
session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>
            Home Page
        </title>
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
    <?php include 'header.php'; ?>
    <div id="mesazhiContainer">
        <label id="mesazhi">
            Në zemër të Tiranës, me makinat më të reja,të sigurta e të përshtatshme për cdo lloj udhëtimi
        </label>
    </div>
    <a id="makinatTona">Makinat Tona</a>
    <script>
        document.getElementById("makinatTona").addEventListener("click", function() {
            document.cookie = "car_id=1; path=/";
            window.location.href = "carDetails.html"; 
        });
    </script>
    <script src="sessionTimeout.js"></script>
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
                        window.location.href = "login/profile.html";
                    } else {
                        // If the 'id' does not exist, redirect to login.html
                        window.location.href = "login/login.html";
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
                    window.location.href = "login/profile.html";
                } else {
                    // If the 'id' does not exist, redirect to login.html
                    window.location.href = "login/login.html";
                }
            });

            document.getElementById("makinatTona").addEventListener("click", function(){
                // vetem per testim kto posht do t ndryshohen
                document.cookie = "car_id=1; path=/";
                window.location.href = "carDetails.html"; 

            });

        

        

        </script>
        <script src="login/sessionTimeout.js"></script>

    </body>
</html>