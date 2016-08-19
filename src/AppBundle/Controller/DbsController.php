<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DbsController extends Controller{
     /**
     * @Route("/dbs/index", name="dbs_index")
     */
    public function indexAction(){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();
        $conn->close();
        return $this->render('/dbs/index.html.twig',array("tables"=>$tables));
    }

    /**
     * @Route("/dbs/getTablesInfo", name="dbs_get_table_info")
     */
    public function getTablesInfoAction(){
        $conn = $this->get('database_connection');
        $currentDBName = $conn->getDatabase();

        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();
        $dbname = $sm->listDatabases();

        $conn->close();
        return $this->render('dbs/sidebar_template_with_data.html.twig',
                                array("tables"=>$tables, 
                                      "databases"=>$dbname,
                                      "databasename"=> $currentDBName));
    }

   

    /**
     * @Route("/dbs/deleteTable/{tablename}", name="dbs_delete_table")
     */
    public function deleteTableAction($tablename){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();


        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        else{
            try {
                $sm->dropTable($tablename);
                $this->addFlash('success','Table named "'.$tablename.'"has been dropped successfully !!!');
            } catch (Exception $e) {
                $this->addFlash('error','There was some error in dropping table. Please try again !!!');
            }
        }
        $tables = $sm->listTableNames();

        $conn->close();
        return $this->render('dbs/index.html.twig',array("tables"=>$tables));
    }
    


    /**
     * @Route("/dbs/browseTable/{tablename}", name="dbs_browse_table")
     */
    public function browseTableAction($tablename){
        return $this->render('dbs/browseTable.html.twig',array("tables"=>$tables));
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

		$sql ="CREATE TABLE IF NOT EXISTS bluedart (
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
		$tables = $sm->listTableNames(); 
		
        $dbname = $sm->listDatabases();

        $dncurn = $conn->getDatabase();
		// $results = array();
		// while ($row = $stmt->fetch()) {
  		//  	array_push($results, $row);
		// }
		// $conn->close();
		//print_r($dbname);
        //echo $dncurn;
        var_dump($dncurn);

		die("fcuk");
	  	//return new JsonResponse(array('name' => $name));
    }	

}
