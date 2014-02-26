<?php

class Employee
    extends GetterSetter
{

    private $_id;
    private $_activeFlag = true;
    private $_username;
    private $_password;
    private $_name;
    private $_address;
    private $_rank;
    private $_taxId;
    private $_numDeductions;
    private $_salary;

    /**
     * Constructs a new Employee object.
     *
     * @param   int     $id
     * @param   boolean $activeFlag
     * @param   string  $username
     * @param   string  $password       TODO: Password should probably not be required... Not the most secure.
     * @param   string  $name
     * @param   string  $address
     * @param   Rank    $rank
     * @param   string  $taxId
     * @param   int     $numDeductions
     * @param   float   $salary
     */
    public function __construct(
            $id, $activeFlag, $username, $password,
            $name, $address, Rank $rank, $taxId, $numDeductions, $salary
        )
    {
        if (!is_int($id))
            throw new Exception("The id must be an integer");
        $this->_id = (int) $id;

        $this->activeFlag = $activeFlag;
        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
        $this->address = $address;
        $this->rank = $rank;
        $this->taxId = $taxId;
        $this->numDeductions = $numDeductions;
        $this->salary = $salary;
    } // __construct
    
    protected function getId() {
        return $this->_id;
    } // getId

    protected function getActiveFlag() {
        return $this->_activeFlag;
    } // getActiveFlag

    protected function setActiveFlag($newActiveFlag) {
        if (!is_bool($newActiveFlag))
            throw new Exception("The activeFlag must be set to a boolean value.");
        $this->_activeFlag = (bool) $newActiveFlag;
    } // setActiveFlag

    protected function getUsername() {
        return $this->_username;
    } // getUsername
    
    protected function setUsername($newUsername) {
        $newUsername = trim($newUsername);
        if (empty($newUsername))
            throw new Exception("Username cannot be empty string");
        $this->_username = $newUsername;
    } // setUsername

    protected function getPassword() {
        return $this->_password;
    } // getPassword
    
    protected function setPassword($newPassword) {
        if (empty($newPassword))
            throw new Exception("Password cannot be empty string");
        $this->_password = $newPassword;
    } // setPassword

    protected function getName() {
        return $this->_name;
    } // getName
    
    protected function setName($newName) {
        $newName = trim($newName);
        if (empty($newName))
            throw new Exception("Name cannot be empty string");
        $this->_name = $newName;
    } // setName

    protected function getAddress() {
        return $this->_address;
    } // getAddress
    
    protected function setAddress($newAddress) {
        $newAddress = trim($newAddress);
        if (empty($newAddress))
            throw new Exception("Address cannot be empty string");
        $this->_address = $newAddress;
    } // setAddress

    protected function getRank() {
        return $this->_rank;
    } // getRank
    
    protected function setRank(Rank $newRank) {
        $this->_rank = $newRank;
    } // setRank

    protected function getTaxId() {
        return $this->_taxId;
    } // getTaxId
    
    protected function setTaxId($newTaxId) {
        $newTaxId = trim($newTaxId);
        if (empty($newTaxId))
            throw new Exception("TaxId cannot be empty string");
        $this->_taxId = $newTaxId;
    } // setTaxId

    protected function getNumDeductions() {
        return $this->_numDeductions;
    } // getNumDeductions
    
    protected function setNumDeductions($newNumDeductions) {
        if (!is_int($newNumDeductions) || ($newNumDeductions < 0))
            throw new Exception("NumDeductions must be an integer greater or equal to 0");
        $this->_numDeductions = (int) $newNumDeductions;
    } // setNumDeductions

    protected function getSalary() {
        return $this->_salary;
    } // getSalary
    
    protected function setSalary($newSalary) {
        if (!is_numeric($newSalary) || ($newSalary < 0))
            throw new Exception("Salary must be an number greater or equal to 0");
        $this->_salary = (double) $newSalary;
    } // setSalary

    public function __toString() {
        return __CLASS__ ."(id=$this->id, activeFlag=$this->activeFlag, username=$this->username, password=$this->password,
            name=$this->name, address=$this->address, rank=$this->rank, taxId=$this->taxId, numDeductions=$this->numDeductions, salary=$this->salary)";
    } // __toString

} // class Employee
