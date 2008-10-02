<?php
/*
AuthorizeNet Processor Class

@package Sandstone
@subpackage Merchant
*/

NameSpace::Using("Sandstone.Address");
NameSpace::Using("Sandstone.CreditCard");

class AuthorizeNetProcessor extends ProcessorBase
{

    const RESULT_DELIMITER = "|";

    const APPROVED_RESPONSE_CODE = 1;
    const DECLINED_RESPONSE_CODE = 2;
    const ERROR_RESPONSE_CODE = 3;
    const REVIEW_RESPONSE_CODE = 4;

    const RESPONSE_CODE = 0;
    const RESPONSE_REASON_CODE = 2;
    const RESPONSE_REASON_TEXT = 3;
    const AUTHORIZATION_CODE = 4;
    const AVS_RESPONSE = 5;
    const TRANSACTION_ID = 6;
    const CARD_CODE_RESPONSE = 38;

    protected $_apiURL;

    public function __construct($Parameters)
    {
        parent::__construct($Parameters);

        //If the test mode flag exists (and is true), we'll use DI's test account
        //against the test API URL
        if ($this->_parameters["testmode"] == 1)
        {
            $this->_apiURL = "https://test.authorize.net/gateway/transact.dll";

            $this->_parameters['x_login'] = "6zz6m5N4Et";
            $this->_parameters['x_tran_key'] = "9V9wUv6Yd92t27t5";
        }
        else
        {
            $this->_apiURL = "https://secure.authorize.net/gateway/transact.dll";
        }


    }

	public function ProcessAuthorization($Amount)
	{
		$this->_amount = $Amount;

		$postValuesArray = new DIarray();

        $this->AddBasicPostValues($postValuesArray);
        $this->AddAuthorizationPostValues($postValuesArray);

        $result = $this->SendRequest($postValuesArray);

        if (is_set($result))
        {
            $returnValue = $this->ProcessResult($result, CreditCardTransaction::AUTHORIZATION_TRANSACTION_TYPE);
        }

		return $returnValue;

	}

	public function ProcessCharge($Amount, $AuthTransaction = null)
	{

		$this->_amount = $Amount;

		$postValuesArray = new DIarray();

        $this->AddBasicPostValues($postValuesArray);
        $this->AddChargePostValues($postValuesArray, $AuthTransaction);

        $result = $this->SendRequest($postValuesArray);

        if (is_set($result))
        {
            $returnValue = $this->ProcessResult($result, CreditCardTransaction::CHARGE_TRANSACTION_TYPE, $AuthTransaction);
        }

		return $returnValue;

	}

	public function ProcessCredit($Amount, $ChargeTransaction = null)
	{

		$this->_amount = $Amount;

		$postValuesArray = new DIarray();

        $this->AddBasicPostValues($postValuesArray);
        $this->AddCreditPostValues($postValuesArray, $ChargeTransaction);

        $result = $this->SendRequest($postValuesArray);

        if (is_set($result))
        {
            $returnValue = $this->ProcessResult($result, CreditCardTransaction::CREDIT_TRANSACTION_TYPE, $ChargeTransaction);
        }

		return $returnValue;

	}

    protected function AddBasicPostValues($PostValuesArray)
    {

        //Account ID
        $PostValuesArray['x_login'] = $this->_parameters['x_login'];
        $PostValuesArray['x_tran_key'] = $this->_parameters['x_tran_key'];

        //Basic Interface Settings
        $PostValuesArray['x_version'] = "3.1";
        $PostValuesArray['x_delim_char'] = self::RESULT_DELIMITER;
        $PostValuesArray['x_delim_data'] = "TRUE";
        $PostValuesArray['x_url'] = "FALSE";
        $PostValuesArray['x_relay_response'] = "FALSE";
        $PostValuesArray['x_method'] = "CC";

        //Card Info
        $PostValuesArray['x_card_num'] = $this->_number;
        $PostValuesArray['x_exp_date'] = $this->_expirationDate->FormatDate('m') . substr($this->_expirationDate->Year, 2,2);
        $PostValuesArray['x_card_code'] = $this->_cvv;

        //Amount
        $PostValuesArray['x_amount'] = $this->_amount;

	}

