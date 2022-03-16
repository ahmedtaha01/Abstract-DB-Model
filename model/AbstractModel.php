<?php
namespace PHPMVC\MODEL;
use PHPMVC\LIB\Database;
use PHPMVC\LIB\Paginate;
// date ----->  '".date('Y-m-d')."'

class AbstractModel{
    private $con;
    private $statement;
    protected static $TABLE_NAME;      
    protected static $TABLE_COLUMNS;       // table columns , must put in array

    private $valid = true;                 // checking for errors
                                           // method chainging can't be broke
    private $operators = array('>','=','<','ASC','DESC');    // add operators ,if you want ,for WHERE 
    
    private $tables = array('users','items','comments','categories');
    //paginate
    public $pagenum;       //current page
    private $offset;        //
    private $limit;         //number of items per page

    function __construct(){
        $this->con = new Database;
    }

    public function select($columns){       // selected columns    
        $data = $this->return_table_values($columns);
        $this->statement = 'SELECT '.$data.' FROM '.static::$TABLE_NAME;
        return $this;
    }

    public function count($column,$alias = 'num'){               
        $this->statement = 'SELECT COUNT('.$column.') as '.$alias.' FROM '.static::$TABLE_NAME;
        return $this;
    }

    public function join($table_name){
        if($this->sanitize_table($table_name)){      
            $this->statement = $this->statement . ' JOIN '.$table_name; 
        } else {
            $this->valid = false;   
        }
        return $this;
    }

    public function on($condition){
        
        $this->statement = $this->statement . ' ON '.$condition; 
        
        return $this;
    }

    public function insert($values){
        $queriedData = $this->establish_evironment($values);
        $this->statement = 'INSERT INTO '.static::$TABLE_NAME.' ('.$this->return_table_columns($queriedData).') '.
        'VALUES '.'('.$this->return_table_values($queriedData).')';
        
        return $this;
    }

    public function update($columns_values,$condition){
        $queriedData = $this->establish_evironment($columns_values);
        if($this->sanitize_column_value($condition)){
            $this->statement = 'UPDATE '.static::$TABLE_NAME.' SET '. $this->return_table_columns_values($queriedData).' WHERE '.$condition;
        } else {
            $this->valid = false;   
        }
        return $this;
    }

    public function where($column , $condition , $value){
        if($this->sanitize_operators($condition)){
            $value = $this->sanitize_values($value);
            $this->statement = $this->statement.' WHERE '.$column.' '.$condition.' '.$value;
        } else {
            $this->valid = false;   
        }
        return $this;
    }

    public function paginate($limit){         // for pagination
        $this->limit = $limit;
        $this->statement =$this->statement . " LIMIT {$this->offset()},$limit";   //offset first , limit second
        return $this;
      }

    public function orderBy($column,$order){
        if($this->sanitize_columns($column) && $this->sanitize_operators($order)){
            $this->statement = $this->statement.' ORDER BY '.$column.' '.$order;
        } else {
            $this->valid = false;   
        }
        return $this;
    }

    public function delete(){
        $this->statement = 'DELETE FROM '.static::$TABLE_NAME;
        return $this;
    }


    public function execute(){
        if($this->valid){
            var_dump($this->statement);
            $this->con->query($this->statement);
            $this->statement = null; //--------> as statement remain exist after execution
            return $this->con->execute();    //so we need to null it for futher querys
        }
        return false;
    }

    public function get(){
        if($this->valid){
            var_dump($this->statement);
            $this->con->query($this->statement);
            $this->statement = null; //--------> as statement remain exist after execution
            return $this->con->resultSet();    //so we need to null it for futher querys
        }
        return false;
   
    }

    private function offset(){
        if($this->pagenum == null || $this->pagenum == 0){
          return $this->offset = 0;
        } else {
          return $this->offset = ($this->pagenum * $this->limit) - $this->limit; 
        }
    }

    private function return_table_columns($array){
        return implode(', ',array_keys($array));
    }

    private function return_table_values($array){
        return implode(', ',$array);
    }

    private function establish_evironment($array){       // creating the environment
        foreach($array as $key1 => $value1){             // insert and update
            $found = false;
            foreach(static::$TABLE_COLUMNS as $key2){
                if($key1 == $key2){
                    $array[$key2] = "'$value1'";
                    $found = true;
                    break;
                }
            }
            if(!$found){
                unset($array[$key1]);
            }
        }
        return $array;
    }

    private function return_table_columns_values($array){       //imploding both  //nice for update
        return urldecode(http_build_query($array,'',','));
    }

    private function sanitize_column_value($all){
        $sentence = explode(',',$all);
        foreach($sentence as $se){
            $column = explode('=',$se);
            if(!$this->sanitize_columns($column[0])){
                return false;
            }
            $this->sanitize_values($column[1]);
        }
        return true;
    }

    private function sanitize_column_column($all){
        $sentence = explode(',',$all);
        foreach($sentence as $se){
            $column = explode('=',$se);
            if($this->sanitize_columns($column[0]) && $this->sanitize_columns($column[1])){
                return true;
            }
        }
        return false;
    }

    private function sanitize_table($table){
        $table = explode(' ',$table);
        if(in_array($table[0],$this->tables)){
            return true;
        }
        return false;
    }

    private function sanitize_columns($columns){        //for columns in database
        $columns = filter_var($columns , FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);
        if($columns != '*'){
            $split = explode(',',$columns);
            foreach($split as $sp){
                if(!in_array(trim($sp) , static::$TABLE_COLUMNS)){
                    return false;
                }
            }
        }
        return true;
    }

    private function sanitize_values($values){    // sanitize values
        $values = filter_var($values , FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES); //removing html 
        $values = str_replace(';','',$values);  //removing injections

        return $values;
    }

    private function sanitize_operators($op){
        if(in_array($op, $this->operators)){
            return true;
        }
        return false;
    }
}





?>