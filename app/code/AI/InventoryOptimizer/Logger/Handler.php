<?php
namespace AI\InventoryOptimizer\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/ai_inventory_optimizer.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Log record with sensitive data removed
     *
     * @param array $record
     * @return bool
     */
    public function write(array $record): bool
    {
        // Sanitize log message to remove sensitive data
        $record['message'] = $this->sanitizeLogMessage($record['message']);
        
        return parent::write($record);
    }

    /**
     * Remove sensitive data from log messages
     *
     * @param string $message
     * @return string
     */
    private function sanitizeLogMessage($message)
    {
        // Remove API keys
        $message = preg_replace('/([\'"]?api_?key[\'"]?\s*[:=]\s*[\'"])[^\'"]+([\'"])/i', '$1[REDACTED]$2', $message);
        
        // Remove authentication tokens
        $message = preg_replace('/(Bearer\s+)[a-zA-Z0-9\._\-]+/i', '$1[REDACTED]', $message);
        
        // Remove email addresses
        $message = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL REDACTED]', $message);
        
        return $message;
    }
} 