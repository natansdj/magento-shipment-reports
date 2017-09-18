<?php

class VTI_ShipmentReport_Block_Adminhtml_Sales_Shipmentdetail_Renderer_Categories extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Render categories to show in export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        /** @var VTI_ShipmentReport_Helper_Data $helper */
        $helper = Mage::helper('vti_shipmentreport');
        $tree = $helper->getTree();

        /** @var Mage_Catalog_Model_Product $product */
        $rowProductID = $row->getProductId();
        $product = Mage::getModel('catalog/product')->load($rowProductID);
        $cats = $product->getCategoryIds();
        $cat_paths = array();
        foreach ($cats as $key => $cat) {
            if (isset($tree[$cat])) {
                $cat_paths[] = $tree[$cat]['full_path'];
            }
        }
        sort($cat_paths);

        return implode("<br />", $cat_paths);
    }
}
