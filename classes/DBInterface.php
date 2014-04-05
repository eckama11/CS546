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
                            ":payPeriodStartDate, :employeeId, :name, :address, ".
                            ":rank, :employeeType, :taxId, :salary, :numDeductions, ".
                            ":taxWithheld, :taxRate, :deductions, :salaryYTD, ".
                            ":taxWithheldYTD, :deductionsYTD ".
                        ")"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare pay stub update"));
        }

        $success = $stmt->execute(Array(
                ':payPeriodStartDate' => $paystub->payPeriodStartDate->format("Y-m-d"),
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
            throw new Exception($this->formatErrorMessage($stmt, "Unable to create paystub record in database"));

        $newId = $this->dbh->lastInsertId();

        $this->writeDepartmentsForPayStub( $newId, $paystub->departments );

        return new PayStub(
                $newId,
                $paystub->payPeriodStartDate,
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
                            "AND LAST_DAY(payPeriodStartDate) < :beforeDate ".
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
     * @param   String  $taxId   The taxId to test for.
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
     * @param   int         $employeeId The ID of the employee to retrieve history for.
     * @param   DateTime    $startDate  If provided, the starting date to filter by.  Only
     *                                  records with an end date greater than or equal to the
     *                                  date provided will be returned.
     * @param   DateTime    $endDate    If provided, the ending date to filter by.  Only records
     *                                  with a start date less than or equal to the provided date
     *                                  will be returned.
     * @param   int         $limit      The number of records to return, most recent first.  If not
     *                                  specified or null, no limit is applied.
     *
     * @return  
     */
    public function readEmployeeHistory(
                        $employeeId,
                        DateTime $startDate = null,
                        DateTime $endDate = null,
                        $limit = null
                    )
    {
        if (!is_numeric($employeeId))
            throw new Exception("Parameter \$employeeId must be an integer");
        $employeeId = (int) $employeeId;

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
                        "WHERE employee = :employeeId ".
                            "AND startDate <= :endDate ".
                            "AND (endDate >= :startDate OR endDate IS NULL) ".
                        "ORDER BY startDate DESC ".
                        "LIMIT :limit"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee history query"));
        }

        if ($startDate == null)
            $startDate = new DateTime('1900-01-01');
        $startDate = $startDate->format("Y-m-d");

        if ($endDate == null)
            $endDate = new DateTime('9999-12-31');
        $endDate = $endDate->format("Y-m-d");

        // bindParam must be used here because :limit param must be bound as an int
        // to function properly
        $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $success = $stmt->execute();
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee history"));

        $rv = [];
        while ($row = $stmt->fetchObject()) {
            $rv[] = new EmployeeHistory(
                $row->id,
                new DateTime( $row->startDate ),
                ($row->endDate
                    ? new DateTime( $row->endDate )
                    : null),
                ($row->lastPayPeriodEndDate
                    ? new DateTime ( $row->lastPayPeriodEndDate )
                    : null),
                $this->readRank( $row->rank ),
                $row->numDeductions,
                $row->salary
            );
        } // while

        return $rv;
    } // readEmployeeHistory

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, activeFlag, username, password, name, address, taxId ".
                        "FROM employee ".
                        "WHERE id = ?"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to employee query"));
        }

        $success = $stmt->execute(Array( $id ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee record"));

        $row = $stmt->fetchObject();
        if ($row === false)
            throw new Exception("No such employee: $id");
		
        $current = $this->readEmployeeHistory( $row->id, null, null, 1 );
        if (count($current) == 0)
            throw new Exception("No history found for employee: $row->id");
        $current = $current[0];

        return new Employee(
                $row->id,
                $row->activeFlag,
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
     * @param   Boolean $activeFlag Whether to retrieve active employees (true) or inactive employees (false)
     * @return  Array[Employee] Array of Employee instances.
     */
    public function readEmployees($activeFlag = true) {
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT id, activeFlag, username, password, name, address, taxId ".
                        "FROM employee ".
                        "WHERE activeFlag=:activeFlag ".
                        "ORDER BY name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employees query"));
        }

        $success = $stmt->execute(Array(
                ':activeFlag' => $activeFlag
            ));
        if ($success === false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query database for employee records"));

        $rv = Array();
        while ($row = $stmt->fetchObject()) {
            $current = $this->readEmployeeHistory( $row->id, null, null, 1 );
            if (count($current) == 0)
                throw new Exception("No history found for employee: $row->id");
            $current = $current[0];

            $rv[] = new Employee(
                    $row->id,
                    $row->activeFlag,
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
                            "activeFlag, username, password, name, address, taxId ".
                        ") VALUES ( ".
                            ":activeFlag, :username, :password, :name, :address, :taxId ".
                        ")"
                );


            if (!$stmtInsert)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee insert"));

            $stmtUpdate = $this->dbh->prepare(
                    "UPDATE employee SET ".
                            "activeFlag = :activeFlag, ".
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

        if (!$employee->activeFlag && ($employee->current->endDate == null))
            throw new Exception("Cannot make employee inactive without specifying end date");

        $params = Array(
                ':activeFlag' => ($employee->activeFlag ? 1 : 0),
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
                $employee->activeFlag,
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
     * Writes and EmployeeHistory entry for an employee.
     *
     * The most recent employee history record will be updated if:
     *
     * - Only one history record exists, and the lastPayPeriodEndDate value on the existing entry
     *   is null, in which case any of the properties may be updated.
     *
     * - The startDate of the new entry matches the start date of the history record
     *   AND EITHER
     *     1) the lastPayPeriodEndDate value on the history record is null, in which case any of
     *        the other properties may be updated (except of course the startDate).
     *   OR
     *     2) the lastPayPeriodEndDate is not less than the lastPayPeriodEndDate value on the
     *        history record.  In this case, only the lastPayPeriodEndDate and endDate properties
     *        may be updated.
     *
     * - the startDate of the new entry is greater than the lastPayPeriodEndDate value on the
     *   most recent history record (In this case, the existing record will have its endDate set,
     *   and a new record is also added)
     *
     * If no history records exist, or none of the above conditions are met a new record will
     * be created.  Unless the startDate of the new entry is not greater than the most
     * recent lastPayPeriodEndDate, in which case an exception is thrown.
     *
     * If at least on history record exists, and its lastPayPeriodEndDate is not null,
     * an exception will be thrown if the lastPayPeriodEndDate on the new entry is less than
     * the existing lastPayPeriodEndDate.
     *
     * @param {int} $employeeId 
     * @param {EmployeeHistory} $entry 
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
            throw new Exception("Parameter \$employeeId must be an integer");
        $employeeId = (int) $employeeId;

        // Read up to 2 existing history records if they exist
        $update = false;
        $history = $this->readEmployeeHistory( $employeeId, null, null, 2 );
        if (count($history) > 0) {
            $current = $history[0];

            if (($current->lastPayPeriodEndDate !== null) &&
                ($entry->lastPayPeriodEndDate < $current->lastPayPeriodEndDate))
            {
                throw new Exception("The new lastPayPeriodEndDate cannot be less than ". $current->lastPayPeriodEndDate->format("Y-m-d"));
            }

            if ($entry->startDate == $current->startDate) {
                $update = ($current->lastPayPeriodEndDate === null) ||
                    (
                        ($entry->rank == $current->rank) &&
                        ($entry->numDeductions == $current->numDeductions) &&
                        ($entry->salary == $current->salary)
                    );
            } else if ($entry->startDate > $current->startDate) {
                if ($entry->lastPayPeriodEndDate !== null) {
                    if (($current->lastPayPeriodEndDate === null) ||
                        ($entry->lastPayPeriodEndDate < $current->lastPayPeriodEndDate))
                    {
                        throw new Exception("The new lastPayPeriodEndDate is invalid");
                    }
                }

                if ($current->endDate === null) {
                    // Update existing record to set end date to day before new start date

                    $params = $this->_employeeHistoryToParams($current);
                    $params[':id'] = $current->id;
                    $endDate = (clone $entry->startDate);
                    $params[':endDate'] = $endDate->sub(new DateInterval('P1D'))->format("Y-m-d");
                    
                    $success = $stmtUpdate->execute($params);
                    if ($success == false)
                        throw new Exception($this->formatErrorMessage($stmtUpdate, "Unable to update employee history record in database"));
                } else if ($entry->startDate <= $current->endDate)
                    throw new Exception("The new startDate must be greater than the prior endDate");
            } else {
                if (count($history) == 1) {
                    // May update IFF no lastPayPeriodEndDate on existing record
                    $update = ($current->lastPayPeriodEndDate === null);
                } else
                    throw new Exception("The new startDate must be greater than the prior endDate");
            }
        }

        $params = $this->_employeeHistoryToParams($entry);

        if ($update) {
            $stmt = $stmtUpdate;
            $params[':id'] = $current->id;
        } else {
            $stmt = $stmtInsert;
            $params[':employee'] = $employeeId;
        }

        $success = $stmt->execute($params);

        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to ". ($update ? "update" : "insert") ." employee history record in database"));

        if ($update)
            $newId = $current->id;
        else
            $newId = $this->dbh->lastInsertId();

        return new EmployeeHistory(
                $newId,
                $entry->startDate,
                $entry->endDate,
                $entry->lastPayPeriodEndDate,
                $entry->rank,
                $entry->numDeductions,
                $entry->salary
            );
    } // writeEmployeeHistory( $employeeId, EmployeeHistory $entry )

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
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT d.id, d.name ".
                        "FROM employeeDepartmentAssociation a ".
                        "INNER JOIN department d ON d.id = a.department ".
                        "WHERE employee=? ".
                        "ORDER BY d.name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee departments query"));
        }

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
     * @param   EmployeeType $employeeType Type of employees to return.  If not specified,
     *                     all employees associated with the department will be returned.
     * @return  Array[Employee]   Array of the Employees for a Department.
     */
    public function readEmployeesForDepartment( $departmentId, EmployeeType $employeeType = null ) {
        if (!is_numeric($departmentId))
            throw new Exception("Parameter \$departmentId must be an integer");
        $departmentId = (int) $departmentId;

        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT a.employee ".
                        "FROM ( ".
                            "SELECT :date as theDate, :employeeType as employeeType, :departmentId as departmentId ".
                        ") s ".
                        "INNER JOIN employeeDepartmentAssociation a ".
                            "ON a.department=s.departmentId ".
                        "INNER JOIN employee e ".
                            "ON e.id = a.employee ".
                        "INNER JOIN employeeHistory h ".
                            "ON h.employee = a.employee ".
                                "AND h.startDate <= s.theDate ".
                                "AND (h.endDate >= s.theDate OR h.endDate IS NULL) ".
                        "INNER JOIN rank r ".
                            "ON r.id = h.rank ".
                                "AND (s.employeeType IS NULL OR r.employeeType = s.employeeType) ".
                        "ORDER BY e.name"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare department employees query"));
        }

        $success = $stmt->execute(Array(
                ':date' => (new DateTime())->format("Y-m-d"),
                ':employeeType' => $employeeType->name,
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


            if (!$insertStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee department insert"));

            $deleteStmt = $this->dbh->prepare(
                    "DELETE FROM employeeDepartmentAssociation ".
                        "WHERE employee = :employee"
                );

            if (!$deleteStmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare employee department delete"));
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
     *
     * @param   $generationDate The effective date to generate pay stubs for.  If null, will
     *                          use the current date.  May be a date string or a DateTime instance.
     *
     * @return  Object  An object with the following properties:
     *                      int      numGenerated    The number of paystubs which were generated.
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
        static $stmt;
        if ($stmt == null) {
            $stmt = $this->dbh->prepare(
                    "SELECT e.id ".
                        "FROM employee e ".
                        "WHERE NOT EXISTS (".
                            "SELECT * ".
                                "FROM paystub p ".
                                "WHERE p.employee = e.id ".
                                    "AND p.payPeriodStartDate >= :payPeriodStartDate ".
                        ")"
                );

            if (!$stmt)
                throw new Exception($this->formatErrorMessage(null, "Unable to prepare generate pay stub query"));
        }

        $success = $stmt->execute(Array(
                        ':payPeriodStartDate' => $payPeriodStartDate->format("Y-m-d")
                    ));
        if ($success == false)
            throw new Exception($this->formatErrorMessage($stmt, "Unable to query employees who need pay stubs generated"));

        $numGenerated = 0;
        while ($row = $stmt->fetchObject()) {
            $employee = $this->readEmployee( $row->id );
            $monthlySalary = $employee->current->salary / 12;
            $tax = $this->computeTax($monthlySalary, $employee->current->numDeductions);

            $effectiveTaxRate = (($monthlySalary > 0) ? $tax->tax / $monthlySalary : 0);

            $departments = $this->readDepartmentsForEmployee( $employee->id );
            $departments = array_map(function($dept) { return $this->departmentToPaystubDepartment($dept); }, $departments);

            // Read previous pay stub for YTD information
            $salaryYTD = $monthlySalary;
            $taxWithheldYTD = $tax->tax;
            $deductionsYTD = $tax->deductions;

            $lastPaystub = $this->readPayStubs($employee->id, $firstOfYear, $payPeriodStartDate);
            if (count($lastPaystub)) {
                $lastPaystub = $lastPaystub[count($lastPaystub) - 1];

                $salaryYTD += $lastPaystub->salaryYTD;
                $taxWithheldYTD += $lastPaystub->taxWithheldYTD;
                $deductionsYTD += $lastPaystub->deductionsYTD;
            }

            $paystub = new PayStub(
                    0, $payPeriodStartDate, $employee, 
                    $employee->name, $employee->address, $employee->current->rank, $employee->current->rank->employeeType, $employee->taxId,
                    $departments, $monthlySalary,
                    $employee->current->numDeductions,
                    $tax->tax, $effectiveTaxRate, $tax->deductions,
                    $salaryYTD, $taxWithheldYTD, $deductionsYTD
                );

            $this->writePaystub( $paystub );
            ++$numGenerated;
        } // while

        return (Object)[
                "numGenerated" => $numGenerated,
                "generationDate" => $generationDate,
                "startDate" => $payPeriodStartDate,
                "endDate" => $payPeriodEndDate
            ];
    } // generatePayStubs

    /**
     * Computesthe tax owed for a given monthly salart and number of deductions.
     * @param   double  $salary         The monthloy salary earned.
     * @param   int     $numDeductions  The number of deductions claimed.
     * @return  double  The tax owed.
     */
    protected function computeTax($salary, $numDeductions) {
        // Annual deduction amounts
        $standardDeduction = 5000;
        $perDeductionAllowance = 1000;

        // Compute the taxable income
        $deductions = ($standardDeduction + $numDeductions * $perDeductionAllowance) / 12;
        $deductions = min($deductions, $salary);
        $taxableSalary = max($salary - $deductions, 0);

        // Compute the tax owed
        $taxOwed = 0;

        $taxRates = $this->readTaxRates();
        $lastMinSalary = null;
        $taxRate = null;
        foreach ($taxRates as $rate) {
            $minSalary = $rate->minimumSalary / 12;
            if (($minSalary <= $taxableSalary) && (($minSalary > $lastMinSalary) || ($lastMinSalary == null))) {
                $lastMinSalary = $minSalary;
                $taxRate = $rate;
            }
        } // foreach

        if ($taxRate == null)
            throw new Exception("Unable to determine tax rate!");

        $taxOwed = $taxRate->taxRate * $taxableSalary;

        return (Object)[
				'tax' => $taxOwed,
				'taxableSalary' => $taxableSalary,
				'deductions' => $deductions
			];
    } // computeTax($salary, $numDeductions)

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
                ':startDate' => $project->startDate,
                ':endDate' => $project->endDate,
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

    public function readDepartmentsForProject($projectId) {
        //XXX
    } // readDepartmentsForProject

    public function readEmployeesForProject($projectId) {
        //XXX
    } // readEmployeesForProject
    
} // DBInterface
