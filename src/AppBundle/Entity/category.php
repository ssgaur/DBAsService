<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\categoryRepository")
 */
class category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var varchar
     *
     * @ORM\Column(name="name", type="varchar")
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="created_by", type="int")
     */
    private $created_by;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="created_at", type="timestamp")
     */
    private $created_at;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param \varchar $name
     *
     * @return category
     */
    public function setName(\varchar $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return \varchar
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdBy
     *
     * @param \int $createdBy
     *
     * @return category
     */
    public function setCreatedBy(\int $createdBy)
    {
        $this->created_by = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \int
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set createdAt
     *
     * @param \timestamp $createdAt
     *
     * @return category
     */
    public function setCreatedAt(\timestamp $createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \timestamp
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}

