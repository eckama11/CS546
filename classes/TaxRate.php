<?php

class TaxRate
    extends GetterSetter
{

    private $_id;
    private $_minimumSalary;
    private $_taxRate;

    /**
     * Constructs a new TaxRate object.
     *
     * @param   int     $id             The unique database ID assigned to the TaxRate.
     * @param   float   $minimumSalary  The minimum salary at which this tax rate is effective.
     * @param   float   $taxRate        The tax rate to apply (eg. 0.10 = tax rate of 10%)
     */
    public function __construct($id, $minimumSalary, $taxRate) {
        if (!is_numeric($id) || ($id < 0))
            throw new Exception('The $id parameter must be an integer');
        $id = (int) $id;

        if (!is_numeric($minimumSalary) || ($minimumSalary < 0))
            throw new Exception('The $minimumSalary parameter must be a number greater or equal to 0');
        $minimumSalary = (double) $minimumSalary;

        if (!is_numeric($taxRate) || ($taxRate < 0))
            throw new Exception('The $taxRate parameter must be a number greater greater or equal to 0');
        $taxRate = (double) $taxRate;

        $this->_id = $id;
        $this->_minimumSalary = $minimumSalary;
        $this->_taxRate = $taxRate;
    } // __construct

    protected function getId() {
        return $this->_id;
    } // getId

    protected function getMinimumSalary() {
        return $this->_minimumSalary;
    } // getMinimumSalary

    protected function getTaxRate() {
        return $this->_taxRate;
    } // getTaxRate

    public function __toString() {
        return __CLASS__ ."(id=$this->id, minimumSalary=$this->minimumSalary, taxRate=$this->taxRate)";
    } // __toString

} // class TaxRate