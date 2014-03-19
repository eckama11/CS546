<?php
//require_once( "LoginSession.php" );
//require_once( "Employee.php" );
//require_once( "EmployeeType.php" );
//require_once( "Rank.php" );

class DBInterface {

    private $dbh;

    /** 
     * Constructs a new DBInterface instance with the specified connection parameters.
     * @param   String $dbServer    The name/IP of the MySQL server instance to connect to.
     * @param   String $dbName      The name of the initial database for the connection.
     * @param   String $dbUsername  The username to use for authentication.
     * @param   String $dbPassword  The password to use for authentication.
     */
    public function __construct( $dbServer, $dbName, $dbUsername, $dbPassword ) {
        $dsn = "mysql:host=$dbServer;dbname=$dbName";
        $this->dbh = new PDO($dsn, $dbUsername, $dbPassword);
    } // __construct

    public function formatErrorMessage($stmt, $message) {
        list($sqlState, $driverErrorCode, $driverErrorMessage) = $stmt->errorInfo();
        return $message .": [$sqlState] $driverErrorCode: $driverErrorMessage";
    } // formatSqlErrorMessage($pdoErrorInfo)

    /**
     * Reads a LoginSession object from the database.
     * @param   int $sessionId  The session ID of the LoginSession record to retrieve.
     * @return  LoginSession    The LoginSession instance for the specified session ID, if one exists.
     */
    public function readLoginSession( $sessionId ) {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT sessionId, authenticatedEmployee ".
                        "FROM loginSession ".
                        "WHERE sessionId=?"
                );

        $stmt->execute(Array($sessionId));
        $res = $stmt->fetchObject();
        if ($res === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to retrieve specified session from database"));

        return new LoginSession($res->sessionId, $this->readEmployee($res->authenticatedEmployee));
    } // readLoginSession

    /**
     * Updates a LoginSession object in the database.
     * @param   LoginSession $session  The session to update.
     * @return  LoginSession    The LoginSession which was passed in.
     */
    public function writeLoginSession( LoginSession $session ) {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "UPDATE loginSession ".
                        "SET authenticatedEmployee=:authenticatedEmployee ".
                        "WHERE sessionID=:sessionId"
                );

        $success = $stmt->execute(Array(
            ':sessionId' => $session->sessionId,
            ':authenticatedEmployee' => $session->authenticatedEmployee
        ));

        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to update specified session in database"));

