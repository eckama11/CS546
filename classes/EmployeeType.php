<?php

class EmployeeType
    extends GetterSetter
{

    public static function Administrator() {
        static $admin;
        if ($admin === null)
            $admin = new EmployeeType("Administrator", true, false, false);
        return $admin;
    } // Administrator()

    public static function Manager() {
        static $manager;
        if ($manager === null)
            $manager = new EmployeeType("Manager", false, true, false);
        return $manager;
    } // Manager()

    public static function SoftwareDeveloper() {
        static $softwareDeveloper;
        if ($softwareDeveloper === null)
            $softwareDeveloper = new EmployeeType("Software Developer", false, false, true);
        return $softwareDeveloper;
    } // SoftwareDeveloper()

    public static function fromName($name) {
        switch (strtolower($name)) {
        case 'administrator':
            return  self::Administrator();
        case 'manager':
            return self::Manager();
        case 'software developer':
            return self::SoftwareDeveloper();
        } // switch
        throw new Exception("Unknown rank identifier '$name'");
    } // fromName

    private $_name;
    private $_isAdministrator;
    private $_isManager;
    private $_isSoftwareDeveloper;

    private function __construct($name, $isAdministrator, $isManager, $isSoftwareDeveloper) {
        $this->_name = $name;
        $this->_isAdministrator = $isAdministrator;
        $this->_isManager = $isManager;
        $this->_isSoftwareDeveloper = $isSoftwareDeveloper;
    } // __construct

    protected function getName() {
        return $this->_name;
    } // getName

    protected function getIsAdministrator() {
        return $this->_isAdministrator;
    } // getIsAdministrator

    protected function getIsManager() {
        return $this->_isManager;
    } // getIsManager

    protected function getIsSoftwareDeveloper() {
        return $this->_isSoftwareDeveloper;
    } // getIsSoftwareDeveloper

    public function __toString() {
        return $this->_name;
    } // __toString

} // class EmployeeType