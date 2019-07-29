<?php
/**
 * This is a command line script that runs through a series of transactions, showing how to...
 *
 * - Authorize and Capture
 * - Authorize
 * - Capture a previously Authorized transaction
 * - Undo an Authorized, but not Captured, transaction
 * - Void a Captured transaction.
 *
 * Running this on a color enabled terminal, like cmder, will make reading the output much easier thanks to the
 * Tracy debugging library.
 *
 * For more information, see:  https://docs.evosnap.com/
 */

use Evosnap\Cws\JSONClient;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\Pro\BankcardCapturePro;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\Pro\BankcardTenderDataPro;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\Pro\BankcardReturnPro;
use Evosnap\Cws\V2\I0\Dataservices\PagingParameters;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\BankcardUndo;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/DataGenerator.php';

require_once __DIR__ . '/config.php';

Tracy\Debugger::$maxLength = 4082;
$client = new JSONClient($clientConfig, $identityToken);

headingLine("Starting processing");

try {
    /**
     * Service Information is what you can use to programmatically detect which backend, and which API calls, you are
     * able to transact against with this set of credentials.  It's full of useful information.
     */
    headingLine("Get service information");
    $sis = $client->getServiceInformation();
    dump($sis);
    $sis = json_encode($sis, JSON_PRETTY_PRINT);

    /**
     * AuthorizeAndCapture() is the most basic transaction call in EVO Snap*.  It both does an online authorization
     * with the card (ensuring funds are available) as well as marking the transaction as ready for batch processing.
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tps-json-requests-bankcard/#authandcaptxn
     */
	headingLine("TPS: Authorize & Capture");
    $authTransaction = DataGenerator::createBankcardTransaction();
    $authTransaction->TransactionData->Amount = "10.00";
    dump($authTransaction);
    $authAndCapResponse = $client->authorizeAndCapture($authTransaction, $applicationProfileId, $merchantProfileId, $serviceId);
    dump($authAndCapResponse);
	resultLine($authAndCapResponse->TransactionId . " - " . $authAndCapResponse->Status . ": " . $authAndCapResponse->TransactionState);

    /**
     * Authorize() is a basic call which simply does an online authorization, it does NOT capture the transaction to
     * make it ready for batch processing.  This will either need to be Capture()d or Undo()ed when a decision is made
     * what to do with the money, or else you'll risk the authorization being aged off by the cardholder's bank.
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tps-json-requests-bankcard/#authorizetxn
     */
    headingLine("TPS: Authorize Only");
    $authTransaction = DataGenerator::createBankcardTransaction();
    dump($authTransaction);
    $authResponse = $client->authorize($authTransaction, $applicationProfileId, $merchantProfileId, $serviceId);
    dump($authResponse);
    resultLine($authResponse->TransactionId . " - " . $authResponse->Status . ": " . $authResponse->TransactionState);

    /**
     * Capture() takes a previously Authorize()d transaction and marks it as ready for batch processing.
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tps-json-requests-bankcard/#capturetxn
     */
    headingLine("TPS: Capture " . $authResponse->TransactionId);
    $capTransaction = new BankcardCapturePro();
    $capTransaction->TransactionId = $authResponse->TransactionId;
    $capTransaction->Amount = $authTransaction->TransactionData->Amount;
    $capTransaction->ChargeType = "NotSet";
    $capTransaction->TipAmount = $authTransaction->TransactionData->TipAmount;
    dump($capTransaction);
    $capResponse = $client->capture($capTransaction, $applicationProfileId, $serviceId);
    dump($capResponse);
    resultLine($capResponse->TransactionId . " - " . $capResponse->Status . ": " . $capResponse->TransactionState);

    /**
     * Undo() is used to cancel an Authorize() that you do not wish to Capture().
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tps-json-requests-bankcard/#undotxn
     */
    headingLine("TPS: Undo ". $capResponse->TransactionId);
    $undo = new BankcardUndo();
    $undo->TransactionId = $capResponse->TransactionId;
    $undo->TransactionCode = "NotSet";
    $undo->ForceVoid = false;
    $undo->PINDebitReason = "NotSet";
    $undo->UndoReason = "CustomerCancellation";
    dump($undo);
    $undoResponse = $client->undo($undo, $applicationProfileId, $serviceId);
    dump($undoResponse);
    resultLine($undoResponse->TransactionId . " - " . $undoResponse->Status . ": " . $undoResponse->TransactionState);

    /**
     * ReturnById() returns Capture()d money back to the cardholder.
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tps-json-requests-bankcard/#returnbyid
     */
    headingLine("TPS: ReturnById " . $authAndCapResponse->TransactionId);
    $bcReturn = new BankcardReturnPro();
    $bcReturn->TransactionId = $authAndCapResponse->TransactionId;
    $bcReturn->Amount = number_format($authAndCapResponse->Amount, 2);
    dump($bcReturn);
    $returnResponse = $client->returnById($bcReturn, $applicationProfileId, $merchantProfileId, $serviceId);
    dump($returnResponse);
    resultLine($returnResponse->TransactionId . " - " . $returnResponse->Status . ": " . $returnResponse->TransactionState);

    /**
     * Every transaction run through Snap* produces a token value, which may be used in future transactions.
     *
     * This token is called PaymentAccountDataToken
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tps-json-requests-bankcard/#returnbyid
     */
    headingLine("TPS: Authroization with Token " . $authAndCapResponse->PaymentAccountDataToken);
    $token = $authAndCapResponse->PaymentAccountDataToken;
    $authTokenTransaction = DataGenerator::createBankcardTransaction();
    //$bankcardTenderData = new BankcardTenderDataPro();
    $authTokenTransaction->TenderData->PaymentAccountDataToken = $token;
    //$bankcardTenderData->CardholderIdType = "DigitalSig";
    //$authTokenTransaction->TenderData = $bankcardTenderData;
    dump($authTokenTransaction);
    $authTokenResponse = $client->authorize($authTokenTransaction, $applicationProfileId, $merchantProfileId, $serviceId);
    dump($authTokenResponse);
    resultLine($authTokenResponse->TransactionId . " - " . $authTokenResponse->Status . ": " . $authTokenResponse->TransactionState);

    /**
     * TMS is the Transaction Management Service, which will allow you to query the EVO Snap* platform for details about
     * any and all transactions run against our platform, indexed either by a specific transaction ID, or using search
     * parameters to bracket the transactions you are looking for.
     *
     * It can be used to build a reporting system without having to track all the data with your local POS, because
     * we have to do all that work anyway!
     *
     * See:  https://docs.evosnap.com/commerce-web-services/cws-api-reference/rest-api-reference/tms-json-requests/
     */

	$parameters = DataGenerator::createQueryTransactionParameters('AND', $authTokenResponse->TransactionId);
    $pagingParameters = new PagingParameters();
    $pagingParameters->Page = 0;
    $pagingParameters->PageSize = 7;


    // "Summaries" contain more less information about transactions, and runs faster.
    headingLine("TMS: Query for transaction summaries");
    $transactionsSummaries = $client->queryTransactionsSummary($parameters, $pagingParameters, true);
//    $transaction=json_encode($transactionsSummaries, JSON_PRETTY_PRINT);
    foreach ($transactionsSummaries as $summary) {
        dump($summary);
    }

    // "Details" contain more detailed information about transactions, and runs slower.
    headingLine("TMS: Query for transaction details");
    $transactionDetails = $client->queryTransactionsDetail($parameters, "CWSTransaction", $pagingParameters, true);
    foreach ($transactionDetails as $detail) {
        dump($detail);
    }

    // A "transaction family" is a group of related transactions.  For example, if you do a
    // ReturnById() on a Capture()d Authorize()...
    //
    // Authorize()->Capture()->ReturnById()
    //
    // All three of these transactions are part of the same family.  You can get a family by
    // using any of the three transaction IDs.
    headingLine("TMS: Query for transaction families");
    $familyDetails = $client->queryTransactionFamilies($parameters, $pagingParameters);
	foreach ($familyDetails as $family) {
        dump($family);
	}
}

catch (Exception $e) {
	dump($e);
}

// The following two functions simply output lines dividing the transactions.
function headingLine($msg) {
    echo terminal_style(str_pad($msg . ' ', 120, '=') . "\n\n", "light-cyan");
}
function resultLine($msg) {
    echo terminal_style(str_pad(' ' . $msg, 120, '=', STR_PAD_LEFT) . "\n\n", "light-yellow");
}