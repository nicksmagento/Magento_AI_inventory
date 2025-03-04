<?php
namespace AI\InventoryOptimizer\Model\Api;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;

class RateLimiter
{
    /**
     * @var CacheInterface
     */
    private $cache;
    
    /**
     * @param CacheInterface $cache
     */
    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
    }
    
    /**
     * Check if request is within rate limits
     *
     * @param string $identifier User or IP identifier
     * @param string $endpoint API endpoint
     * @param int $limit Maximum requests per period
     * @param int $period Period in seconds
     * @return bool
     * @throws LocalizedException
     */
    public function checkLimit($identifier, $endpoint, $limit = 100, $period = 3600)
    {
        $cacheKey = 'rate_limit_' . md5($identifier . '_' . $endpoint);
        $currentData = $this->cache->load($cacheKey);
        
        if ($currentData) {
            $data = json_decode($currentData, true);
            $count = $data['count'];
            $timestamp = $data['timestamp'];
            
            // Reset counter if period has passed
            if (time() - $timestamp > $period) {
                $count = 1;
                $timestamp = time();
            } else {
                $count++;
            }
            
            // Check if limit exceeded
            if ($count > $limit) {
                throw new LocalizedException(
                    __('Rate limit exceeded. Please try again later.')
                );
            }
        } else {
            $count = 1;
            $timestamp = time();
        }
        
        // Update cache
        $this->cache->save(
            json_encode(['count' => $count, 'timestamp' => $timestamp]),
            $cacheKey,
            [],
            $period
        );
        
        return true;
    }
} 