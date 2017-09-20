<?php
/**
 * @category    VTI
 * @package     VTI_ShipmentReport
 * @version     1.0.0
 *
 */

/**
 * Class VTI_ShipmentReport_Helper_Data
 */
class VTI_ShipmentReport_Helper_Data extends Mage_Core_Helper_Abstract
{
    public static $tree = null;
    protected $_categories = null;

    public function getProductAttributeOptionsByCode($code)
    {
        $attributesOptions = array();
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $code);
        foreach ($attribute->getSource()->getAllOptions(true, true) as $option) {
            $attributesOptions[$option['value']] = $option['label'];
        }
        return $attributesOptions;
    }

    /**
     * get categories for options
     *
     * @return array
     */
    public function getTreeOptions()
    {
        $tree = $this->getTree();
        $options = array();
        foreach ($tree as $id => $v) {
            if ($id == 3) {
                continue;
            }
            $options[$id] = $v['full_path'];
        }
        asort($options);
        return $options;
    }

    /**
     * get categories tree for export
     *
     * @param string $separator
     * @return array|null
     */
    public function getTree($separator = " > ")
    {
        if (self::$tree) {
            return self::$tree;
        }

        $category_tree = Mage::getModel('catalog/category');
        $tree = $category_tree->getTreeModel();
        $tree->load();

        $ids = $tree->getCollection()->getAllIds();
        $categories = array();
        if ($ids) {
            foreach ($ids as $id) {
                $category_tree->load($id);
                $path = $category_tree->getPath();
                $p = explode("/", $path);

                if (count($p) <= 1 || $p[1] != 3) {
                    continue;
                }
                $categories[$id]['name'] = $category_tree->getName();
                $categories[$id]['path'] = $path;
            }

            foreach ($categories as $id => $cat) {
                $cat['full_path'] = array();
                $pcat = explode('/', $cat['path']);
                foreach ($pcat as $c) {
                    if ($c == 1 || $c == 3) {
                        continue;
                    }
                    $cat['full_path'][] = $categories[$c]['name'];
                }
                $categories[$id]['full_path'] = implode($separator, $cat['full_path']);
            }
        }
        self::$tree = $categories;
        return self::$tree;
    }

    /**
     * @param $value
     * @param Mage_Sales_Model_Order_Shipment_Item $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $block
     * @return mixed
     */
    public function decorateHTML($value, $row, $block, $isExport)
    {
        if ($isExport) {
            //Remove html tags, but leave "<" and ">" signs
//            $val = $block->helper('core')->removeTags($value);

            //unescape html entities
            $valUnescaped = htmlspecialchars_decode($value, ENT_QUOTES);
            return $valUnescaped;
        }

        return $value;
    }
}
