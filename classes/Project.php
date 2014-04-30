<?php

class Project
    extends GetterSetter
    implements JsonSerializable
{

    private $_id;
    private $_startDate;
    private $_endDate;
    private $_name;
    private $_description;
    private $_otherCosts;
    private $_departments;

    /**
     * Constructs a new Project object.
     *
     * @param   int      $id
     * @param   DateTime $startDate
     * @param   DateTime $endDate
     * @param   string   $name
     * @param   string   $description
     */
    public function __construct(
            $id, DateTime $startDate, DateTime $endDate,
            $name, $description, $otherCosts
        )
    {
        if (!is_numeric($id))
            throw new Exception("The \$id parameter must be an integer");
        $this->_id = (int) $id;

        $this->_startDate = $startDate;

        $this->_endDate = $endDate;

        $name = trim($name);
        if (empty($name))
            throw new Exception("The \$name parameter cannot be empty string");
        $this->_name = $name;

        $description = trim($description);
        if (empty($description))
            throw new Exception("The \$description parameter cannot be empty string");
        $this->_description = $description;

        if (!is_numeric($otherCosts) || ($otherCosts < 0))
            throw new Exception("The \$otherCosts parameter must be a number greater or equal to 0");
        $this->_otherCosts = (double) $otherCosts;
    } // __construct

    public function jsonSerialize() {
        $rv = new StdClass();
        $rv->id = $this->id;
        $rv->startDate = $this->startDate->format("Y-m-d");
        $rv->endDate = $this->endDate->format("Y-m-d");
        $rv->name = $this->name;
        $rv->description = $this->description;
        $rv->otherCosts = $this->otherCosts;
        if ($this->departments != null)
            $rv->departments = $this->departments;
        return $rv;
    } // jsonSerialize

    protected function getId() {
        return $this->_id;
    } // getId

    protected function getStartDate() {
        return $this->_startDate;
    } // getStartDate

    protected function getEndDate() {
        return $this->_endDate;
    } // getEndDate

    protected function getName() {
        return $this->_name;
    } // getName

    protected function getDescription() {
        return $this->_description;
    } // getDescription

    protected function getOtherCosts() {
        return $this->_otherCosts;
    } // getOtherCosts

    protected function getDepartments() {
        return $this->_departments;
    } // getDepartments()

    /* A hack to allow specifying a departments property in some cases */
    protected function setDepartments($newDepartments) {
        $this->_departments = $newDepartments;
    } // setDepartments()

    public function __toString() {
        return __CLASS__ ."(id=$this->id, startDate=$this->startDate, endDate=$this->endDate, name=$this->name, description=$this->description, otherCosts=$this->otherCosts". ($this->departments ? ", departments=". implode(",", $this->departments) : "") .")";
    } // __toString

} // class Project
