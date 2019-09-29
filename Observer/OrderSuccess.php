<?php
/**
 * Created by PhpStorm.
 * User: Amit Thakur
 * Date: 15/8/18
 * Time: 6:08 PM
 */

namespace Amitshree\TwilioSms\Observer;


use Amitshree\TwilioSms\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

class OrderSuccess implements ObserverInterface
{


    public function __construct(
        Session $session,
        Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
        $this->session = $session;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isModuleEnabled = $this->helper->isModuleEnabled();
        if (!$isModuleEnabled) {
            return $this;
        }

        $orderId = $observer->getEvent()->getOrderIds()[0];
        $incId = $this->orderRepository->get($orderId)->getIncrementId();
        $customer = $this->session->getCustomer();
        $firstName = $customer->getFirstname();
        $mobileNo = $customer->getMobileNo();
        $message = $this->helper->getOrderMessage($firstName, $incId);
        $this->helper->sendSms($message, $mobileNo);
    }
}