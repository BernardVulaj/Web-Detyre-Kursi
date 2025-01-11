<?php
// Start the session at the beginning of the file
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="css/checkout.css"> <!-- Link to checkout specific CSS -->
</head>
<body>

    <?php include('header.php'); ?> <!-- Include header.php for navigation -->

    <div id="checkout-container">
        <h1>CHECKOUT</h1>

        <!-- Checkout Form -->
        <form id="checkout-form">
            <div class="form-group">
                <label for="name">Emri dhe Mbiemri</label>
                <input type="text" id="name" name="name" placeholder="Futni emrin dhe mbiemrin tuaj" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Futni emailin tuaj" required>
            </div>

            <div class="form-group">
                <label for="address">Adresa</label>
                <input type="text" id="address" name="address" placeholder="Futni adresën tuaj" required>
            </div>

            <div class="form-group">
                <label for="phone">Numri i telefonit</label>
                <input type="text" id="phone" name="phone" placeholder="Futni numrin tuaj të telefonit" required>
            </div>

            <div class="form-group">
                <label for="payment">Mënyra e Pagesës</label>
                <select id="payment" name="payment" required>
                    <option value="credit_card">Kartë krediti</option>
                    <option value="paypal">PayPal</option>
                    <option value="cash_on_delivery">Pagesë me dorëzim</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" id="checkout-button">Paguaj</button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript to handle form submission (could be extended for AJAX)
        document.getElementById('checkout-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            alert('Pagesa është dërguar me sukses!');
        });
    </script>

</body>
</html>
