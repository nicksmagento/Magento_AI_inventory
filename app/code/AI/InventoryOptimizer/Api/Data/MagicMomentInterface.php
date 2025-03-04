<?php
namespace AI\InventoryOptimizer\Api\Data;

interface MagicMomentInterface
{
    /**
     * Constants for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const MOMENT_TYPE = 'moment_type';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const IMPACT_VALUE = 'impact_value';
    const IMPACT_TYPE = 'impact_type';
    const DATA = 'data';
    const IS_READ = 'is_read';
    const IS_ACTIONED = 'is_actioned';
    const CREATED_AT = 'created_at';

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
     * Get moment type
     *
     * @return string
     */
    public function getMomentType();

    /**
     * Set moment type
     *
     * @param string $momentType
     * @return $this
     */
    public function setMomentType($momentType);

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
     * @param float $value
     * @return $this
     */
    public function setImpactValue($value);

    /**
     * Get impact type
     *
     * @return string|null
     */
    public function getImpactType();

    /**
     * Set impact type
     *
     * @param string $type
     * @return $this
     */
    public function setImpactType($type);

    /**
     * Get data
     *
     * @return string|null
     */
    public function getData();

    /**
     * Set data
     *
     * @param string $data
     * @return $this
     */
    public function setData($data);

    /**
     * Is read
     *
     * @return bool
     */
    public function isRead();

    /**
     * Set is read
     *
     * @param bool $isRead
     * @return $this
     */
    public function setIsRead($isRead);

    /**
     * Is actioned
     *
     * @return bool
     */
    public function isActioned();

    /**
     * Set is actioned
     *
     * @param bool $isActioned
     * @return $this
     */
    public function setIsActioned($isActioned);

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
} 