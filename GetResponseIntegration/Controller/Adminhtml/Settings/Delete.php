<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Model\Account;
use GetResponse\GetResponseIntegration\Model\Automation;
use GetResponse\GetResponseIntegration\Model\Settings;
use GetResponse\GetResponseIntegration\Model\Webform;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Delete extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Delete constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        /** @var Settings $settings */
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        $settings->load($storeId, 'id_shop')->delete();

        /** @var Account $account */
        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');
        $account->load($storeId, 'id_shop')->delete();

        /** @var Webform $webform */
        $webform = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        $webform->load($storeId, 'id_shop')->delete();

        /** @var Automation $automation */
        $automation = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        $automations = $automation->getCollection()->addFieldToFilter('id_shop', $storeId);
        foreach ($automations as $automation) {
            $automation->delete();
        }

        $this->messageManager->addSuccessMessage('You disconnected your Magento from GetResponse.');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse account');

        return $resultPage;
    }
}