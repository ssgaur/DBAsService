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
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();

        if($sm->tablesExist(array($table_data['new_table_name']))){
            $this->addFlash('error','This table already exists in connected database. See left sidebar');
            return $this->render('dbs/newtable.html.twig');
        }
        $sql ="CREATE TABLE IF NOT EXISTS ".$table_data['new_table_name']." (id serial,";

        for ($i=0; $i < count($table_data['field_name']); $i++) {
            $queryString = ' ';
            if(isset($table_data['field_name'][$i]) && !empty($table_data['field_name'][$i])){
                    //email varchar(255) ,
                    $queryString .= $table_data['field_name'][$i].' ';

                    if($table_data['field_type'][$i] == "text")
                        $queryString .= "varchar ";
                    else 
                        $queryString .=  $table_data['field_type'][$i];
                    if(isset($table_data['field_length'][$i]) && !empty($table_data['field_length'][$i])){
                        if($table_data['field_type'][$i] == 'varchar' || $table_data['field_type'][$i] == 'text')
                            $queryString .= '('.$table_data['field_length'][$i].')';
                    }
                    else{
                        switch ($table_data['field_type'][$i]) {
                            case 'varchar':
                                $queryString .= '(255)';
                                break;
                            case 'text':
                                $queryString .= '(2000)';
                                break;
                            default:
                                $queryString .= ' ';
                                break;
                        }
                    }
                    if(isset($table_data['field_null'][$i])){
                        $queryString .= ' NULL';
                    }
                    else{
                        $queryString .= ' NOT NULL';
                    }
            }
            else{
                $this->addFlash('error','Field name is mandatory.');
                return $this->render('dbs/newtable.html.twig');
            }
            $queryString .= ', ';
            $sql .= $queryString;
        }   
        $sql .= "PRIMARY KEY(id) );" ;
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $this->addFlash('success','You table has been created successfully.');
            return $this->redirectToRoute('dbs_index');
        } catch (Exception $e) {
            $this->addFlash('error','Some error in creating table.');
        }
        return $this->render('dbs/newtable.html.twig');
    }



    /**
     * @Route("/dbs/browseTable/{tablename}", name="dbs_browse_table")
     */
    public function browseTableAction($tablename){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();

        $tblClm = $sm->listTableColumns($tablename);
        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }

        $queryBuilder = $conn->createQueryBuilder();
        $queryBuilder->select('*')->from($tablename);

        $tableData = array();
        try {
            $result = $queryBuilder->execute()->fetchAll();
            $tableData = $result;
        } catch (Exception $e) {
            $this->addFlash('error','This table does not exists in connected database !!!');
        }

        return $this->render('dbs/browseTable.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tableData" => $tableData,
                                    "tablename" =>$tablename
                                        ));
    }

    /**
     * @Route("/dbs/editRowInTable/{tablename}/{id}", name="dbs_edit_row_in_table")
     */
    public function editRowInTableAction($tablename,$id){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();
        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }

        $tables = $sm->listTableNames();

        $tblClm = $sm->listTableColumns($tablename);
        $tableColumns = array();
        foreach ($tblClm as $column => $property) {
            $tableColumns[$column] = array();
            $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
            $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
            $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
        }

        $queryBuilder = $conn->createQueryBuilder();
        $queryBuilder->select('*')->from($tablename)->where('id = :id');
        $queryBuilder->setParameter(':id',(int)$id);
        $rowData = array();
        try {
            $result = $queryBuilder->execute()->fetchAll();
            $rowData = $result;
        } catch (Exception $e) {
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        return $this->render('dbs/editTableRow.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tablename" => $tablename,
                                    "rowData" => $rowData[0]
                                        ));
    }


    /**
     * @Route("/dbs/updateRowInTable", name="dbs_update_row_in_table")
     * @Method("POST")
     */
    public function updateRowInTableAction(Request $request){
        $table_data = $request->request->all();
        $tablename = $table_data['tablename'];
        $editId = $table_data['editId'];
        $conn = $this->get('database_connection');
        $queryBuilder = $conn->createQueryBuilder();
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();
        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }

        $tblClm = $sm->listTableColumns($tablename);

        $setArray = array();
        foreach ($table_data['row'] as $columnName => $value) {
            $columnName  = str_replace("'", "", $columnName);
            $setArray[$columnName] = '?';
        }
        $query = $queryBuilder->update($tablename)->where('id = '.$editId);

        $tableColumns = array();
        $i = 0;
        foreach ($tblClm as $column => $property) {
            if($i != 0){
                $tableColumns[$column] = array();
                $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
                $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
                $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
                $query->set($column,'?');
            }
            $i++;
        }

        $i = 0;
        foreach ($table_data['row'] as $columnName => $value) {
            if($i != 0){
                $columnName  = str_replace("'", "", $columnName);
                if($tableColumns[$columnName]['type'] == 'Integer' )
                    $query->setParameter($i, (int)$value);
                else
                    $query->setParameter($i, $value);
            }
            $i++;
        }
        //$query->setParameter($i,(int)$editId);

        try {
            $result = $query->execute();
            $this->addFlash('success','One row has been inserted successfully !!!');
        } catch (Exception $e) {
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        return $this->render('dbs/insertTable.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tablename" => $tablename
                                        ));

    }

    /**
     * @Route("/dbs/deleteRowInTable/{tablename}/{id}", name="dbs_delete_row_in_table")
     */
    public function deleteRowInTableAction($tablename,$id){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();

        $tblClm = $sm->listTableColumns($tablename);
        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }


        $queryBuilder = $conn->createQueryBuilder();
        $query = $queryBuilder->delete($tablename)->where('id = :id');
        $query->setParameter(':id', (int)$id);

        try {
            $result = $query->execute();
            $this->addFlash('success','One row has been deleted successfully !!!');
        } catch (Exception $e) {
            $this->addFlash('error','This table does not have row with given id. !!!');
        }

        $queryBuilder = $conn->createQueryBuilder();
        $queryBuilder->select('*')->from($tablename);

        $tableData = array();
        try {
            $result = $queryBuilder->execute()->fetchAll();
            $tableData = $result;
        } catch (Exception $e) {
            $this->addFlash('error','This table does not exists in connected database !!!');
        }

        return $this->render('dbs/browseTable.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tableData" => $tableData,
                                    "tablename" => $tablename
                                        ));
    }



     /**
     * @Route("/dbs/insertTable/{tablename}", name="dbs_insert_table")
     */
    public function insertTableAction($tablename){
        $conn = $this->get('database_connection');
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();
        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }

        $tables = $sm->listTableNames();

        $tblClm = $sm->listTableColumns($tablename);
        $tableColumns = array();
        foreach ($tblClm as $column => $property) {
            $tableColumns[$column] = array();
            $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
            $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
            $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
        }
        return $this->render('dbs/insertTable.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tablename" => $tablename
                                        ));
    }



     /**
     * @Route("/dbs/newInsertTable", name="dbs_insert_into_table")
     * @Method("POST")
     */
    public function newInsertTableAction(Request $request){
        $table_data = $request->request->all();
        $tablename = $table_data['tablename'];
        $conn = $this->get('database_connection');
        $queryBuilder = $conn->createQueryBuilder();
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTableNames();
        if(!$sm->tablesExist(array($tablename))){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->render('dbs/index.html.twig',array("tables"=>$tables));
        }

        $tblClm = $sm->listTableColumns($tablename);
        $tableColumns = array();
        foreach ($tblClm as $column => $property) {
            $tableColumns[$column] = array();
            $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
            $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
            $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
        }

        $setArray = array();
        foreach ($table_data['row'] as $columnName => $value) {
            $columnName  = str_replace("'", "", $columnName);
            $setArray[$columnName] = '?';
        }
        $query = $queryBuilder->insert($tablename)->values($setArray);

        $i = 0;
        foreach ($table_data['row'] as $columnName => $value) {
            $columnName  = str_replace("'", "", $columnName);
            if($tableColumns[$columnName]['type'] == 'Integer' )
                $query->setParameter($i, (int)$value);
            else
                $query->setParameter($i, $value);
            $i++;
        }
        try {
            $result = $query->execute();
            $this->addFlash('success','One row has been inserted successfully !!!');
        } catch (Exception $e) {
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        return $this->render('dbs/insertTable.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tablename" => $tablename
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
