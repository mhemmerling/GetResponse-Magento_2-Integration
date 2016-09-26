<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Block\Settings;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeFromRegister
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromRegister implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * SubscribeFromRegister constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var Settings $block */
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $settings = $block->getSettings();

        if ($settings['active_subscription'] != true) {
            return $this;
        }
        $customer = $observer->getEvent()->getCustomer();

        $subscriber = $this->_objectManager->create('Magento\Newsletter\Model\Subscriber');
        $subscriber->loadByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {
            $params = [];
            $params['campaign'] = ['campaignId' => $settings['campaign_id']];
            $params['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            $params['email'] = $customer->getEmail();

            if (isset($settings['cycle_day'])) {
                $params['dayOfCycle'] = (int)$settings['cycle_day'];
            }

            $block->getClient()->addContact($params);
        }

        return $this;
    }
}
