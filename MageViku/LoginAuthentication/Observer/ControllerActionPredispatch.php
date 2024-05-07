<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\LoginAuthentication\Observer;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use MageViku\LoginAuthentication\Helper\Data as LoginOtpHelper;
use Magento\Backend\Model\Auth\Session as AuthSession;

/**
 * Handle redirection to 2FA page if required
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var AbstractAction|null
     */
    private $action;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Backend authorization session
     *
     * @var AuthSession
     */
    protected $authSession;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param ActionFlag $actionFlag
     * @param AuthSession $authSession
     * @param UrlInterface $url
     * @param AuthorizationInterface $authorization
     * @param UserContextInterface $userContext
     */
    public function __construct(
        ActionFlag $actionFlag,
        AuthSession $authSession,
        UrlInterface $url,
        AuthorizationInterface $authorization,
        UserContextInterface $userContext,
        LoginOtpHelper     $loginOtpHelper
    ) {
        $this->actionFlag = $actionFlag;
        $this->authSession     = $authSession;
        $this->url = $url;
        $this->authorization = $authorization;
        $this->userContext = $userContext;
        $this->loginOtpHelper = $loginOtpHelper;
    }

    /**
     * Get current user
     * @return User|null
     */
    private function getUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * Redirect user to given URL.
     *
     * @param string $url
     * @return void
     */
    private function redirect(string $url): void
    {
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        $this->action->getResponse()->setRedirect($this->url->getUrl($url));
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->loginOtpHelper->isEnabled()) {
            return;
        }
        /** @var $controllerAction AbstractAction */
        $controllerAction = $observer->getEvent()->getData('controller_action');
        $this->action = $controllerAction;
        $fullActionName = $observer->getEvent()->getData('request')->getFullActionName();
        $user                    = $this->getUser();
        $userId = $this->userContext->getUserId();
        $allowForce2faActionList = [
            'adminhtml_system_account_index',
            'adminhtml_system_account_save',
            'adminhtml_auth_logout',
            'adminhtml_auth_login',
            'adminhtml_auth_forgotpassword',
            'login_auth_otp_requestotp',
            'login_auth_otp_authpost',
            'mui_index_render'
        ];
        if (in_array($fullActionName, $allowForce2faActionList, true)) {
            //Actions that are used for 2FA must remain accessible.
            return;
        }
        $otpVerified = $this->loginOtpHelper->getOtpSessionModel()->getOtpVerified();
        if ($userId && $this->loginOtpHelper->isEnabled()
            && !$otpVerified
        ) {
            $this->redirect('loginauthentication/otp/requestotp');
        }
    }
}