<?php
/**
 * @category    VTI
 * @package     VTI_ShipmentReport
 * @version     1.0.0
 *
 */

/**
 * Class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Renderer_Categories
 */
class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Renderer_Categories extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render categories to show in grid
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $cat_paths = $this->baseRender($row);

        return implode("<br />", $cat_paths);
    }

    /**
     * @param Varien_Object $row
     * @return array
     */
    protected function baseRender(Varien_Object $row)
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
            if (isset($tree[$cat]) && $tree[$cat]['full_path']) {
                $cat_paths[] = $tree[$cat]['full_path'];
            }
        }
        sort($cat_paths);

        return $cat_paths;
    }

    /**
     * Render categories to show in export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $cat_paths = $this->baseRender($row);

        return implode(", \n", $cat_paths);
    }
}
