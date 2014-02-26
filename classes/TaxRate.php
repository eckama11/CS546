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
    public __construct($id, $minimumSalary, $taxRate) {
        if (!is_int($id) || ($id < 0))
            throw new Exception('The $id parameter must be an integer');

        if (!is_numeric($minimumSalary) || ($minimumSalary < 0))
            throw new Exception('The $minimumSalary parameter must be a number greater than 0');

        if (!is_numeric($taxRate) || ($taxRate < 0))
            throw new Exception('The $taxRate parameter must be a number greater than 0');

        $this->_id = $id;
        $this->_minimumSalary = $minimumSalary;
        $this->_taxRate = $taxRate;
    } // __construct

    protected getId() {
        return $this->_id;
    } // getId

    protected getMinimumSalary() {
        return $this->_minimumSalary;
    } // getMinimumSalary

    protected getTaxRate() {
        return $this->_taxRate;
    } // getTaxRate

    public function __toString() {
        return __CLASS__ ."(id=$this->id, minimumSalary=$this->minimumSalary, taxRate=$this->taxRate)";
    } // __toString

} // class TaxRate