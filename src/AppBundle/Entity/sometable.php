<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * sometable
 *
 * @ORM\Table(name="sometable")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\sometableRepository")
 */
class sometable
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
     * @ORM\Column(name="category", type="varchar(255)", length=255)
     */
    private $category;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="creater_ar", type="timestamp")
     */
    private $creater_Ar;


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
     * Set category
     *
     * @param \varchar(255) $category
     *
     * @return sometable
     */
    public function setCategory(\varchar(255) $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \varchar(255)
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set createrAr
     *
     * @param \timestamp $createrAr
     *
     * @return sometable
     */
    public function setCreaterAr(\timestamp $createrAr)
    {
        $this->creater_Ar = $createrAr;

        return $this;
    }

    /**
     * Get createrAr
     *
     * @return \timestamp
     */
    public function getCreaterAr()
    {
        return $this->creater_Ar;
    }
}

