<?php
namespace AppBundle\Toolbox;
use Doctrine\DBAL\Connection;

class StringToolbox
{
    /**
    *
    * @var Connection
    */
    private $connection;

    public function __construct(Connection $dbalConnection)  {
        $this->connection = $dbalConnection;    
    }

    public function lookupSomething($foo)
    {

    $sql = "SELECT bar FROM bar_list WHERE foo = :foo";
    $stmt = $this->connection->prepare($sql);
    $stmt->bindValue("foo", $foo);
    $stmt->execute();


    return $bar;
    }


}
