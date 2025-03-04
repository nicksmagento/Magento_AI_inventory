<?php
namespace AI\InventoryOptimizer\Api\Data;

interface TrainingResultInterface
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
     * @return float
     */
    public function getAccuracy();
    
    /**
     * @param float $accuracy
     * @return $this
     */
    public function setAccuracy($accuracy);
    
    /**
     * @return string
     */
    public function getModelVersion();
    
    /**
     * @param string $modelVersion
     * @return $this
     */
    public function setModelVersion($modelVersion);
    
    /**
     * @return string
     */
    public function getTrainingTime();
    
    /**
     * @param string $trainingTime
     * @return $this
     */
    public function setTrainingTime($trainingTime);
} 