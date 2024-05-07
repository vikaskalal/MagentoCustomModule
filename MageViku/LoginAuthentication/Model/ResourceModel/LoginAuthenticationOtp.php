<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageViku\LoginAuthentication\Model\ResourceModel;

/**
 * Class LoginAuthenticationOtp
 * @package MageViku\LoginAuthentication\Model\ResourceModel
 */
class LoginAuthenticationOtp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('login_authentication_otp', 'entity_id');
    }
}