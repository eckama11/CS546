<?php
require_once("GetterSetter.php");

class LoginSession
    extends GetterSetter
{

    private $_sessionId;
    private $_authenticatedEmployee;

    /**
     * Constructs a new LoginSession object.
     *
     * @param   string   $sessionId              The unique database session ID assigned to the LoginSession.
     * @param   Employee $authenticatedEmployee  The employee authenticated to this session.
     */
    public function __construct($sessionId, Employee $authenticatedEmployee) {
        $this->_sessionId = $sessionId;
        $this->_authenticatedEmployee = $authenticatedEmployee;
    } // __construct

    protected function getSessionId() {
        return $this->_sessionId;
    } // getId

    protected function getAuthenticatedEmployee() {
        return $this->_authenticatedEmployee;
    } // getAuthenticatedEmployee

    protected function getIsAdministrator() {
    	if (!$this->authenticatedEmployee->isActive) {
    		return false;
    	}
        return $this->authenticatedEmployee->current->rank->employeeType->isAdministrator;
        
    } // getIsAdministrator()

    public function __toString() {
        return __CLASS__ ."(sessionId=$this->sessionId, authenticatedEmployee=$this->authenticatedEmployee)";
    } // __toString

} // class LoginSession