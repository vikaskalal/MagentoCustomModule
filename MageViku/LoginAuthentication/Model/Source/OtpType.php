<?php
/**
 * Copyright © MageViku, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageViku\LoginAuthentication\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Otptype
 * @package Cinovic\Otplogin\Model\Source
 */
class Otptype implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'number', 'label' => __('Number')],
            ['value' => 'alphabets', 'label' => __('Alphabets')],
            ['value' => 'alphanumeric', 'label' => __('Alphanumeric')]
        ];
    }
}