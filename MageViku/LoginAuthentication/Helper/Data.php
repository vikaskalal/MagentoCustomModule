<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageViku\LoginAuthentication\Helper;

use MageViku\LoginAuthentication\Model\Exception\NotificationException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;
use MageViku\LoginAuthentication\Model\LoginAuthenticationOtpFactory;
use Psr\Log\LoggerInterface;
use MageViku\LoginAuthentication\Model\OtpSession;

/**
 * Class Data
 * @package MageViku\LoginAuthentication\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const OTP_MODULE_ENABLE = 'mageviku_otp_login/mageviku_otp_authentication/enable';
    const OTP_TYPE = 'mageviku_otp_login/mageviku_otp_authentication/otp_type';
    const OTP_LENGTH = 'mageviku_otp_login/mageviku_otp_authentication/otp_length';
    const EXPIRE_TIME = 'mageviku_otp_login/mageviku_otp_authentication/expire_time';
    const OTP_EMAIL_TEMPLATE = 'mageviku_otp_login/mageviku_otp_authentication/email_template';
    const SECONDS_IN_MINUTE = 60;
    const DEFAULT_OTP_EXPIRED_TIME = 300;//otp expired time is in seconds.
    const OTP_VALID_STATUS = 1;
    const OTP_INVALID_STATUS = 0;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var LoginAuthenticationOtpFactory
     */
    private $loginAuthenticationOtp;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var OtpSession
     */
    public $otpSession;

    /**
     * Data constructor
     *
     * @param ScopeConfigInterface $scopeConfig [description]
     * @param Context $context [description]
     * @param StoreManagerInterface $storeManager [description]
     * @param TransportBuilder $transportBuilder
     * @param LoginAuthenticationOtpFactory $loginAuthenticationOtp
     * @param LoggerInterface $logger
     * @param OtpSession $otpSession
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        LoginAuthenticationOtpFactory        $loginAuthenticationOtp,
        LoggerInterface $logger,
        OtpSession $otpSession
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->loginAuthenticationOtp = $loginAuthenticationOtp;
        $this->logger = $logger;
        $this->otpSession = $otpSession;
        return parent::__construct($context);
    }

    /**
     * @param  String $path
     * @param int $storeId
     * @return string
     */
    public function getConfigvalue($path, $storeId = 0)
    {
        $storeId = ($storeId) ? $storeId : $this->getStore()->getStoreId();
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getConfigvalue(self::OTP_MODULE_ENABLE);
    }

    /**
     * @return string
     */
    public function getOtptype()
    {
        return $this->getConfigvalue(self::OTP_TYPE);
    }

    /**
     * @return string
     */
    public function getOtplength()
    {
        return $this->getConfigvalue(self::OTP_LENGTH);
    }

    /**
     * @return string
     */
    public function getExpiretime()
    {
        $expiredTime = $this->getConfigvalue(self::EXPIRE_TIME);
        return ($expiredTime) ? $expiredTime : self::DEFAULT_OTP_EXPIRED_TIME;
    }

    /**
     * Get admin set expire time in minutes.
     *
     * @return int
     */
    public function getExpireTimeInMinutes()
    {
        $expireTimeInSec = $this->getExpiretime();
        return floor($expireTimeInSec/self::SECONDS_IN_MINUTE);
    }

    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getEmailTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * @return string
     */
    public function generateOtpCode()
    {
        $otp_type = $this->getOtptype();
        $otp_length = $this->getOtplength();

        if (empty($otp_length)) {
            $otp_length = 4;
        }
        if ($otp_type == "number") {
            $str_result = '0123456789';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } elseif ($otp_type == "alphabets") {
            $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } elseif ($otp_type == "alphanumeric") {
            $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $otp_code =  substr(str_shuffle($str_result), 0, $otp_length);
        } else {
            $otp_code = mt_rand(10000, 99999);
        }
        return $otp_code;
    }

    /**
     * Send otp email to the admin user.
     *
     * @param User $user
     * @param string $token
     * @param string $emailTemplateId
     * @param string $url
     * @return void
     */
    public function sendEmail(
        User $user,
        string $emailTemplateId,
        $otpCode
    ): void {
        try {
            $username = $user->getFirstName() . ' ' . $user->getLastName();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplateId)
                ->setTemplateOptions([
                    'area' => 'adminhtml',
                    'store' => 0
                ])
                ->setTemplateVars(
                    [
                        'username' => $username,
                        'otp' => $otpCode,
                        'otp_expired_time' => $this->getExpireTimeInMinutes(),
                        'store_name' => $this->storeManager->getStore()->getFrontendName()
                    ]
                )
                ->setFromByScope(
                    $this->scopeConfig->getValue('admin/emails/forgot_email_identity')
                )
                ->addTo($user->getEmail(), $username)
                ->getTransport();
            $transport->sendMessage();
        } catch (\Throwable $exception) {
            $this->logger->critical($exception);
            throw new NotificationException('Failed to send Otp E-mail to a user', 0, $exception);
        }
    }

    /**
     * Save otp data and email
     *
     * @param $user
     * @return array|void
     */
    public function sendEmailWithotp($user){
        if(!$user->getUserId()) return [];
        try {
            $otpCode         = $this->generateOtpCode();
            $emailTemplateId = $this->getEmailTemplateId(self::OTP_EMAIL_TEMPLATE);
            $this->sendEmail($user,$emailTemplateId,$otpCode);
            $loginAuthenticationModel = $this->loginAuthenticationOtp->create();
            $otpSaveData = [
                'user_id' => $user->getUserId(),
                'customer_email' => $user->getEmail(),
                'user_name' => $user->getUserName(),
                'otp' => $otpCode,
                'status' => self::OTP_VALID_STATUS,
            ];
            $this->updateOtpStatus($user);//make old otp invalid when new is generated
            $loginAuthenticationModel->setData($otpSaveData)->save();//Save new otp
        } catch (\Exception $e){
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Validate otp
     *
     * @param $user
     * @param $request
     * @return array
     */
    public function verifyOtp($user, $request){
        $otp = $request->getOtpCode();
        $response = [];
        $loginAuthenticationModel = $this->loginAuthenticationOtp->create();
        $loginAuthenticationCode = $loginAuthenticationModel->getCollection()
            ->addFieldToFilter('otp', $otp)
            ->addFieldToFilter('status', self::OTP_VALID_STATUS)
            ->addFieldToFilter('user_id', $user->getUserId())
            ->setOrder('created_at', 'DESC')
            ->getFirstItem();
        //config expire time
        $expiredtime = $this->getExpiretime();
        // check value is empty or not
        if (!empty($loginAuthenticationCode->getId())) {
            $created_at = (int) strtotime($loginAuthenticationCode->getCreatedAt());
            $now = time();
            $now = (int) $now;
            $expire = $now -= $created_at;
            $otpstatus = $loginAuthenticationCode->getStatus();
            if ($otpstatus == self::OTP_VALID_STATUS) {
                //check expiredtime
                if ($expire <= $expiredtime) {
                    $response = [
                        'success' => true,
                        'message' => __("Logged In Successfully.")
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => __("OTP Expired")
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __("Invalid OTP")
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => __("Invalid OTP")
            ];
        }
        return $response;
    }

    /**
     * Update Otp
     *
     * @param $user
     * @return int
     */
    public function updateOtpStatus($user)
    {
        $loginAuthenticationModel = $this->loginAuthenticationOtp->create();
        $connection               = $loginAuthenticationModel->getResource()->getConnection();
        $tableName                = $connection->getTableName('login_authentication_otp');
        $whereConditions          = sprintf('user_id = %s AND status = %s',$user->getUserId(), self::OTP_VALID_STATUS);
        $updateData               = ["status" => self::OTP_INVALID_STATUS];
        return $connection->update($tableName, $updateData, $whereConditions);
    }

    /**
     * Get Otp store session data
     *
     * @return OtpSession
     */
    public function getOtpSessionModel(){
        return $this->otpSession;
    }
}
