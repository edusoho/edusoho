<?php
class Person {
    private $_name;
    private $_department;
    public function __construct($name,$department)
    {
      $this->_name = $name;
      $this->_department = $department;
    }
    public function getName()
    {
      return $this->_name;
    }
    public function getManager()
    {
      return $this->_department->getManager();
    }
    public  function getDepartment()
    {
       return $this->_department;
    }
    public function setDepartment(Department $arg)
    {
       $this->_department = $arg;
    }
}

class Department {
    private $_name;
    private $_manager;
    public function __construct($name,$manager)
    {
      $this->_name = $name;
      $this->_manager = $manager;
    }

    public function  getManager()
    {
       return $this->_manager;
    }
  }
  $normalPerson = new Person('张三','');
  $managePerson = new Person('李四','');
  $department = new Department("技术部",$managePerson);
  $normalPerson->setDepartment($department);
  // echo '张三的经理是'.$normalPerson->getDepartment()->getManager()->getName();
  // $manager = $department->getManager()->getName();
  // echo '技术部的经理是'.$manager;
  echo $normalPerson->getManager()->getName();
