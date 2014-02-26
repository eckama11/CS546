<?php

class Department
    extends GetterSetter
{

    private $_id;
    private $_name;

    /**
     * Constructs a new Department object.
     *
     * @param   int     $id    The unique database ID assigned to the Department.
     * @param   string  $name  The name assigned to the department.
     */
    public function __construct($id, $name) {
        if (!is_int($id) || ($id < 0))
            throw new Exception('The $id parameter must be an integer');

        $name = trim($name);
        if (empty($name))
            throw new Exception('The $name parameter must be a non-empty string');

        $this->_id = $id;
        $this->_name = $name;
    } // __construct

    protected function getId() {
        return $this->_id;
    } // getId

    protected function getName() {
        return $this->_name;
    } // getName

    public function __toString() {
        return __CLASS__ ."(id=$this->id, name=$this->name)";
    } // __toString

} // class Department