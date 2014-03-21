<?php

class PayStubDepartment
    extends GetterSetter
{

    private $_id;
    private $_name;
    private $_managers;

    /**
     * Constructs a new PayStubDepartment object.
     *
     * @param   int     $id    The unique database ID assigned to the department.
     * @param   string  $name  The name assigned to the department.
     * @param   array[String] $managers   The name(s) of the managers assigned to the department
     */
    public function __construct($id, $name, $managers) {
        if (!is_numeric($id) || ($id < 0))
            throw new Exception('The $id parameter must be an integer');
        $id = (int) $id;

        $name = trim($name);
        if (empty($name))
            throw new Exception('The $name parameter must be a non-empty string');

        if (!is_array($managers))
            throw new Exception("The \$managers parameter must be an array");

        $this->_id = $id;
        $this->_name = $name;
        $this->_managers = array_map(function($item) { return (string) $item; }, $managers);
    } // __construct

    protected function getId() {
        return $this->_id;
    } // getId

    protected function getName() {
        return $this->_name;
    } // getName

    protected function getManagers() {
        return $this->_managers;
    } // getManagers

    public function __toString() {
        return __CLASS__ ."(id=$this->id, name=$this->name, managers=". implode(',', $this->managers) .")";
    } // __toString

} // class Department