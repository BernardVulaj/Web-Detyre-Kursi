<?php
// Start the session at the beginning of the file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<html>
    <head>
        <link rel="stylesheet" href="css/index.css">
    </head>
    <body>
</html>
<div id="header">
    <div id="navigationBar">
        <a href="index.php">Home</a>
        <a href="makinat.php">Makinat</a>
        <a href="rrethNesh.php">Rreth Nesh</a>
        <a href="kontakto.php">Kontakto</a>
    </div>
    <a id="profileLink">
        <img id="profileImage" src="images/profileImage.jpg" alt="Profile">
    </a>
</div>
<script>
    // Check if the 'id' exists in the PHP session and store it in a JavaScript variable
    <?php if (isset($_SESSION['id'])): ?>
        var userId = '<?php echo $_SESSION['id']; ?>';
    <?php else: ?>
        var userId = null;
    <?php endif; ?>

    // Profile link click event
    document.getElementById("profileLink").addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default action
        if (userId) {
            window.location.href = "login/profile.html";
        } else {
            window.location.href = "login/login.html";
        }
    });
</script>
