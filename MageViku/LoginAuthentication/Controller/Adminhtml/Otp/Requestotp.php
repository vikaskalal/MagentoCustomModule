<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\LoginAuthentication\Controller\Adminhtml\Otp;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use MageViku\LoginAuthentication\Helper\Data as LoginOtpHelper;
use Magento\Backend\App\Action;
use \Magento\Framework\UrlInterface;

/**
 * Request 2FA config from the user.
 */
class Requestotp extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['requestotp'];

    /**
     * @var LoginOtpHelper
     */
    private $loginOtpHelper;

    /**
     * @var Session
     */
    private $session;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param Context $context
     * @param Session $session
     */
    public function __construct(
        Context $context,
        LoginOtpHelper     $loginOtpHelper,
        Session $session,
        UrlInterface $_urlBuilder
    ) {
        parent::__construct($context);
        $this->loginOtpHelper = $loginOtpHelper;
        $this->session = $session;
        $this->_urlBuilder = $_urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->loginOtpHelper->isEnabled()) {
            $this->_response->setStatusHeader(403, '1.1', 'Forbidden');
            return $this->_redirect('*/auth/login');
        }

        return parent::dispatch($request);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $user = $this->session->getUser();
        if ($this->loginOtpHelper->getOtpSessionModel()->getOtpVerified()) {
            return $this->_redirect($this->_urlBuilder->getStartupPageUrl());
        }
        try {
            $this->loginOtpHelper->sendEmailWithotp($user);
        }  catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong %1',$exception->getMessage()));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}