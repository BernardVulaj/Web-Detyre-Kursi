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

            document.getElementById("makinatTona").addEventListener("click", function(){
                // vetem per testim kto posht do t ndryshohen
                document.cookie = "car_id=1; path=/";
                window.location.href = "carDetails.html"; 

            });

        

        

        </script>
        <script src="sessionTimeout.js"></script>

    </body>
</html>