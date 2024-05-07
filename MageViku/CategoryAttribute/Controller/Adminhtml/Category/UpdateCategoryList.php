<?php
/**
 * MageViku
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageViku.com license that is
 * available through the world-wide-web at this URL:
 * http://MageViku.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   MageViku
 * @package    MageViku_CategoryAttribute
 * @copyright  Copyright (c) MageViku (http://www.MageViku.com/)
 * @license    http://www.MageViku.com/LICENSE-1.0.html
 */
namespace MageViku\CategoryAttribute\Controller\Adminhtml\Category;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class UpdateCategoryList extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Category List page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Category'));
        return $resultPage;
    }
}
