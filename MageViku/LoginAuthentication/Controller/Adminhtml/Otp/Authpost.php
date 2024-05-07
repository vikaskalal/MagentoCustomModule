<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\LoginAuthentication\Controller\Adminhtml\Otp;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use MageViku\LoginAuthentication\Helper\Data as LoginOtpHelper;
use Magento\User\Model\User;

/**
 * Otp authenticator post controller
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Authpost extends Action implements HttpPostActionInterface
{
    /**
     * @var LoginOtpHelper
     */
    private $loginOtpHelper;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param Context $context
     * @param Session $session
     * @param JsonFactory $jsonFactory
     * @param LoginOtpHelper $loginOtpHelper
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        JsonFactory $jsonFactory,
        LoginOtpHelper     $loginOtpHelper,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->loginOtpHelper = $loginOtpHelper;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritdoc
     *
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $user = $this->session->getUser();
        $response = $this->jsonFactory->create();
        /** @var \Magento\Framework\DataObject $request */
        $request = $this->dataObjectFactory->create(['data' => $this->getRequest()->getParams()]);
        $result = $this->loginOtpHelper->verifyOtp($user, $request);
        if ($result && isset($result['success']) && $result['success'] == true) {
            $this->loginOtpHelper->getOtpSessionModel()->setOtpVerified();
            $this->loginOtpHelper->updateOtpStatus($user);
            $response->setData(['success' => true]);
        } else {
            $response->setData($result);
        }

        return $response;
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $user = $this->session->getUser();

        return $user
            && $this->loginOtpHelper->isEnabled();
    }
}