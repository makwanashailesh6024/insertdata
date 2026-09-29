<?php
// index.php
//
// Google Pay API TEST-mode demo.
// No real money is charged.
//
// PHP is used here to provide the amount/order information.
// Google Pay itself is initialized in JavaScript.

$orderId = 'TEST_ORDER_' . date('YmdHis');
$amount = '100.00';
$currency = 'INR';
$merchantName = 'My Demo Store';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Google Pay TEST Payment</title>

    <script
        async
        src="https://pay.google.com/gp/p/js/pay.js">
    </script>

    <style>

        body {
            margin: 0;
            padding: 40px 15px;
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .payment-box {
            max-width: 420px;
            margin: auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,.12);
            text-align: center;
        }

        h2 {
            margin-top: 0;
        }

        .amount {
            font-size: 30px;
            font-weight: bold;
            margin: 20px 0;
        }

        .order {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        #google-pay-button {
            margin-top: 20px;
        }

        .sandbox {
            margin-top: 25px;
            padding: 12px;
            background: #fff3cd;
            color: #856404;
            border-radius: 8px;
            font-size: 13px;
        }

        #result {
            margin-top: 20px;
            text-align: left;
            word-break: break-word;
        }

    </style>

</head>

<body>

<div class="payment-box">

    <h2>Google Pay Test Payment</h2>

    <div class="amount">
        ₹<?php echo htmlspecialchars($amount); ?>
    </div>

    <div class="order">
        Order ID:
        <strong>
            <?php echo htmlspecialchars($orderId); ?>
        </strong>
    </div>

    <div id="google-pay-button"></div>

    <div id="result"></div>

    <div class="sandbox">
        <strong>TEST / SANDBOX MODE</strong><br><br>
        This integration uses Google's TEST environment.
        It does not make a real payment.
    </div>

</div>


<script>

var baseRequest = {
    apiVersion: 2,
    apiVersionMinor: 0
};


// Test card configuration
var baseCardPaymentMethod = {

    type: 'CARD',

    parameters: {

        allowedAuthMethods: [
            'PAN_ONLY',
            'CRYPTOGRAM_3DS'
        ],

        allowedCardNetworks: [
            'AMEX',
            'DISCOVER',
            'INTERAC',
            'JCB',
            'MASTERCARD',
            'VISA'
        ]

    }

};


// TEST tokenization.
//
// "example" is Google's documented test gateway.
// It is suitable for testing the Google Pay payment flow.
// It is NOT a real payment gateway.
var tokenizationSpecification = {

    type: 'PAYMENT_GATEWAY',

    parameters: {

        gateway: 'example',

        gatewayMerchantId: 'exampleGatewayMerchantId'

    }

};


var cardPaymentMethod = Object.assign(
    {},
    baseCardPaymentMethod,
    {
        tokenizationSpecification:
            tokenizationSpecification
    }
);


var paymentsClient = null;


function getPaymentsClient() {

    if (!paymentsClient) {

        paymentsClient =
            new google.payments.api.PaymentsClient({

                environment: 'TEST'

            });

    }

    return paymentsClient;
}


function getIsReadyToPayRequest() {

    return Object.assign(
        {},
        baseRequest,
        {
            allowedPaymentMethods: [
                baseCardPaymentMethod
            ]
        }
    );

}


function getPaymentDataRequest() {

    var paymentDataRequest =
        Object.assign({}, baseRequest);

    paymentDataRequest.allowedPaymentMethods = [
        cardPaymentMethod
    ];


    paymentDataRequest.transactionInfo = {

        totalPriceStatus: 'FINAL',

        totalPrice:
            '<?php echo htmlspecialchars($amount, ENT_QUOTES); ?>',

        currencyCode:
            '<?php echo htmlspecialchars($currency, ENT_QUOTES); ?>',

        countryCode: 'IN'

    };


    paymentDataRequest.merchantInfo = {

        merchantName:
            '<?php echo htmlspecialchars($merchantName, ENT_QUOTES); ?>'

    };


    return paymentDataRequest;

}


function onGooglePayLoaded() {

    var client = getPaymentsClient();

    client.isReadyToPay(
        getIsReadyToPayRequest()
    )

    .then(function(response) {

        if (response.result) {

            addGooglePayButton();

        } else {

            showResult(
                'Google Pay is not available.'
            );

        }

    })

    .catch(function(error) {

        console.error(error);

        showResult(
            'Unable to initialize Google Pay.'
        );

    });

}


function addGooglePayButton() {

    var button =
        getPaymentsClient().createButton({

            onClick: onGooglePayClicked,

            buttonColor: 'black',

            buttonType: 'pay'

        });


    document
        .getElementById('google-pay-button')
        .appendChild(button);

}


function onGooglePayClicked() {

    var paymentRequest =
        getPaymentDataRequest();


    getPaymentsClient()
        .loadPaymentData(paymentRequest)

       .then(function(paymentData) {

    console.log("Payment Data:", paymentData);

    var json = JSON.stringify(paymentData, null, 2);

    document.getElementById("result").innerHTML =
        '<h3>PaymentData JSON</h3>' +
        '<pre style="' +
            'background:#111827;' +
            'color:#22c55e;' +
            'padding:15px;' +
            'border-radius:8px;' +
            'overflow:auto;' +
            'text-align:left;' +
            'white-space:pre-wrap;' +
        '">' +
        escapeHtml(json) +
        '</pre>';

})

        .catch(function(error) {

            console.error(error);

            showResult(
                '<strong>Payment cancelled or failed.</strong><br><br>' +
                escapeHtml(
                    error.statusCode ||
                    'Unknown error'
                )
            );

        });

}


function showResult(message) {

    document.getElementById('result').innerHTML =
        '<div style="' +
        'padding:12px;' +
        'background:#f1f5f9;' +
        'border-radius:8px;' +
        '">' +
        message +
        '</div>';

}


function escapeHtml(text) {

    var div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;

}


// Wait for Google Pay JavaScript
// library to become available.

var googlePayWait = setInterval(
    function() {

        if (
            typeof google !== 'undefined' &&
            google.payments &&
            google.payments.api
        ) {

            clearInterval(googlePayWait);

            onGooglePayLoaded();

        }

    },
    100
);

</script>

</body>
</html>
