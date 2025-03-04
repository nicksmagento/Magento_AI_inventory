<?php
namespace AI\InventoryOptimizer\Controller\Adminhtml\MagicMoments;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check if user is allowed to access controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('AI_InventoryOptimizer::magic_moments');
    }

    /**
     * Magic Moments list page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Validate request parameters
        $params = $this->getRequest()->getParams();
        foreach ($params as $key => $value) {
            // Sanitize inputs
            $params[$key] = $this->sanitizeInput($value);
        }
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('AI_InventoryOptimizer::magic_moments');
        $resultPage->getConfig()->getTitle()->prepend(__('Magic Moments'));
        return $resultPage;
    }

    /**
     * Sanitize user input
     *
     * @param mixed $input
     * @return mixed
     */
    private function sanitizeInput($input)
    {
        if (is_string($input)) {
            // Remove potential XSS vectors
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        } elseif (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitizeInput($value);
            }
        }
        
        return $input;
    }
} 