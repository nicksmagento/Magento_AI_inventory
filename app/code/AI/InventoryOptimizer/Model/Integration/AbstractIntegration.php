<?php
namespace AI\InventoryOptimizer\Model\Integration;

use AI\InventoryOptimizer\Api\IntegrationInterface;
use AI\InventoryOptimizer\Model\Config;
use AI\InventoryOptimizer\Logger\Logger;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;

abstract class AbstractIntegration implements IntegrationInterface
{
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    
    /**
     * @var Json
     */
    protected $jsonSerializer;
    
    /**
     * @var string
     */
    protected $code;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @param Config $config
     * @param Logger $logger
     * @param ClientInterface $httpClient
     * @param Json $jsonSerializer
     * @param string $code
     * @param string $name
     */
    public function __construct(
        Config $config,
        Logger $logger,
        ClientInterface $httpClient,
        Json $jsonSerializer,
        $code = '',
        $name = ''
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->jsonSerializer = $jsonSerializer;
        $this->code = $code;
        $this->name = $name;
    }
    
    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->config->isIntegrationEnabled($this->getCode());
    }
    
    /**
     * Get API credentials
     *
     * @return array
     */
    protected function getCredentials()
    {
        return $this->config->getIntegrationCredentials($this->getCode());
    }
    
    /**
     * Get API endpoint
     *
     * @param string $path
     * @return string
     */
    protected function getApiEndpoint($path = '')
    {
        $baseUrl = $this->config->getIntegrationApiUrl($this->getCode());
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Make API request
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return array
     */
    protected function makeApiRequest($method, $endpoint, array $data = [], array $headers = [])
    {
        try {
            $url = $this->getApiEndpoint($endpoint);
            
            // Set default headers
            $defaultHeaders = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];
            
            // Merge with custom headers
            $headers = array_merge($defaultHeaders, $headers);
            
            // Set headers
            foreach ($headers as $name => $value) {
                $this->httpClient->addHeader($name, $value);
            }
            
            // Make request
            switch (strtoupper($method)) {
                case 'GET':
                    if (!empty($data)) {
                        $url .= '?' . http_build_query($data);
                    }
                    $this->httpClient->get($url);
                    break;
                    
                case 'POST':
                    $this->httpClient->post($url, $this->jsonSerializer->serialize($data));
                    break;
                    
                case 'PUT':
                    $this->httpClient->put($url, $this->jsonSerializer->serialize($data));
                    break;
                    
                case 'DELETE':
                    $this->httpClient->delete($url);
                    break;
                    
                default:
                    throw new \Exception("Unsupported HTTP method: {$method}");
            }
            
            // Get response
            $responseBody = $this->httpClient->getBody();
            $responseCode = $this->httpClient->getStatus();
            
            if ($responseCode >= 200 && $responseCode < 300) {
                return $this->jsonSerializer->unserialize($responseBody);
            } else {
                $this->logger->error("API Error ({$this->getCode()}): {$responseCode} - {$responseBody}");
                throw new \Exception("API Error: {$responseCode}");
            }
        } catch (\Exception $e) {
            $this->logger->error("Integration error ({$this->getCode()}): " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log integration activity
     *
     * @param string $action
     * @param string $message
     * @param array $data
     * @return void
     */
    protected function logActivity($action, $message, array $data = [])
    {
        $this->logger->info(
            sprintf(
                "Integration %s (%s): %s - %s",
                $this->getName(),
                $this->getCode(),
                $action,
                $message
            ),
            $data
        );
    }
} 