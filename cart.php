<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PayPal Sandbox</title>
</head>

<body>

<h2>Pay $10.00</h2>

<div id="paypal-button-container"></div>

<!-- PayPal SDK MUST load before paypal.Buttons() is called -->
<script src="https://www.paypal.com/sdk/js?client-id=BAAJlXs7VPXMRzjspBJTaNOJTpSxGjcuGdb0qYRdp98aaD9JNgqJkuGNs0ZXOiGIQUWaRzg0z7oGLWHMW8&currency=USD"></script>

<script>
if (typeof paypal === "undefined") {
    console.error("PayPal SDK failed to load.");
} else {

    paypal.Buttons({

        createOrder: function () {
            return fetch("create-order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    amount: "10.00"
                })
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error("create-order.php returned HTTP " + response.status);
                }

                return response.json();
            })
            .then(function(data) {
                console.log("Create order response:", data);

                if (!data.id) {
                    throw new Error(
                        data.error || "PayPal did not return an order ID."
                    );
                }

                return data.id;
            });
        },

        onApprove: function(data) {

            return fetch("capture-order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    orderID: data.orderID
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(result) {

                console.log("Capture response:", result);

                if (result.status === "COMPLETED") {
                    alert("Payment successful!");
                } else {
                    alert("Payment was not completed.");
                }

            });

        },

        onCancel: function(data) {
            console.log("Payment cancelled:", data);
        },

        onError: function(err) {
            console.error("PayPal error:", err);
            alert("PayPal payment error. Check the browser console.");
        }

    }).render("#paypal-button-container");

}
</script>

</body>
</html>
