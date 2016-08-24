<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * sometabe
 *
 * @ORM\Table(name="sometabe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\sometabeRepository")
 */
class sometabe
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
     * @var timestamp
     *
     * @ORM\Column(name="col2", type="timestamp")
     */
    private $col2;


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
     * @return sometabe
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

    /**
     * Set col2
     *
     * @param \timestamp $col2
     *
     * @return sometabe
     */
    public function setCol2(\timestamp $col2)
    {
        $this->col2 = $col2;

        return $this;
    }

    /**
     * Get col2
     *
     * @return \timestamp
     */
    public function getCol2()
    {
        return $this->col2;
    }
}

