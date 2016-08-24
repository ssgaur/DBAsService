<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * vik
 *
 * @ORM\Table(name="vik")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\vikRepository")
 */
class vik
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
     * @ORM\Column(name="col1", type="varchar")
     */
    private $col1;


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
     * Set col1
     *
     * @param \varchar $col1
     *
     * @return vik
     */
    public function setCol1(\varchar $col1)
    {
        $this->col1 = $col1;

        return $this;
    }

    /**
     * Get col1
     *
     * @return \varchar
     */
    public function getCol1()
    {
        return $this->col1;
    }
}

