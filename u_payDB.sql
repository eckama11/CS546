DROP DATABASE IF EXISTS u_pay;

CREATE DATABASE u_pay;
USE u_pay;


CREATE TABLE taxRate(
    id INT NOT NULL AUTO_INCREMENT,
    minimumSalary DECIMAL(9,2) UNSIGNED NOT NULL,
    taxRate DECIMAL(7,6) UNSIGNED NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE rank(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    baseSalary DECIMAL(9,2) UNSIGNED NOT NULL,
    employeeType ENUM('Administrator', 'Manager', 'Software Developer') NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (name)
);

CREATE TABLE department(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    UNIQUE KEY (name),
    PRIMARY KEY (id)
);

CREATE TABLE employee(
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    taxId VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (taxId),
    UNIQUE KEY (username)
);

CREATE TABLE employeeHistory(
    id INT NOT NULL AUTO_INCREMENT,
    employee INT NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NULL,
    lastPayPeriodEndDate DATE NULL,
    rank INT NOT NULL,
    numDeductions INT NOT NULL,
    salary DECIMAL(9,2) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (employee) REFERENCES employee(id),
    FOREIGN KEY (rank) REFERENCES rank(id),
    INDEX (employee, startDate)
);

CREATE TABLE employeeDepartmentAssociation(
    employeeHistory INT NOT NULL,
    department INT NOT NULL,
    FOREIGN KEY (employeeHistory) REFERENCES employeeHistory(id),
    FOREIGN KEY (department) REFERENCES department(id),
    PRIMARY KEY (employeeHistory, department)
);

CREATE TABLE loginSession(
    sessionId VARCHAR(255) NOT NULL,
    authenticatedEmployee INT NOT NULL,
    PRIMARY KEY(sessionID),
    FOREIGN KEY(authenticatedEmployee) REFERENCES employee(id)
);

CREATE TABLE paystub(
    id INT NOT NULL AUTO_INCREMENT,
    payPeriodStartDate DATE NOT NULL,
    payPeriodEndDate DATE NOT NULL,
    employee INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    rank VARCHAR(255) NOT NULL,
    employeeType VARCHAR(255) NOT NULL,
    taxId VARCHAR(255) NOT NULL,
    salary DECIMAL(9,2) UNSIGNED NOT NULL,
    numDeductions INT NOT NULL,
    taxWithheld DECIMAL(9,2) UNSIGNED NOT NULL,
    taxRate DECIMAL(7,6) UNSIGNED NOT NULL,
    deductions DECIMAL(9,2) UNSIGNED NOT NULL,
    salaryYTD DECIMAL(9,2) UNSIGNED NOT NULL,
    taxWithheldYTD DECIMAL(9,2) UNSIGNED NOT NULL,
    deductionsYTD DECIMAL(9,2) UNSIGNED NOT NULL,
    FOREIGN KEY (employee) REFERENCES employee(id),
    PRIMARY KEY (id)
);	

CREATE TABLE paystubDepartmentAssociation(
    paystub INT NOT NULL,
    department INT NOT NULL,
    departmentName VARCHAR(255) NOT NULL,
    departmentManagers TEXT NOT NULL,
    FOREIGN KEY (paystub) REFERENCES paystub(id),
    FOREIGN KEY (department) REFERENCES department(id),
    PRIMARY KEY (paystub, department)
);

CREATE TABLE project(
    id INT NOT NULL AUTO_INCREMENT,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    name VARCHAR(255),
    description TEXT,
    otherCosts DECIMAL(9,2) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (name),
    INDEX (startDate)
);

CREATE TABLE projectCostHistory(
    project INT NOT NULL,
    paystub INT NULL,
    department INT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    cost DECIMAL(9,2) UNSIGNED NOT NULL,
    FOREIGN KEY (project) REFERENCES project(id),
    FOREIGN KEY (paystub) REFERENCES paystub(id),
    FOREIGN KEY (department) REFERENCES department(id),
    INDEX (project),
    INDEX (paystub),
    INDEX (department),
    INDEX (startDate)
);

CREATE TABLE projectDepartmentAssociation(
    project INT NOT NULL,
    department INT NOT NULL,
    FOREIGN KEY (project) REFERENCES project(id),
    FOREIGN KEY (department) REFERENCES department(id),
    PRIMARY KEY (project, department),
    INDEX (department)
);

CREATE TABLE projectEmployeeAssociation(
    id INT NOT NULL AUTO_INCREMENT,
    project INT NOT NULL,
    employee INT NOT NULL,
    department INT NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NULL,
    lastPayPeriodEndDate DATE NULL,
    percentAllocation DECIMAL(9,6),
    PRIMARY KEY (id),
    FOREIGN KEY (project) REFERENCES project(id),
    FOREIGN KEY (employee) REFERENCES employee(id),
    FOREIGN KEY (department) REFERENCES department(id),
    INDEX (project, startDate),
    INDEX (employee, startDate),
    INDEX (department)
);

-- Create the user which the app will use to connect to the DB
DROP PROCEDURE IF EXISTS u_pay.drop_user_if_exists ;
DELIMITER $$
CREATE PROCEDURE u_pay.drop_user_if_exists()
BEGIN
  DECLARE foo BIGINT DEFAULT 0 ;
  SELECT COUNT(*)
  INTO foo
    FROM mysql.user
      WHERE User = 'u_pay' and  Host = 'localhost';
   IF foo > 0 THEN
         DROP USER 'u_pay'@'localhost' ;
  END IF;
END ;$$
DELIMITER ;
CALL u_pay.drop_user_if_exists() ;
DROP PROCEDURE IF EXISTS u_pay.drop_users_if_exists ;

CREATE USER 'u_pay'@'localhost' IDENTIFIED BY 'u_pay';
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, LOCK TABLES, CREATE TEMPORARY TABLES ON u_pay.* TO 'u_pay'@'localhost';

-- Populate the tax tates
INSERT INTO taxRate (
    minimumSalary, taxRate
  ) VALUES
    (     0.00, 0.050000 ), -- $0 to $9999.99
    ( 10000.00, 0.100000 ), -- $10000 to $19999.99
    ( 20000.00, 0.150000 ),
    ( 30000.00, 0.200000 ),
    ( 40000.00, 0.250000 )
;

-- Populate the departments
INSERT INTO department (
    name
  ) VALUES
    ( 'Corporate' ),
  /*
    ( 'Human Resources' ),
    ( 'Marketing' ),
    ( 'Customer Support' ),
    ( 'Quality Assurance' ),
    ( 'Graphic Design' ),
    ( 'Documentation' ),
    ( 'Legacy Product Maintenance' ),
    ( 'New Product Development' )
    */
    ( 'Mobile Development' ),
    ( 'Data Application Development' )
;

-- Populate the ranks
INSERT INTO rank (
    name, baseSalary, employeeType
  ) VALUES
    -- Administrator roles
    ( 'Built-in System Administrator', 0.00, 'Administrator' ),
    ( 'President',               1000000.00, 'Administrator' ),
    ( 'Vice-President',           500000.00, 'Administrator' ),
--    ( 'Human Resources Manager',  100000.00, 'Administrator' ),
    -- Manager roles
    ( 'Mobile Development Manager',      125000.00, 'Manager' ),
    ( 'Data Application Manager',   125000.00, 'Manager' ),
    -- ( 'Customer Support Manager', 125000.00, 'Manager' ),
    -- ( 'Project Leader',           115000.00, 'Manager' ),
    -- Developer roles
    -- ( 'Senior Software Developer', 80000.00, 'Software Developer' ),
    -- ( 'Software Developer II',     60000.00, 'Software Developer' ),
    ( 'Software Developer',        50000.00, 'Software Developer' ),
    ( 'Programmer',                45000.00, 'Software Developer' )
;

-- Populate some the pre-defined system admin user and some employees
INSERT INTO employee (
    username, password, name, address, taxId
  ) VALUES
    ( 'admin', 'admin', 'Administrator', 'No Address', 'Untaxable' ),
    ( 'joe', 'password', 'Joe Josephson', '123 Main Street
Smalltown, WI 55555', '999-88-7777' ),
    ( 'linda', 'password', 'Linda Linders', 'W1234 Highway 12
Westville, WI 55556', '111-22-3333' ),
    ( 'mobile1', 'password', 'Mobile Developer 1', 'Mobile Dev 1 Address', '001-45-6789' ),
    ( 'mobile2', 'password', 'Mobile Developer 2', 'Mobile Dev 2 Address', '002-45-6789' ),
    ( 'mobile3', 'password', 'Mobile Developer 3', 'Mobile Dev 3 Address', '003-45-6789' ),
    ( 'data1', 'password', 'Data Developer 1', 'Data Dev 1 Address', '004-45-6789' ),
    ( 'data2', 'password', 'Data Developer 2', 'Data Dev 2 Address', '005-45-6789' ),
    ( 'data3', 'password', 'Data Developer 3', 'Data Dev 3 Address', '006-45-6789' )
;

INSERT INTO employeeHistory (
    employee, startDate, rank, numDeductions, salary
  ) VALUES
    (1, DATE_SUB(CURDATE(), INTERVAL 1 YEAR), 1, 0, 0.00),
    (2, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 4, 3, 125000.00),
    (3, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), 5, 2, 125000.00),

    (4, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), 6, 1, 52000.00),
    (5, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), 7, 1, 48000.00),
    (6, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 6, 2, 60000.00),
    (7, DATE_SUB(CURDATE(), INTERVAL 5 MONTH), 6, 1, 50000.00),
    (8, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 7, 2, 65000.00),
    (9, DATE_SUB(CURDATE(), INTERVAL 7 MONTH), 6, 2, 55000.00)
;

INSERT INTO employeeDepartmentAssociation (
	employeeHistory, department
  ) VALUES
    ( 1, 1 ),
    ( 2, 2 ),
    ( 3, 3 ),

    ( 4, 2 ),
    ( 5, 2 ),
    ( 6, 2 ),
    ( 7, 3 ),
    ( 8, 3 ),
    ( 9, 3 )
;
