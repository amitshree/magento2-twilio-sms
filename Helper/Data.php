<?php
/**
 * Created by PhpStorm.
 * User: Amit Thakur
 * Date: 15/8/18
 * Time: 6:19 PM
 */
namespace Amitshree\TwilioSms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Twilio\Rest\Client;

class Data extends AbstractHelper {

    const XML_PATH_TWILIO = 'twilio/';
    const XML_REGISTER_SUCCESS_MESSAGE = 'registration_message';
    const XML_ORDER_SUCESS_MESSAGE = 'order_success_message';
    const XML_MODULE_ENABLED = 'enable';

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        parent::__construct($context);
    }


    public function getConfigValues($field, $storeId) {

        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }
    public function getGeneralConfig($code, $storeId = null) {
        return $this->getConfigValues(self::XML_PATH_TWILIO . 'general/' . $code, $storeId);
    }

    public function getRegistrationMessage($firstName) {
        $messageTemplate = $this->getGeneralConfig(self::XML_REGISTER_SUCCESS_MESSAGE);
        if(preg_match('/\{\{([a-z0-9_]+)\}\}/', $messageTemplate, $matches)) {
            $message = str_replace('{{' . $matches[1] . '}}', $firstName, $messageTemplate);
        };
        return $message;
    }

        public function getOrderMessage($firstName, $orderId) {
        $messageTemplate = $this->getGeneralConfig(self::XML_ORDER_SUCESS_MESSAGE);
        $message = str_replace('{{first_name}}', $firstName, $messageTemplate);
        $message = str_replace('{{order_id}}', $orderId, $message);
        return $message;
    }

    public function isModuleEnabled() {
        return $this->getGeneralConfig(self::XML_MODULE_ENABLED);
    }

    public function sendSms($message, $mobileNo) {
        $accountSid = $this->getGeneralConfig('account_sid');
        $authToken = $this->getGeneralConfig('auth_token');
        $twilioNumber = $this->getGeneralConfig('twilio_number');
        $client = new Client($accountSid, $authToken);
        try {
        $client->messages->create(
            $mobileNo,
            array(
                'from' => $twilioNumber,
                'body' => $message
            )
        );
        } catch (\Exception $e) {
            $this->logger->info('Issue with sending sms : ' . $e->getMessage());
        }
    }
}