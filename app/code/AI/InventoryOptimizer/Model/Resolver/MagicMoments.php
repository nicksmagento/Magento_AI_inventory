<?php
namespace AI\InventoryOptimizer\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use AI\InventoryOptimizer\Api\MagicMomentRepositoryInterface;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;

class MagicMoments implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * @var MagicMomentRepositoryInterface
     */
    private $magicMomentRepository;
    
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param MagicMomentRepositoryInterface $magicMomentRepository
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MagicMomentRepositoryInterface $magicMomentRepository,
        Config $config,
        Logger $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->magicMomentRepository = $magicMomentRepository;
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$this->config->isModuleEnabled() || !$this->config->isMagicMomentsEnabled()) {
            return [
                'items' => [],
                'total_count' => 0,
                'page_info' => [
                    'page_size' => 0,
                    'current_page' => 1,
                    'total_pages' => 0
                ]
            ];
        }
        
        try {
            $searchCriteria = $this->searchCriteriaBuilder->build('ai_inventory_magic_moments', $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($args['pageSize']);
            
            $searchResult = $this->magicMomentRepository->getList($searchCriteria);
            
            return [
                'items' => $searchResult->getItems(),
                'total_count' => $searchResult->getTotalCount(),
                'page_info' => [
                    'page_size' => $searchCriteria->getPageSize(),
                    'current_page' => $searchCriteria->getCurrentPage(),
                    'total_pages' => ceil($searchResult->getTotalCount() / $searchCriteria->getPageSize())
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error('GraphQL error retrieving magic moments: ' . $e->getMessage());
            throw new \Exception(__('An error occurred while retrieving magic moments.'));
        }
    }
} 