<?php

class AbstractModel{


    private $Flag ;

    private $select = array(); 

    private $query = [];

    protected static $TableName ;

    private $whereClause;

    private $limit; 

    private $insert = [];

    private $update = "";

    private $orderBy ; 

    private $order ;
    
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

    public function orderBy($orderby,$order = "ASC")
    {
        $this->orderBy = trim($orderby);
        if($order != "ASC" && $order != "DESC"){
            $order = "ASC";
        }
        $this->order = $order;
        return $this;
    }

    public function update($actions){
        $this->Flag = "update";
        $this->update = $actions;
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
        
        $keyArray=array_keys($this->insert);
        $columns = "( ";
        foreach($keyArray as $column){
            $columns .=  $column. (($column != end($keyArray))? ",":"");
        }
        $columns .= " ) ";
        $this->query [] = $columns ;

        $this->query [] = " VALUES ";

        $ArrayValues=array_values($this->insert);
        $values = "( ";
        foreach($ArrayValues as $column){
            $values .=  $column. (($column != end($ArrayValues))? ",":"");
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
        }
        $this->query = join(" ",$this->query);
        return $this->query ;
    }
}

