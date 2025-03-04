<?php
namespace AI\InventoryOptimizer\Api\Data;

interface ChatResponseInterface
{
    /**
     * @return bool
     */
    public function getSuccess();
    
    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success);
    
    /**
     * @return string
     */
    public function getMessage();
    
    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message);
    
    /**
     * @return string
     */
    public function getIntent();
    
    /**
     * @param string $intent
     * @return $this
     */
    public function setIntent($intent);
    
    /**
     * @return mixed
     */
    public function getParameters();
    
    /**
     * @param mixed $parameters
     * @return $this
     */
    public function setParameters($parameters);
} 