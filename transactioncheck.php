<?php
// set configs in here
$TargetAddressSnn = "";
$MinAmount = 0.02;

////////////////////////////
///////////// > input validation
$transactionHash = "YOUR_TRANSACTION_HASH";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve transaction hash from POST data
    $transactionHash = $_POST['transactionHash'];

    
    $minLength = 40;  
    $maxLength = 100;  

    // Check the length of the transaction hash
    $hashLength = strlen($transactionHash);
    if ($hashLength >= $minLength && $hashLength <= $maxLength) {
        // Check if the transaction hash is base58 encoded
        if (preg_match('/^[1-9A-HJ-NP-Za-km-z]+$/', $transactionHash)) {
        } else {
            echo "Invalid transaction hash format.";
            exit; // Stop further execution
        }
    } else {
        echo "Invalid transaction hash length.";
        exit; // Stop further execution
    }
} else {
    // Handle invalid request method
    echo "Invalid request method";
    exit; // Stop further execution
}

///////////////////////////////
///// - check transaction

// Set Mainnet API endpoint
$apiUrl = 'https://api.mainnet-beta.solana.com';

// Set JSON payload
$data = array(
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'getTransaction',
    'params' => array(
        $transactionHash,
        'json'
    )
);

// Convert data array to JSON string
$dataJson = json_encode($data);

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($dataJson)
));

// Execute cURL request
$rawresponse = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
    // Handle error accordingly
}

// Close cURL session
curl_close($ch);


// Decode the JSON response
$response = json_decode($rawresponse, true);

// Extract the relevant information
$transaction = $response['result']['transaction'];
$preBalances = $response['result']['meta']['preBalances'];
$postBalances = $response['result']['meta']['postBalances'];

// Extract account keys (addresses)
$accountKeys = $transaction['message']['accountKeys'];

// Assuming the first account key is the sender and the second is the receiver
$sender = $accountKeys[0];
$receiver = $accountKeys[1];

// Calculate the transferred amount
$transferredAmountLamports  = $preBalances[0] - $postBalances[0];
$lamportsPerSol = 1000000000;
$transferredAmountSol = $transferredAmountLamports / $lamportsPerSol;

if($transferredAmountSol>=$MinAmount && $receiver==$TargetAddressSnn)
{

    echo "true";
}
else
{
    echo "false";
}


