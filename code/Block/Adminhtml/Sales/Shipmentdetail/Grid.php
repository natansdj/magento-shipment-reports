<?php

class VTI_ShipmentReport_Block_Adminhtml_Sales_Shipmentdetail_Grid extends VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    /**
     * @param Varien_Object $filterData
     * @return Mage_Sales_Model_Resource_Order_Shipment_Collection
     */
    protected function _getGridCollection($filterData)
    {
        //execute
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $collection */
        $collection = Mage::getResourceModel('sales/order_shipment_collection');
        $collection
            ->addFieldToFilter('main_table.created_at', array('gteq' => $filterData->getData('from')))
            ->addFieldToFilter('main_table.created_at', array('lteq' => $filterData->getData('to')));
        $collection->addFieldToSelect(
            array(
                'created_at',
                'increment_id'
            )
        );

        //VesBrand
        $vb_attributeCode = 'vesbrand';

        /** @var Mage_Eav_Model_Entity_Attribute $vb_attribute */
        $vb_attribute = Mage::getModel("eav/entity_attribute")
            ->loadByCode('catalog_product', $vb_attributeCode);
        $vb_attributeId = $vb_attribute->getId();
        $vb_valueTable = $vb_attributeCode . '_t1';

        $vb_brand_table = Mage::getSingleton('core/resource')->getTableName('ves_brand/brand');
        $vb_tablePkName = 'brand_id';
        $vb_optionTable = $vb_attributeCode . '_option_value_t1';
        $store_id = 0;

        $collection
            ->getSelect()
            ->columns('DATE(main_table.created_at) AS date')
            ->joinLeft(
                array('shipment_item' => Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_item')),
                'main_table.entity_id = shipment_item.parent_id',
                array(
                    'entity_id' => 'shipment_item.entity_id',
                    'name' => 'shipment_item.name',
                    'sku' => 'shipment_item.sku',
                    'qty' => 'shipment_item.qty',
                )
            )->joinLeft(
                array('shipment_track' => Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track')),
                'main_table.order_id = shipment_track.order_id',
                array(
                    'track_method' => 'shipment_track.title',
                    'track_time' => 'shipment_track.expected_time'
                )
            )->joinLeft(
                array('shipping_address' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')),
                'shipping_address.entity_id = main_table.shipping_address_id AND shipping_address.address_type=\'shipping\'',
                array('region', 'country_id', 'city')
            )
            ->joinLeft(
                array('order_items' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item')),
                'order_items.order_id = main_table.entity_id AND order_items.parent_item_id = NULL',
                array(
                    'store_id' => "main_table.store_id"
                )
            )->joinLeft(
                array($vb_valueTable => $vb_attribute->getBackend()->getTable()),
                "shipment_item.product_id={$vb_valueTable}.entity_id"
                . " AND {$vb_valueTable}.attribute_id='{$vb_attributeId}'"
                . " AND {$vb_valueTable}.store_id={$store_id}",
                array('vesbrand' => "{$vb_valueTable}.value")
            )
            ->joinLeft(
                array($vb_optionTable => $vb_brand_table),
                "{$vb_optionTable}.{$vb_tablePkName}={$vb_valueTable}.value",
                array('vesbrand_title' => "{$vb_optionTable}.title", 'brand_id')
            )
            ->joinLeft(
                array('configurable' => Mage::getSingleton('core/resource')->getTableName('catalog/product')),
                'configurable.entity_id = shipment_item.product_id',
                array(
                    'configurable_id' => 'configurable.entity_id',
                    'configurable_sku' => 'configurable.sku',
                )
            )
            ->group('shipment_item.entity_id');

        Mage::log((string)$collection->getSelect());

        return $collection;
    }

    protected function _afterLoadCollection()
    {
        $configurableProductsIds = implode(',', $this->getConfigurableIds($this->getCollection()));

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $catProductTable = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
        $sql = "SELECT category_id, product_id FROM `{$catProductTable}` WHERE `product_id` in ({$configurableProductsIds})";
        $rows = $connection->fetchAll($sql);

        $productCategoriesIds = array();
        foreach ($rows as $row) {
            $productCategoriesIds[$row['product_id']][] = $row['category_id'];
        }

        foreach ($this->getCollection() as $item) {
            if (array_key_exists($item->getConfigurableId(), $productCategoriesIds)) {
                $item->setProductCategoriesIds($productCategoriesIds[$item->getConfigurableId()]);
            }
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Resource_Collection_Abstract|Varien_Data_Collection $collection
     * @return array
     */
    public function getConfigurableIds($collection)
    {
        /** @var Varien_Db_Select $idsSelect */
        $idsSelect = clone $collection->getSelect();
        $idsSelect->reset(Zend_Db_Select::COLUMNS);

        $idsSelect
            ->columns(array('entity_id'), 'configurable')
            ->distinct(true);

        return $collection->getConnection()->fetchCol($idsSelect);
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('vti_shipmentreport');

        $this->addColumn('date', array(
            'header' => $helper->__('Ship Date'),
            'index' => 'date',
            'width' => 100,
            'filter' => false,
            'sortable' => false,
            'align' => 'right',
            'type' => 'date',
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('increment_id', array(
            'header' => $helper->__('Order Number'),
            'index' => 'increment_id',
            'width' => 100,
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('track_method', array(
            'header' => $helper->__('Delivery Method'),
            'index' => 'track_method',
            'width' => 100,
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('track_time', array(
            'header' => $helper->__('Delivery Time'),
            'index' => 'track_time',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('region', array(
            'header' => $helper->__('Delivery Region'),
            'index' => 'region',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('name', array(
            'header' => $helper->__('Product Name'),
            'index' => 'name',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('sku', array(
            'header' => $helper->__('SKU'),
            'index' => 'sku',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('qty', array(
            'header' => $helper->__('Product Qty'),
            'index' => 'qty',
            'width' => 100,
            'filter' => false,
            'sortable' => false,
            'type' => 'number'
        ));

        $this->addColumn('vesbrand_title', array(
            'header' => $helper->__('Brand'),
            'index' => 'vesbrand_title',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('product_categories_ids', array(
            'header' => $helper->__('Product Categories'),
            'index' => 'product_categories_ids',
            'width' => 200,
            'filter' => false,
            'sortable' => false,
            'renderer' => 'vti_shipmentreport/adminhtml_sales_shipmentdetail_renderer_categories',
        ));

        $this->addExportType('*/*/exportReportCsv', $helper->__('CSV'));
        $this->addExportType('*/*/exportReportExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
