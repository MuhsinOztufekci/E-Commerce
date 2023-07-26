<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

</head>

<body>
    <?php
    var_dump($_SESSION); ?>
    <h2>Ödemeyi burada alıyoruz.</h2>
    <button id="completePayment">Ödemeyi Tamamla</button>
</body>

<script>
    $(document).ready(function() {
        // Attach a click event handler to the button
        $("#completePayment").click(function() {
            // Get the customerID from the session
            const customerID = <?php echo $_SESSION['customerID'] ?? 'null'; ?>;

            if (!customerID) {
                console.error('CustomerID not available in session.');
                return;
            }

            // Perform AJAX request to the PHP script for handling the payment
            $.ajax({
                url: "checkout_sql.php",
                method: "POST",
                data: {
                    customerID: customerID
                },
                dataType: "text",
                success: function(data) {
                    console.log(data);
                    redi
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Payment process failed.", errorThrown);
                }
            });
        });
    });
</script>

</html>