<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Utility\Utility;


class DbsController extends Controller{
    protected $connection;

    /**
     * @Route("/dbs/getTablesInfo", name="dbs_get_table_info")
    */
    public function getTablesInfoAction(){
        $conn = $this->getDatabaseConnection();
        $dbnames = $this->getAllDatabaseForCurrentUser();
        $currentDBName = $this->getCurrentDatabaseName();
        $tables = $this->getAllTablesInCurrentDatabase();
        return $this->render('dbs/sidebar_template_with_data.html.twig',
                                array("tables"=>$tables, 
                                      "databases"=>$dbnames,
                                      "databasename"=> $currentDBName));
    }

    private function getDatabaseConnection(){
        return $this->get('database_connection');
    }
    private function getSchemaManager(){
        return $this->getDatabaseConnection()->getSchemaManager();
    }
    private function getAllDatabaseForCurrentUser(){
        return $this->getDatabaseConnection()->getSchemaManager()->listDatabases();
    }
    private function getCurrentDatabaseName(){
        return $this->getDatabaseConnection()->getDatabase();
    }
    private function getAllTablesInCurrentDatabase(){
        return $this->getDatabaseConnection()->getSchemaManager()->listTableNames();
    }
    private function tableExistInCurrentDatabase($tablename){
        return $this->getDatabaseConnection()->getSchemaManager()
                        ->tablesExist(array($tablename));
    }

    /**
     * @Route("/dbs/index", name="dbs_index")
    */
    public function indexAction(){
        $tables = $this->getAllTablesInCurrentDatabase();
        return $this->render('/dbs/index.html.twig',array("tables"=>$tables));
    }
   
