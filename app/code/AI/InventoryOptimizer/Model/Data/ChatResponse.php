<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\ChatResponseInterface;
use Magento\Framework\DataObject;

class ChatResponse extends DataObject implements ChatResponseInterface
{
    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->getData('success');
    }
    
    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        return $this->setData('success', $success);
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData('message');
    }
    
    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return $this->setData('message', $message);
    }
    
    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->getData('intent');
    }
    
    /**
     * @param string $intent
     * @return $this
     */
    public function setIntent($intent)
    {
        return $this->setData('intent', $intent);
    }
    
    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->getData('parameters');
    }
    
    /**
     * @param mixed $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        return $this->setData('parameters', $parameters);
    }
} 