<?php
/**
 * @category    VTI
 * @package     VTI_ShipmentReport
 * @version     1.0.0
 *
 */

/**
 * Class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Renderer_Render
 */
class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Renderer_Render extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_attributeCode = '';

    protected $_attributeOptions = array();

    public function render(Varien_Object $row)
    {
        $attributeId = (int)$row->getData($this->_attributeCode);
        if (!key_exists($attributeId, $this->_attributeOptions)) {
            /** @var VTI_ShipmentReport_Helper_Data $helper */
            $helper = Mage::helper('vti_shipmentreport');
            $this->_attributeOptions = $helper->getProductAttributeOptionsByCode($this->_attributeCode);
        }
        if (key_exists($attributeId, $this->_attributeOptions)) {
            return $this->_attributeOptions[$attributeId];
        }
        return;
    }
}
