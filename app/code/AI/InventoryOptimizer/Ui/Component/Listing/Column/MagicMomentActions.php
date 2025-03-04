<?php
namespace AI\InventoryOptimizer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class MagicMomentActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                'ai_inventory/magicmoments/view',
                                ['id' => $item['entity_id']]
                            ),
                            'label' => __('View')
                        ],
                        'mark_read' => [
                            'href' => $this->urlBuilder->getUrl(
                                'ai_inventory/magicmoments/markread',
                                ['id' => $item['entity_id']]
                            ),
                            'label' => __('Mark as Read'),
                            'confirm' => [
                                'title' => __('Mark as Read'),
                                'message' => __('Are you sure you want to mark this magic moment as read?')
                            ],
                            'hidden' => (bool)$item['is_read']
                        ],
                        'mark_actioned' => [
                            'href' => $this->urlBuilder->getUrl(
                                'ai_inventory/magicmoments/markactioned',
                                ['id' => $item['entity_id']]
                            ),
                            'label' => __('Mark as Actioned'),
                            'confirm' => [
                                'title' => __('Mark as Actioned'),
                                'message' => __('Are you sure you want to mark this magic moment as actioned?')
                            ],
                            'hidden' => (bool)$item['is_actioned']
                        ]
                    ];
                }
            }
        }
        
        return $dataSource;
    }
} 