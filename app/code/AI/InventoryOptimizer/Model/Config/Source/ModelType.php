<?php
namespace AI\InventoryOptimizer\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ModelType implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'lstm', 'label' => __('LSTM (Deep Learning)')],
            ['value' => 'prophet', 'label' => __('Prophet (Facebook)')],
            ['value' => 'xgboost', 'label' => __('XGBoost')],
            ['value' => 'arima', 'label' => __('ARIMA')],
            ['value' => 'ensemble', 'label' => __('Ensemble (Multiple Models)')]
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
            'lstm' => __('LSTM (Deep Learning)'),
            'prophet' => __('Prophet (Facebook)'),
            'xgboost' => __('XGBoost'),
            'arima' => __('ARIMA'),
            'ensemble' => __('Ensemble (Multiple Models)')
        ];
    }
} 