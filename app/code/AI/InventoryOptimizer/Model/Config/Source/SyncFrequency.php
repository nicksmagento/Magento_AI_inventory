<?php
namespace AI\InventoryOptimizer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SyncFrequency implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '15', 'label' => __('Every 15 minutes')],
            ['value' => '30', 'label' => __('Every 30 minutes')],
            ['value' => '60', 'label' => __('Every hour')],
            ['value' => '120', 'label' => __('Every 2 hours')],
            ['value' => '360', 'label' => __('Every 6 hours')],
            ['value' => '720', 'label' => __('Every 12 hours')],
            ['value' => '1440', 'label' => __('Daily')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            '15' => __('Every 15 minutes'),
            '30' => __('Every 30 minutes'),
            '60' => __('Every hour'),
            '120' => __('Every 2 hours'),
            '360' => __('Every 6 hours'),
            '720' => __('Every 12 hours'),
            '1440' => __('Daily')
        ];
    }
} 