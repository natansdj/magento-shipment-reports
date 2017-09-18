<?php

class VTI_ShipmentReport_Block_Adminhtml_Sales_Shipmentdetail_Grid extends VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Grid
{
    protected $_filterVisibility = false;
    protected $_defaultSort = 'date';
    protected $_defaultDir = 'desc';

    /**
     * @param Varien_Object $filterData
     * @return Mage_Sales_Model_Resource_Collection_Abstract
     */
    protected function _getGridCollection($filterData)
    {
        //execute
        /** @var Mage_Sales_Model_Resource_Order_Shipment_Item_Collection $collection */
        $collection = Mage::getResourceModel('vti_shipmentreport/order_shipment_item_collection');
        $collection
            ->addFieldToFilter('shipment.created_at', array('gteq' => $filterData->getData('from')))
            ->addFieldToFilter('shipment.created_at', array('lteq' => $filterData->getData('to')));
        $collection->addFieldToSelect(
            array(
                'name',
                'sku',
                'qty',
                'product_id',
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

        /** @var Magento_Db_Adapter_Pdo_Mysql $adapter */
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $shipmentTrackSelect = $adapter->select()
            ->from(
                Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track'),
                array(
                    'order_id',
                    'title',
                    'expected_time'
                )
            )
            ->group('parent_id');

        $collection
            ->getSelect()
            ->columns("DATE(shipment.created_at) AS date")
            ->joinLeft(
                array('shipment' => Mage::getSingleton('core/resource')->getTableName('sales/shipment')),
                'main_table.parent_id = shipment.entity_id',
                array(
                    'created_at' => 'shipment.created_at',
                )
            )
            ->joinLeft(
                array('shipment_track' => $shipmentTrackSelect),
                'shipment.order_id = shipment_track.order_id',
                array(
                    'track_method' => 'shipment_track.title',
                    'track_time' => 'shipment_track.expected_time'
                )
            )
            ->joinLeft(
                array('shipment_grid' => Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_grid')),
                'shipment.entity_id = shipment_grid.entity_id',
                array(
                    'order_increment_id' => 'shipment_grid.order_increment_id',
                )
            )
            ->joinLeft(
                array('shipping_address' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')),
                'shipping_address.entity_id = shipment.shipping_address_id AND shipping_address.address_type=\'shipping\'',
                array('region', 'country_id', 'city')
            )
            ->joinLeft(
                array('order_items' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item')),
                'order_items.order_id = shipment.entity_id AND order_items.parent_item_id = NULL',
                array(
                    'store_id' => "shipment.store_id"
                )
            )->joinLeft(
                array($vb_valueTable => $vb_attribute->getBackend()->getTable()),
                "main_table.product_id={$vb_valueTable}.entity_id"
                . " AND {$vb_valueTable}.attribute_id='{$vb_attributeId}'"
                . " AND {$vb_valueTable}.store_id={$store_id}",
                array('vesbrand' => "{$vb_valueTable}.value")
            )
            ->joinLeft(
                array($vb_optionTable => $vb_brand_table),
                "{$vb_optionTable}.{$vb_tablePkName}={$vb_valueTable}.value",
                array('vesbrand_title' => "{$vb_optionTable}.title", 'brand_id')
            );

        $collection->getSelect()->group('main_table.entity_id');

        $collection->setOrder('main_table.entity_id', 'DESC');

        return $collection;
    }

    protected function _afterLoadCollection()
    {
        //Add group to collection here
        if (array_key_exists('main_table', $this->getCollection()->getSelect()->getPart('from'))
            && !in_array('main_table.entity_id', $this->getCollection()->getSelect()->getPart('group'))
        ) {
            $this->getCollection()->getSelect()->group('main_table.entity_id');
        }

        $this->getCollection()
            ->getSelect()
            ->distinct(true);

        return $this;
    }

    protected function _prepareColumns()
    {
        /** @var VTI_ShipmentReport_Helper_Data $helper */
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

        $this->addColumn('order_increment_id', array(
            'header' => $helper->__('Order Number'),
            'index' => 'order_increment_id',
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
//        $this->addExportType('*/*/exportReportExcel', $helper->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
