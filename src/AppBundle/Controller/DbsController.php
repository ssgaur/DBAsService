<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Utility\Utility;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Doctrine\DBAL\Connection;

class DbsController extends Controller{
  

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

    /**
     * @Route("/dbs/index", name="dbs_index")
    */
    public function indexAction(){
        $tables = $this->getAllTablesInCurrentDatabase();
        return $this->render('/dbs/index.html.twig',array("tables"=>$tables));
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
        $tablename = $table_data['new_table_name'];
        
        if(empty($table_data['new_table_name'])){
            $this->addFlash('error','Please provide some table name.');
            return $this->render('dbs/newtable.html.twig');
        }

        $uniqueFields = $this->fieldsHasUniqueName($table_data);
        if(!$uniqueFields){
            $this->addFlash('error','Field names must be unique.');
            $tableData = $this->newTableFormErrorPrevData($table_data);
            // AsNewTable  is sending because it will use the edit table tempplate making usabale
            return $this->render('dbs/editTableStructure.html.twig',array(
                                                        "tablename"=> $tablename,
                                                        "tableColumns" => $tableData,
                                                        "columnCount" =>  count($tableData),
                                                        "AsNewTable" =>true
                                                    ));
        }
        
        $tableExist =  $this->tableExistInCurrentDatabase($tablename);
        if($tableExist){
            $this->addFlash('error','This table already exists in connected database !!!');
            return $this->redirectToRoute('dbs_new_table');
        }
        
        try {
            $sql = $this->createSQLforCreateTable($table_data);
            $stmt = $this->getDatabaseConnection()->prepare($sql);
            $stmt->execute();
            $this->addFlash('success','You table has been created successfully.');

            // $s = new Utility();
            // $fieldStringForEntityGenerator = $this->createStringForEntityGenerator($table_data);
            // $s->createTABLE($this->get('service_container'), $fieldStringForEntityGenerator , $tablename);

            return $this->redirectToRoute('dbs_index');
        } 
        catch (\Exception $e) {
            $this->addFlash('error','Some error in creating table.');
            $tableData = $this->newTableFormErrorPrevData($table_data);

            // AsNewTable  is sending because it will use the edit table tempplate making usabale
            return $this->render('dbs/editTableStructure.html.twig',array(
                                                        "tablename"=> $tablename,
                                                        "tableColumns" => $tableData,
                                                        "columnCount" =>  count($tableData),
                                                        "AsNewTable" =>true
                                                    ));
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
     * @Route("/dbs/edittable/{tablename}", name="dbs_edit_table_structure")
     */
    public function editTableAction($tablename){
        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }
        if($tablename == 'users' || $tablename == 'todo'){
            $this->addFlash('error','No One can not alter me. I am heart of this application. If I am altered, U will cry');
            return $this->redirectToRoute('dbs_index');
        }
        $tableColumns = $this->getTableColumnsAsArray($tablename);
        array_shift($tableColumns);
        return $this->render('dbs/editTableStructure.html.twig',array(
                                                        "tablename"=> $tablename,
                                                        "tableColumns" => $tableColumns,
                                                        "columnCount" =>  count($tableColumns)
                                                    ));
    }

    /**
     * @Route("/dbs/altertable", name="dbs_alter_table")
     * @Method("POST")
     */
    public function alterTableAction(Request $request){
        $table_data = $request->request->all();
        if(empty($table_data['new_table_name'])){
            $this->addFlash('error','Please provide some table name.');
            return $this->render('dbs/newtable.html.twig');
        }

        $uniqueFields = $this->fieldsHasUniqueName($table_data);
        if(!$uniqueFields){
            $this->addFlash('error','Field name must be unique.');
        }

        $tablename = $table_data['new_table_name'];
        $oldTableName = $table_data['tablename'];

        if($tablename == 'users' || $tablename == 'todo' 
                    || $oldTableName == 'users' || $oldTableName == 'todo'){
            $this->addFlash('error','No One can not alter me. I am heart of this application. If I am altered, U will cry');
            return $this->redirectToRoute('dbs_index');
        }
        try {
            $this->getSchemaManager()->dropTable($tablename);
            
            $sql = $this->createSQLforCreateTable($table_data);
            $stmt = $this->getDatabaseConnection()->prepare($sql);
            $stmt->execute();
            $this->addFlash('success','You table has been Updated successfully.');
                $fs = new Filesystem();
                try {

                    $fs->remove('../src/AppBundle/Entity/'.$oldTableName.'.php');
                    $fs->remove('../src/AppBundle/Repository/'.$oldTableName.'Repository'.'.php');
               
                } catch (IOExceptionInterface $e) {
                    echo "An error occurred while creating your directory at ".$e->getPath();
                }

            // $s = new Utility();
            // $fieldStringForEntityGenerator = $this->createStringForEntityGenerator($table_data);
            // $s->createTABLE($this->get('service_container'), $fieldStringForEntityGenerator , $tablename);

            return $this->redirectToRoute('dbs_index');
        } catch (\Exception $e) {
            $this->addFlash('error','Some error in updating table structure.');
            $tableData = $this->newTableFormErrorPrevData($table_data);

            // AsNewTable  is sending because it will use the edit table tempplate making usabale
            return $this->render('dbs/editTableStructure.html.twig',array(
                                                        "tablename"=> $tablename,
                                                        "tableColumns" => $tableData,
                                                        "columnCount" =>  count($tableData),
                                                        "AsNewTable" =>true
                                                    ));
        }
        return $this->redirectToRoute('dbs_edit_table_structure', array('tablename' => $oldTableName), 301);
    }

    /**
     * @Route("/dbs/deleteTable/{tablename}", name="dbs_delete_table")
    */
    public function deleteTableAction($tablename){
        $tableExist =  $this->tableExistInCurrentDatabase($tablename);
        if(!$tableExist){
            $this->addFlash('error','This table does not exists in connected database !!!');
        }
        if($tablename == 'users' || $tablename == 'todo'){
            $this->addFlash('error','No One can not drop me. I am heart of this application. If I die, U die');
            return $this->redirectToRoute('dbs_index');
        }
        else{
            try {
                $this->getSchemaManager()->dropTable($tablename);
                $this->addFlash('success','Table named "'.$tablename.'"has been dropped successfully !!!');

                $fs = new Filesystem();
                try {
                    $fs->remove('../src/AppBundle/Entity/'.$tablename.'.php');
                    $fs->remove('../src/AppBundle/Repository/'.$tablename.'Repository'.'.php');
                   // $fs->remove('../src/AppBundle/Entity/lola.php');
               
                } catch (IOExceptionInterface $e) {
                    echo "An error occurred while creating your directory at ".$e->getPath();
                }

            } catch (\Exception $e) {
                $this->addFlash('error','There was some error in dropping table. Please try again !!!');
            }
        }
        

        return $this->redirectToRoute('dbs_index');
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

    /**
     * @Route("/dbs/editRowInTable/{tablename}/{id}", name="dbs_edit_row_in_table")
     */
    public function editRowInTableAction($tablename,$id){
        if(!$this->tableExistInCurrentDatabase($tablename)){
            $this->addFlash('error','This table does not exists in connected database !!!');
            return $this->redirectToRoute('dbs_index');
        }

        $tblClmFullInfo = $this->getSchemaManager()->listTableColumns($tablename);
        $queryBuilder = $this->getDatabaseConnection()->createQueryBuilder();
        $queryBuilder->select('*')->from($tablename)->where('id = :id');
        $queryBuilder->setParameter(':id',(int)$id);
        $rowData = array();
        try {
            $result = $queryBuilder->execute()->fetchAll();
            $rowData = $result[0];
        } catch (\Exception $e) {
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



    /*
        Private function working as helping function
    */

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
    private function createSQLforCreateTable($table_data){
        $tablename = $table_data['new_table_name'];
        $tempString ="CREATE TABLE IF NOT EXISTS ".$tablename." (id serial,";

        $sqlString = '';
        $properFieldArray = $this->makeProperArrayFromUserInputColumns($table_data);
        foreach ($properFieldArray as $key => $field) {
            foreach ($field as $property => $value) {
                if($property == 'columnName'){
                    $sqlString .= $value.' ';
                }
                if($property == 'columnDatatype'){
                    $sqlString .= $value;
                }
                if($property == 'columnSize'){
                    if(!empty($value))
                    $sqlString .= '('.$value.')';
                }
                if($property == 'columnNotnull'){
                    $sqlString .= ' NOT NULL';
                }
            }
            $sqlString .= ',';
        }
        $sqlString .= ' PRIMARY KEY(id) )';
        $tempString .= $sqlString;
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

    private function makeProperArrayFromUserInputColumns($table_data){
        $properArray = array();
        foreach ($table_data as $property => $fieldValues) {
            if(is_array($fieldValues)){
                foreach ($fieldValues as $index => $value) {
                    if($property == 'field_name'){
                        if(!empty($value)){
                            if(!isset($properArray[$index])){
                                $properArray[$index] = array();
                            }
                            $properArray[$index]['columnName'] = $value;
                        }
                    }
                    if($property == 'field_type'){
                        if(isset($properArray[$index])){
                            $properArray[$index]['columnDatatype'] = $value;
                        }
                    }
                    if($property == 'field_length'){
                        if(isset($properArray[$index])){
                            $properArray[$index]['columnSize'] = $value;
                        }
                    }
                    if($property == 'field_null'){
                        if(isset($properArray[$index])){
                            $properArray[$index]['columnNotnull'] = $value;
                        }
                    }
                }

            }
        }
        return $properArray;
    }
    private function getTableColumnsAsArray($tablename){
        $tblClm = $this->getSchemaManager()->listTableColumns($tablename);
        $tableColumns = array();
        foreach ($tblClm as $column => $property) {
                $tableColumns[$column] = array();
                $tableColumns[$column]['size'] = $tblClm[$column]->getLength();
                $tableColumns[$column]['notnull'] = $tblClm[$column]->getNotnull();
                $tableColumns[$column]['type'] = (string) $tblClm[$column]->getType();
        }
        return $tableColumns;
    }
    private function fieldsHasUniqueName($table_data){
        $fieldArray = $this->makeProperArrayFromUserInputColumns($table_data);
        $tempArray = array();
        foreach ($fieldArray as $key => $field) {
            array_push($tempArray, $field['columnName']);
        }

        $arrayLength = count($tempArray);
        $newUniqueArray = array_unique($tempArray);
        $uniqueArrayLength = count($newUniqueArray);
        if($arrayLength == $uniqueArrayLength)
            return true;
        else 
            return false;
    }
    private function newTableFormErrorPrevData($table_data){
        $df=  $this->makeProperArrayFromUserInputColumns($table_data);
        $fieldDisplayIfFormError = array();
        foreach ($df as $key => $field) {
           $fieldDisplayIfFormError[$field['columnName']] = array();
           $fieldDisplayIfFormError[$field['columnName']]['size'] = '';
           $fieldDisplayIfFormError[$field['columnName']]['notnull'] = 0;
           $fieldDisplayIfFormError[$field['columnName']]['type']= '';
           if(isset($field['columnDatatype'])){
                $fieldDisplayIfFormError[$field['columnName']]['type'] = $field['columnDatatype'];
           }
           if(isset($field['columnSize'])){
                $fieldDisplayIfFormError[$field['columnName']]['size'] = $field['columnSize'];
           }
           if(isset($field['columnNotnull'])){
                $fieldDisplayIfFormError[$field['columnName']]['notnull'] = 1;
           }
        }
        return $fieldDisplayIfFormError;
    }

}
