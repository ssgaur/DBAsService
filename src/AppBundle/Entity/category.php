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
     * @var varchar(255)
     *
     * @ORM\Column(name="name", type="varchar(255)", length=255)
     */
    private $name;

    /**
     * @var intcreated_at
     *
     * @ORM\Column(name="created_by", type="intcreated_at")
     */
    private $created_by;


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
     * @param \varchar(255) $name
     *
     * @return category
     */
    public function setName(\varchar(255) $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return \varchar(255)
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdBy
     *
     * @param \intcreated_at $createdBy
     *
     * @return category
     */
    public function setCreatedBy(\intcreated_at $createdBy)
    {
        $this->created_by = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \intcreated_at
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }
}

