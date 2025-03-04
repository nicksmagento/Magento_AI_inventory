<?php
namespace AI\InventoryOptimizer\Model;

use AI\InventoryOptimizer\Api\Data\MagicMomentInterface;
use Magento\Framework\Model\AbstractModel;

class MagicMoment extends AbstractModel implements MagicMomentInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AI\InventoryOptimizer\Model\ResourceModel\MagicMoment::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get moment type
     *
     * @return string
     */
    public function getMomentType()
    {
        return $this->getData(self::MOMENT_TYPE);
    }

    /**
     * Set moment type
     *
     * @param string $momentType
     * @return $this
     */
    public function setMomentType($momentType)
    {
        return $this->setData(self::MOMENT_TYPE, $momentType);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get impact value
     *
     * @return float|null
     */
    public function getImpactValue()
    {
        return $this->getData(self::IMPACT_VALUE);
    }

    /**
     * Set impact value
     *
     * @param float $value
     * @return $this
     */
    public function setImpactValue($value)
    {
        return $this->setData(self::IMPACT_VALUE, $value);
    }

    /**
     * Get impact type
     *
     * @return string|null
     */
    public function getImpactType()
    {
        return $this->getData(self::IMPACT_TYPE);
    }

    /**
     * Set impact type
     *
     * @param string $type
     * @return $this
     */
    public function setImpactType($type)
    {
        return $this->setData(self::IMPACT_TYPE, $type);
    }

    /**
     * Get data
     *
     * @return string|null
     */
    public function getData()
    {
        return $this->_getData(self::DATA);
    }

    /**
     * Set data
     *
     * @param string $data
     * @return $this
     */
    public function setData($data)
    {
        return $this->setData(self::DATA, $data);
    }

    /**
     * Is read
     *
     * @return bool
     */
    public function isRead()
    {
        return (bool)$this->getData(self::IS_READ);
    }

    /**
     * Set is read
     *
     * @param bool $isRead
     * @return $this
     */
    public function setIsRead($isRead)
    {
        return $this->setData(self::IS_READ, $isRead);
    }

    /**
     * Is actioned
     *
     * @return bool
     */
    public function isActioned()
    {
        return (bool)$this->getData(self::IS_ACTIONED);
    }

    /**
     * Set is actioned
     *
     * @param bool $isActioned
     * @return $this
     */
    public function setIsActioned($isActioned)
    {
        return $this->setData(self::IS_ACTIONED, $isActioned);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
} 