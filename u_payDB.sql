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
    activeFlag BIT NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    rank INT NOT NULL, taxId VARCHAR(255) NOT NULL,
    numDeductions INT NOT NULL,
    salary DECIMAL(9,2) UNSIGNED NOT NULL,
    FOREIGN KEY (rank) REFERENCES rank(id),
    PRIMARY KEY (id)
);

CREATE TABLE loginSession(
    sessionId VARCHAR(255) NOT NULL,
    authenticatedEmployee INT NOT NULL,
    PRIMARY KEY(sessionID),
    FOREIGN KEY(authenticatedEmployee) REFERENCES employee(id)
);


CREATE TABLE employeeDepartmentAssociation(
    employee INT NOT NULL,
    department INT NOT NULL,
    FOREIGN KEY (employee) REFERENCES employee(id),
    FOREIGN KEY (department) REFERENCES department(id),
    PRIMARY KEY (employee, department)
);

CREATE TABLE paystub(
    id INT NOT NULL AUTO_INCREMENT,
    payPeriodStartDate DATETIME NOT NULL,
    employee INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    rank INT NOT NULL,
    taxId VARCHAR(255) NOT NULL,
    salary DECIMAL(9,2) UNSIGNED NOT NULL,
    numDeductions INT NOT NULL,
    taxWithheld DECIMAL(9,2) UNSIGNED NOT NULL,
    taxRate DECIMAL(7,6) UNSIGNED NOT NULL,
    FOREIGN KEY (employee) REFERENCES employee(id),
    FOREIGN KEY (rank) REFERENCES rank(id),
    PRIMARY KEY (id)
);	

CREATE TABLE paystubDepartmentAssociation(
    paystub INT NOT NULL,
    department INT NOT NULL,
    FOREIGN KEY (paystub) REFERENCES paystub(id),
    FOREIGN KEY (department) REFERENCES department(id),
    PRIMARY KEY (paystub, department)
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
    (    0.00, 0.000000 ),
    ( 1000.00, 0.100000 ),
    ( 2000.00, 0.150000 ),
    ( 3000.00, 0.200000 ),
    ( 4000.00, 0.250000 )
;

-- Populate the departments
INSERT INTO department (
    name
  ) VALUES
    ( 'Corporate' ),
    ( 'Human Resources' ),
    ( 'Marketing' ),
    ( 'Customer Support' ),
    ( 'Quality Assurance' ),
    ( 'Graphic Design' ),
    ( 'Documentation' ),
    ( 'Legacy Product Maintenance' ),
    ( 'New Product Development' )
;

-- Populate the ranks
INSERT INTO rank (
    name, baseSalary, employeeType
  ) VALUES
    -- Administrator roles
    ( 'Built-in System Administrator', 0.00, 'Administrator' ),
    ( 'President',               1000000.00, 'Administrator' ),
    ( 'Vice-President',           500000.00, 'Administrator' ),
    ( 'Human Resources Manager',  100000.00, 'Administrator' ),
    -- Manager roles
    ( 'New Product Manager',      125000.00, 'Manager' ),
    ( 'Legacy Product Manager',   125000.00, 'Manager' ),
    ( 'Customer Support Manager', 125000.00, 'Manager' ),
    ( 'Project Leader',           115000.00, 'Manager' ),
    -- Developer roles
    ( 'Senior Software Developer', 80000.00, 'Software Developer' ),
    ( 'Software Developer II',     60000.00, 'Software Developer' ),
    ( 'Software Developer',        50000.00, 'Software Developer' ),
    ( 'Programmer',                45000.00, 'Software Developer' )
;

-- Populate the pre-defined system admin user
INSERT INTO employee (
    activeFlag, username, password, name, address, rank, taxId, numDeductions, salary
  ) VALUES
    ( 1, 'admin', 'admin', 'Administrator', '',  1, '', 0, 0.00 );