        return $session;
    } // writeLoginSession

   /**
     * Authenticates an employee and creates a LoginSession.
     *
     * @param   String  $username   The username of the employee to authenticate.
     * @param   String  $password   The password to use for authentication.
     *
     * @return  LoginSession    A new LoginSession instance for the authenticated employee.
     */
    public function createLoginSession( $username, $password ) {
        // Authenticate the employee based on username/password
        static $loginStmt;
        static $insertStmt;
        if ($loginStmt == null) {
            $loginStmt = $this->dbh->prepare(
                  "SELECT id ".
                    "FROM employee ".
                    "WHERE username=:username ".
                        "AND password=:password "
                );

            $insertStmt = $this->dbh->prepare(
                    "INSERT INTO loginSession ( ".
                            "sessionId, authenticatedEmployee ".
                        ") VALUES ( ".
                            ":sessionId, :authenticatedEmployee ".
                        ")"
                );
        }

        $success = $loginStmt->execute(Array(
                ':username' => $username,
                ':password' => $password
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($loginStmt, "Unable to query database to authenticate employee"));

        $row = $loginStmt->fetchObject();
        if ($row === false)
            throw new Exception("Unable to authenticate employee, incorrect username or password");

        $authenticatedEmployee = $row->id;

        // Generate a new session ID
        // This may be somewhat predictable, but should be strong enough for purposes of the demo
        $sessionId = md5(uniqid(microtime()) . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

        $rv = new LoginSession( $sessionId, $this->readEmployee($authenticatedEmployee) );

        // Create the loginSession record
        $success = $insertStmt->execute(Array(
                ':sessionId' => $sessionId,
                ':authenticatedEmployee' => $authenticatedEmployee
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($insertStmt, "Unable to create session record in database"));

        return $rv;
    } // createLoginSession

    /**
     * Removes a login session from the database.
     * @param   LoginSession    $session    The session to destroy.
     */
    public function destroyLoginSession( LoginSession $session ) {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "DELETE FROM loginSession ".
                        "WHERE sessionId = ?"
                );

        $success = $stmt->execute(Array( $session->sessionId ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to destroy session record"));
    } // destroyLoginSession

    /**
     * Reads a TaxRate record from the database.
     * @param   int $id The ID of the TaxRate to read.
     * @return  TaxRate A TaxRate instance matching the specified ID.
     */
    public function readTaxRate( $id ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, minimumSalary, taxRate ".
                        "FROM taxRate ".
                        "WHERE id = ?"
                );

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for tax rate record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such tax rate: $id");

        return new TaxRate( $row->id, $row->minimumSalary, $row->taxRate );
    } // readTaxRate

    /**
     * Reads all of the defined tax rates from the database.
     * @return  Array[TaxRate]  List of TaxRate instances.
     */
    public function readTaxRates() {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, minimumSalary, taxRate ".
                        "FROM taxRate ".
                        "ORDER BY minimumSalary ASC"
                );

        $success = $stmt->execute();
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for tax rates"));

        $rv = Array();
        while ($row = $stmt->fetchObject())
          $rv[] = new TaxRate( $row->id, $row->minimumSalary, $row->taxRate );

        return $rv;
    } // readTaxRates

    /**
     * Reads a Department record from the database.
     * @param   int $id The ID of the Department to read.
     * @return  Department  A Department instance matching the specified ID.
     */
    public function readDepartment( $id ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, name ".
                        "FROM department ".
                        "WHERE id = ?"
                );

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for department record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such department: $id");

        return new Department( $row->id, $row->name );
    } // readDepartment

    /**
     * Reads all of the defined departments from the database.
     * @return  Array[Department]  List of Department instances.
     */
    public function readDepartments() {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, name ".
                        "FROM department ".
                        "ORDER BY name ASC"
                );

        $success = $stmt->execute();
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for departments"));

        $rv = Array();
        while ($row = $stmt->fetchObject())
          $rv[] = new Department( $row->id, $row->name );

        return $rv;
    } // readDepartments

    /**
     * Reads a Rank record from the database.
     * @param   int $id The ID of the Rank to read.
     * @return  Rank  A Rank instance matching the specified ID.
     */
    public function readRank( $id ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, name, baseSalary, employeeType ".
                        "FROM rank ".
                        "WHERE id = ?"
                );

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for rank record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such rank: $id");

        return new Rank( $row->id, $row->name, $row->baseSalary, EmployeeType::fromName($row->employeeType) );
    } // readRank

    /**
     * Reads all of the defined ranks from the database.
     * @return  Array[Rank]  List of Rank instances.
     */
    public function readRanks() {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, name, baseSalary, employeeType ".
                        "FROM rank ".
                        "ORDER BY name"
                );

        $success = $stmt->execute();
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for ranks"));

        $rv = Array();
        while ($row = $stmt->fetchObject())
          $rv[] = new Rank( $row->id, $row->name, $row->baseSalary, EmployeeType::fromName($row->employeeType) );

        return $rv;
    } // readRanks

    /**
     * Reads all of the departments associated with a paystub.
     * @param   int $paystubId  The ID of the paystub to retrieve the departments for.
     * @return  Array[Department]   Array of the departments for the paystub.
     */
    protected function readDepartmentsForPayStub( $paystubId ) {
        if (!is_numeric($paystubId))
            throw new Exception("Parameter \$paystubId must be an integer");
        $paystubId = (int) $paystubId;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT d.id, d.name ".
                        "FROM paystubDepartmentAssociation a ".
                        "INNER JOIN department d ON d.id = a.department ".
                        "WHERE a.paystub=? ".
                        "ORDER BY d.name"
                );

        $success = $stmt->execute(Array( $paystubId ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for paystub departments"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new Department( $row->id, $row->name );
        } // while

        return $rv;
    } // readDepartmentsForPayStub

    /**
     * Writes the departments associated with a paystub.
     * @param   int $paystubId  The ID of the paystub to write the association records for.
     * @param   Array[Department]   $departments    Array of Departments that associations should be created for.
     */
    protected function writeDepartmentsForPayStub( $paystubId, $departments ) {
        if (!is_numeric($paystubId))
            throw new Exception("\$paystubId must be an integer");
        $paystubId = (int) $paystubId;

        foreach ($departments as $dept) {
            if (!($dept instanceof Department))
                throw new Exception("\$departments must be an array of Department");
        } // foreach

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "INSERT INTO paystubDepartmentAssociation ( ".
                            "paystub, department ".
                        ") VALUES ( ".
                            ":paystub, :department ".
                        ")"
                );

        foreach ($departments as $dept) {
            $success = $stmt->execute(Array(
                    ":paystub" => $paystubId,
                    ":department" => $dept->id
                ));
            if ($success == false)
                throw new Exception($this->formatErrorMessage($stmt, "Unable to write paystubDepartmentAssociation to database"));
        } // foreach
    } // writeDepartmentsForPayStub

    /**
     * Reads a pay stub from the database.
     * @param   int $id The ID of the paystub to retrieve.
     * @return  PayStub A PayStub instance containing the data for the requested pay stub.
     */
    public function readPayStub( $id ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, payPeriodStartDate, employee, name, address, rank, taxId, salary, numDeductions, taxWithheld, taxRate ".
                        "FROM paystub ".
                        "WHERE id = ?"
                );

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for paystub record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such paystub: $id");

        return new PayStub(
                $row->id,
                $row->name,
                $row->address,
                $this->readRank( $row->rank ),
                $row->taxId,
                $this->readDepartmentsForPayStub( $row->id ),
                $row->salary,
                $row->numDeductions,
                $row->taxWithheld,
                $row->taxRate
            );
    } // readPayStub

    /**
     * Writes a pay stub to the database.
     * @param   PayStub $paystub    The pay stub to write.  The id property must be 0.
     * @return  PayStub A new PayStub instance with the id populated.
     */
    public function writePayStub( PayStub $paystub ) {
        if ($paystub->id != 0)
            throw new Exception("The id property of the \$paystub must be 0.  Updating existing pay stubs is not permitted.");

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "INSERT INTO paystub ( ".
                            "name, address, rank, taxId, salary, numDeductions, taxWithheld, taxRate ".
                        ") VALUES ( ".
                            ":name, :address, :rank, :taxId, :salary, :numDeductions, :taxWithheld, :taxRate ".
                        ")"
                );

        $success = $stmt->execute(Array(
                ':name' => $paystub->name,
                ':address' => $paystub->address,
                ':rank' => $paystub->rank->id,
                ':taxId' => $paystub->taxId,
                ':salary' => $paystub->salary,
                ':numDeductions' => $paystub->numDeductions,
                ':taxWithheld' => $paystub->taxWithheld,
                ':taxRate' => $paystub->taxRate
            ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to create paystub record in database"));

        $newId = $this->dbh->lastInsertId();

        $this->writeDepartmentsForPayStub( $newId, $paystub->departments );

        return new PayStub(
                $newId,
                $paystub->name,
                $paystub->address,
                $paystub->rank,
                $paystub->taxId,
                $paystub->departments,
                $paystub->salary,
                $paystub->numDeductions,
                $paystub->taxWithheld,
                $paystub->taxRate
            );
    } // writePayStub

    /**
     * Reads the list of pay stubs for an employee.
     * @param   int $employeeId The ID of the employee to retrieve the paystubs for.
     * @return  Array[PayStub]  Array of PayStub instances.
     */
    public function readPayStubs( $employeeId ) {
        if (!is_numeric($employeeId))
            throw new Exception("Parameter \$employeeId must be an integer");
        $employeeId = (int) $employeeId;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, payPeriodStartDate, employee, name, address, rank, taxId, salary, numDeductions, taxWithheld, taxRate ".
                        "FROM paystub ".
                        "WHERE employee = ?"
                );

        $success = $stmt->execute(Array( $employeeId ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for paystubs"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new PayStub(
                    $row->id,
                    $row->name,
                    $row->address,
                    $this->readRank( $row->rank ),
                    $row->taxId,
                    $this->readDepartmentsForPayStub( $row->id ),
                    $row->salary,
                    $row->numDeductions,
                    $row->taxWithheld,
                    $row->taxRate
                );
        } // while

        return $rv;
    } // readPayStubs

    /**
     * Tests whether a specific username is in currently assigned to an employee or not.
     *
     * @param   String  $username   The username to test for.
     *
     * @return  Boolean    True if the username is assigned to an existing employee, false if not.
     */
    public function isUsernameInUse( $username ) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                  "SELECT id ".
                    "FROM employee ".
                    "WHERE username=:username"
                );
        }

        $success = $stmt->execute(Array(
                ':username' => $username
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for username"));

        $row = $stmt->fetchObject();
        return ($row !== false);
    } // isUsernameInUse

	/**
     * Tests whether a specific taxId is in currently assigned to an employee or not.
     *
     * @param   String  $taxId   The taxId to test for.
     *
     * @return  Boolean    True if the taxId is assigned to an existing employee, false if not.
     */
     public function isTaxIdInUse( $taxId ) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                  "SELECT id ".
                    "FROM employee ".
                    "WHERE taxId=:taxId"
                );
        }

        $success = $stmt->execute(Array(
                ':taxId' => $taxId
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for taxId"));

        $row = $stmt->fetchObject();
        return ($row !== false);
    } // isTaxIdInUse
    
    /**
     * Reads an Employee from the database.
     * @param   int $id The ID of the employee to retrieve.
     * @return  Employee    An instance of Employee.
     */
    public function readEmployee( $id ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, activeFlag, username, password, name, address, rank, taxId, numDeductions, salary ".
                        "FROM employee ".
                        "WHERE id = ?"
                );

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such employee: $id");
		
        return new Employee(
                $row->id,
                $row->activeFlag,
                $row->username,
                $row->password,
                $row->name,
                $row->address,
                $this->readRank( $row->rank ),
                $row->taxId,
                $row->numDeductions,
                $row->salary
            );
    } // readEmployee

    /**
     * Reads a list of all employees from the database.
     * @param   Boolean $activeFlag Whether to retrieve active employees (true) or inactive employees (false)
     * @return  Array[Employee] Array of Employee instances.
     */
    public function readEmployees($activeFlag = true) {
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT id, activeFlag, username, password, name, address, rank, taxId, numDeductions, salary ".
                        "FROM employee ".
                        "WHERE activeFlag=:activeFlag ".
                        "ORDER BY name"
                );

        $success = $stmt->execute(Array(
                ':activeFlag' => $activeFlag
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee records"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new Employee(
                    $row->id,
                    $row->activeFlag,
                    $row->username,
                    $row->password,
                    $row->name,
                    $row->address,
                    $this->readRank( $row->rank ),
                    $row->taxId,
                    $row->numDeductions,
                    $row->salary
                );
        } // while

        return $rv;
    } // readEmployees

    /**
     * Writes an Employee to the database.
     * @param   Employee    $employee   The Employee to write.  If the id property is 0, a new
     *                                  record will be created, otherwise an existing record matching
     *                                  the id will be updated.
     * @return  Employee    A new Employee instance (with the new id if a new record was created).
     */
    public function writeEmployee( Employee $employee ) {
        static $stmtInsert;
        static $stmtUpdate;
        if ($stmtInsert == null) {
            $stmtInsert = $this->dbh->prepare(
                    "INSERT INTO employee ( ".
                            "activeFlag, username, password, name, address, rank, taxId, numDeductions, salary ".
                        ") VALUES ( ".
                            ":activeFlag, :username, :password, :name, :address, :rank, :taxId, :numDeductions, :salary ".
                        ")"
                );

            $stmtUpdate = $this->dbh->prepare(
                    "UPDATE employee SET ".
                            "activeFlag = :activeFlag, ".
                            "username = :username, ".
                            "password = :password, ".
                            "name = :name, ".
                            "address = :address, ".
                            "rank = :rank, ".
                            "taxId = :taxId, ".
                            "numDeductions = :numDeductions, ".
                            "salary = :salary ".
                        "WHERE id = :id"
                );
        }

        $params = Array(
                ':activeFlag' => $employee->activeFlag,
                ':username' => $employee->username,
                ':password' => $employee->password,
                ':name' => $employee->name,
                ':address' => $employee->address,
                ':rank' => $employee->rank->id,
                ':taxId' => $employee->taxId,
                ':numDeductions' => $employee->numDeductions,
                ':salary' => $employee->salary
            );

        if ($employee->id == 0) {
            $stmt = $stmtInsert;
        } else {
            $params[':id'] = $employee->id;
            $stmt = $stmtUpdate;
        }

        $success = $stmt->execute($params);

        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to store employee record in database"));

        if ($employee->id == 0)
            $newId = $this->dbh->lastInsertId();
        else
            $newId = $employee->id;

        return new Employee(
                $newId,
                $employee->activeFlag,
                $employee->username,
                $employee->password,
                $employee->name,
                $employee->address,
                $employee->rank,
                $employee->taxId,
                $employee->numDeductions,
                $employee->salary
            );
    } // writeEmployee

   /**
     * Reads all of the departments associated with an employee.
     * @param   int $employeeId The ID of the employee to retrieve the departments for.
     * @return  Array[Department]   Array of the departments for the employee.
     */
    public function readDepartmentsForEmployee( $employeeId ) {
        if (!is_numeric($employeeId))
            throw new Exception("Parameter \$employeeId must be an integer");
        $employeeId = (int) $employeeId;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT d.id, d.name ".
                        "FROM employeeDepartmentAssociation a ".
                        "INNER JOIN department d ON d.id = a.department ".
                        "WHERE employee=? ".
                        "ORDER BY d.name"
                );

        $success = $stmt->execute(Array( $employeeId ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee departments"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new Department( $row->id, $row->name );
        } // while

        return $rv;
    } // readDepartmentsForEmployee

   /**
     * Reads all of the Employees associated with a Department.
     * @param   int $departmentId The ID of the Department to retrieve the Employees for.
     * @return  Array[Employee]   Array of the Employees for a Department.
     */
    public function readEmployeesForDepartment( $departmentId ) {
        if (!is_numeric($departmentId))
            throw new Exception("Parameter \$departmentId must be an integer");
        $departmentId = (int) $departmentId;

        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT a.employee ".
                        "FROM employeeDepartmentAssociation a ".
                        "INNER JOIN employee e ON e.id = a.employee ".
                        "WHERE department=? ".
                        "ORDER BY e.name"
                );

        $success = $stmt->execute(Array( $departmentId ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for department employees"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = $this->readEmployee( $row->employee );
        } // while

        return $rv;
    } // readEmployeesForDepartment

    /**
     * Writes EmployeeDepartmentAssociation records to the database.
     * @param   Employee   $employee    The employee to update the departments for.
     * @param   Array[Department]   $departments    The list of departments to associate the employee with.
     * @return
     */
    public function writeDepartmentsForEmployee( Employee $employee, $departments ) {
        if ($employee->id == 0)
            throw new Exception("The id property of the employee cannot be 0.");

        if (!is_array($departments))
            throw new Exception("The departments parameter must be an array.");

        foreach ($departments as $dept) {
            if (!($dept instanceof Department))
                throw new Exception("Every element in the departments parameter must be an instance of Department.");

            if ($dept->id == 0)
                throw new Exception("The id property of a department cannot be 0.");
        } // foreach

        static $insertStmt;
        static $deleteStmt;
        if ($insertStmt == null) {
            $insertStmt = $this->dbh->prepare(
                    "INSERT INTO employeeDepartmentAssociation ( ".
                            "employee, department ".
                        ") VALUES ( ".
                            ":employee, :department ".
                        ")"
                );

            $deleteStmt = $this->dbh->prepare(
                    "DELETE FROM employeeDepartmentAssociation ".
                        "WHERE employee = :employee"
                );
        }

        // Remove existing association records for the employee
        $success = $deleteStmt->execute(Array(
                ':employee' => $employee->id
            ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($deleteStmt, "Unable to delete existing employeeDepartmentAssociation records for employee ". $employee->id));

        // Create new association records for the employee
        foreach ($departments as $dept) {
            $success = $insertStmt->execute(Array(
                    ':employee' => $employee->id,
                    ':department' => $dept->id
                ));
            if ($success == false)
                throw new Exception($this->formatErrorMessage($insertStmt, "Unable to create employeeDepartmentAssociation record in database"));
        } // foreach
    } // writeDepartmentsForEmployee

    /**
     * Generates new pay stubs for employees who have not yet had pay stubs generated for the current month.
     * @return  int The number of paystubs which were generated.
     */
    public function generatePayStubs() {
        $currentDate = new DateTime();
        $currentDate->setTimezone(new DateTimeZone('GMT'));

        $payPeriodStartDate = new DateTime( $currentDate->format("Y-m-01T00:00:00P") );

        // Determine which employees need to have pay stubs generated
        static $stmt;
        if ($stmt == null)
            $stmt = $this->dbh->prepare(
                    "SELECT e.id ".
                        "FROM employee e ".
                        "LEFT JOIN paystub p ".
                            "ON p.employee = e.id ".
                                "AND p.payPeriodStartDate = ? ".
                        "WHERE p.id IS NULL"
                );

        $success = $stmt->execute(Array( $payPeriodStartDate->format("Y-m-d H:i:s") ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query employees who need pay stubs generated"));

        $rv = 0;
        while ($row = $stmt->fetchObject) {
            $employee = $this->readEmployee( $row->id );

            $taxWithheld = $this->computeTax($employee->salary, $employee->numDeductions);

            $paystub = new PayStub(0, $payPeriodStartDate, $employee, 
                    $employee->name, $employee->address, $employee->rank, $employee->taxId,
                    $this->readDepartmentsForEmployee( $employee->id ), $employee->salary,
                    $employee->numDeductions,
                    $taxWithheld, $taxWithheld / $employee->salary
                );

            $this->writePaystub( $paystub );
            ++$rv;
        } // while

        return $rv;
    } // generatePayStubs

    /**
     * Computesthe tax owed for a given monthly salart and number of deductions.
     * @param   double  $salary         The monthloy salary earned.
     * @param   int     $numDeductions  The number of deductions claimed.
     * @return  double  The tax owed.
     */
    protected function computeTax($salary, $numDeductions) {
        // TODO: Finish this!
        throw new Exception("NOT IMPLEMENTED: ".  __METHOD__);

        // Annual deduction amounts
        $standardDeduction = 5000;
        $perDeductionAllowance = 1000;

        // Compute the taxable income
        $taxableSalary = $salary - ($standardDeduction + $numDeductions * $perDeductionAllowance) / 12;

        // Compute the tax owed
        $taxOwed = 0;

        $taxRates = $this->getTaxRates();
        foreach ($taxRates as $rate) {

// TODO: Finish this!

        } // foreach

        return $taxOwed;
    } // computeTax($salary, $numDeductions)

} // DBInterface
