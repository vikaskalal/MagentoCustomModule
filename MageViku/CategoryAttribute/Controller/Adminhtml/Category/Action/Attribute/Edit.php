<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageViku\CategoryAttribute\Controller\Adminhtml\Category\Action\Attribute;

use MageViku\CategoryAttribute\Controller\Adminhtml\Category\Action\Attribute as AttributeAction;
use MageViku\CategoryAttribute\Helper\Category\Edit\Action\Attribute;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Form for mass updatings categories' attributes.
 * Can be accessed by GET since it's a form,
 * can be accessed by POST since it's used as a processor of a mass-action button.
 */
class Edit extends AttributeAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;


    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Attribute $attributeHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Attribute $attributeHelper
    ) {
        parent::__construct($context,$attributeHelper);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $categoryIds = $this->getRequest()->getParam('categories');
        if (!empty($categoryIds)) {
            $this->_attributeHelper->setCategoryIds($categoryIds);
        }
        if (!$this->_validateCategories()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/category/', ['_current' => true]);
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Update Attributes'));
        return $resultPage;
    }
}
