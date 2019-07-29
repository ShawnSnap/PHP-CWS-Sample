<?php
/**
 * Data Generator will generate basic transaction objects for testing.  Please do not use these in your development
 * beyond proof of concept.  You want to properly build the transactions for yourself.
 *
 * Notice that much of createBankcardTransaction() is commented out because these bits aren't necessarily required.
 */

use Evosnap\Cws\V2\I0\Dataservices\Tms\QueryTransactionsParameters;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\CardData;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\Pro\BankcardTenderDataPro;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\CardSecurityData;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\LineItemDetail;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\CardOnFileInfo;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\Pro\BankcardTransactionPro;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\Pro\BankcardTransactionDataPro;
use Evosnap\Cws\V2\I0\Dataservices\DateRange;
use Evosnap\Cws\V2\I0\Transactions\Bankcard\AVSData;

class DataGenerator
{

    public static function createQueryTransactionParameters($queryType, $transactionIds)
    {
        $qtp = new QueryTransactionsParameters();
        
        $qtp->QueryType = $queryType;
        
        /*if (! empty($transactionIds)) {
            $qtp->TransactionIds = "c0ee9d383ca44edba5957d2c2d925b0e"; // This needs to have the proper TxnIds
        }*/
        $qtp->TransactionDateRange = new DateRange();
        $date = new DateTime();
        $date->add(new DateInterval('P1D'));
        $qtp->TransactionDateRange->EndDateTime = $date->format('c');
        $date->sub(new DateInterval('P2D'));
        $qtp->TransactionDateRange->StartDateTime = $date->format('c');
        return $qtp;
    }

    //creates the variables for the cardholder details
    public static function createBankcardTransaction()
    {
        /**
         * BankcardTransactionPro() is the object used to describe a transaction to EVO Snap*'s endpoints.
         *
         * At a minimum, it consists of three parts:
         *
         * - CardData(), which describes the actual card/account being used for the transaction.
         * - CardSecurityData(), which lays out security information about the card (such as CVV)
         * - BankcardTenderDataPro(), which describes the money being tendered as part of the transaction.
         *
         */
        $cardData = new CardData();
        $tenderData = new BankcardTenderDataPro();
        $cardSecureData = new CardSecurityData();
        
        $cardData = new CardData();
        $cardData->CardholderName = "Test Cardholder";
        $cardData->CardType = "Visa";
        $cardData->Expire = "1220";
        $cardData->PAN = "4111111111111111";
		
		$LineItemDetail = new LineItemDetail();
		$LineItemDetail->ProductCode = "12345";

        /*
         $avsData = new AVSData();
         $avsData->City = "Denver";
         $avsData->Country = 'USA';
         $avsData->PostalCode = "80202";
         $avsData->StateProvince = "CO";
         $avsData->Street = "1112 Some Street with 20 characters";
         $cardSecureData->AVSData = $avsData;
        */

        $cardSecureData->CVData = "111";
        $cardSecureData->CVDataProvided = "Provided";
        $tenderData->CardSecurityData = $cardSecureData;
        $tenderData->CardholderIdType = "NoEAuth";
        
        $tenderData->CardData = $cardData;
        
        $txn = new BankcardTransactionPro();
        $txn->TenderData =$tenderData;
		
		//TransactionReportingData

        $transactionData = new BankcardTransactionDataPro();
		
       $transactionData->Amount = "10.00";
       // $transactionData->CashBackAmount = "0.00";
       // $transactionData->TipAmount = "0.00";
       // $transactionData->FeeAmount = "0.00";
        $transactionData->AccountType = "NotSet";
        $transactionData->CardholderAuthenticationEntity = "NotSet";
        $transactionData->CardPresence = false;
        $transactionData->CustomerPresent = "Ecommerce";
        $transactionData->EntryMode = "Keyed";
        $transactionData->GoodsType = "DigitalGoods"; // DigitalGoods - PhysicalGoods - Used only for Ecommerce
        $transactionData->CurrencyCode = "USD";
        $transactionData->SignatureCaptured = false; // Required
        $transactionData->IsQuasiCash = false; // Optional
        $transactionData->IsPartialShipment = false; // Optional
        $transactionData->PartialApprovalCapable = "NotSet";
        $transactionData->Reference = "42";
        
        $date = new DateTime();
        $transactionData->TransactionDateTime = $date->format('c');
        $transactionData->OrderNumber = "12345";
        $transactionData->TransactionCode = "NotSet";
        $transactionData->Is3DSecure = false;
        $transactionData->Reference = ""; // This needs to be a unique value for all Magensa transactions.
        $transactionData->CardholderAuthenticationEntity = "Merch";
		//$transactionData->CardOnFileInfo->CardOnFile = "Repeat";
		//$transactionData->CardOnFileInfo->InitiatedBy = "Merchant";
		//$transactionData->CardOnFileInfo->OriginalTransactionId = "062E6FA261E04CF68178FBD905632B7E";
		//$cardonfile = new CardOnFileInfo();
		//$cardonfile->CardOnFile="Repeat";
		//$cardonfile->InitiatedBy="Merchant";
		//$cardonfile->OriginalTransactionId  = "062E6FA261E04CF68178FBD905632B7E";
		//$transactionData->CardOnFileInfo = $cardonfile;
        
        $txn->TransactionData = $transactionData;
        return $txn;
    }
}
