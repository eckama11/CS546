<?php

class Employee extends Base {

    private $id;
    private $activeFlag = true;
    private $username;
    private $password;
    private $name;
    private $address;
    private $rank;
    private $taxID;
    private $numDeductions;
    private $salary;

    public function getId() {
        return $this->id;
    } // getId()

    public function getUsername() {
        return $this->username;
    }
    
    public function setUsername($newUsername) {
        if ($$newUsername == '')
            throw new Exception("Username cannot be empty string");
        $this->username = $newUsername;
    }

} // class Employee
