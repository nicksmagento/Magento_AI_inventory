<?php
namespace AI\InventoryOptimizer\Api;

interface AiTrainingServiceInterface
{
    /**
     * Train the forecasting model with historical data
     *
     * @param string $modelType
     * @param array $trainingData
     * @param array $parameters
     * @return \AI\InventoryOptimizer\Api\Data\TrainingResultInterface
     */
    public function trainModel($modelType, array $trainingData, array $parameters = []);
    
    /**
     * Get training status for a specific model
     *
     * @param string $modelType
     * @return \AI\InventoryOptimizer\Api\Data\TrainingStatusInterface
     */
    public function getTrainingStatus($modelType);
    
    /**
     * Schedule training job for a specific model
     *
     * @param string $modelType
     * @param array $parameters
     * @return bool
     */
    public function scheduleTraining($modelType, array $parameters = []);
} 