    /**
     * @Route("/dbs/deleteTable/{tablename}", name="dbs_delete_table")
    */
    public function deleteTableAction($tablename){
        $tableExist =  $this->tableExistInCurrentDatabase($tablename);
        if(!$tableExist){
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        else{
            try {
                $this->getSchemaManager()->dropTable($tablename);
                $this->addFlash('success','Table named "'.$tablename.'"has been dropped successfully !!!');
            } catch (\Doctrine\ORM\ORMException $e) {
                $this->addFlash('error','There was some error in dropping table. Please try again !!!');
            }
        }
        return $this->redirectToRoute('dbs_index');
    }

    /**
     * @Route("/dbs/newTable", name="dbs_new_table")
     */
    public function newTableAction(){
        return $this->render('dbs/newtable.html.twig');
    }
    

    private function arrayHasUniqueValues($array){
        $arrayLength = count($array);
        $newUniqueArray = array_unique($array);
        $uniqueArrayLength = count($newUniqueArray);
        if($arrayLength == $uniqueArrayLength)
            return true;
        else 
            return false;
    }



    /*
        CREATE TABLE IF NOT EXISTS user  (
            id int(11) AUTO_INCREMENT NOT NULL,
            firstname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            PRIMARY KEY(id) 
        );

        $table_data = 
            Array
            (
                [new_table_name] => sometabek
                [field_name] => Array
                    (
                        [0] => col1
                        [1] => col2
                        [2] => col3
                        [3] => col4
                    )

                [field_type] => Array
                    (
                        [0] => varchar
                        [1] => int
                        [2] => text
                        [3] => timestamp
                    )

                [field_length] => Array
                    (
                        [0] => 255
                        [1] => 
                        [2] => 
                        [3] => 
                    )

                [field_null] => Array
                    (
                        [1] => on
                        [2] => on
                    )

            )
        
    */
    private function createSQLforCreateTable($tableData){
        $tablename = $tableData['new_table_name'];
        $tempString ="CREATE TABLE IF NOT EXISTS ".$tablename." (id serial,";

        //print_r($tableData);
        $tempSqlArray = array();
        foreach ($tableData as $key => $fieldOptions) {
            if(is_array($fieldOptions) && !empty($fieldOptions)){
                foreach($fieldOptions as $columnNumber => $columnOption) {
                    
                    if(!isset($tempSqlArray[$columnNumber])){
                        $tempSqlArray[$columnNumber] = '';
                    }
                    
                    if($key == 'field_name'){
                        $tempSqlArray[$columnNumber] .= $columnOption;
                    }
                    if($key == 'field_type'){
                        $tempSqlArray[$columnNumber] .= ' '.$columnOption;
                    }
                    if($key == 'field_length'){
                        if(!empty($columnOption))
                            $tempSqlArray[$columnNumber] .= ' ('.$columnOption.')';
                        else
                            $tempSqlArray[$columnNumber] .= ' ';
                    }
                    if($key == 'field_null'){
                        if($fieldOptions)
                            $tempSqlArray[$columnNumber] .= 'NOT NULL';
                    }
                }
            }
        }
        $i = 0;
        $length = count($tempSqlArray);
        foreach ($tempSqlArray as $index => $columnString) {
            $tempString .= $columnString.', ';
            $i++;
        }
        $tempString .='PRIMARY KEY(id) )';
        return $tempString;
    }
    private function createStringForEntityGenerator($table_data){
        $tempString = '';
        for ($i=0; $i < count($table_data['field_name']); $i++) {
            if(isset($table_data['field_name'][$i]) && !empty($table_data['field_name'][$i])){
                $tempString .= $table_data['field_name'][$i].":".$table_data['field_type'][$i];
                if(isset($table_data['field_length'][$i]) && !empty($table_data['field_length'][$i])){
                    if($table_data['field_type'][$i] == 'varchar' || 
                        $table_data['field_type'][$i] == 'int'){
                        //$tempString .= '('.$table_data['field_length'][$i].')';
                    }
                }
            }
            $tempString .=" ";
        }
        return $tempString;
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

        $uniqueFields = $this->arrayHasUniqueValues($table_data['field_name']);
        if(!$uniqueFields){
            $this->addFlash('error','Each name must be non-empty and unique.');
            return $this->redirectToRoute('dbs_new_table');
        }

        $tablename = $table_data['new_table_name'];
        $tableExist =  $this->tableExistInCurrentDatabase($tablename);
        if($tableExist){
            $this->addFlash('error','This table already exists in connected database !!!');
            return $this->redirectToRoute('dbs_new_table');
        }
        $sql = $this->createSQLforCreateTable($table_data);
        try {
            $stmt = $this->getDatabaseConnection()->prepare($sql);
            $stmt->execute();
            $this->addFlash('success','You table has been created successfully.');

            $s = new Utility();
            $fieldStringForEntityGenerator = $this->createStringForEntityGenerator($table_data);
            $s->createTABLE($this->get('service_container'), $fieldStringForEntityGenerator , $tablename);

            return $this->redirectToRoute('dbs_index');
        } catch (\Exception $e) {
            $this->addFlash('error','Some error in creating table.');
        }
        return $this->render('dbs/newtable.html.twig');
    
    }



    /**
     * @Route("/dbs/browseTable/{tablename}", name="dbs_browse_table")
     */
    public function browseTableAction($tablename){
        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }
        $tblClm = $this->getSchemaManager()->listTableColumns($tablename);

        $queryBuilder = $this->getDatabaseConnection()->createQueryBuilder();
        $queryBuilder->select('*')->from($tablename);

        $tableData = array();
        try {
            $result = $queryBuilder->execute()->fetchAll();
            $tableData = $result;
        } catch (\Exception $e) {
            $this->addFlash('error','There was some error in browsing the table !!!');
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
        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }

        $tblClmFullInfo = $this->getSchemaManager()->listTableColumns($tablename);
        // $tableColumns = array();
        // foreach ($tblClm as $column => $property) {
        //     $tableColumns[$column] = array();
        //     $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
        //     $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
        //     $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
        // }

        $queryBuilder = $this->getDatabaseConnection()->createQueryBuilder();
        $queryBuilder->select('*')->from($tablename)->where('id = :id');
        $queryBuilder->setParameter(':id',(int)$id);
        $rowData = array();
        try {
            $result = $queryBuilder->execute()->fetchAll();
            $rowData = $result[0];
        } catch (Exception $e) {
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        return $this->render('dbs/editTableRow.html.twig',array(
                                    "tableColumns"  => $tblClmFullInfo,
                                    "tablename" => $tablename,
                                    "rowData" => $rowData
                                        ));
    }


    /**
     * @Route("/dbs/updateRowInTable", name="dbs_update_row_in_table")
     * @Method("POST")
     */
    public function updateRowInTableAction(Request $request){
        $table_data = $request->request->all();
        $tablename = $table_data['tablename'];

        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }


        $setArray = array();
        foreach ($table_data['row'] as $columnName => $value) {
            $columnName  = str_replace("'", "", $columnName);
            $setArray[$columnName] = ':';
        }

        $editId = $table_data['editId'];
        $queryBuilder = $this->getDatabaseConnection()->createQueryBuilder();
        $query = $queryBuilder->update($tablename)->where('id = '.$editId);

        $tableColumns = array();
        $i = 0;

        $tblClm = $this->getSchemaManager()->listTableColumns($tablename);
        array_shift($tblClm);
        foreach ($tblClm as $column => $property) {
                $tableColumns[$column] = array();
                $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
                $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
                $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
                $query->set($column,'?');
        }

        $i = 0;
        array_shift($table_data['row']);
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
            $this->addFlash('success','One row has been Updated successfully !!!');
            return $this->redirectToRoute('dbs_browse_table', array('tablename' => $tablename), 301);
        } catch (\Exception $e) {
            $this->addFlash('error','There was an error in updating. !!!');
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
        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }
        $queryBuilder = $this->getDatabaseConnection()->createQueryBuilder();
        $query = $queryBuilder->delete($tablename)->where('id = :id');
        $query->setParameter(':id', (int)$id);
        try {
            $result = $query->execute();
            $this->addFlash('success','One row has been deleted successfully !!!');
        } catch (\Exception $e) {
            $this->addFlash('error','This table does not have row with given id. !!!');
        }
        return $this->redirectToRoute('dbs_browse_table',array('tablename'=>$tablename),301);
    }



     /**
     * @Route("/dbs/insertTable/{tablename}", name="dbs_insert_table")
     */
    public function insertTableAction($tablename){
        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }
        $tblClm = $this->getSchemaManager()->listTableColumns($tablename);
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

        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }

        $tblClm = $this->getSchemaManager()->listTableColumns($tablename);
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
        $queryBuilder = $this->getDatabaseConnection()->createQueryBuilder();
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
        } catch (\Exception $e) {
            $this->addFlash('error','There was an error in creating !!!');
        }
        return $this->render('dbs/insertTable.html.twig',array(
                                    "tableColumns"  => $tblClm,
                                    "tablename" => $tablename
                                        ));
    }

}
