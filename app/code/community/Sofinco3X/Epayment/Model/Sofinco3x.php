<?php
/**
 * Sofinco3X Epayment module for Magento
 *
 * Feel free to contact Sofinco3X at support@paybox.com for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * support@paybox.com so we can mail you a copy immediately.
 *
 *
 * @version   3.0.6
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Sofinco3X
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Sofinco3X_Epayment_Model_Sofinco3X
{
    private $_currencyDecimals = array(
        '008' => 2,
        '012' => 2,
        '032' => 2,
        '036' => 2,
        '044' => 2,
        '048' => 3,
        '050' => 2,
        '051' => 2,
        '052' => 2,
        '060' => 2,
        '064' => 2,
        '068' => 2,
        '072' => 2,
        '084' => 2,
        '090' => 2,
        '096' => 2,
        '104' => 2,
        '108' => 0,
        '116' => 2,
        '124' => 2,
        '132' => 2,
        '136' => 2,
        '144' => 2,
        '152' => 0,
        '156' => 2,
        '170' => 2,
        '174' => 0,
        '188' => 2,
        '191' => 2,
        '192' => 2,
        '203' => 2,
        '208' => 2,
        '214' => 2,
        '222' => 2,
        '230' => 2,
        '232' => 2,
        '238' => 2,
        '242' => 2,
        '262' => 0,
        '270' => 2,
        '292' => 2,
        '320' => 2,
        '324' => 0,
        '328' => 2,
        '332' => 2,
        '340' => 2,
        '344' => 2,
        '348' => 2,
        '352' => 0,
        '356' => 2,
        '360' => 2,
        '364' => 2,
        '368' => 3,
        '376' => 2,
        '388' => 2,
        '392' => 0,
        '398' => 2,
        '400' => 3,
        '404' => 2,
        '408' => 2,
        '410' => 0,
        '414' => 3,
        '417' => 2,
        '418' => 2,
        '422' => 2,
        '426' => 2,
        '428' => 2,
        '430' => 2,
        '434' => 3,
        '440' => 2,
        '446' => 2,
        '454' => 2,
        '458' => 2,
        '462' => 2,
        '478' => 2,
        '480' => 2,
        '484' => 2,
        '496' => 2,
        '498' => 2,
        '504' => 2,
        '504' => 2,
        '512' => 3,
        '516' => 2,
        '524' => 2,
        '532' => 2,
        '532' => 2,
        '533' => 2,
        '548' => 0,
        '554' => 2,
        '558' => 2,
        '566' => 2,
        '578' => 2,
        '586' => 2,
        '590' => 2,
        '598' => 2,
        '600' => 0,
        '604' => 2,
        '608' => 2,
        '634' => 2,
        '643' => 2,
        '646' => 0,
        '654' => 2,
        '678' => 2,
        '682' => 2,
        '690' => 2,
        '694' => 2,
        '702' => 2,
        '704' => 0,
        '706' => 2,
        '710' => 2,
        '728' => 2,
        '748' => 2,
        '752' => 2,
        '756' => 2,
        '760' => 2,
        '764' => 2,
        '776' => 2,
        '780' => 2,
        '784' => 2,
        '788' => 3,
        '800' => 2,
        '807' => 2,
        '818' => 2,
        '826' => 2,
        '834' => 2,
        '840' => 2,
        '858' => 2,
        '860' => 2,
        '882' => 2,
        '886' => 2,
        '901' => 2,
        '931' => 2,
        '932' => 2,
        '934' => 2,
        '936' => 2,
        '937' => 2,
        '938' => 2,
        '940' => 0,
        '941' => 2,
        '943' => 2,
        '944' => 2,
        '946' => 2,
        '947' => 2,
        '948' => 2,
        '949' => 2,
        '950' => 0,
        '951' => 2,
        '952' => 0,
        '953' => 0,
        '967' => 2,
        '968' => 2,
        '969' => 2,
        '970' => 2,
        '971' => 2,
        '972' => 2,
        '973' => 2,
        '974' => 0,
        '975' => 2,
        '976' => 2,
        '977' => 2,
        '978' => 2,
        '979' => 2,
        '980' => 2,
        '981' => 2,
        '984' => 2,
        '985' => 2,
        '986' => 2,
        '990' => 0,
        '997' => 2,
        '998' => 2,
        );
    private $_errorCode = array(
        '00000' => 'Successful operation',
        '00001' => 'Payment system not available',
        '00003' => 'Paybor error',
        '00004' => 'Card number or invalid cryptogram',
        '00006' => 'Access denied or invalid identification',
        '00008' => 'Invalid validity date',
        '00009' => 'Subscription creation failed',
        '00010' => 'Unknown currency',
        '00011' => 'Invalid amount',
        '00015' => 'Payment already done',
        '00016' => 'Existing subscriber',
        '00021' => 'Unauthorized card',
        '00029' => 'Invalid card',
        '00030' => 'Timeout',
        '00033' => 'Unauthorized IP country',
        '00040' => 'No 3-D Secure',
        );
    private $_resultMapping = array(
        'M' => 'amount',
        'R' => 'reference',
        'T' => 'call',
        'A' => 'authorization',
        'B' => 'subscription',
        'C' => 'cardType',
        'D' => 'validity',
        'E' => 'error',
        'F' => '3ds',
        'G' => '3dsWarranty',
        'H' => 'imprint',
        'I' => 'ip',
        'J' => 'lastNumbers',
        'K' => 'sign',
        'N' => 'firstNumbers',
        'O' => '3dsInlistment',
        'o' => 'celetemType',
        'P' => 'paymentType',
        'Q' => 'time',
        'S' => 'transaction',
        'U' => 'subscriptionData',
        'W' => 'date',
        'Y' => 'country',
        'Z' => 'paymentIndex',
        );

    protected function _buildUrl($url)
    {
        $url = Mage::getUrl($url, array('_secure' => true));
        $url = Mage::getModel('core/url')->sessionUrlVar($url);
        return $url;
    }

    protected function _callDirect($type, $amount, Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        $config = $this->getConfig();
        $config->setStore($order->getStoreId());

        $amountScale = $this->getCurrencyScale($order);
        $amount = round($amount * $amountScale);

        // Transaction information
        $callNumber = $transaction->getAdditionalInformation(Sofinco3X_Epayment_Model_Payment_Abstract::CALL_NUMBER);
        $transNumber = $transaction->getAdditionalInformation(Sofinco3X_Epayment_Model_Payment_Abstract::TRANSACTION_NUMBER);

        $version = '00103';
        $password = $config->getPassword();
        // if ($config->getSubscription() == 'plus') {
            // $version = '00104';
            // $password = $config->getPasswordplus();
        // }

        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $fields = array(
            'ACTIVITE' => '024',
            'VERSION' => $version,
            'CLE' => $password,
            'DATEQ' => $now->format('dmYHis'),
            'DEVISE' => sprintf('%03d', $this->getCurrency($order)),
            'IDENTIFIANT' => $config->getIdentifier(),
            'MONTANT' => sprintf('%010d', $amount),
            'NUMAPPEL' => sprintf('%010d', $callNumber),
            'NUMQUESTION' => sprintf('%010d', $now->format('U')),
            'NUMTRANS' => sprintf('%010d', $transNumber),
            'RANG' => sprintf('%02d', $config->getRank()),
            'REFERENCE' => $this->tokenizeOrder($order),
            'SITE' => sprintf('%07d', $config->getSite()),
            'TYPE' => sprintf('%05d', (int) $type),
            );

        // Specific PayPal
        $details = $transaction->getAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS);
        if (!empty($details['cardType'])) {
            switch ($details['cardType']) {
                case 'PAYPAL':
                $fields['ACQUEREUR'] = 'PAYPAL';
                    break;
            }
        }

        $urls = $config->getDirectUrls();
        $url = $this->checkUrls($urls);

        // Init client
        $clt = new Varien_Http_Client(
            $url, array(
            'maxredirects' => 0,
            'useragent' => 'Magento Sofinco3X module',
            'timeout' => 5,
            )
        );
        $clt->setMethod(Varien_Http_Client::POST);
        $clt->setRawData(http_build_query($fields));

        // Do call
        $response = $clt->request();

        if ($response->isSuccessful()) {
            // Process result
            $result = array();
            parse_str($response->getBody(), $result);
            return $result;
        }

        // Here, there's a problem
        Mage::throwException(Mage::helper('sf3xep')->__('Sofinco3X not available. Please try again later.'));
    }

    public function buildSystemParams(Mage_Sales_Model_Order $order, Sofinco3X_Epayment_Model_Payment_Abstract $payment)
    {
        $config = $this->getConfig();

        // URLs
        $baseUrl = 'sf3xep/payment';
        $values = array(
            'PBX_ANNULE' => $this->_buildUrl($baseUrl . '/cancel'),
            'PBX_EFFECTUE' => $this->_buildUrl($baseUrl . '/success'),
            'PBX_REFUSE' => $this->_buildUrl($baseUrl . '/failed'),
            'PBX_REPONDRE_A' => $this->_buildUrl($baseUrl . '/ipn'),
            );

        $values['PBX_VERSION'] = 'Magento_' . Mage::getVersion() . '-Sofinco3X_' . Mage::helper('sf3xep')->getExtensionVersion();

        // Merchant information
        $values['PBX_SITE'] = $config->getSite();
        $values['PBX_RANG'] = substr(sprintf('%03d', $config->getRank()), -3);
        $values['PBX_IDENTIFIANT'] = $config->getIdentifier();

        // Card information
        $cards = $payment->getCards();
        if ($payment->getHasCctypes()) {
            $code = $order->getPayment()->getData('cc_type');
        } else {
            $code = array_keys($cards);
            $code = $code[0];
        }

        if (!isset($cards[$code])) {
            $message = 'No card with code %s.';
            Mage::throwException(Mage::helper('sf3xep')->__($message), $code);
        }

        $card = $cards[$code];
        // $values['PBX_TYPEPAIEMENT'] = $card['payment'];
		// $values['PBX_TYPECARTE'] = $config->getSubscription();

        //Customer information
        $values['PBX_CUSTOMER'] = trim(substr($this->getCustomerInformation($order),21));
        //Billing information
        $values['PBX_BILLING'] = trim(substr($this->getBillingInformation($order),21));


        $values['PBX_PORTEUR'] = $this->getBillingEmail($order);
        $values['PBX_DEVISE'] = $this->getCurrency($order);
        $values['PBX_CMD'] = $this->tokenizeOrder($order);

        // Amount
        $orderAmount = $order->getBaseGrandTotal();
        // Amount
        $currencies = Mage::app()->getStore()->getAvailableCurrencyCodes();
        if (count($currencies) > 1 && $this->getConfig()->getCurrencyConfig() == 0) {
            $orderAmount = $order->getGrandTotal();
        } else {
            $orderAmount = $order->getBaseGrandTotal();
        }

        $amountScale = $this->_currencyDecimals[$values['PBX_DEVISE']];
        $amountScale = pow(10, $amountScale);

        if ($payment->getCode() == 'sf3xep_threetime') {
            $amounts = $this->computeThreetimePayments($orderAmount, $amountScale);
            foreach ($amounts as $k => $v) {
                $values[$k] = $v;
            }
        } else {
            $values['PBX_TOTAL'] = sprintf('%03d', round($orderAmount * $amountScale));
            switch ($payment->getSofinco3XAction()) {
                case Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_MANUAL:
                $values['PBX_AUTOSEULE'] = 'O';
                    break;

                case Sofinco3X_Epayment_Model_Payment_Abstract::PBXACTION_DEFERRED:
                $delay = (int) $payment->getConfigData('delay');
                if ($delay < 1) {
                    $delay = 1;
                } elseif ($delay > 7) {
                    $delay = 7;
                }

                $values['PBX_DIFF'] = sprintf('%02d', $delay);
                    break;
            }
        }

        // 3-D Secure
        if (!$payment->is3DSEnabled($order)) {
            $values['PBX_3DS'] = 'N';
        }

        // Sofinco3X => Magento
        $values['PBX_RETOUR'] = 'M:M;R:R;T:T;A:A;B:B;C:C;D:D;E:E;F:F;G:G;I:I;J:J;N:N;O:O;P:P;Q:Q;S:S;W:W;Y:Y;K:K';
        $values['PBX_RUF1'] = 'POST';

        // Choose correct language
        $lang = Mage::app()->getLocale();
        if (!empty($lang)) {
            $lang = preg_replace('#_.*$#', '', $lang->getLocaleCode());
        }

        $languages = $config->getLanguages();
        if (!array_key_exists($lang, $languages)) {
            $lang = 'default';
        }

        $lang = $languages[$lang];
        $values['PBX_LANGUE'] = $lang;

        if($card['payment'] != 'LIMONETIK'){
			// Choose page format depending on browser/devise
			if (Mage::helper('sf3xep/mobile')->isMobile()) {
				$values['PBX_SOURCE'] = 'XHTML';
			}

			if ($config->getResponsiveConfig() == 1) {
				$values['PBX_SOURCE'] = 'RWD';
			}
		}
        // Specific PayPal
        if ($payment->getCode() == 'sf3xep_paypal') {
            $separator = '#';
            $address = $order->getBillingAddress();
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $data_Paypal = $this->cleanForPaypalData($this->getBillingName($order), 32);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getStreet(1), 100);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getStreet(2), 100);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getCity(), 40);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getRegion(), 40);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getPostcode(), 20);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getCountry(), 2);
            $data_Paypal .= $separator;
            $data_Paypal .= $this->cleanForPaypalData($address->getTelephone(), 20);
            $data_Paypal .= $separator;
            $items = $order->getAllVisibleItems();
            $products = array();
            foreach ($items as $item) {
                $products[] = $item->getName();
            }

            $data_Paypal .= $this->cleanForPaypalData(implode('-', $products), 127);

            $values['PBX_PAYPAL_DATA'] = $data_Paypal;
        }


        // Misc.
        $values['PBX_TIME'] = date('c');
        $values['PBX_HASH'] = strtoupper($config->getHmacAlgo());

        // Card specific workaround
        if (($card['payment'] == 'LEETCHI') && ($card['card'] == 'LEETCHI')) {
            $values['PBX_EFFECTUE'] .= '?R=' . urlencode($values['PBX_CMD']);
            $values['PBX_REFUSE'] .= '?R=' . urlencode($values['PBX_CMD']);
        } elseif (($card['payment'] == 'PREPAYEE') && ($card['card'] == 'IDEAL')) {
            $s = '?C=IDEAL&P=PREPAYEE';
            $values['PBX_ANNULE'] .= $s;
            $values['PBX_EFFECTUE'] .= $s;
            $values['PBX_REFUSE'] .= $s;
            $values['PBX_REPONDRE_A'] .= $s;
        }

        // PBX Version
        $values['PBX_VERSION'] = 'Magento_' . Mage::getVersion() . '-' . 'sofinco' . '_' . Mage::getConfig()->getModuleConfig("Sofinco3X_Epayment")->version;

        // Sort parameters for simpler debug
        ksort($values);

        // Sign values
        $sign = $this->signValues($values);

        // Hash HMAC
        $values['PBX_HMAC'] = $sign;
        return $values;
    }



/**generating xml for customer and billing**/
    public function getCustomerInformation(Mage_Sales_Model_Order $order)
    {
        // if (null !== $order->getCustomerId()) {
            // $customer = $this->_objectManager
                // ->get('Magento\Customer\Model\Customer')
                // ->load($order->getCustomerId());
        // }
        $simpleXMLElement = new SimpleXMLElement("<Customer/>");
        // $customer = $simpleXMLElement->addChild('Customer');
        $simpleXMLElement->addChild('Id',$order->getCustomerId());        
//$order->getCustomerName()        
        return  $simpleXMLElement->asXML();
    }
	
    public function getBillingInformation(Mage_Sales_Model_Order $order)
    {
        $address = $order->getBillingAddress();
        $firstName = $order->getCustomerFirstname();
        $lastName = $order->getCustomerLastname();
        $address1 = is_array($address->getStreet()) ? $address->getStreet()[0] : $address->getStreet();
        $zipCode = $address->getPostcode();
        $city = $address->getCity();
        $countryCode = $this->getCountryCode($address->country_id);
        $countryName = $address->country_id;
        $countryCodeHomePhone = $this->getCountryPhoneCode($address->country_id);
        $homePhone = substr($address->getTelephone(),3);
        $countryCodeMobilePhone = $this->getCountryPhoneCode($address->country_id);
        $mobilePhone = substr($address->getTelephone(),3);
		$title = $order->getCustomerGender();
        if(empty($title))$title="Mr";
        $simpleXMLElement = new SimpleXMLElement("<Billing/>");
        // $billingXML = $simpleXMLElement->addChild('Billing');
        $addressXML = $simpleXMLElement->addChild('Address');
        $addressXML->addChild('Title',$title);
        $addressXML->addChild('FirstName',$firstName);
        $addressXML->addChild('LastName',$lastName);
        $addressXML->addChild('Address1',$address1);
        $addressXML->addChild('ZipCode',$zipCode);
        $addressXML->addChild('City',$city);
        $addressXML->addChild('CountryCode',$countryCode);
        $addressXML->addChild('CountryName',$countryName);
        $addressXML->addChild('CountryCodeHomePhone',$countryCodeHomePhone);
        $addressXML->addChild('HomePhone',$homePhone);
        $addressXML->addChild('CountryCodeMobilePhone',$countryCodeMobilePhone);
        $addressXML->addChild('MobilePhone',$mobilePhone);
        
        return $simpleXMLElement->asXML();
    }

    public function getCountryCode($countryCode)
    {
        $countryMapper = Mage::getSingleton('sf3xep/IsoCountry');
        return $countryMapper->getIsoCode($countryCode);
    }

    public function getCountryPhoneCode($countryCode)
    {
        $countryMapper = Mage::getSingleton('sf3xep/IsoCountry');
        return $countryMapper->getPhoneCode($countryCode);
    }
    
    public function cleanForPaypalData($string, $nbCaracter = 0)
    {
        $string = trim(preg_replace("/[^-+. a-zA-Z0-9]/", " ", Mage::helper('core')->removeAccents($string)));
        if ($nbCaracter > 0) {
            $string = substr($string, 0, $nbCaracter);
        }

        return $string;
    }

    public function checkUrls(array $urls)
    {
        // Init client
        $client = new Varien_Http_Client(
            null, array(
            'maxredirects' => 0,
            'useragent' => 'Magento Sofinco3X module',
            'timeout' => 5,
            )
        );
        $client->setMethod(Varien_Http_Client::GET);

        $error = null;
        foreach ($urls as $url) {
            $testUrl = preg_replace('#^([a-zA-Z0-9]+://[^/]+)(/.*)?$#', '\1/load.html', $url);
            $client->setUri($testUrl);

            try {
                $response = $client->request();
                if ($response->isSuccessful()) {
                    return $url;
                }
            } catch (Exception $e) {
                $error = $e;
            }
        }

        // Here, there's a problem
        throw new Exception(Mage::helper('sf3xep')->__('Sofinco3X not available. Please try again later.'));
    }

    public function computeThreetimePayments($orderAmount, $amountScale)
    {
        $values = array();
        // Compute each payment amount
        $step = round($orderAmount * $amountScale / 3);
        $firstStep = ($orderAmount * $amountScale) - 2 * $step;
        $values['PBX_TOTAL'] = sprintf('%03d', $firstStep);
        $values['PBX_2MONT1'] = sprintf('%03d', $step);
        $values['PBX_2MONT2'] = sprintf('%03d', $step);

        // Payment dates
        $now = new DateTime();
        $now->modify('1 month');
        $values['PBX_DATE1'] = $now->format('d/m/Y');
        $now->modify('1 month');
        $values['PBX_DATE2'] = $now->format('d/m/Y');


        // Force validity date of card
        $values['PBX_DATEVALMAX'] = $now->format('ym');
        return $values;
    }

    public function convertParams(array $params)
    {
        $result = array();
        foreach ($this->_resultMapping as $param => $key) {
            if (isset($params[$param])) {
                $result[$key] = utf8_encode($params[$param]);
            }
        }

        return $result;
    }

    /**
     * Create transaction ID from Sofinco3X data
     */
    protected function createTransactionId(array $sofincoData)
    {
        $transaction = (int) (isset($sofincoData['transaction']) ? $sofincoData['transaction'] : $sofincoData['NUMTRANS']);
        $now = new DateTime('now', new DateTimeZone('Europe/Paris'));
        return $transaction . '/' . $now->format('U');
    }

    public function directCapture($amount, Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        return $this->_callDirect(2, $amount, $order, $transaction);
    }

    public function directRecurringDelete(Mage_Sales_Model_Order $order)
    {
        $config = $this->getConfig();
        $urls = $config->getResAboUrls();
        $url = $this->checkUrls($urls);

        // Call parameters
        $fields = array(
            'IDENTIFIANT' => $config->getIdentifier(),
            'MACH' => sprintf('%03d', $config->getRank()),
            'REFERENCE' => $order->getIncrementId() . ' - ' . $this->getBillingName($order),
            'SITE' => $config->getSite(),
            'TYPE' => '001',
            'VERSION' => '001',
            );

        // Init client
        $clt = new Varien_Http_Client(
            $url, array(
            'maxredirects' => 0,
            'useragent' => 'Magento Sofinco3X module',
            'timeout' => 5,
            )
        );
        $clt->setMethod(Varien_Http_Client::POST);
        $clt->setRawData(http_build_query($fields));

        // Do call
        $response = $clt->request();
        if ($response->isSuccessful()) {
            // Process result
            if (strtolower($response->getHeader('transfer-encoding')) == 'chunked') {
                $body = $this->decodeChunkedBody($response->getRawBody());
            } else {
                $body = $response->getBody();
            }

            $result = array();
            parse_str($body, $result);
            return $result;
        }

        // Here, there's a problem
        Mage::throwException(Mage::helper('sf3xep')->__('Sofinco3X not available. Please try again later.'));
    }

    public function decodeChunkedBody($body)
    {
        $decBody = '';

        // If mbstring overloads substr and strlen functions, we have to
        // override it's internal encoding
        if (function_exists('mb_internal_encoding') &&
            ((int) ini_get('mbstring.func_overload')) & 2) {
            $mbIntEnc = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }

        while (trim($body)) {
            if (!preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $body, $m)) {
                #require_once 'Zend/Http/Exception.php';
            $body = sprintf("%x\r\n%s\r\n", strlen($body), $body);
            }

            if (!preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $body, $m)) {
                throw new Zend_Http_Exception("Error parsing body - doesn't seem to be a chunked message");
            }

            $length = hexdec(trim($m[1]));
            $cut = strlen($m[0]);
            $decBody .= substr($body, $cut, $length);
            $body = substr($body, $cut + $length + 2);
        }

        if (isset($mbIntEnc)) {
            mb_internal_encoding($mbIntEnc);
        }

        return $decBody;
    }

    public function directRefund($amount, Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Payment_Transaction $transaction)
    {
        return $this->_callDirect(14, $amount, $order, $transaction);
    }

    public function getBillingEmail(Mage_Sales_Model_Order $order)
    {
        return $order->getCustomerEmail();
    }

    public function getBillingName(Mage_Sales_Model_Order $order)
    {
        return trim(preg_replace("/[^-. a-zA-Z0-9]/", " ", Mage::helper('core')->removeAccents($order->getCustomerName())));
    }

    /**
     * @return Sofinco3X_Epayment_Model_Config Sofinco3X configuration object
     */
    public function getConfig()
    {
        return Mage::getSingleton('sf3xep/config');
    }

    public function getCurrency(Mage_Sales_Model_Order $order)
    {
        $currencyMapper = Mage::getSingleton('sf3xep/iso4217Currency');

        $currencies = Mage::app()->getStore()->getAvailableCurrencyCodes();
        if (count($currencies) > 1 && $this->getConfig()->getCurrencyConfig() == 0) {
            $currency = $order->getOrderCurrencyCode();
        } else {
            $currency = $order->getBaseCurrencyCode();
        }

        return $currencyMapper->getIsoCode($currency);
    }

    public function getCurrencyDecimals($cartOrOrder)
    {
        return $this->_currencyDecimals[$this->getCurrency($cartOrOrder)];
    }

    public function getCurrencyScale($cartOrOrder)
    {
        return pow(10, $this->getCurrencyDecimals($cartOrOrder));
    }

    public function getParams($logParams = false, $checkSign = true)
    {
        // Retrieves data
        $data = file_get_contents('php://input');
        if (empty($data)) {
            $data = $_SERVER['QUERY_STRING'];
        }

        if (empty($data)) {
            $helper = Mage::helper('sf3xep');
            Mage::throwException($helper->__('An unexpected error in Sofinco3X call has occured: no parameters.'));
        }

        // Log params if needed
        if ($logParams) {
            $this->logDebug(sprintf('Call params: %s', $data));
        }

        // Check signature if needed
        if ($checkSign) {
            // Extract signature
            $matches = array();
            if (!preg_match('#^(.*)&K=(.*)$#', $data, $matches)) {
                $helper = Mage::helper('sf3xep');
                Mage::throwException($helper->__('An unexpected error in Sofinco3X call has occured: missing signature.'));
            }

            // Check signature
            $signature = base64_decode(urldecode($matches[2]));
            $pubkey = file_get_contents(Mage::getModuleDir('etc', 'Sofinco3X_Epayment') . '/pubkey.pem');
            $res = (boolean) openssl_verify($matches[1], $signature, $pubkey);

            if (!$res) {
                if (preg_match('#^C=IDEAL&P=PREPAYEE&(.*)&K=(.*)$#', $data, $matches)) {
                    $signature = base64_decode(urldecode($matches[2]));
                    $res = (boolean) openssl_verify($matches[1], $signature, $pubkey);
                }

                if (!$res) {
                    $helper = Mage::helper('sf3xep');
//                    Mage::throwException($helper->__('An unexpected error in Sofinco3X call has occured: invalid signature.'));
                }
            }
        }

        $rawParams = array();
        parse_str($data, $rawParams);

        // Decrypt params
        $params = $this->convertParams($rawParams);
        if (empty($params)) {
            $helper = Mage::helper('sf3xep');
            Mage::throwException($helper->__('An unexpected error in Sofinco3X call has occured.'));
        }

        return $params;
    }

    public function getSystemUrl()
    {
        $config = $this->getConfig();
        $urls = $config->getSystemUrls();
        if (empty($urls)) {
            $message = 'Missing URL for Sofinco3X system in configuration';
            $helper = Mage::helper('sf3xep');
            Mage::throwException($helper->__($message));
        }

        $url = $this->checkUrls($urls);

        return $url;
    }

    public function getResponsiveUrl()
    {
        $config = $this->getConfig();
        $urls = $config->getResponsiveUrls();
        if (empty($urls)) {
            $message = 'Missing URL for Sofinco3X responsive in configuration';
            throw new \LogicException(__($message));
        }

        $url = $this->checkUrls($urls);

        return $url;
    }


    public function getKwixoUrl()
    {
        $config = $this->getConfig();
        $urls = $config->getKwixoUrls();
        if (empty($urls)) {
            $message = 'Missing URL for Sofinco3X system in configuration';
            $helper = Mage::helper('sf3xep');
            Mage::throwException($helper->__($message));
        }

        $url = $this->checkUrls($urls);

        return $url;
    }

    public function getAncvUrl()
    {
        $config = $this->getConfig();
        $urls = $config->getAncvUrls();
        if (empty($urls)) {
            $message = 'Missing URL for Sofinco3X system in configuration';
            $helper = Mage::helper('sf3xep');
            Mage::throwException($helper->__($message));
        }

        $url = $this->checkUrls($urls);

        return $url;
    }

    public function logDebug($message)
    {
        Mage::log($message, Zend_Log::DEBUG, 'sofinco-epayment.log');
    }

    public function logWarning($message)
    {
        Mage::log($message, Zend_Log::WARN, 'sofinco-epayment.log');
    }

    public function logError($message)
    {
        Mage::log($message, Zend_Log::ERR, 'sofinco-epayment.log');
    }

    public function logFatal($message)
    {
        Mage::log($message, Zend_Log::ALERT, 'sofinco-epayment.log');
    }

    public function signValues(array $values)
    {
        $config = $this->getConfig();

        // Serialize values
        $query = array();
        foreach ($values as $name => $value) {
            $query[] = $name . '=' . $value;
        }

        $query = implode('&', $query);

        // Prepare key
        $key = pack('H*', $config->getHmacKey());

        // Sign values
        $sign = hash_hmac($config->getHmacAlgo(), $query, $key);
        if ($sign === false) {
            $errorMsg = 'Unable to create hmac signature. Maybe a wrong configuration.';
            $helper = Mage::helper('sf3xep');
            Mage::throwException($helper->__($errorMsg));
        }

        return strtoupper($sign);
    }

    public function toErrorMessage($code)
    {
        if (isset($this->_errorCode[$code])) {
            return $this->_errorCode[$code];
        }

        return 'Unknown error ' . $code;
    }

    public function tokenizeOrder(Mage_Sales_Model_Order $order)
    {
        $reference = array();
        $reference[] = $order->getRealOrderId();
        $reference[] = $this->getBillingName($order);
        $reference = implode(' - ', $reference);
        return $reference;
    }

    /**
     * Load order from the $token
     * @param string $token Token (@see tokenizeOrder)
     * @return Mage_Sales_Model_Order
     */
    public function untokenizeOrder($token)
    {
        $parts = explode(' - ', $token, 2);
        if (count($parts) < 2) {
            $message = 'Invalid decrypted token "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $token));
        }

        // Retrieves order
        $order = Mage::getSingleton('sales/order')->loadByIncrementId($parts[0]);
        if (empty($order)) {
            $message = 'Not existing order id from decrypted token "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $token));
        }

        if (is_null($order->getId())) {
            $message = 'Not existing order id from decrypted token "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $token));
        }

        $goodName = $this->getBillingName($order);
        if (($goodName != utf8_decode($parts[1])) && ($goodName != $parts[1])) {
            $message = 'Consistency error on descrypted token "%s"';
            Mage::throwException(Mage::helper('sf3xep')->__($message, $token));
        }

        return $order;
    }
}
