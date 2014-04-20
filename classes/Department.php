<?php

class Department
    extends GetterSetter
    implements JsonSerializable
{

    private $_id;
    private $_name;
    private $_managers;

    /**
     * Constructs a new Department object.
     *
     * @param   int     $id    The unique database ID assigned to the Department.
     * @param   string  $name  The name assigned to the department.
     */
    public function __construct($id, $name) {
        if (!is_numeric($id) || ($id < 0))
            throw new Exception('The $id parameter must be an integer');
        $id = (int) $id;

        $name = trim($name);
        if (empty($name))
            throw new Exception('The $name parameter must be a non-empty string');

        $this->_id = $id;
        $this->_name = $name;
    } // __construct

    public function jsonSerialize() {
        $rv = new StdClass();
        $rv->id = $this->id;
        $rv->name = $this->name;
        if ($this->managers != null)
            $rv->managers = $this->managers;
        return $rv;
    } // jsonSerialize

    protected function getId() {
        return $this->_id;
    } // getId

    protected function getName() {
        return $this->_name;
    } // getName

    /* A hack to allow specifying a managers property in some cases */
    protected function setManagers($managers) {
        $this->_managers = $managers;
    } // setManagers()

    protected function getManagers() {
        return $this->_managers;
    } // getManagers()

    public function __toString() {
        return __CLASS__ ."(id=$this->id, name=$this->name". ($this->managers ? ", managers=".implode($this->managers) : "") .")";
    } // __toString

} // class Department