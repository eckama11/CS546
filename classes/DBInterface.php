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
        //$this->dbh->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
    } // __construct

    public function formatErrorMessage($stmt, $message) {
        if (!$stmt)
            $stmt = $this->dbh;
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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT sessionId, authenticatedEmployee ".
                        "FROM loginSession ".
                        "WHERE sessionId=?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare login session query"));
        }


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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "UPDATE loginSession ".
                        "SET authenticatedEmployee=:authenticatedEmployee ".
                        "WHERE sessionID=:sessionId"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to login session update"));
        }

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

            if (!$loginStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare login query"));

            $insertStmt = $this->dbh->prepare(
                    "INSERT INTO loginSession ( ".
                            "sessionId, authenticatedEmployee ".
                        ") VALUES ( ".
                            ":sessionId, :authenticatedEmployee ".
                        ")"
                );

            if (!$insertStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare login session insert"));
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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "DELETE FROM loginSession ".
                        "WHERE sessionId = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare login session delete"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, minimumSalary, taxRate ".
                        "FROM taxRate ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare tax rate query"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, minimumSalary, taxRate ".
                        "FROM taxRate ".
                        "ORDER BY minimumSalary ASC"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare tax rates query"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, name ".
                        "FROM department ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare department query"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, name ".
                        "FROM department ".
                        "ORDER BY name ASC"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare departments query"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, name, baseSalary, employeeType ".
                        "FROM rank ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare rank query"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, name, baseSalary, employeeType ".
                        "FROM rank ".
                        "ORDER BY name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare ranks query"));
        }

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT department, departmentName, departmentManagers ".
                        "FROM paystubDepartmentAssociation ".
                        "WHERE paystub=? ".
                        "ORDER BY departmentName"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to paystub departments query"));
        }

        $success = $stmt->execute(Array( $paystubId ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for pay stub departments"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new PayStubDepartment( $row->department, $row->departmentName, explode("\n", $row->departmentManagers) );
        } // while

        return $rv;
    } // readDepartmentsForPayStub

    /**
     * Writes the departments associated with a pay stub.
     * @param   int $paystubId  The ID of the pay stub to write the association records for.
     * @param   Array[PayStubDepartment|Department]   $departments    Array of PayStubDepartments or Departments that associations should be created for.
     */
    protected function writeDepartmentsForPayStub( $paystubId, $departments ) {
        if (!is_numeric($paystubId))
            throw new Exception("\$paystubId must be an integer");
        $paystubId = (int) $paystubId;

        for ($i = count($departments) - 1; $i >= 0; --$i) {
            $dept = $departments[$i];
            if (!($dept instanceof PayStubDepartment)) {
                if ($dept instanceof Department)
                    $departments[$i] = $this->departmentToPayStubDepartment($dept);
                else
                    throw new Exception("\$departments must be an array of Department");
            }
        } // for

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "INSERT INTO paystubDepartmentAssociation ( ".
                            "paystub, department, departmentName, departmentManagers ".
                        ") VALUES ( ".
                            ":paystub, :department, :departmentName, :departmentManagers ".
                        ")"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to paystub departments update"));
        }

        foreach ($departments as $dept) {
            $success = $stmt->execute(Array(
                    ":paystub" => $paystubId,
                    ":department" => $dept->id,
                    ":departmentName" => $dept->name,
                    ":departmentManagers" => implode("\n", $dept->managers)
                ));
            if ($success == false)
                throw new Exception($this->formatErrorMessage($stmt, "Unable to write paystubDepartmentAssociation to database"));
        } // foreach
    } // writeDepartmentsForPayStub

    /**
     * Creates a PayStubDepartment from a Department instance.
     * @param   Department $department  The Department to convert to a PayStubDepartment.
     * @return  A new PayStubDepartment instance.
     */
    public function departmentToPayStubDepartment(Department $department) {
        $managers = $this->readEmployeesForDepartment($department->id, EmployeeType::Manager());
        $managers = array_map(function($mgr) { return $mgr->name; }, $managers);
        return new PayStubDepartment( $department->id, $department->name, $managers );
    } // departmentToPayStubDepartment

    private static $payStubColumns = Array(
                "payPeriodStartDate",
                "payPeriodEndDate",
                "employee",
                "name",
                "address",
                "rank",
                "employeeType",
                "taxId",
                "salary",
                "numDeductions",
                "taxWithheld",
                "taxRate",
                "deductions",
                "salaryYTD",
                "taxWithheldYTD",
                "deductionsYTD"
            );

    private function _rowToPayStub($row) {
        return new PayStub(
                    $row->id,
                    new DateTime( $row->payPeriodStartDate ),
                    new DateTime( $row->payPeriodEndDate ),
                    $this->readEmployee( $row->employee ),
                    $row->name,
                    $row->address,
                    $row->rank,
                    $row->employeeType,
                    $row->taxId,
                    $this->readDepartmentsForPayStub( $row->id ),
                    $row->salary,
                    $row->numDeductions,
                    $row->taxWithheld,
                    $row->taxRate,
                    $row->deductions,
                    $row->salaryYTD,
                    $row->taxWithheldYTD,
                    $row->deductionsYTD
                );
    } // _rowToPayStub()

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, ". implode(", ", self::$payStubColumns) ." ".
                        "FROM paystub ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to pay stub query"));
        }

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for paystub record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such paystub: $id");

        return $this->_rowToPayStub($row);
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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "INSERT INTO paystub ( ".
                            implode(", ", self::$payStubColumns) .
                        ") VALUES ( ".
                            ":payPeriodStartDate, :payPeriodEndDate, :employeeId, :name, ".
                            ":address, :rank, :employeeType, :taxId, :salary, :numDeductions, ".
                            ":taxWithheld, :taxRate, :deductions, :salaryYTD, ".
                            ":taxWithheldYTD, :deductionsYTD ".
                        ")"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare pay stub update"));
        }

        $success = $stmt->execute(Array(
                ':payPeriodStartDate' => $paystub->payPeriodStartDate->format("Y-m-d"),
                ':payPeriodEndDate' => $paystub->payPeriodEndDate->format("Y-m-d"),
                ':employeeId' => $paystub->employee->id,
                ':name' => $paystub->name,
                ':address' => $paystub->address,
                ':rank' => $paystub->rank,
                ':employeeType' => $paystub->employeeType,
                ':taxId' => $paystub->taxId,
                ':salary' => $paystub->salary,
                ':numDeductions' => $paystub->numDeductions,
                ':taxWithheld' => $paystub->taxWithheld,
                ':taxRate' => $paystub->taxRate,
                ':deductions' => $paystub->deductions,
                ':salaryYTD' => $paystub->salaryYTD,
                ':taxWithheldYTD' => $paystub->taxWithheldYTD,
                ':deductionsYTD' => $paystub->deductionsYTD
            ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to create pay stub record in database"));

        $newId = $this->dbh->lastInsertId();

        $this->writeDepartmentsForPayStub( $newId, $paystub->departments );

        return new PayStub(
                $newId,
                $paystub->payPeriodStartDate,
                $paystub->payPeriodEndDate,
                $paystub->employee,
                $paystub->name,
                $paystub->address,
                $paystub->rank,
                $paystub->employeeType,
                $paystub->taxId,
                $paystub->departments,
                $paystub->salary,
                $paystub->numDeductions,
                $paystub->taxWithheld,
                $paystub->taxRate,
                $paystub->deductions,
                $paystub->salaryYTD,
                $paystub->taxWithheldYTD,
                $paystub->deductionsYTD
            );
    } // writePayStub

    /**
     * Reads the list of pay stubs for an employee.
     *
     * @param   int              $employeeId The ID of the employee to retrieve the paystubs for.
     * @param   DateTime|String  $afterDate  Only return pay stubs generated on or after the specified date.
     * @param   DateTime|String  $beforeDate Only return pay stubs generated before, but not including, the specified date.
     *
     * @return  Array[PayStub]  Array of PayStub instances.
     */
    public function readPayStubs( $employeeId, $afterDate = null, $beforeDate = null ) {
        if (!is_numeric($employeeId))
            throw new Exception("Parameter \$employeeId must be an integer");
        $employeeId = (int) $employeeId;

        if ($afterDate == null)
            $afterDate = new DateTime('1900-01-01 00:00:00');
        else if (!($afterDate instanceof DateTime))
            $afterDate = new DateTime($afterDate);

        if ($beforeDate == null)
            $beforeDate = new DateTime('9999-12-31 23:59:59');
        else if (!($beforeDate instanceof DateTime))
            $beforeDate = new DateTime($beforeDate);

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, ". implode(", ", self::$payStubColumns) ." ".
                        "FROM paystub ".
                        "WHERE employee = :employeeId ".
                            "AND payPeriodStartDate >= :afterDate ".
                            "AND payPeriodEndDate < :beforeDate ".
                        "ORDER BY payPeriodStartDate ASC"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to pay stub query"));
        }

        $success = $stmt->execute(Array(
                        ':employeeId' => $employeeId,
                        ':afterDate' => $afterDate->format('Y-m-d'),
                        ':beforeDate' => $beforeDate->format('Y-m-d')
                    ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for pay stubs"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = $this->_rowToPayStub($row);
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

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare username query"));
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
     * @param   String  $taxId              The taxId to test for.
     * @param   int     $ignoreEmployeeId   The ID of an Employee entry to ignore (eg. typically,
     *                                      the Employee being updated)
     *
     * @return  int    Returns the employee ID of the employee the tax ID is assigned to if found,
     *                  false if the tax ID is not assigned to an existing employee.
     */
     public function isTaxIdInUse( $taxId, $ignoreEmployeeId = null ) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                  "SELECT id ".
                    "FROM employee ".
                    "WHERE taxId=:taxId"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare taxId query"));
        }

        $success = $stmt->execute(Array(
                ':taxId' => $taxId
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for taxId"));

        $row = $stmt->fetchObject();
        if ($row == false)
            return false;

        return $row->id;
    } // isTaxIdInUse

    /**
     * Reads employee history data.
     *
     * @param   int         $historyId  The ID of a EmployeeHistoryrecord to retrieve.  If this
     *                                  parameter is not null, the $startDate, $endDate, and $limit
     *                                  parameters must be null.
     * @param   int         $employeeId The ID of the employee to retrieve history for.  If
     *                                  $historyId is also specified, the $employeeId must match
     *                                  the employee on the specified history record.
     * @param   DateTime    $startDate  When provided, the starting date to filter by.
     *                                  Only records with an end date greater than or equal to the
     *                                  date provided or with no end date specified will be
     *                                  returned.
     * @param   DateTime    $endDate    If provided, the ending date to filter by.  Only records
     *                                  with a start date less than or equal to the provided date
     *                                  will be returned.
     * @param   int         $limit      The number of records to return, most recent first.  If not
     *                                  specified or null, no limit is applied.
     *
     * @return  A single EmployeeHistory instance when $historyId is specified, otherwise,
     *          an array of EmployeeHistory instances that match the specified parameters.
     */
    public function readEmployeeHistory(
                        $historyId,
                        $employeeId = null,
                        DateTime $startDate = null,
                        DateTime $endDate = null,
                        $limit = null
                    )
    {
        if ($historyId != null) {
            if (!is_numeric($historyId))
                throw new Exception("Parameter \$historyId must be an integer");
            $historyId = (int) $historyId;

            if (($startDate != null) || ($endDate != null) || ($limit != null))
                throw new Exception("Parameter \$historyId must be null if any other arguments are non-null.");
        }

        if ($employeeId != null) {
            if (!is_numeric($employeeId))
                throw new Exception("Parameter \$employeeId must be an integer");
            $employeeId = (int) $employeeId;
        }

        if ($limit !== null) {
            if (!is_numeric($limit) || ($limit <= 0))
                throw new Exception("Parameter \$limit must be a positive integer");
            $limit = (int) $limit;
        } else
            $limit = PHP_INT_MAX;

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, startDate, endDate, lastPayPeriodEndDate, rank, numDeductions, salary ".
                        "FROM employeeHistory ".
                        "WHERE (id = :historyId AND (employee = :employeeId OR :employeeId IS NULL)) ".
                            "OR (".
                                "employee = :employeeId ".
                                "AND startDate <= :endDate ".
                                "AND (endDate >= :startDate OR endDate IS NULL) ".
                            ") ".
                        "ORDER BY startDate DESC ".
                        "LIMIT :limit"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee history query"));
        }

        if ($historyId == null) {
            if ($startDate == null)
                $startDate = new DateTime('1900-01-01');
            $startDate = $startDate->format("Y-m-d");

            if ($endDate == null)
                $endDate = new DateTime('9999-12-31');
            $endDate = $endDate->format("Y-m-d");
        }

        // bindParam must be used here because :limit param must be bound as an int
        // to function properly
        $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
        $stmt->bindParam(':historyId', $historyId);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $success = $stmt->execute();
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee history"));

        $rv = [];
        while ($row = $stmt->fetchObject()) {
            $startDate = new DateTime( $row->startDate );

            $rv[] = new EmployeeHistory(
                $row->id,
                $startDate,
                ($row->endDate
                    ? new DateTime( $row->endDate )
                    : null),
                ($row->lastPayPeriodEndDate
                    ? new DateTime ( $row->lastPayPeriodEndDate )
                    : null),
                $this->readDepartmentsForEmployeeHistory( $row->id ),
                $this->readRank( $row->rank ),
                $row->numDeductions,
                $row->salary
            );
        } // while

        if ($historyId != null) {
            if (count($rv) != 1)
                throw new Exception("No such employee history: $historyId");
            $rv = $rv[0];
        }

        return $rv;
    } // readEmployeeHistory

    /**
     * Reads an Employee from the database.
     *
     * @param   int $id The ID of the employee to retrieve.
     * @param   DateTime|null $effectiveDate The date for which to determine the value for the
     *              current property.  If a DateTime is given, current will be set to the
     *              EmployeeHistory record that is active for the specified date, or null if no
     *              such record exists.  If not specified, defaults to the current date.
     *
     * @return  Employee    An instance of Employee.
     */
    public function readEmployee( $id, DateTime $effectiveDate = null ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        if ($effectiveDate === null)
            $effectiveDate = new DateTime();

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, username, password, name, address, taxId ".
                        "FROM employee ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee query"));
        }

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such employee: $id");
		
        $current = $this->readEmployeeHistory( null, $row->id, $effectiveDate, $effectiveDate, 1 );
        if (count($current) == 0)
            $current = null;
        else
            $current = $current[0];

        return new Employee(
                $row->id,
                $row->username,
                $row->password,
                $row->name,
                $row->address,
                $row->taxId,
                $current
            );
    } // readEmployee

    /**
     * Reads a list of all employees from the database.
     *
     * @param   DateTime|null|false $effectiveDate
     *                                  Determines which employees are returned.  If a DateTime is
     *                                  given, only employees that are active for that date are
     *                                  returned.  If null or not specified, all employees are
     *                                  returned, and if false, returns inactive employees only.
     * @param   int     $projectId      The ID of a project to filter employees by.  If specified,
     *                                  $departmentId must also be provided.
     * @param   int     $departmentId   The ID of department associated with the project.  If specified,
     *                                  $projectId must also be provided.
     * @param   boolean $getUnassigned  When a $projectId and $departmentId are provided, whether to
     *                                  return employees that are associated to the project via the
     *                                  department (false), or to return employees associated with the
     *                                  department that are not assigned to the project (true).
     *
     * @return  Array[Employee] Array of matching Employee instances.
     */
    public function readEmployees($effectiveDate = null, $projectId = null, $departmentId = null, $getUnassigned = false) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT e.id, e.username, e.password, e.name, e.address, e.taxId ".
                        "FROM employee e ".
                        "LEFT JOIN employeeHistory h ".
                            "ON h.employee = e.id ".
                                "AND h.startDate <= :effectiveDate ".
                                "AND (h.endDate IS NULL OR h.endDate >= :effectiveDate) ".
                        "LEFT JOIN employeeDepartmentAssociation eda ".
                            "ON eda.employeeHistory = h.id ".
                                "AND eda.department = :departmentId ".
                        "LEFT JOIN projectEmployeeAssociation pea ".
                            "ON pea.project = :projectId ".
                                "AND pea.department = :departmentId ".
                                "AND pea.employee = e.id ".
                        "WHERE ( ".
                                ":returnAll = 1 ".
                                "OR CASE WHEN (h.id IS NOT NULL) THEN 1 ELSE 0 END = :activeFlag ".
                            ") AND ( ".
                                "(:projectId IS NULL) ".
                                "OR ".
                                "( :getUnassigned = 0 AND pea.id IS NOT NULL ) ". // Return assigned
                                "OR ".
                                "( :getUnassigned = 1 AND eda.employeeHistory IS NOT NULL AND pea.id IS NULL ) ". // Return unassigned
                            ") ".
                        "ORDER BY name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employees query"));
        }

        if ($projectId !== null) {
            if (!is_numeric($projectId))
                throw new Exception("Parameter \$projectId must be an integer");
            $projectId = (int) $projectId;
        } else if ($departmentId !== null)
            throw new Exception("The \$projectId must be provided if a \$departmentId is given");

        if ($departmentId !== null) {
            if (!is_numeric($departmentId))
                throw new Exception("Parameter \$departmentId must be an integer");
            $departmentId = (int) $departmentId;
        } else if ($projectId !== null)
            throw new Exception("The \$departmentId must be provided if a \$projectId is given");

        $returnAll = false;
        $activeFlag = true;
        if ($effectiveDate === null) {
            $returnAll = true;
            $effectiveDate = new DateTime();
        } else if ($effectiveDate === false) {
            $effectiveDate = new DateTime('9999-12-31 23:59:59');
            $activeFlag = false;
        } else if (!($effectiveDate instanceof DateTime))
            throw new Exception("The \$effectiveDate parameter must be either null, false, or a DateTime instance.");

        $success = $stmt->execute(Array(
                ':effectiveDate' => $effectiveDate->format('Y-m-d'),
                ':activeFlag' => ($activeFlag ? 1 : 0),
                ':returnAll' => ($returnAll ? 1 : 0),
                ':projectId' => $projectId,
                ':departmentId' => $departmentId,
                ':getUnassigned' => ($getUnassigned ? 1 : 0),
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee records"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $current = $this->readEmployeeHistory( null, $row->id, $effectiveDate, $effectiveDate, 1 );
            if (count($current) == 0)
                $current = null;
            else
                $current = $current[0];

            $rv[] = new Employee(
                    $row->id,
                    $row->username,
                    $row->password,
                    $row->name,
                    $row->address,
                    $row->taxId,
                    $current
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
                            "username, password, name, address, taxId ".
                        ") VALUES ( ".
                            ":username, :password, :name, :address, :taxId ".
                        ")"
                );

            if (!$stmtInsert)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee insert"));

            $stmtUpdate = $this->dbh->prepare(
                    "UPDATE employee SET ".
                            "username = :username, ".
                            "password = :password, ".
                            "name = :name, ".
                            "address = :address, ".
                            "taxId = :taxId ".
                        "WHERE id = :id"
                );

            if (!$stmtUpdate)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee update"));
        }

        $params = Array(
                ':username' => $employee->username,
                ':password' => $employee->password,
                ':name' => $employee->name,
                ':address' => $employee->address,
                ':taxId' => $employee->taxId
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

        $current = $this->writeEmployeeHistory( $newId, $employee->current );

        return new Employee(
                $newId,
                $employee->username,
                $employee->password,
                $employee->name,
                $employee->address,
                $employee->taxId,
                $current
            );
    } // writeEmployee

    private function _employeeHistoryToParams($entry) {
        return Array(
                ":startDate" => $entry->startDate->format("Y-m-d"),
                ":endDate" => ($entry->endDate
                                ? $entry->endDate->format("Y-m-d")
                                : null),
                ":lastPayPeriodEndDate" => ($entry->lastPayPeriodEndDate
                                                ? $entry->lastPayPeriodEndDate->format("Y-m-d")
                                                : null),
                ":rank" => $entry->rank->id,
                ":numDeductions" => $entry->numDeductions,
                ":salary" => $entry->salary
            );
    } // _employeeHistoryToParams

    /**
     * Writes an EmployeeHistory entry for an employee.
     *
     * @param {int} $employeeId     The ID of the employee to write the record for.
     * @param {EmployeeHistory} $entry The EmployeeHistory entry to write.
     *
     * @return  Returns a new EmployeeHistory instance (with a new id if a new record was created).
     */
    public function writeEmployeeHistory( $employeeId, EmployeeHistory $entry ) {
        static $stmtInsert;
        static $stmtUpdate;
        if ($stmtInsert == null) {
            $stmtInsert = $this->dbh->prepare(
                    "INSERT INTO employeeHistory ( ".
                            "employee, startDate, endDate, lastPayPeriodEndDate, ".
                            "rank, numDeductions, salary ".
                        ") VALUES ( ".
                            ":employee, :startDate, :endDate, :lastPayPeriodEndDate, ".
                            ":rank, :numDeductions, :salary ".
                        ")"
                );

            if (!$stmtInsert)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee history insert"));

            $stmtUpdate = $this->dbh->prepare(
                    "UPDATE employeeHistory SET ".
                            "startDate = :startDate, ".
                            "endDate = :endDate, ".
                            "lastPayPeriodEndDate = :lastPayPeriodEndDate, ".
                            "rank = :rank, ".
                            "numDeductions = :numDeductions, ".
                            "salary = :salary ".
                        "WHERE id = :id"
                );

            if (!$stmtUpdate)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee history update"));
        }

        if (!is_numeric($employeeId))
            throw new Exception("Parameter \$employeeId must be an integer when inserting a new history record.");
        $employeeId = (int) $employeeId;

        // Validate the new properties by reading the existing state from the DB
        $history = $this->readEmployeeHistory( null, $employeeId, $entry->startDate, $entry->endDate );

        $verifyDepartments = function($listA, $listB) {
            if (count($listA) != count($listB))
                return false;
            $mapper = function($item) { return $item->id; };
            $listA = array_map($mapper, $listA);
            $listB = array_map($mapper, $listB);
            return count(array_intersect($listA, $listB)) == count($listA);
        };

        foreach ($history as $e) {
            if ($entry->id == $e->id) {
                if ($e->lastPayPeriodEndDate) {
                    // May only modify the endDate or lastPayPeriodEndDate
                    $err = null;
                    if ($entry->startDate != $e->startDate)
                        $err = "startDate";
                    else if ($entry->rank->id != $e->rank->id)
                        $err = "rank";
                    else if ($entry->numDeductions != $e->numDeductions)
                        $err = "numDeductions";
                    else if ($entry->salary != $e->salary)
                        $err = "salary";
                    else if (!$verifyDepartments($entry->departments, $e->departments))
                        $err = "departments";

                    if ($err)
                        throw new Exception("Modification of '$err' property is not supported when last payPeriodEndDate is set.");
                }
            } else {
                throw new Exception("Employee history ". ($entry->id ? "update" : "insert") ." conflicts with an existing entry.");
            }
        } // foreach

        $params = $this->_employeeHistoryToParams($entry);

        if ($entry->id) {
            $stmt = $stmtUpdate;
            $params[':id'] = $entry->id;
        } else {
            $stmt = $stmtInsert;
            $params[':employee'] = $employeeId;
        }

        $success = $stmt->execute($params);

        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to ". ($entry->id ? "update" : "insert") ." employee history record in database"));

        if ($entry->id)
            $newId = $entry->id;
        else
            $newId = $this->dbh->lastInsertId();

        // Create/update the department associations
        $this->writeDepartmentsForEmployeeHistory($newId, $entry->departments);

        return new EmployeeHistory(
                $newId,
                $entry->startDate,
                $entry->endDate,
                $entry->lastPayPeriodEndDate,
                $entry->departments,
                $entry->rank,
                $entry->numDeductions,
                $entry->salary
            );
    } // writeEmployeeHistory( $employeeId, EmployeeHistory $entry )

   /**
     * Reads all of the departments associated with an employee on a given date.
     *
     * @param   DateTime|int $dateOrHistoryId  The date to retrieve the departments for, or the ID of the EmployeeHistory entry to retrieve departments for.
     * @param   int $employeeId The ID of the employee to retrieve the departments for.  Must be specified if $dateOrHistoryId contains a date, and must be omitted or null otherwise.
     *
     * @return  Array[Department]   Array of the departments for the employee.
     */
    public function readDepartmentsForEmployeeHistory( $dateOrHistoryId, $employeeId = null ) {
        $date = null;
        $historyId = null;
        if ($dateOrHistoryId instanceof DateTime) {
            $date = $dateOrHistoryId;

            if (!is_numeric($employeeId))
                throw new Exception("Parameter \$employeeId must be an integer");
            $employeeId = (int) $employeeId;
        } else {
            if (!is_numeric($dateOrHistoryId))
                throw new Exception("Parameter \$dateOrHistoryId must be an integer or an instance of DateTime");
            $historyId = (int) $dateOrHistoryId;

            if ($employeeId != null)
                throw new Exception("The \$employeeId parameter cannot be specified if a history ID is provided");
        }

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT d.id, d.name ".
                        "FROM employeeHistory h ".
                        "INNER JOIN employeeDepartmentAssociation a ".
                            "ON a.employeeHistory = h.id ".
                        "INNER JOIN department d ON d.id = a.department ".
                        "WHERE  ".
                            "(h.id = :historyId) ".
                            "OR (".
                                "(h.employee = :employeeId) AND ".
                                "(h.startDate <= :date) AND ".
                                "((h.endDate IS NULL) OR (h.endDate >= :date)) ".
                            ") ".
                        "ORDER BY d.name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee departments query"));
        }

        $success = $stmt->execute(Array(
                            ':employeeId' => $employeeId,
                            ':date' => ($date ? $date->format("Y-m-d") : null),
                            ':historyId' => $historyId
                        ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee departments"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new Department( $row->id, $row->name );
        } // while

        return $rv;
    } // readDepartmentsForEmployeeHistory

   /**
     * Reads all of the Employees associated with a Department.
     * @param   int $departmentId The ID of the Department to retrieve the Employees for.
     * @param   EmployeeType $employeeType Type of employees to return.  If not specified,
     *                     all employees associated with the department will be returned.
     * @param   DateTime    $startDate  The starting date to return matches for.  If not provided, the
     *                                  current date will be used.
     * @param   DateTime    $endDate    The ending date to return matches for.  If not provided, the
     *                                  $startDate will be used.
     * @return  Array[Employee]   Array of the Employees for a Department.
     */
    public function readEmployeesForDepartment( $departmentId, EmployeeType $employeeType = null, DateTime $startDate = null, DateTime $endDate = null ) {
        if (!is_numeric($departmentId))
            throw new Exception("Parameter \$departmentId must be an integer");
        $departmentId = (int) $departmentId;

        if ($startDate === null)
            $startDate = new DateTime();

        if ($endDate === null)
            $endDate = $startDate;

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT DISTINCT h.employee ".
                        "FROM ( ".
                            "SELECT :startDate as startDate, :endDate as endDate, :employeeType as employeeType, :departmentId as departmentId ".
                        ") s ".
                        "INNER JOIN employeeDepartmentAssociation a ".
                            "ON a.department = s.departmentId ".
                        "INNER JOIN employeeHistory h ".
                            "ON h.id = a.employeeHistory ".
                                "AND h.startDate <= s.endDate ".
                                "AND (h.endDate >= s.startDate OR h.endDate IS NULL) ".
                        "INNER JOIN rank r ".
                            "ON r.id = h.rank ".
                                "AND (s.employeeType IS NULL OR r.employeeType = s.employeeType) ".
                        "INNER JOIN employee e ".
                            "ON e.id = h.employee ".
                        "ORDER BY e.name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare department employees query"));
        }

        $success = $stmt->execute(Array(
                ':startDate' => $startDate->format("Y-m-d"),
                ':endDate' => $endDate->format("Y-m-d"),
                ':employeeType' => ($employeeType == null ? null : $employeeType->name),
                ':departmentId' => $departmentId,
            ));
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
     * @param   int   $employeeHistoryId    The employee history record id to update the departments for.
     * @param   Array[Department]   $departments    The list of departments to associate the employee with.
     */
    public function writeDepartmentsForEmployeeHistory( $employeeHistoryId, $departments ) {
        if (!is_numeric($employeeHistoryId))
            throw new Exception("Parameter \$employeeHistoryId must be an integer");
        $employeeHistoryId = (int) $employeeHistoryId;

        if (!is_array($departments))
            throw new Exception("The \$departments parameter must be an array.");

        foreach ($departments as $dept) {
            if (!($dept instanceof Department))
                throw new Exception("Every element in the \$departments parameter must be an instance of Department.");

            if ($dept->id == 0)
                throw new Exception("The id property of a department cannot be 0.");
        } // foreach

        static $insertStmt;
        static $deleteStmt;
        if ($insertStmt == null) {
            $insertStmt = $this->dbh->prepare(
                    "INSERT INTO employeeDepartmentAssociation ( ".
                            "employeeHistory, department ".
                        ") VALUES ( ".
                            ":employeeHistoryId, :department ".
                        ")"
                );


            if (!$insertStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee department insert"));

            $deleteStmt = $this->dbh->prepare(
                    "DELETE FROM employeeDepartmentAssociation ".
                        "WHERE employeeHistory = :employeeHistoryId"
                );

            if (!$deleteStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee department delete"));
        }

        // Remove existing association records for the employee
        $success = $deleteStmt->execute(Array(
                ':employeeHistoryId' => $employeeHistoryId
            ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($deleteStmt, "Unable to delete existing employeeDepartmentAssociation records"));

        // Create new association records for the employee
        foreach ($departments as $dept) {
            $success = $insertStmt->execute(Array(
                    ':employeeHistoryId' => $employeeHistoryId,
                    ':department' => $dept->id
                ));
            if ($success == false)
                throw new Exception($this->formatErrorMessage($insertStmt, "Unable to create employeeDepartmentAssociation record in database"));
        } // foreach
    } // writeDepartmentsForEmployeeHistory

    /**
     * Generates new pay stubs for employees who have not yet had pay stubs generated for the current month.
     *
     * @param   $generationDate The effective date to generate pay stubs for.  If null, will
     *                          use the current date.  May be a date string or a DateTime instance.
     *
     * @return  Object  An object with the following properties:
     *                      int      numGenerated    The number of pay stubs which were generated.
     *                      DateTime startDate       The pay period start date
     *                      DateTime endDate         The pay period end date
     */
    public function generatePayStubs( $generationDate ) {
        if ($generationDate == null) {
            $generationDate = new DateTime();
            $generationDate->setTimezone(new DateTimeZone('GMT'));
        } else if (!($generationDate instanceof DateTime))
            $generationDate = new DateTime($generationDate);

        $payPeriodStartDate = new DateTime( $generationDate->format("Y-m-01T00:00:00P") );

        $payPeriodEndDate = (clone $payPeriodStartDate);
        $payPeriodEndDate->add(new DateInterval('P1M'))->sub(new DateInterval('P1D'));

        $firstOfYear = new DateTime( $generationDate->format("Y-01-01T00:00:00P") );

        // Determine which employees need to have pay stubs generated
        static $stmtEmployee;
        static $stmtProject;
        if ($stmtEmployee == null) {
            $stmtEmployee = $this->dbh->prepare(
                    "SELECT e.id ".
                        "FROM employee e ".
                        "WHERE NOT EXISTS (".
                                "SELECT * ".
                                    "FROM paystub p ".
                                    "WHERE p.employee = e.id ".
                                        "AND p.payPeriodStartDate >= :payPeriodStartDate ".
                            ") ".
                            "AND EXISTS ( ".
                                "SELECT * ".
                                    "FROM employeeHistory h ".
                                    "WHERE h.employee = e.id ".
                                        "AND h.startDate <= :payPeriodEndDate ".
                                        "AND ((h.endDate IS NULL) OR (h.endDate >= :payPeriodStartDate)) ".
                            ")"
                );

            if (!$stmtEmployee)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare generate pay stub employee query"));

            $stmtProject = $this->dbh->prepare(
                    "SELECT p.id, p.startDate, p.endDate, p.otherCosts ".
                        "FROM project p ".
                        "WHERE NOT EXISTS ( ".
                                "SELECT * ".
                                    "FROM projectCostHistory h ".
                                    "WHERE h.project = p.id ".
                                        "AND h.startDate <= :payPeriodEndDate ".
                                        "AND h.endDate >= :payPeriodStartDate ".
                            ") ".
                            "AND p.startDate <= :payPeriodEndDate ".
                            "AND p.endDate >= :payPeriodStartDate"
                );

            if (!$stmtProject)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare generate pay stub project query"));
        }

        // Compute "other" (non-employee) project costs for the pay period
        $success = $stmtProject->execute(Array(
                        ':payPeriodStartDate' => $payPeriodStartDate->format("Y-m-d"),
                        ':payPeriodEndDate' => $payPeriodEndDate->format("Y-m-d")
                    ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmtProject, "Unable to query projects which need costs generated"));

        while ($row = $stmtProject->fetchObject()) {
            $startDate = new DateTime($row->startDate);
            $startDate = ($payPeriodStartDate >= $startDate ? $payPeriodStartDate : $startDate);

            $endDate = new DateTime($row->endDate);
            $endDate = ($payPeriodEndDate >= $endDate ? $payPeriodEndDate : $endDate);

            $this->writeProjectCostHistory(
                    new ProjectCostHistory(
                        $startDate,
                        $endDate,
                        null,
                        $this->readProject($row->id),
                        null,
                        $row->otherCosts * 12 * $this->portionOfYear($startDate, $endDate)
                    )
                );
        } // while

        // Process each employee
        $success = $stmtEmployee->execute(Array(
                        ':payPeriodStartDate' => $payPeriodStartDate->format("Y-m-d"),
                        ':payPeriodEndDate' => $payPeriodEndDate->format("Y-m-d")
                    ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmtEmployee, "Unable to query employees who need pay stubs generated"));

        $numGenerated = 0;
        while ($row = $stmtEmployee->fetchObject()) {
            $employee = $this->readEmployee( $row->id );
            $history = $this->readEmployeeHistory( null, $row->id, $payPeriodStartDate, $payPeriodEndDate );

            // This first call just gets an entry with all zero values
            // Subsequent calls will add onto these values
            $tax = $this->computeTax($payPeriodStartDate, $payPeriodEndDate, null, null, null);
            foreach ($history as $entry) {
                $tax = $this->computeTax($payPeriodStartDate, $payPeriodEndDate, $employee, $entry, $tax);

                $entry->lastPayPeriodEndDate = $payPeriodEndDate;
                $this->writeEmployeeHistory( $employee->id, $entry );
            } // foreach

            $effectiveTaxRate = (($tax->salary > 0) ? $tax->tax / $tax->salary : 0);

            // Read previous pay stub for YTD information
            $salaryYTD = $tax->salary;
            $taxWithheldYTD = $tax->tax;
            $deductionsYTD = $tax->deductions;

            $lastPaystub = $this->readPayStubs($employee->id, $firstOfYear, $payPeriodStartDate);
            if (count($lastPaystub)) {
                $lastPaystub = $lastPaystub[count($lastPaystub) - 1];

                $salaryYTD += $lastPaystub->salaryYTD;
                $taxWithheldYTD += $lastPaystub->taxWithheldYTD;
                $deductionsYTD += $lastPaystub->deductionsYTD;
            }

            $current = $history[count($history) - 1];

            $departments = $this->readDepartmentsForEmployeeHistory( $current->id );
            $departments = array_map(function($dept) { return $this->departmentToPaystubDepartment($dept); }, $departments);

            $paystub = new PayStub(
                    0,
                    $payPeriodStartDate,
                    $payPeriodEndDate,
                    $employee, 
                    $employee->name,
                    $employee->address,
                    $current->rank,
                    $current->rank->employeeType,
                    $employee->taxId,
                    $departments,
                    $tax->salary,
                    $current->numDeductions,
                    $tax->tax,
                    $effectiveTaxRate,
                    $tax->deductions,
                    $salaryYTD,
                    $taxWithheldYTD,
                    $deductionsYTD
                );

            $paystub = $this->writePaystub( $paystub );

            // Write the cost history records, now that we know the pay stub id
            foreach ($tax->projectCosts as $entry) {
                $entry->payStub = $paystub;
                $this->writeProjectCostHistory($entry);
            } // foreach

            ++$numGenerated;
        } // while

        return (Object)[
                "numGenerated" => $numGenerated,
                "generationDate" => $generationDate,
                "startDate" => $payPeriodStartDate,
                "endDate" => $payPeriodEndDate
            ];
    } // generatePayStubs

    protected function portionOfYear($startDate, $endDate) {
        $daysInRange = $endDate->diff($startDate)->format("%d") + 1;
        return $daysInRange / 365;
    }

    /**
     * Computes the tax owed for a given monthly salary and number of deductions as well as updating related project costs.
     *
     * @param   DateTime        $startDate  The starting date of the pay period to compute the tax
     *                                      for.  The greater of the $entry->startDate and this date
     *                                      will be used for the actual computation.
     * @param   DateTime        $endDate    The ending date of the pay period to compute the tax
     *                                      for.  The lesser of the $entry->endDate and this date
     *                                      will be used for the actual computation.
     * @param   Employee        $employee   The employee to compute the tax for
     * @param   EmployeeHistory $entry      An employee history entry to compute the tax for
     * @param   Object          $prevTax    A previous result object from this function (for cumulative totals).
     *
     * @return  Object  An object with the following properties:
     *                  {
     *                      "salary"
     *                      "tax"
     *                      "taxableSalary"
     *                      "deductions"
     *                      "projectCosts"
     *                  }
     */
    protected function computeTax(DateTime $startDate, DateTime $endDate, $employee, EmployeeHistory $entry = null, $prevTax = null) {
        if ($entry == null) {
            return (Object)[
                    'salary' => 0,
                    'tax' => 0,
                    'taxableSalary' => 0,
                    'deductions' => 0,
                    'projectCosts' => [ ], // Array( ProjectCostHistory )
                ];
        }

        $payPeriodEndDate = $endDate;

        if ($startDate < $entry->startDate)
            $startDate = $entry->startDate;

        if ($entry->endDate && ($endDate > $entry->endDate))
            $endDate = $entry->endDate;

        $portionOfYear = $this->portionOfYear($startDate, $endDate);

        $salary = $entry->salary * $portionOfYear;

        // Annual deduction amounts
        $standardDeduction = 5000;
        $perDeductionAllowance = 1000;

        // Compute the taxable income
        $deductions = ($standardDeduction + $entry->numDeductions * $perDeductionAllowance) * $portionOfYear;
        $deductions = min($deductions, $salary);
        $taxableSalary = max($salary - $deductions, 0);

        // Compute the tax owed
        $taxOwed = 0;

        $taxRates = $this->readTaxRates();
        $lastMinSalary = null;
        $taxRate = null;
        foreach ($taxRates as $rate) {
            $minSalary = $rate->minimumSalary * $portionOfYear;
            if (($minSalary <= $taxableSalary) && (($minSalary > $lastMinSalary) || ($lastMinSalary == null))) {
                $lastMinSalary = $minSalary;
                $taxRate = $rate;
            }
        } // foreach

        if ($taxRate == null)
            throw new Exception("Unable to determine tax rate!");

        $taxOwed = $taxRate->taxRate * $taxableSalary;

        if (!$prevTax)
            $prevTax = $this->computeTax($startDate, $endDate, null, null);

        // Update project cost records if needed
        $projectCosts = $prevTax->projectCosts;
        $associations = $this->readProjectEmployeeAssociations($employee, $startDate, $endDate);
        foreach ($associations as $assoc) {
            $sDate = $startDate;
            if ($sDate < $assoc->startDate)
                $sDate = $assoc->startDate;

            $eDate = $endDate;
            if ($assoc->endDate && ($eDate > $assoc->endDate))
                $eDate = $assoc->endDate;

            $cost = $entry->salary * $this->portionOfYear($sDate, $eDate) * $assoc->percentAllocation / 100;

            array_push(
                    $projectCosts,
                    new ProjectCostHistory(
                        $sDate,
                        $eDate,
                        null,   // To be filled in later after the pay stub entry is written
                        $assoc->project,
                        $assoc->department,
                        $cost
                    )
                );

            $assoc->lastPayPeriodEndDate = $payPeriodEndDate;
            $this->writeProjectEmployeeAssociation($assoc);
        } // foreach

        return (Object)[
                'salary' => $salary + $prevTax->salary,
				'tax' => $taxOwed + $prevTax->tax,
				'taxableSalary' => $taxableSalary + $prevTax->taxableSalary,
				'deductions' => $deductions + $prevTax->deductions,
                'projectCosts' => $projectCosts
			];
    } // computeTax

	/**
     * Tests whether a specific name is in currently assigned to a project or not.
     *
     * @param   String  $name               The name to test for.
     * @param   int     $ignoreProjectId    The ID of an Project entry to ignore (eg. typically,
     *                                      the Project being updated)
     *
     * @return  int    Returns the employee ID of the employee the tax ID is assigned to if found,
     *                  false if the tax ID is not assigned to an existing employee.
     */
     public function isProjectNameInUse( $name, $ignoreProjectId = null ) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                  "SELECT id ".
                    "FROM project ".
                    "WHERE name = :name ".
                        "AND id != :ignoreProjectId"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project name query"));
        }

        if ($ignoreProjectId !== null) {
            if (!is_numeric($ignoreProjectId))
                throw new Exception("The \$ignoreProjectId parameter must be an integer");
            $ignoreProjectId = (int) $ignoreProjectId;
        }

        $success = $stmt->execute(Array(
                ':name' => $name,
                'ignoreProjectId' => $ignoreProjectId
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for project name"));

        $row = $stmt->fetchObject();
        return ($row !== false);
    } // isProjectNameInUse

    /**
     * Reads a Project from the database.
     * @param   int     $id The ID of the project to retrieve.
     * @return  Project An instance of Project.
     */
    public function readProject( $id ) {
        if (!is_numeric($id))
            throw new Exception("Parameter \$id must be an integer");
        $id = (int) $id;

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, startDate, endDate, name, description, otherCosts ".
                        "FROM project ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project query"));
        }

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for project record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such project: $id");
		
        return new Project(
                $row->id,
                new DateTime( $row->startDate ),
                new DateTime( $row->endDate ),
                $row->name,
                $row->description,
                $row->otherCosts
            );
    } // readProject

    /**
     * Reads a list of all projects from the database.
     * @return  Array[Project] Array of Project instances.
     */
    public function readProjects() {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, startDate, endDate, name, description, otherCosts ".
                        "FROM project ".
                        "ORDER BY name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare projects query"));
        }

        $success = $stmt->execute(Array( ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for project records"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new Project(
                    $row->id,
                    new DateTime( $row->startDate ),
                    new DateTime( $row->endDate ),
                    $row->name,
                    $row->description,
                    $row->otherCosts
                );
        } // while

        return $rv;
    } // readProjects

    /**
     * Writes a Project to the database.
     * @param   Project    $project   The Project to write.  If the id property is 0, a new
     *                                  record will be created, otherwise an existing record matching
     *                                  the id will be updated.
     * @return  Project    A new Project instance (with the new id if a new record was created).
     */
    public function writeProject( Project $project ) {
        static $stmtInsert;
        static $stmtUpdate;
        if ($stmtInsert == null) {
            $stmtInsert = $this->dbh->prepare(
                    "INSERT INTO project ( ".
                            "startDate, endDate, name, description, otherCosts ".
                        ") VALUES ( ".
                            ":startDate, :endDate, :name, :description, :otherCosts ".
                        ")"
                );


            if (!$stmtInsert)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project insert"));

            $stmtUpdate = $this->dbh->prepare(
                    "UPDATE project SET ".
                            "startDate = :startDate, ".
                            "endDate = :endDate, ".
                            "name = :name, ".
                            "description = :description, ".
                            "otherCosts = :otherCosts ".
                        "WHERE id = :id"
                );

            if (!$stmtUpdate)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project update"));
        }

        $params = Array(
                ':startDate' => $project->startDate->format('Y-m-d'),
                ':endDate' => ($project->endDate ? $project->endDate->format('Y-m-d') : null),
                ':name' => $project->name,
                ':description' => $project->description,
                ':otherCosts' => $project->otherCosts
            );

        if ($project->id == 0) {
            $stmt = $stmtInsert;
        } else {
            $params[':id'] = $project->id;
            $stmt = $stmtUpdate;
        }

        $success = $stmt->execute($params);

        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to store project record in database"));

        if ($project->id == 0)
            $newId = $this->dbh->lastInsertId();
        else
            $newId = $project->id;

        return new Project(
                $newId,
                $project->startDate,
                $project->endDate,
                $project->name,
                $project->description,
                $project->otherCosts
            );
    } // writeProject

   /**
     * Reads all of the departments associated with a project.
     *
     * @param   int $projectId  The ID of the PRoject entry to retrieve departments for.
     *
     * @return  Array[Department]   Array of the departments for the project.
     */
    public function readDepartmentsForProject($projectId) {
        if (!is_numeric($projectId))
            throw new Exception("Parameter \$projectId must be an integer");
        $projectId = (int) $projectId;

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT d.id, d.name ".
                        "FROM projectDepartmentAssociation a ".
                        "INNER JOIN department d ON d.id = a.department ".
                        "WHERE  ".
                            "a.project = :projectId ".
                        "ORDER BY d.name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project departments query"));
        }

        $success = $stmt->execute(Array(
                            ':projectId' => $projectId
                        ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for project departments"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new Department( $row->id, $row->name );
        } // while

        return $rv;
    } // readDepartmentsForProject

    /**
     * Writes ProjectDepartmentAssociation records to the database.
     * @param   int   $projectId    The project record id to update the departments for.
     * @param   Array[Department]   $departments    The list of departments to associate the project with.
     */
    public function writeDepartmentsForProject($projectId, $departments) {
        if (!is_numeric($projectId))
            throw new Exception("Parameter \$projectId must be an integer");
        $projectId = (int) $projectId;

        if (!is_array($departments))
            throw new Exception("The \$departments parameter must be an array.");

        foreach ($departments as $dept) {
            if (!($dept instanceof Department))
                throw new Exception("Every element in the \$departments parameter must be an instance of Department.");

            if ($dept->id == 0)
                throw new Exception("The id property of a department cannot be 0.");
        } // foreach

        static $insertStmt;
        static $deleteStmt;
        if ($insertStmt == null) {
            $insertStmt = $this->dbh->prepare(
                    "INSERT INTO projectDepartmentAssociation ( ".
                            "project, department ".
                        ") VALUES ( ".
                            ":projectId, :department ".
                        ")"
                );


            if (!$insertStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project department insert"));

            $deleteStmt = $this->dbh->prepare(
                    "DELETE FROM projectDepartmentAssociation ".
                        "WHERE project = :projectId"
                );

            if (!$deleteStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project department delete"));
        }

        // Remove existing association records for the project
        $success = $deleteStmt->execute(Array(
                ':projectId' => $projectId
            ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($deleteStmt, "Unable to delete existing projectDepartmentAssociation records"));

        // Create new association records for the project
        foreach ($departments as $dept) {
            $success = $insertStmt->execute(Array(
                    ':projectId' => $projectId,
                    ':department' => $dept->id
                ));
            if ($success == false)
                throw new Exception($this->formatErrorMessage($insertStmt, "Unable to create projectDepartmentAssociation record in database"));
        } // foreach
    } // writeDepartmentsForProject

    /**
     * Reads ProjectEmployeeAssociation records from the database.
     *
     * @param   Project|Employee|Department|int  $for  The object to retrieve the related associations for.
     * @param   DateTime    $startDate
     * @param   DateTime    $endDate
     *
     * @return  A single ProjectEmployee instance or null when $for is an integer, otherwise
     *          an array of ProjectEmployee instances that match the specified parameters.
     */
    public function readProjectEmployeeAssociations($for, DateTime $startDate = null, DateTime $endDate = null) {
        $project = $employee = $department = $projectEmployeeId = null;

        if ($for instanceof Project) {
            $project = $for;
        } else if ($for instanceof Employee) {
            $employee = $for;
        } else if ($for instanceof Department) {
            $department = $for;
        } else if (is_numeric($for)) {
            $projectEmployeeId = (int) $for;
        } else
            throw new Exception("The $for parameter must be an instance of Department, Employee or Project or an integer.");

        if ($project && !$project->id)
            throw new Exception("The Project must have an ID assigned");

        if ($employee && !$employee->id)
            throw new Exception("The Employee must have an ID assigned");

        if ($department && !$department->id)
            throw new Exception("The Employee must have an ID assigned");

        if (($projectEmployeeId !== null) && !$projectEmployeeId)
            throw new Exception("The id cannot be 0");

        if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT a.id, a.project, a.employee, a.department, ".
                    "a.startDate, a.endDate, a.lastPayPeriodEndDate, a.percentAllocation ".
                        "FROM projectEmployeeAssociation a ".
                        "WHERE  ".
                            "( ".
                                "a.id = :projectEmployeeId ".
                                "OR a.project = :projectId ".
                                "OR a.employee = :employeeId ".
                                "OR a.department = :departmentId ".
                            ") AND ( ".
                                "(a.endDate IS NULL OR a.endDate >= :startDate) AND a.startDate <= :endDate ".
                            ") ".
                        "ORDER BY a.startDate DESC"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project employees association query"));
        }

        $success = $stmt->execute(Array(
                            ':projectEmployeeId' => $projectEmployeeId,
                            ':projectId' => ($project ? $project->id : null),
                            ':employeeId' => ($employee ? $employee->id : null),
                            ':departmentId' => ($department ? $department->id : null),
                            ':startDate' => $startDate,
                            ':endDate' => $endDate,
                        ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for project employee associations"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new ProjectEmployee(
                        $row->id,
                        new DateTime($row->startDate),
                        ($row->endDate ? new DateTime($row->endDate) : null),
                        ($row->lastPayPeriodEndDate ? new DateTime($row->lastPayPeriodEndDate) : null),
                        ($project ? $project : $this->readProject($row->project)),
                        ($employee ? $employee : $this->readEmployee($row->employee)),
                        ($department ? $department : $this->readDepartment($row->department)),
                        $row->percentAllocation
                    );
        } // while

        if ($projectEmployeeId != null) {
            if (count($rv) != 1)
                throw new Exception("No such project employee association: $projectEmployeeId");
            $rv = $rv[0];
        }

        return $rv;
    } // readProjectEmployeeAssociations

    /**
     * Writes a ProjectEmployee entry.
     *
     * @param ProjectEmployee $association
     *
     * @return  Returns a new ProjectEmployee instance (with a new id if a new record was created).
     */
    public function writeProjectEmployeeAssociation(ProjectEmployee $association) {
        static $stmtInsert;
        static $stmtUpdate;
        if ($stmtInsert == null) {
            $stmtInsert = $this->dbh->prepare(
                    "INSERT INTO projectEmployeeAssociation ( ".
                            "startDate, endDate, lastPayPeriodEndDate, ".
                            "project, department, employee, percentAllocation ".
                        ") VALUES ( ".
                            ":startDate, :endDate, :lastPayPeriodEndDate, ".
                            ":project, :department, :employee, :percentAllocation ".
                        ")"
                );

            if (!$stmtInsert)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project employee insert"));

            $stmtUpdate = $this->dbh->prepare(
                    "UPDATE projectEmployeeAssociation SET ".
                            "startDate = :startDate, ".
                            "endDate = :endDate, ".
                            "lastPayPeriodEndDate = :lastPayPeriodEndDate, ".
                            "project = :project, ".
                            "department = :department, ".
                            "employee = :employee, ".
                            "percentAllocation = :percentAllocation ".
                        "WHERE id = :id"
                );

            if (!$stmtUpdate)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project employee update"));
        }
        
        $params = Array(
                ':startDate' => $association->startDate->format("Y-m-d"),
                ':endDate' => ($association->endDate
                                ? $association->endDate->format("Y-m-d")
                                : null),
                ':lastPayPeriodEndDate' => ($association->lastPayPeriodEndDate
                                ? $association->lastPayPeriodEndDate->format("Y-m-d")
                                : null),
                ':project' => $association->project->id,
                ':department' => $association->department->id,
                ':employee' => $association->employee->id,
                ':percentAllocation' => $association->percentAllocation,
            );

        if ($association->id == 0) {
            $stmt = $stmtInsert;
        } else {
            $params[':id'] = $association->id;
            $stmt = $stmtUpdate;
        }

        $success = $stmt->execute($params);

        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to store project employee record in database"));

        if ($association->id == 0)
            $newId = $this->dbh->lastInsertId();
        else
            $newId = $association->id;

        return new ProjectEmployee(
                $newId,
                $association->startDate,
                $association->endDate,
                $association->lastPayPeriodEndDate,
                $association->project,
                $association->employee,
                $association->department,
                $association->percentAllocation
            );
    } // writeProjectEmployeeAssociation

    /**
     * Reads ProjectCostHistory records for a Project, PayStub, Department or Employee.
     *
     * @param   Project|PayStub|Department|Employee $for    The object to retrieve associated cost history records for.
     * @param   DateTime    $startDate  
     * @param   DateTime    $endDate    
     *
     * @return  Array[ProjectCostHistory] Array of matching ProjectCostHistory records.
     */
    public function readProjectCostHistory($for, DateTime $startDate = null, DateTime $endDate = null) {
        $project = $payStub = $department = $employee = null;

        if ($for instanceof Project) {
            $project = $for;
        } else if ($for instanceof PayStub) {
            $payStub = $for;
        } else if ($for instanceof Department) {
            $department = $for;
        } else if ($for instanceof Employee) {
            $employee = $for;
        } else
            throw new Exception("The $for parameter must be an instance of Department, Employee, PayStub or Project.");

        if ($project && !$project->id)
            throw new Exception("The Project must have an ID assigned");

        if ($payStub && !$payStub->id)
            throw new Exception("The PayStub must have an ID assigned");

        if ($department && !$department->id)
            throw new Exception("The Department must have an ID assigned");

        if ($employee && !$employee->id)
            throw new Exception("The Employee must have an ID assigned");

        if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT h.project, h.paystub, h.department, ".
                            "h.startDate, h.endDate, h.cost ".
                        "FROM projectCostHistory h ".
                        "LEFT JOIN paystub ps ".
                            "ON ps.id = h.paystub ".
                        "WHERE  ".
                            "( ".
                                "h.project = :projectId ".
                                "OR h.paystub = :paystubId ".
                                "OR h.department = :departmentId ".
                                "OR ps.employee = :employeeId ".
                            ") AND ( ".
                                "h.endDate >= :startDate AND h.startDate <= :endDate ".
                            ") ".
                        "ORDER BY h.startDate DESC"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project cost history query"));
        }

        $success = $stmt->execute(Array(
                            ':startDate' => $startDate,
                            ':endDate' => $endDate,
                            ':projectId' => ($project ? $project->id : null),
                            ':paystubId' => ($payStub ? $payStub->id : null),
                            ':departmentId' => ($department ? $department->id : null),
                            ':employeeId' => ($employee ? $employee->id : null),
                        ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for project cost history"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $rv[] = new ProjectCostHistory(
                        $row->startDate,
                        $row->endDate,
                        ($payStub ? $payStub : $this->readPayStub($row->paystub)),
                        ($project ? $project : $this->readProject($row->project)),
                        ($department ? $department : $this->readDepartment($row->department)),
                        $row->cost
                    );
        } // while

        return $rv;
    } // readProjectCostHistory

    /**
     *
     */
    public function writeProjectCostHistory(ProjectCostHistory $history) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "INSERT INTO projectCostHistory ( ".
                            "startDate, endDate, paystub, ".
                            "project, department, cost ".
                        ") VALUES ( ".
                            ":startDate, :endDate, :paystub, ".
                            ":project, :department, :cost ".
                        ")"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project cost history insert"));
        }
        
        $params = Array(
                ':startDate' => $history->startDate->format("Y-m-d"),
                ':endDate' => $history->endDate->format("Y-m-d"),
                ':paystub' => ($history->payStub ? $history->payStub->id : null),
                ':project' => $history->project->id,
                ':department' => ($history->department ? $history->department->id : null),
                ':cost' => $history->cost,
            );

        $success = $stmt->execute($params);

        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to store project cost history record in database"));

        return new ProjectCostHistory(
                $history->startDate,
                $history->endDate,
                $history->payStub,
                $history->project,
                $history->department,
                $history->cost
            );
    } // writeProjectCostHistory

	public function readProjectChartEmployees($project, DateTime $startDate = null, DateTime $endDate = null) {
	 	static $stmt;
	 	 
	 	if ($stmt == null) {
            $stmt = $this->dbh->prepare(
            	"SELECT COALESCE(P.name, 'Other Costs') as name, SUM(T.cost) as totalCost ".
                    "FROM projectCostHistory T ".
                    "LEFT JOIN paystub P ".
                        "ON T.paystub = P.id ".
                    "WHERE T.project = :project ".
                        "AND T.startDate <= :endDate ".
                        "AND T.endDate >= :startDate ".
                    "GROUP BY P.id, P.name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare project employee cost query"));
        }

        if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        $params = Array(
                ':project' => $project,
                ':startDate' => $startDate,
                ':endDate' => $endDate
            );

        $success = $stmt->execute($params);

        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to select employee costs for project."));
		
		$rv = Array();
		
        while ($row = $stmt->fetchObject()) {
            $rv[] = array(
                        $row->name,
                        (double)$row->totalCost
                    );
        } // while
		return $rv;
    } // readProjectChartEmployees

	public function readProjectChartProjects($project, DateTime $startDate = null, DateTime $endDate = null) {
	 	static $stmt;
	 		if ($stmt == null) {
				$stmt = $this->dbh->prepare(
					"SELECT COALESCE(D.name, 'Other Costs') as name, SUM(P.cost) as totalCost ".
						"FROM projectCostHistory P ".
						"LEFT JOIN department D ".
							"ON P.department = D.id ".
						"WHERE P.project = :project ". 
							"AND P.startDate <= :endDate ".
							"AND P.endDate >= :startDate ".
						"GROUP BY D.id, D.name"
					);

				if (!$stmt)
					throw new Exception($this->formatErrorMessage(null, "Unable to prepare project department cost query"));
			}
	 		//throw new Exception($this->formatErrorMessage(null, "project 0"));
	 		
        if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        $params = Array(
                ':project' => $project,
                ':startDate' => $startDate,
                ':endDate' => $endDate
            );

        $success = $stmt->execute($params);

        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to select department costs for project."));
		
		$rv = Array();
		
        while ($row = $stmt->fetchObject()) {
            $rv[] = array(
            			$row->name,
                        (double)$row->totalCost
                    );
        } // while
		return $rv;
    } // readProjectChartProjects

	public function readProjectChartAllDepartments($project, DateTime $startDate = null, DateTime $endDate = null) {
		static $stmt;
		if ($stmt == null) {
				$stmt = $this->dbh->prepare(
					"SELECT D.name, SUM(P.cost) as totalCost ".
                    "FROM projectCostHistory P, department D ".
                    "WHERE P.department = D.id ".
                    	"AND P.startDate <= :endDate ".
						"AND P.endDate >= :startDate ".
                    "GROUP BY D.id, D.name"
				);

				if (!$stmt)
					throw new Exception($this->formatErrorMessage(null, "Unable to prepare project department cost query"));
			}
		if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        $params = Array(
                ':startDate' => $startDate,
                ':endDate' => $endDate
            );

        $success = $stmt->execute($params);

        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to select department costs for project."));
		
		$rv = Array();
		
        while ($row = $stmt->fetchObject()) {
            $rv[] = array(
            			$row->name,
                        (double)$row->totalCost
                    );
        } // while
		return $rv;
	}// readProjectChartAllDepartments
	
	public function readProjectChartAll($project, DateTime $startDate = null, DateTime $endDate = null) {
		static $stmt;
		if ($stmt == null) {
				$stmt = $this->dbh->prepare(
					"SELECT SUM(cost) as totalCost ".
                    "FROM projectCostHistory ".
                    "WHERE department IS NULL ".
                    	"AND startDate <= :endDate ".
						"AND endDate >= :startDate"
				);

				if (!$stmt)
					throw new Exception($this->formatErrorMessage(null, "Unable to prepare other costs query"));
			}
		if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        $params = Array(
                ':startDate' => $startDate,
                ':endDate' => $endDate
            );

        $success = $stmt->execute($params);

        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to select other costs for all projects."));
		
		$rv = Array();
		
        while ($row = $stmt->fetchObject()) {
            $rv[] = array(
            			'Other Costs',
                        (double)$row->totalCost
                    );
        } // while
		return $rv;
	}// readProjectChartAll

} // DBInterface
