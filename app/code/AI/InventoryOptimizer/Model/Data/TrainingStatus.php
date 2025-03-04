<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\TrainingStatusInterface;
use Magento\Framework\DataObject;

class TrainingStatus extends DataObject implements TrainingStatusInterface
{
    /**
     * @return string
     */
    public function getModelType()
    {
        return $this->getData('model_type');
    }
    
    /**
     * @param string $modelType
     * @return $this
     */
    public function setModelType($modelType)
    {
        return $this->setData('model_type', $modelType);
    }
    
    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData('status');
    }
    
    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }
    
    /**
     * @return string
     */
    public function getLastTrainingDate()
    {
        return $this->getData('last_training_date');
    }
    
    /**
     * @param string $date
     * @return $this
     */
    public function setLastTrainingDate($date)
    {
        return $this->setData('last_training_date', $date);
    }
    
    /**
     * @return string
     */
    public function getNextScheduledTraining()
    {
        return $this->getData('next_scheduled_training');
    }
    
    /**
     * @param string $date
     * @return $this
     */
    public function setNextScheduledTraining($date)
    {
        return $this->setData('next_scheduled_training', $date);
    }
    
    /**
     * @return float
     */
    public function getCurrentAccuracy()
    {
        return $this->getData('current_accuracy');
    }
    
    /**
     * @param float $accuracy
     * @return $this
     */
    public function setCurrentAccuracy($accuracy)
    {
        return $this->setData('current_accuracy', $accuracy);
    }
} 