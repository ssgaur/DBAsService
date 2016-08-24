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
     * @var varchar
     *
     * @ORM\Column(name="col2", type="varchar")
     */
    private $col2;

    /**
     * @var varchar
     *
     * @ORM\Column(name="col3", type="varchar")
     */
    private $col3;


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
     * @param \varchar $col2
     *
     * @return sometabe
     */
    public function setCol2(\varchar $col2)
    {
        $this->col2 = $col2;

        return $this;
    }

    /**
     * Get col2
     *
     * @return \varchar
     */
    public function getCol2()
    {
        return $this->col2;
    }

    /**
     * Set col3
     *
     * @param \varchar $col3
     *
     * @return sometabe
     */
    public function setCol3(\varchar $col3)
    {
        $this->col3 = $col3;

        return $this;
    }

    /**
     * Get col3
     *
     * @return \varchar
     */
    public function getCol3()
    {
        return $this->col3;
    }
}

