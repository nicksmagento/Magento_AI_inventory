<?php

namespace AI\InventoryOptimizer\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

class Save extends Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ResultFactory $resultFactory
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ResultFactory $resultFactory,
        Validator $formKeyValidator,
        ManagerInterface $messageManager
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->resultFactory = $resultFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute action with CSRF validation
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Validate form key for CSRF protection
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        // Continue with save operation
        // ... existing code ...
    }
} 