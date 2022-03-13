<?php

require_once "AbstractModel.php";

class StudentModel extends AbstractModel {

    protected static $TableName = "students";



    public function GetAllStudents()
    {
        return $this->select()->execute();
    }

    public function GetUnderAgeStudents($age)
    {
        return $this->select("name")->where("age < $age")->limit(3)->orderBy("username","DESC")->execute();
    }

    public function AddStudent($values){
        return $this->insert($values)->execute();
    } 

    public function Delete_Student($cond = 1)
    {
        return $this->delete()->where($cond)->execute();
    }

    public function UpdateStudent($actions)
    {
        return $this->update($actions)->where("id = 4")->execute();
    }
}

$sql1 = new StudentModel();
var_dump($sql1->GetAllStudents());


$sql2 = new StudentModel();
var_dump($sql2->GetUnderAgeStudents(4));


$sql3 = new StudentModel();
$students = [
    "name"      => "omar",
    "age"       => "21",
    "id"        => "800156632",
    "address"   => "Egypt"
]
;
var_dump($sql3->AddStudent($students));


$sql4 = new StudentModel();
var_dump($sql4->Delete_Student());

$sql5 = new StudentModel();
var_dump($sql5->UpdateStudent("name = omar,age = 21,address = mansoura"));

