<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
     * @Route("/dbs/newTable", name="dbs_new_table")
     */
    public function newTableAction(){
        return $this->render('dbs/newtable.html.twig');
    }
    
    /**
     * @Route("/dbs/createNewTable", name="dbs_create_new_table")
     * @Method("POST")
     */
    public function createNewTableAction(Request $request){

        $table_data = $request->request->all();

        if(empty($table_data['new_table_name'])){
            $this->addFlash('error','Please provide some table name.');
            return $this->render('dbs/newtable.html.twig');
        }

        // All column name must be unique. If duplicates send error
        if(count($table_data['field_name']) != count( array_unique($table_data['field_name']))){
            $this->addFlash('error','Each name must be non-empty and unique.');
            return $this->render('dbs/newtable.html.twig');
        }
        //print_r($table_data);
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();

        if($sm->tablesExist(array($table_data['new_table_name']))){
            $this->addFlash('error','This table already exists in connected database. See left sidebar');
            return $this->render('dbs/newtable.html.twig');
        }

        // if(!array_key_exists('field_length' ,$table_data)){
        //     $this->addFlash('error','Every Column must define the length');
        //     return $this->render('dbs/newtable.html.twig');
        // }

        /*
                $table_data['field_name']
                $table_data['field_type']
                $table_data['field_length']
                $table_data['field_null']
                $table_data['field_ai']
                $table_data['field_primary']
        */
        print_r($table_data);

        /*
        for ($i=0; $i < count($table_data['field_name']); $i++) { 
            if( isset($table_data['field_name'][$i]) && isset($table_data['field_type'][$i]) &&
                    isset($table_data['field_length'][$i]) && !empty($table_data['field_name'][$i]) && 
                    !empty($table_data['field_type'][$i]) && !empty($table_data['field_length'][$i]) 
                ){

            }   
            else{
                $this->addFlash('error','Field name, type and length are mandatory.');
                return $this->render('dbs/newtable.html.twig');
            }
        } */
        $queryString = '';
        for ($i=0; $i < count($table_data['field_name']); $i++) {
            if(isset($table_data['field_name'][$i]) && !empty($table_data['field_name'][$i])){
                    //email varchar(255) ,
                    if(isset($table_data['field_type'][$i])){

                    }
                    else{

                    }
                    if(isset($table_data['field_length'][$i])){

                    }
                    else{
                        
                    }

            }
            else{
                $this->addFlash('error','Field name is mandatory.');
                return $this->render('dbs/newtable.html.twig');
            }
        }   

        //if($table_data['field_primary'])
        return new jsonResponse(array("affd"=>"afsa"));
        //return $this->render('dbs/newtable.html.twig');
    }



    /**
     * @Route("/dbs/browseTable/{tablename}", name="dbs_browse_table")
     */
    public function browseTableAction($tablename){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();

        $tables = $sm->listTableNames();

        $tableColumns = $sm->listTableColumns($tablename);

        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }
        print_r($tableColumns);
        return $this->render('dbs/browseTable.html.twig',array(
                                    "tableColumns"  => $tableColumns
                                        ));
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
}
