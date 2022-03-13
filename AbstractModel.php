<?php
namespace PHPMVC\lib;

use PHPMVC\LIB\Database ;

class AbstractModel{

    private $db ;

    private $Flag ;

    private $select = array(); 

    private $query = array() ;

    protected static $TableName ;

    private $whereClause;

    private $limit; 

    private $insert ;

    private $update = "";

    private $orderBy ; 

    private $order ;

    private $JoinInfo ;

    
    public function __construct()
    {
        $this->db = new Database();
    }

    public function select()
    {
        $this->Flag = "select";
        $this->select = func_get_args();
        return $this;
    }

   
    public function delete()
    {
        $this->Flag = "delete";
        return $this;
    }


    public function insert($values)
    {
        $this->Flag = "insert";
        $this->insert = $values;
        return $this;
    }

    public function where($where="")
    {
        $this->whereClause = trim($where) ; 
        return $this;
    }

    public function limit($limit="")
    {
        $this->limit = trim($limit) ;
        return $this;
    }

    public function orderBy($orderby,$order = " ASC ")
    {
        $this->orderBy = " ".trim($orderby);
        $this->order = " ".trim($order);
        return $this;
    }


    //actions :- 
    // "name = omar,age = 21,address = mansoura"
    
    public function update($actions){
        $this->Flag = "update";
        $this->update = $actions;
        return $this;
    }

    public function join($JoinInfo)
    {
        $this->Flag = "join";
        $this->JoinInfo = $JoinInfo;
        return $this;
    }




    private function SELECT_QUERY()
    {
        $this->query [] = "SELECT";
        if(!empty($this->select)){
            $this->query [] = join(" , " ,$this->select);
        }else{
            $this->query [] = "*";
        }

        
        $this->query [] = " FROM "; 

        $this->query [] = static::$TableName ;



        if(!empty($this->whereClause)){
            $this->query [] = " WHERE "; 
            $this->query [] = $this->whereClause;
        }


        if(!empty($this->orderBy)){
            $this->query [] = "  ORDER BY  ";
            $this->query [] = $this->orderBy;
            $this->query [] = $this->order;
        }


        if(!empty($this->limit)){
            $this->query [] = "  LIMIT ";
            $this->query [] = $this->limit;
        }
        
    }

    private function DELETE_QUERY()
    {
        $this->query [] = "DELETE ";
        
        $this->query [] = " FROM "; 

        $this->query [] = static::$TableName ;



        if(!empty($this->whereClause)){
            $this->query [] = " WHERE "; 
            $this->query [] = $this->whereClause;
        }
   }

    private function INSERT_QUERY()
    {
    
        $this->query [] = "INSERT INTO ";
        
        $this->query [] = static::$TableName ;

        $this->Insert_Helper();

    }

    public function Insert_Helper()
    {
        $keyArray=array_keys($this->insert);
        $columns = " ( ";
        foreach($keyArray as $column){
            $columns .=  $column. (($column != end($keyArray))? ",":"");
        }
        $columns .= " ) ";
        $this->query [] = $columns ;

        $this->query [] = " VALUES ";

        $ArrayValues=array_values($this->insert);
        $values = "( ";
        foreach($ArrayValues as $column){
            $values .=  '"'.$column.'"'. (($column != end($ArrayValues))? ",":"");
        }
        $values .= " ) ";

        $this->query [] = $values ;
    }


    private function UPDATE_QUERY()
    {
        $this->query [] = "UPDATE ";

        $this->query [] = static::$TableName ; 

        $this->query [] = "SET ";

        $this->query [] = $this->update;

        $this->query [] = " WHERE ";

        $this->query [] = $this->whereClause;

    }

    private function JOIN_QUERY()
    {
        if ($this->JoinInfo['table3'] == "" ){
            $this->query = "SELECT " .$this->JoinInfo['ColsName']. " FROM ". $this->JoinInfo['table1']."
                                    INNER JOIN ".$this->JoinInfo['table2']." ON ".$this->JoinInfo['cond1']." WHERE "
                                    .$this->JoinInfo['where']." ";
            
            
            
        }else{
            //join multi tables

           $this->query = "SELECT ".$this->JoinInfo['ColsName']." FROM ". $this->JoinInfo['table1']."
                                    INNER JOIN ".$this->JoinInfo['table2']." ON ".$this->JoinInfo['cond1']."      
                                    INNER JOIN ".$this->JoinInfo['table3']." ON ".$this->JoinInfo['cond2'].
                                     " WHERE ".$this->JoinInfo['where']." ";
            
        }

        if(!empty($this->order)){
            $this->query .= "  ORDER BY  ";
            $this->query .= $this->orderBy;
            $this->query .= $this->order;
        }


        if(!empty($this->limit)){
            $this->query .= "  LIMIT ";
            $this->query .= $this->limit;
        }
        return $this->query;
    }

    public function execute()
    {
        if($this->Flag == "select"){
            $this->SELECT_QUERY();
        }elseif($this->Flag == "delete"){
            $this->DELETE_QUERY();
        }elseif($this->Flag == "insert"){
            $this->INSERT_QUERY();
        }elseif($this->Flag == "update"){
            $this->UPDATE_QUERY();
        }elseif($this->Flag == "join"){
            $this->JOIN_QUERY();
        }

        if($this->Flag !="join"){
            $this->query = join(" ",$this->query);
        }

        $this->db->query($this->query);
        if($this->db->execute()){
            unset($this->query);
            $this->query = array();
            if($this->Flag == "select" || $this->Flag =="join")
            {
                return $this->Fetch();
            }else{
                return true;
            }
        }
        return false;
    }

    public function Fetch()
    {
        return $this->db->fetchAll();
    }
}


