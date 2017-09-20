<?php
/**
 * @category    VTI
 * @package     VTI_ShipmentReport
 * @version     1.0.0
 *
 */

/**
 * Class VTI_ShipmentReport_Model_Order
 */
class VTI_ShipmentReport_Model_Order extends Mage_Sales_Model_Order
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _getCollection()
    {
        return $this->getCollection();
    }
}
