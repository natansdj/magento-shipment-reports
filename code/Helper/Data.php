<?php

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
     * @return array|null
     */
    public function getTree()
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
                $categories[$id]['full_path'] = implode(" > ", $cat['full_path']);
            }
        }
        self::$tree = $categories;
        return self::$tree;
    }
}
