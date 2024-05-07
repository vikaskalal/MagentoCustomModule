<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageViku\LoginAuthentication\Model;

use Magento\Framework\Session\SessionManager;

/**
 * @inheritDoc
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class OtpSession extends SessionManager
{
    const OTP_KEY = 'otp_verified';

    /**
     * @inheritDoc
     */
    public function setOtpVerified(): void
    {
        $this->storage->setData(self::OTP_KEY, true);
    }

    /**
     * @inheritDoc
     */
    public function getOtpVerified(): bool
    {
        return (bool) $this->storage->getData(self::OTP_KEY);
    }
}