<?php
namespace AI\InventoryOptimizer\Api\Data;

interface SuccessTrackerInterface
{
    const ENTITY_ID = 'entity_id';
    const USER_ID = 'user_id';
    const SUCCESS_TYPE = 'success_type';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const IMPACT_VALUE = 'impact_value';
    const IMPACT_TYPE = 'impact_type';
    const TIME_SAVED = 'time_saved';
    const IS_HIGHLIGHTED = 'is_highlighted';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);
    
    /**
     * Get user ID
     *
     * @return int
     */
    public function getUserId();
    
    /**
     * Set user ID
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);
    
    /**
     * Get success type
     *
     * @return string
     */
    public function getSuccessType();
    
    /**
     * Set success type
     *
     * @param string $successType
     * @return $this
     */
    public function setSuccessType($successType);
    
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();
    
    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);
    
    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();
    
    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);
    
    /**
     * Get impact value
     *
     * @return float|null
     */
    public function getImpactValue();
    
    /**
     * Set impact value
     *
     * @param float $impactValue
     * @return $this
     */
    public function setImpactValue($impactValue);
    
    /**
     * Get impact type
     *
     * @return string|null
     */
    public function getImpactType();
    
    /**
     * Set impact type
     *
     * @param string $impactType
     * @return $this
     */
    public function setImpactType($impactType);
    
    /**
     * Get time saved
     *
     * @return float|null
     */
    public function getTimeSaved();
    
    /**
     * Set time saved
     *
     * @param float $timeSaved
     * @return $this
     */
    public function setTimeSaved($timeSaved);
    
    /**
     * Get is highlighted
     *
     * @return bool
     */
    public function getIsHighlighted();
    
    /**
     * Set is highlighted
     *
     * @param bool $isHighlighted
     * @return $this
     */
    public function setIsHighlighted($isHighlighted);
    
    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();
    
    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();
    
    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
} 