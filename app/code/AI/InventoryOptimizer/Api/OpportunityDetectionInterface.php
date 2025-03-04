<?php
namespace AI\InventoryOptimizer\Api;

interface OpportunityDetectionInterface
{
    /**
     * Detect all opportunities
     *
     * @return bool
     */
    public function detectAllOpportunities();
    
    /**
     * Detect competitor stockouts
     *
     * @return bool
     */
    public function detectCompetitorStockouts();
    
    /**
     * Detect social trends
     *
     * @return bool
     */
    public function detectSocialTrends();
    
    /**
     * Detect weather issues
     *
     * @return bool
     */
    public function detectWeatherIssues();
    
    /**
     * Generate success stories
     *
     * @return bool
     */
    public function generateSuccessStories();
} 