<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>PayPal Sandbox Payment</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 40px;
        }

        .payment-box {
            max-width: 450px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .payment-box h1 {
            margin-bottom: 10px;
        }

        .amount {
            font-size: 28px;
            font-weight: bold;
            margin: 20px 0;
        }

        #paypal-button-container {
            margin-top: 25px;
        }

        #message {
            margin-top: 20px;
            padding: 12px;
            display: none;
            border-radius: 5px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

    </style>

</head>

<body>

<div class="payment-box">

    <h1>PayPal Sandbox</h1>

    <p>Test payment</p>

    <div class="amount">
        $10.00 USD
    </div>

    <div id="paypal-button-container"></div>

    <div id="message"></div>

</div>


<!--
============================================================
PAYPAL SDK
============================================================

Replace the client-id below with your Sandbox Client ID.

DO NOT put the Client Secret here.
============================================================
-->

<script src="https://www.paypal.com/sdk/js?client-id=BAAJlXs7VPXMRzjspBJTaNOJTpSxGjcuGdb0qYRdp98aaD9JNgqJkuGNs0ZXOiGIQUWaRzg0z7oGLWHMW8&currency=USD"></script>


<script>

(function () {

    "use strict";


    // ========================================================
    // MESSAGE FUNCTION
    // ========================================================

    function showMessage(message, type) {

        var box =
            document.getElementById("message");

        box.innerHTML = message;

        box.className = type;

        box.style.display = "block";
    }


    // ========================================================
    // CHECK PAYPAL SDK
    // ========================================================

    if (typeof paypal === "undefined") {

        console.error(
            "PayPal SDK failed to load."
        );

        showMessage(
            "PayPal SDK failed to load.",
            "error"
        );

        return;
    }


    // ========================================================
    // PAYPAL BUTTONS
    // ========================================================

    paypal.Buttons({


        // ====================================================
        // CREATE ORDER
        // ====================================================

        createOrder: function () {

            console.log(
                "Creating PayPal order..."
            );


            return fetch(
                "create-order.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json"
                    },

                    body: JSON.stringify({
                        amount: "10.00"
                    })
                }
            )


            .then(function (response) {

                return response.text();

            })


            .then(function (text) {

                console.log(
                    "create-order.php response:",
                    text
                );


                var data;


                try {

                    data =
                        JSON.parse(text);

                } catch (error) {

                    console.error(
                        "Invalid JSON from create-order.php:",
                        text
                    );

                    throw new Error(
                        "create-order.php did not return JSON."
                    );
                }


                if (!data.id) {

                    console.error(
                        "PayPal create order error:",
                        data
                    );


                    throw new Error(
                        data.error ||
                        data.message ||
                        "PayPal order ID was not returned."
                    );
                }


                console.log(
                    "PayPal Order ID:",
                    data.id
                );


                return data.id;

            })

            .catch(function (error) {

                console.error(
                    "Create order error:",
                    error
                );


                showMessage(
                    "Unable to create PayPal order: " +
                    error.message,
                    "error"
                );


                throw error;

            });

        },


        // ====================================================
        // PAYMENT APPROVED
        // ====================================================

        onApprove: function (data) {

            console.log(
                "PayPal payment approved."
            );


            console.log(
                "Order ID:",
                data.orderID
            );


            showMessage(
                "Payment approved. Capturing payment...",
                ""
            );


            // =================================================
            // CAPTURE ORDER
            // =================================================

            return fetch(
                "capture-order.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json"
                    },

                    body: JSON.stringify({

                        orderID:
                            data.orderID

                    })
                }
            )


            // =================================================
            // READ RESPONSE AS TEXT FIRST
            // =================================================

            .then(function (response) {

                return response.text();

            })


            // =================================================
            // PARSE JSON
            // =================================================

            .then(function (text) {

                console.log(
                    "capture-order.php response:",
                    text
                );


                var result;


                try {

                    result =
                        JSON.parse(text);

                } catch (error) {

                    console.error(
                        "capture-order.php returned invalid JSON:"
                    );

                    console.error(text);


                    throw new Error(
                        "capture-order.php returned invalid JSON."
                    );
                }


                return result;

            })


            // =================================================
            // HANDLE RESULT
            // =================================================

            .then(function (result) {

                console.log(
                    "PayPal Capture Result:",
                    result
                );


                if (
                    result.status ===
                    "COMPLETED"
                ) {

                    showMessage(
                        "Payment successful!",
                        "success"
                    );


                    alert(
                        "Payment successful!\n\n" +
                        "PayPal Order ID: " +
                        result.id
                    );


                    console.log(
                        "Payment completed:",
                        result
                    );


                    /*
                     * Optional:
                     *
                     * window.location.href =
                     *     "success.php";
                     */

                } else {

                    console.error(
                        "Payment was not completed:",
                        result
                    );


                    var errorMessage =
                        "Payment was not completed.";


                    if (result.message) {

                        errorMessage =
                            result.message;

                    }


                    if (result.error) {

                        errorMessage =
                            result.error;

                    }


                    showMessage(
                        errorMessage,
                        "error"
                    );

                }

            })


            // =================================================
            // ERROR
            // =================================================

            .catch(function (error) {

                console.error(
                    "Capture error:",
                    error
                );


                showMessage(
                    "Payment capture failed: " +
                    error.message,
                    "error"
                );


                alert(
                    "Payment capture failed.\n\n" +
                    error.message
                );

            });

        },


        // ====================================================
        // CANCEL
        // ====================================================

        onCancel: function (data) {

            console.log(
                "Payment cancelled:",
                data
            );


            showMessage(
                "Payment cancelled.",
                "error"
            );

        },


        // ====================================================
        // PAYPAL ERROR
        // ====================================================

        onError: function (error) {

            console.error(
                "PayPal error:",
                error
            );


            showMessage(
                "PayPal payment error. Check the browser console.",
                "error"
            );

        }

    })


    // ========================================================
    // RENDER
    // ========================================================

    .render(
        "#paypal-button-container"
    )

    .catch(function (error) {

        console.error(
            "PayPal render error:",
            error
        );

    });


})();

</script>

</body>
</html>
