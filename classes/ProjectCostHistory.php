<?php

class ProjectCostHistory
    extends GetterSetter
    implements JsonSerializable
{

    private $_startDate;
    private $_endDate;
    private $_payStub;
    private $_project;
    private $_department;   // Null for "other" (non-employee) costs
    private $_cost;


    /**
     * Constructs a new ProjectCostHistory object.
     *
     * @param   DateTime    $startDate    First date that this entry is effective.
     * @param   DateTime    $endDate      Last date (inclusive) that this entry is effective
     * @param   PayStub     $payStub      
     * @param   Project     $project
     * @param   Department  $department
     * @param   double      $cost
     */
    public function __construct(
            DateTime $startDate, DateTime $endDate,
            PayStub $payStub = null, Project $project, Department $department = null, $cost
        )
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->payStub = $payStub;
        $this->project = $project;
        $this->department = $department;

        $this->cost = $cost;
    } // __construct

    public function jsonSerialize() {
        $rv = new StdClass();
        $rv->id = $this->id;
        $rv->startDate = $this->startDate->format("Y-m-d");
        $rv->endDate = ($this->endDate ? $this->endDate->format("Y-m-d") : null);
        $rv->paystub = $this->paystub;
        $rv->project = $this->project;
        $rv->department = $this->department;
        $rv->cost = $this->cost;
        return $rv;
    } // jsonSerialize

    protected function getStartDate() {
        return $this->_startDate;
    } // getStartDate

    protected function setStartDate(DateTime $newStartDate) {
        if ($this->endDate && ($newStartDate > $this->endDate))
            throw new Exception("The startDate cannot be greater than the endDate");

        $this->_startDate = $newStartDate;
    } // setStartDate

    protected function getEndDate() {
        return $this->_endDate;
    } // getEndDate

    protected function setEndDate(DateTime $newEndDate) {
        if ($newEndDate < $this->startDate)
            throw new Exception("The endDate cannot be less than the startDate");

        $this->_endDate = $newEndDate;
    } // setEndDate

    protected function getDepartment() {
        return $this->_department;
    } // getDepartment

    protected function setDepartment( Department $newDepartment = null ) {
        $this->_department = $newDepartment;
    } // setDepartment

    protected function getPayStub() {
        return $this->_payStub;
    } // getPayStub
    
    protected function setPayStub(PayStub $newPayStub = null) {
        $this->_payStub = $newPayStub;
    } // setPayStub

    protected function getProject() {
        return $this->_project;
    } // getProject
    
    protected function setProject(Project $newProject) {
        $this->_project = $newProject;
    } // setProject

    protected function getCost() {
        return $this->_cost;
    } // getCost
    
    protected function setCost($newCost) {
        if (!is_numeric($newCost) || ($newCost < 0))
            throw new Exception("Cost must be a number greater than or equal to 0");

        $this->_cost = (double) $newCost;
    } // setCost

    public function __toString() {
        return __CLASS__ ."(startDate=$this->startDate, endDate=$this->endDate, payStub=$this->payStub, department=$this->department, cost=$this->cost)";
    } // __toString

} // class ProjectCostHistory
