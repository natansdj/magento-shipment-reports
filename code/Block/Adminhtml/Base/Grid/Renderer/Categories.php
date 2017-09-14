<?php

class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Renderer_Categories extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $_allProductCategories = array();
        /** @var VTI_ShipmentReport_Helper_Data $helper */
        $helper = Mage::helper('vti_shipmentreport');
        $categoriesTree = $helper->getCategoriesTree();

        $product = Mage::getModel('catalog/product')->setId($row->getConfigurableId());
        $productCategories = $product->getCategoryIds();

        foreach ($productCategories as $category) {
            if (key_exists($category, $categoriesTree)) {
                $_allProductCategories[] = $categoriesTree[$category];
            }
        }
        return implode(', ', $_allProductCategories);
    }
}
