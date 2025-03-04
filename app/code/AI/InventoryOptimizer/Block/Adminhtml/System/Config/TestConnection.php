<?php
namespace AI\InventoryOptimizer\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnection extends Field
{
    /**
     * @var string
     */
    protected $_template = 'AI_InventoryOptimizer::system/config/test_connection.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for test button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('ai_inventory/integration/test');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
            'id' => 'test_connection_button',
            'label' => __('Test Connection'),
        ]);

        return $button->toHtml();
    }
    
    /**
     * Get integration code from element path
     *
     * @return string
     */
    public function getIntegrationCode()
    {
        $path = $this->getPath();
        $parts = explode('/', $path);
        
        // Path format: ai_inventory/integrations/[type]/[code]/test_connection
        if (count($parts) >= 4) {
            return $parts[3];
        }
        
        return '';
    }
    
    /**
     * Get integration type from element path
     *
     * @return string
     */
    public function getIntegrationType()
    {
        $path = $this->getPath();
        $parts = explode('/', $path);
        
        // Path format: ai_inventory/integrations/[type]/[code]/test_connection
        if (count($parts) >= 3) {
            return $parts[2];
        }
        
        return '';
    }
} 