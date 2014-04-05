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
    private $_taxId;
    private $_current;

    /**
     * Constructs a new Employee object.
     *
     * @param   int     $id
     * @param   boolean $activeFlag
     * @param   string  $username
     * @param   string  $password       TODO: Password should probably not be required... Not the most secure.
     * @param   string  $name
     * @param   string  $address
     * @param   string  $taxId
     * @param   EmployeeHistory $current
     */
    public function __construct(
            $id, $activeFlag, $username, $password,
            $name, $address, $taxId,
            EmployeeHistory $current
        )
    {
        if (!is_numeric($id))
            throw new Exception("The \$id parameter must be an integer");
        $this->_id = (int) $id;

        $this->activeFlag = $activeFlag;
        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
        $this->address = $address;
        $this->taxId = $taxId;
        $this->current = $current;
    } // __construct
    
    protected function getId() {
        return $this->_id;
    } // getId

    protected function getActiveFlag() {
        return $this->_activeFlag;
    } // getActiveFlag

    protected function setActiveFlag($newActiveFlag) {
        if ($newActiveFlag == "true")
            $newActiveFlag = true;
        else if (($newActiveFlag == "false") || ($newActiveFlag == null))
            $newActiveFlag = false;

        if (!is_bool($newActiveFlag) && !is_numeric($newActiveFlag))
            throw new Exception("The activeFlag must be set to a boolean value.");

        $this->_activeFlag = ($newActiveFlag && true);
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

    protected function getTaxId() {
        return $this->_taxId;
    } // getTaxId
    
    protected function setTaxId($newTaxId) {
        $newTaxId = trim($newTaxId);
        if (empty($newTaxId))
            throw new Exception("TaxId cannot be empty string");
        $this->_taxId = $newTaxId;
    } // setTaxId

    protected function getCurrent() {
        return $this->_current;
    } // getCurrent

    protected function setCurrent(EmployeeHistory $newCurrent) {
        $this->_current = $newCurrent;
    } // setCurrent

    public function __toString() {
        return __CLASS__ ."(id=$this->id, activeFlag=$this->activeFlag, username=$this->username, password=$this->password, name=$this->name, address=$this->address, taxId=$this->taxId)";
    } // __toString

} // class Employee
