<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * demotable
 *
 * @ORM\Table(name="demotable")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\demotableRepository")
 */
class demotable
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
     * @var int
     *
     * @ORM\Column(name="col2", type="int")
     */
    private $col2;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="col3", type="timestamp")
     */
    private $col3;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="col4", type="timestamp")
     */
    private $col4;


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
     * @return demotable
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
     * @param \int $col2
     *
     * @return demotable
     */
    public function setCol2(\int $col2)
    {
        $this->col2 = $col2;

        return $this;
    }

    /**
     * Get col2
     *
     * @return \int
     */
    public function getCol2()
    {
        return $this->col2;
    }

    /**
     * Set col3
     *
     * @param \timestamp $col3
     *
     * @return demotable
     */
    public function setCol3(\timestamp $col3)
    {
        $this->col3 = $col3;

        return $this;
    }

    /**
     * Get col3
     *
     * @return \timestamp
     */
    public function getCol3()
    {
        return $this->col3;
    }

    /**
     * Set col4
     *
     * @param \timestamp $col4
     *
     * @return demotable
     */
    public function setCol4(\timestamp $col4)
    {
        $this->col4 = $col4;

        return $this;
    }

    /**
     * Get col4
     *
     * @return \timestamp
     */
    public function getCol4()
    {
        return $this->col4;
    }
}

