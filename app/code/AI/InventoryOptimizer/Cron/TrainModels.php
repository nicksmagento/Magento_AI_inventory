<?php
namespace AI\InventoryOptimizer\Cron;

use AI\InventoryOptimizer\Api\AiTrainingServiceInterface;
use AI\InventoryOptimizer\Model\ResourceModel\AiModel\CollectionFactory;
use AI\InventoryOptimizer\Model\Service\DataPreparationService;
use AI\InventoryOptimizer\Helper\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class TrainModels
{
    /**
     * @var AiTrainingServiceInterface
     */
    private $aiTrainingService;
    
    /**
     * @var CollectionFactory
     */
    private $aiModelCollectionFactory;
    
    /**
     * @var DataPreparationService
     */
    private $dataPreparationService;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var DateTime
     */
    private $dateTime;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param AiTrainingServiceInterface $aiTrainingService
     * @param CollectionFactory $aiModelCollectionFactory
     * @param DataPreparationService $dataPreparationService
     * @param Config $config
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        AiTrainingServiceInterface $aiTrainingService,
        CollectionFactory $aiModelCollectionFactory,
        DataPreparationService $dataPreparationService,
        Config $config,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->aiTrainingService = $aiTrainingService;
        $this->aiModelCollectionFactory = $aiModelCollectionFactory;
        $this->dataPreparationService = $dataPreparationService;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }
    
    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }
        
        $this->logger->info('Starting scheduled AI model training');
        
        $now = $this->dateTime->gmtDate();
        
        // Get models scheduled for training
        $collection = $this->aiModelCollectionFactory->create();
        $collection->addFieldToFilter('status', 'scheduled');
        $collection->addFieldToFilter('next_scheduled_training', ['lteq' => $now]);
        
        if ($collection->getSize() === 0) {
            $this->logger->info('No models scheduled for training at this time');
            return;
        }
        
        foreach ($collection as $model) {
            try {
                $modelType = $model->getModelType();
                $parameters = json_decode($model->getTrainingParameters(), true) ?: [];
                
                $this->logger->info(sprintf('Processing scheduled training for model: %s', $modelType));
                
                // Prepare training data based on model type
                $trainingData = $this->dataPreparationService->prepareTrainingData($modelType);
                
                // Train the model
                $result = $this->aiTrainingService->trainModel($modelType, $trainingData, $parameters);
                
                $this->logger->info(sprintf(
                    'Scheduled training completed for model: %s. Status: %s, Accuracy: %s',
                    $modelType,
                    $result->getStatus(),
                    $result->getAccuracy()
                ));
                
                // Schedule next training if auto-schedule is enabled
                if ($this->config->isAutoScheduleEnabled()) {
                    $this->aiTrainingService->scheduleTraining($modelType, $parameters);
                }
            } catch (\Exception $e) {
                $this->logger->error(sprintf(
                    'Error during scheduled training for model %s: %s',
                    $model->getModelType(),
                    $e->getMessage()
                ));
            }
        }
    }
} 