<?php

class Rank
    extends GetterSetter
    implements JsonSerializable
{

    private $_id;
    private $_name;
    private $_baseSalary;
    private $_employeeType;

    /**
     * Constructs a new Rank object.
     *
     * @param   int          $id             The unique database ID assigned to the Rank.
     * @param   string       $name           The user-friendly name of the rank.
     * @param   float        $baseSalary     The minimum salary for employees with this rank.
     * @param   EmployeeType $employeeType   The type of employee for this rank (Administrator, Manager, Developer)
     */
    public function __construct($id, $name, $baseSalary, EmployeeType $employeeType) {
        if (!is_numeric($id) || ($id < 0))
            throw new Exception('The $id parameter must be an integer');
        $id = (int) $id;

        $name = trim($name);
        if (empty($name))
            throw new Exception('The $name parameter must be a non-empty string');

        if (!is_numeric($baseSalary) || ($baseSalary < 0))
            throw new Exception('The $baseSalary parameter must be a number greater than 0');
        $baseSalary = (double) $baseSalary;

        $this->_id = $id;
        $this->_name = $name;
        $this->_baseSalary = $baseSalary;
        $this->_employeeType = $employeeType;
    } // __construct

    public function jsonSerialize() {
        $rv = new StdClass();
        $rv->id = $this->id;
        $rv->name = $this->name;
        $rv->baseSalary = $this->baseSalary;
        $rv->employeeType = (string) $this->employeeType;
        return $rv;
    } // jsonSerialize

    protected function getId() {
        return $this->_id;
    } // getId

    protected function getName() {
        return $this->_name;
    } // getName

    protected function getBaseSalary() {
        return $this->_baseSalary;
    } // getBaseSalary

    protected function getEmployeeType() {
        return $this->_employeeType;
    } // getEmployeeType

    public function __toString() {
        return __CLASS__ ."(id=$this->id, name=$this->name, baseSalary=$this->baseSalary, employeeType=$this->employeeType)";
    } // __toString

} // class Rank