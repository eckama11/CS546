<?php

class DBInterface {

    

    public function __construct( $dbServer, $dbName, $dbUsername, $dbPassword ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // __construct


    public function readLoginSession( $sessionID ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readLoginSession

    public function writeLoginSession( LoginSession $session ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // writeLoginSession

    public function createLoginSession( $username, $password ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // createLoginSession

    public function destroyLoginSession( LoginSession $session ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // destroyLoginSession

    public function readTaxRate( $id ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readTaxRate

    public function readTaxRates() {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readTaxRates

    public function readDepartment( $id ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readDepartment

    public function readDepartments() {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readDepartments

    public function readRank( $id ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readRank

    public function readRanks() {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readRanks

    public function readPayStub( $id ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readPayStub

    public function writePayStub( PayStub $paystub ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // writePayStub

    public function readPayStubs( $employeeId ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readPayStubs

    public function generatePayStubs() {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // generatePayStubs

    public function readEmployee( $id ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readEmployee

    public function writeEmployee( Employee $employee ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // writeEmployee

    public function readEmployees() {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readEmployees

    public function readDepartmentsForEmployee( $employeeId ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readDepartmentsForEmployee

    public function readEmployeesForDepartment( $departmentId ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // readEmployeesForDepartment

    public function writeEmployeeDepartmentAssociation( EmployeeDepartmentAssociation $assoc ) {
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);
    } // writeEmployeeDepartmentAssociation

} // DBInterface
