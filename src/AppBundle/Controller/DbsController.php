<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DbsController extends Controller{
    /**
     * @Route("/", name="dbs_home")
     */
    public function indexAction(Request $request){
        // replace this example code with whatever you need
        return $this->render('dbs/tablemanager.html.twig');
    }

    /**
     * @Route("/dbs/some", name="dbs_home")
     */
    public function someAction(Request $request){
        // replace this example code with whatever you need
        return $this->render('dbs/some.html.twig');
    }

    /**
     * @Route("/dbs/createTable", name="dbs_create_table")
     */
    public function createTableAction(Request $request){
    	//get connection
		$conn = $this->get('database_connection');
		//run a query
		$queryBuilder = $conn->createQueryBuilder();

		$sql ="CREATE TABLE IF NOT EXISTS ww (
					email varchar(255) ,
					id serial ,
					PRIMARY KEY(id)
				);" ;

		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$conn->close();
	  	die("worked");
    }	
  	
  	/**
     * @Route("/dbs/showTables", name="dbs_show_table")
     */
  	public function showTablesAction(Request $request){
    	//get connection
		$conn = $this->get('database_connection');
		$sm = $conn->getSchemaManager();
		$tables = $sm->listTables(); 
		

		// $results = array();
		// while ($row = $stmt->fetch()) {
  		//  	array_push($results, $row);
		// }
		// $conn->close();
		print_r($tables);

		die("fcuk");
	  	//return new JsonResponse(array('name' => $name));
    }	

}
