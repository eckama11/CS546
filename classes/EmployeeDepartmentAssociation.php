<?php

class EmployeeDepartmentAssociation
    extends GetterSetter
{

    private $_employee;
    private $_department;

    /**
     * Constructs a new EmployeeDepartmentAssociation object.
     *
     * @param   Employee    $employee   The employee.
     * @param   Department  $department The department.
     */
    public function __construct(Employee $employee, Department $department) {
        $this->_employee = $employee;
        $this->_department = $department;
    } // __construct

    protected function getEmployee() {
        return $this->_employee;
    } // getEmployee

    protected function getDepartment() {
        return $this->_department;
    } // getDepartment

    public function __toString() {
        return __CLASS__ ."(employee=$this->employee, department=$this->department)";
    } // __toString

} // class EmployeeDepartmentAssociation