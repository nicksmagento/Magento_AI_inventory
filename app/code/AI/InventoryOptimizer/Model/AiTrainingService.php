<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\AiTrainingServiceInterface;
use AI\InventoryOptimizer\Api\Data\TrainingResultInterfaceFactory;
use AI\InventoryOptimizer\Api\Data\TrainingStatusInterfaceFactory;
use AI\InventoryOptimizer\Model\ResourceModel\AiModel\CollectionFactory as AiModelCollectionFactory;
use AI\InventoryOptimizer\Model\AiModelFactory;
use AI\InventoryOptimizer\Model\ResourceModel\AiModel as AiModelResource;
use AI\InventoryOptimizer\Model\Service\AiService;
use AI\InventoryOptimizer\Helper\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class AiTrainingService implements AiTrainingServiceInterface
{
    /**
     * @var AiService
     */
    private $aiService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var TrainingResultInterfaceFactory
     */
    private $trainingResultFactory;
    
    /**
     * @var TrainingStatusInterfaceFactory
     */
    private $trainingStatusFactory;
    
    /**
     * @var AiModelCollectionFactory
     */
    private $aiModelCollectionFactory;
    
    /**
     * @var AiModelFactory
     */
    private $aiModelFactory;
    
    /**
     * @var AiModelResource
     */
    private $aiModelResource;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param AiService $aiService
     * @param Config $config
     * @param TrainingResultInterfaceFactory $trainingResultFactory
     * @param TrainingStatusInterfaceFactory $trainingStatusFactory
     * @param AiModelCollectionFactory $aiModelCollectionFactory
     * @param AiModelFactory $aiModelFactory
     * @param AiModelResource $aiModelResource
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        AiService $aiService,
        Config $config,
        TrainingResultInterfaceFactory $trainingResultFactory,
        TrainingStatusInterfaceFactory $trainingStatusFactory,
        AiModelCollectionFactory $aiModelCollectionFactory,
        AiModelFactory $aiModelFactory,
        AiModelResource $aiModelResource,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->aiService = $aiService;
        $this->config = $config;
        $this->trainingResultFactory = $trainingResultFactory;
        $this->trainingStatusFactory = $trainingStatusFactory;
        $this->aiModelCollectionFactory = $aiModelCollectionFactory;
        $this->aiModelFactory = $aiModelFactory;
        $this->aiModelResource = $aiModelResource;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }
    
    /**
     * Train the forecasting model with historical data
     *
     * @param string $modelType
     * @param array $trainingData
     * @param array $parameters
     * @return \AI\InventoryOptimizer\Api\Data\TrainingResultInterface
     */
    public function trainModel($modelType, array $trainingData, array $parameters = [])
    {
        try {
            $startTime = microtime(true);
            
            $this->logger->info(sprintf(
                'Starting training for model type: %s with %d training samples',
                $modelType,
                count($trainingData)
            ));
            
            // Call AI service to train the model
            $trainingResponse = $this->aiService->trainModel($modelType, $trainingData, $parameters);
            
            // Calculate training time
            $trainingTime = microtime(true) - $startTime;
            
            // Save model metadata to database
            $aiModel = $this->getOrCreateModel($modelType);
            $aiModel->setModelVersion($trainingResponse['model_version']);
            $aiModel->setAccuracy($trainingResponse['accuracy']);
            $aiModel->setLastTrainingDate($this->dateTime->gmtDate());
            $aiModel->setStatus('active');
            $aiModel->setTrainingParameters(json_encode($parameters));
            $this->aiModelResource->save($aiModel);
            
            // Create and return training result
            $result = $this->trainingResultFactory->create();
            $result->setModelType($modelType);
            $result->setStatus('success');
            $result->setAccuracy($trainingResponse['accuracy']);
            $result->setModelVersion($trainingResponse['model_version']);
            $result->setTrainingTime(number_format($trainingTime, 2) . ' seconds');
            
            $this->logger->info(sprintf(
                'Training completed for model type: %s. Accuracy: %s, Version: %s, Time: %s seconds',
                $modelType,
                $trainingResponse['accuracy'],
                $trainingResponse['model_version'],
                number_format($trainingTime, 2)
            ));
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('AI Training Error: ' . $e->getMessage());
            
            $result = $this->trainingResultFactory->create();
            $result->setModelType($modelType);
            $result->setStatus('error');
            $result->setAccuracy(0);
            $result->setModelVersion('');
            $result->setTrainingTime('0 seconds');
            
            return $result;
        }
    }
    
    /**
     * Get training status for a specific model
     *
     * @param string $modelType
     * @return \AI\InventoryOptimizer\Api\Data\TrainingStatusInterface
     */
    public function getTrainingStatus($modelType)
    {
        try {
            $aiModel = $this->getOrCreateModel($modelType);
            
            $status = $this->trainingStatusFactory->create();
            $status->setModelType($modelType);
            $status->setStatus($aiModel->getStatus());
            $status->setLastTrainingDate($aiModel->getLastTrainingDate());
            $status->setNextScheduledTraining($aiModel->getNextScheduledTraining());
            $status->setCurrentAccuracy($aiModel->getAccuracy());
            
            return $status;
        } catch (\Exception $e) {
            $this->logger->error('Get Training Status Error: ' . $e->getMessage());
            
            $status = $this->trainingStatusFactory->create();
            $status->setModelType($modelType);
            $status->setStatus('unknown');
            $status->setLastTrainingDate('');
            $status->setNextScheduledTraining('');
            $status->setCurrentAccuracy(0);
            
            return $status;
        }
    }
    
    /**
     * Schedule training job for a specific model
     *
     * @param string $modelType
     * @param array $parameters
     * @return bool
     */
    public function scheduleTraining($modelType, array $parameters = [])
    {
        try {
            $aiModel = $this->getOrCreateModel($modelType);
            
            // Set next scheduled training date (default: 24 hours from now)
            $nextTrainingDate = $this->dateTime->gmtDate(
                null,
                $this->dateTime->gmtTimestamp() + 86400
            );
            
            if (isset($parameters['schedule_time'])) {
                $nextTrainingDate = $parameters['schedule_time'];
            }
            
            $aiModel->setNextScheduledTraining($nextTrainingDate);
            $aiModel->setTrainingParameters(json_encode($parameters));
            $aiModel->setStatus('scheduled');
            $this->aiModelResource->save($aiModel);
            
            $this->logger->info(sprintf(
                'Training scheduled for model type: %s at %s',
                $modelType,
                $nextTrainingDate
            ));
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Schedule Training Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get or create AI model entity
     *
     * @param string $modelType
     * @return \AI\InventoryOptimizer\Model\AiModel
     */
    private function getOrCreateModel($modelType)
    {
        $collection = $this->aiModelCollectionFactory->create();
        $collection->addFieldToFilter('model_type', $modelType);
        $model = $collection->getFirstItem();
        
        if (!$model->getId()) {
            $model = $this->aiModelFactory->create();
            $model->setModelType($modelType);
            $model->setStatus('new');
            $model->setAccuracy(0);
            $model->setModelVersion('0.0.1');
        }
        
        return $model;
    }
} 