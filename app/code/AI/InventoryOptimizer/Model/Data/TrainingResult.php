<?php
namespace AI\InventoryOptimizer\Model\Data;

use AI\InventoryOptimizer\Api\Data\TrainingResultInterface;
use Magento\Framework\DataObject;

class TrainingResult extends DataObject implements TrainingResultInterface
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
     * @return float
     */
    public function getAccuracy()
    {
        return $this->getData('accuracy');
    }
    
    /**
     * @param float $accuracy
     * @return $this
     */
    public function setAccuracy($accuracy)
    {
        return $this->setData('accuracy', $accuracy);
    }
    
    /**
     * @return string
     */
    public function getModelVersion()
    {
        return $this->getData('model_version');
    }
    
    /**
     * @param string $modelVersion
     * @return $this
     */
    public function setModelVersion($modelVersion)
    {
        return $this->setData('model_version', $modelVersion);
    }
    
    /**
     * @return string
     */
    public function getTrainingTime()
    {
        return $this->getData('training_time');
    }
    
    /**
     * @param string $trainingTime
     * @return $this
     */
    public function setTrainingTime($trainingTime)
    {
        return $this->setData('training_time', $trainingTime);
    }
} 