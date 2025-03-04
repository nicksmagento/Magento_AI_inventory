<?php
namespace AI\InventoryOptimizer\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MomentType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'competitor_stockout', 'label' => __('Competitor Stockout')],
            ['value' => 'social_trend', 'label' => __('Social Media Trend')],
            ['value' => 'weather_issue', 'label' => __('Weather Issue')],
            ['value' => 'success_story', 'label' => __('Success Story')],
            ['value' => 'potential_stockout', 'label' => __('Potential Stockout')],
            ['value' => 'inventory_imbalance', 'label' => __('Inventory Imbalance')],
            ['value' => 'low_safety_stock', 'label' => __('Low Safety Stock')],
            ['value' => 'seasonal_product', 'label' => __('Seasonal Product')]
        ];
    }
} 