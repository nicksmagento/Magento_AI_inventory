<?php

namespace AI\InventoryOptimizer\Model\Communication;

use Magento\Framework\Exception\LocalizedException;

class ExternalApiClient
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $httpClient;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $json;

    /**
     * @var \AI\InventoryOptimizer\Model\Config
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ExternalApiClient constructor.
     *
     * @param \Magento\Framework\HTTP\Client\Curl $httpClient
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \AI\InventoryOptimizer\Model\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $httpClient,
        \Magento\Framework\Json\Helper\Data $json,
        \AI\InventoryOptimizer\Model\Config $config,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Send data to external API with proper security measures
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    public function sendRequest($endpoint, array $data)
    {
        // Remove sensitive information
        $data = $this->removeSensitiveData($data);
        
        // Log request (without sensitive data)
        $this->logger->debug('Sending request to external API: ' . $endpoint);
        
        try {
            $response = $this->httpClient->post(
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->config->getApiKey(),
                        'Content-Type' => 'application/json'
                    ],
                    'body' => $this->json->serialize($data),
                    'timeout' => 30,
                    'connect_timeout' => 5
                ]
            );
            
            return $this->json->unserialize($response->getBody()->getContents());
        } catch (\Exception $e) {
            $this->logger->critical('API request failed: ' . $e->getMessage());
            throw new LocalizedException(__('External API communication failed. Please try again later.'));
        }
    }

    /**
     * Remove sensitive data before sending to external API
     *
     * @param array $data
     * @return array
     */
    private function removeSensitiveData(array $data)
    {
        // Remove customer PII
        if (isset($data['customer'])) {
            unset($data['customer']['email']);
            unset($data['customer']['phone']);
            // Keep only country and region from address
            if (isset($data['customer']['address'])) {
                $data['customer']['address'] = [
                    'country_id' => $data['customer']['address']['country_id'] ?? '',
                    'region' => $data['customer']['address']['region'] ?? ''
                ];
            }
        }
        
        // Remove payment information
        unset($data['payment']);
        
        return $data;
    }
} 