	protected function AddCustomerInfoPostValues($PostValuesArray)
	{
        $PostValuesArray['x_first_name'] = $this->_firstName;
        $PostValuesArray['x_last_name'] = $this->_lastName;
        $PostValuesArray['x_address'] = $this->_billingAddress->Street;
        $PostValuesArray['x_city'] = $this->_billingAddress->City;
        $PostValuesArray['x_state'] = $this->_billingAddress->ProvinceCode;
        $PostValuesArray['x_zip'] = $this->_billingAddress->PostalCode;
	}

	protected function AddAuthorizationPostValues($PostValuesArray)
	{
    	$PostValuesArray['x_type'] = "AUTH_ONLY";
        $this->AddCustomerInfoPostValues($PostValuesArray);
	}

    protected function AddChargePostValues($PostValuesArray, $AuthTransaction = null)
    {

        if ($AuthTransaction instanceof CreditCardTransaction && $AuthTransaction->IsLoaded)
        {
            $PostValuesArray['x_type'] = "PRIOR_AUTH_CAPTURE";
            $PostValuesArray['x_trans_id'] = $AuthTransaction->MerchantTransactionID;
        }
        else
        {
            $PostValuesArray['x_type'] = "AUTH_CAPTURE";
            $this->AddCustomerInfoPostValues($PostValuesArray);
        }

    }

	protected function AddCreditPostValues($PostValuesArray, $CreditTransaction = null)
	{
    	$PostValuesArray['x_type'] = "CREDIT";

        if ($CreditTransaction instanceof CreditCardTransaction && $CreditTransaction->IsLoaded)
        {
			$PostValuesArray['x_trans_id'] = $CreditTransaction->MerchantTransactionID;
		}
	}

    protected function SendRequest($PostValuesArray)
    {

        //Build the query string for the post values
        $postValuesString = DIarray::ImplodeAssoc("=", "&", $PostValuesArray);

        $ch = curl_init($this->_apiURL);

        // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // Returns response data instead of TRUE(1)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // use HTTP POST to send form data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postValuesString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        //execute post and get results
        $returnValue = curl_exec($ch);

        curl_close ($ch);

        return $returnValue;
    }

    protected function ProcessResult($ResultString, $TransactionTypeID, $RelatedTransaction = null)
    {

        $returnValue = new CreditCardTransaction();

        //Explode the Results to an array
        $resultsArray = explode(self::RESULT_DELIMITER, $ResultString);

		$returnValue->MerchantAccount = Application::License()->ActiveMerchantAccount;
		$returnValue->CreditCardTransactionTypeID = $TransactionTypeID;
		$returnValue->RelatedTransaction = $RelatedTransaction;

        $returnValue->CreditCard = new CreditCard($this->_creditCardID);
        $returnValue->Timestamp = new Date();
        $returnValue->Amount = $this->_amount;
        $returnValue->MerchantTransactionID = $resultsArray[self::TRANSACTION_ID];

        //Was this successful?
        if ($resultsArray[self::RESPONSE_CODE] == self::APPROVED_RESPONSE_CODE)
        {
            $returnValue->IsSuccessful = true;
            $returnValue->AddMessage('Authorization Code: ' . $resultsArray[self::AUTHORIZATION_CODE]);
        }
        else
        {
            $returnValue->IsSuccessful = false;

            switch ($resultsArray[self::RESPONSE_CODE])
            {
                case self::DECLINED_RESPONSE_CODE:
                    $returnValue->AddMessage("This transaction has been declined.");
                    break;

                case self::ERROR_RESPONSE_CODE:
                    $returnValue->AddMessage("There has been an error processing this transaction.");
                    break;

                case self::REVIEW_RESPONSE_CODE:
                    $returnValue->AddMessage("This transaction is being held for review.");
                    break;

                default:
                    $returnValue->AddMessage("ERROR: Unknown Response Code [{$resultsArray[self::RESPONSE_CODE]}]");
                    break;
            }

            //Log the reason and other info
            $returnValue->AddMessage("Reason: ({$resultsArray[self::RESPONSE_REASON_CODE]}) {$resultsArray[self::RESPONSE_REASON_TEXT]}");
            $returnValue->AddMessage("AVS Response: {$resultsArray[self::AVS_RESPONSE]}");
            $returnValue->AddMessage("Card Code Response: {$resultsArray[self::CARD_CODE_RESPONSE]}");
        }


        $returnValue->Save();

        return $returnValue;

    }

}
?>
