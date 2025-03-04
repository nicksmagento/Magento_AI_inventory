<?php
namespace AI\InventoryOptimizer\Controller\Adminhtml\Chat;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use AI\InventoryOptimizer\Model\ChatCopilotAgent;
use Psr\Log\LoggerInterface;

class SendCommand extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var ChatCopilotAgent
     */
    protected $chatCopilotAgent;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ChatCopilotAgent $chatCopilotAgent
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ChatCopilotAgent $chatCopilotAgent,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->chatCopilotAgent = $chatCopilotAgent;
        $this->logger = $logger;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('AI_InventoryOptimizer::chat');
    }

    /**
     * Process chat command
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        
        try {
            $command = $this->getRequest()->getParam('command');
            
            if (!$command) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('No command provided')
                ]);
            }
            
            $result = $this->chatCopilotAgent->processCommand($command);
            
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $this->logger->error('AI Chat Copilot Error: ' . $e->getMessage());
            
            return $resultJson->setData([
                'success' => false,
                'message' => __('An error occurred: %1', $e->getMessage())
            ]);
        }
    }
} 