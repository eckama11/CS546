<?php

class PayStub
    extends GetterSetter
{

    private $_id;
    private $_payPeriodStartDate;
    private $_employee;
    private $_name;
    private $_address;
    private $_rank;
    private $_taxId;
    private $_departments;
    private $_salary;
    private $_numDeductions;
    private $_taxWithheld;
    private $_taxRate;

    /**
     * Constructs a new PayStub instance.
     *
     * @param   int             $id
     * @param   Date            $payPeriodStartDate
     * @param   Employee        $employee
     * @param   string          $name
     * @param   string          $address
     * @param   string          $rank
     * @param   string          $taxId
     * @param   array[String]   $departments
     * @param   double          $salary
     * @param   int             $numDeductions
     * @param   double          $taxWithheld
     * @param   double          $taxRate
     */
    public function __construct(
        $id, DateTime $payPeriodStartDate, Employee $employee,
        $name, $address, $rank, $taxId, $departments,
        $salary, $numDeductions, $taxWithheld, $taxRate
    ) {
        if (!is_int($id))
            throw new Exception("The id must be an integer");
        $this->_id = (int) $id;

        $this->_payPeriodStartDate = $payPeriodStartDate;

        $this->_employee = $employee;

        $name = trim($name);
        if (empty($name))
            throw new Exception("The name cannot be an empty string");
        $this->_name = $name;

        $address = trim($address);
        if (empty($address))
            throw new Exception("The address cannot be an empty string");
        $this->_address = $address;

        $rank = trim($rank);
        if (empty($rank))
            throw new Exception("The rank cannot be an empty string");
        $this->_rank = $rank;

        $taxId = trim($taxId);
        if (empty($taxId))
            throw new Exception("The taxId cannot be an empty string");
        $this->_taxId = $taxId;

        try {
            if (!is_array($departments))
                throw new Exception();

            $callback = function($item) {
                    return (string) $item;
                };
            $departments = array_map($callback, $departments);
        } catch (Exception $e) {
            throw new Exception("The departments must be an array of strings");
        } // try/catch
        $this->_departments = $departments;

        if (!is_numeric($salary) || ($salary < 0))
            throw new Exception("The salary must be an number greater or equal to 0");
        $this->_salary = (double) $salary;

        if (!is_int($numDeductions) || ($numDeductions < 0))
            throw new Exception("The numDeductions must be an integer greater or equal to 0");
        $this->_numDeductions = (int) $numDeductions;

        if (!is_numeric($taxWithheld) || ($taxWithheld < 0))
            throw new Exception("The taxWithheld must be an number greater or equal to 0");
        $this->_taxWithheld = (double) $taxWithheld;

        if (!is_numeric($taxRate) || ($taxRate < 0))
            throw new Exception("The taxRate must be an number greater or equal to 0");
        $this->_taxRate = (double) $taxRate;
    } // __construct

    protected function getId() {
        return $this->_id;
    } // getId()

    protected function getPayPeriodStartDate() {
        return $this->_payPeriodStartDate;
    } // getPayPeriodStartDate()

    protected function getEmployee() {
        return $this->_employee;
    } // getEmployee()

    protected function getName() {
        return $this->_name;
    } // getName()

    protected function getAddress() {
        return $this->_address;
    } // getAddress()

    protected function getRank() {
        return $this->_rank;
    } // getRank()

    protected function getTaxId() {
        return $this->_taxId;
    } // getTaxId()

    protected function getDepartments() {
        return $this->_departments;
    } // getDepartments()

    protected function getSalary() {
        return $this->_salary;
    } // getSalary()

    protected function getNumDeductions() {
        return $this->_numDeductions;
    } // getNumDeductions()

    protected function getTaxWithheld() {
        return $this->_taxWithheld;
    } // getTaxWithheld()

    protected function getTaxRate() {
        return $this->_taxRate;
    } // getTaxRate()

    public function __toString() {
        return __CLASS__ ."(id=$this->id, payPeriodStartDate=". $this->payPeriodStartDate->format("Y-m-d H:i:sP") .", employee=$this->employee, name=$this->name, address=$this->address, rank=$this->rank, taxId=$this->taxId, departments=". implode(',', $this->departments) .", salary=$this->salary, numDeductions=$this->numDeductions, taxWithheld=$this->taxWithheld, taxRate=$this->taxRate)";
    } // __toString

} // class PayStub