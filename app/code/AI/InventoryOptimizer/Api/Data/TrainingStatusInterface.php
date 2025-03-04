<?php
namespace AI\InventoryOptimizer\Api\Data;

interface TrainingStatusInterface
{
    /**
     * @return string
     */
    public function getModelType();
    
    /**
     * @param string $modelType
     * @return $this
     */
    public function setModelType($modelType);
    
    /**
     * @return string
     */
    public function getStatus();
    
    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);
    
    /**
     * @return string
     */
    public function getLastTrainingDate();
    
    /**
     * @param string $date
     * @return $this
     */
    public function setLastTrainingDate($date);
    
    /**
     * @return string
     */
    public function getNextScheduledTraining();
    
    /**
     * @param string $date
     * @return $this
     */
    public function setNextScheduledTraining($date);
    
    /**
     * @return float
     */
    public function getCurrentAccuracy();
    
    /**
     * @param float $accuracy
     * @return $this
     */
    public function setCurrentAccuracy($accuracy);
} 