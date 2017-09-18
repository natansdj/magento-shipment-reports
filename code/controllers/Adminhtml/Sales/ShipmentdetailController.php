<?php

class VTI_ShipmentReport_Adminhtml_Sales_ShipmentdetailController extends Mage_Adminhtml_Controller_Report_Abstract
{
    public function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/vti_shipmentreport_sales_shipment_detail');
    }

    public function reportAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Shipment Detail Report'));

        $this->loadLayout();
        $this->_setActiveMenu('report');

        $gridBlock = $this->getLayout()->getBlock('vti_shipmentreport.sales.shipmentdetail')->getChild('grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array($gridBlock, $filterFormBlock));

        $this->renderLayout();
    }

    public function exportReportCsvAction()
    {
        $fileName = 'shipment_detail_report.csv';
        $grid = $this->getLayout()->createBlock('vti_shipmentreport/adminhtml_sales_shipmentdetail_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportReportExcelAction()
    {
        $fileName = 'shipment_detail_report.xml';
        $grid = $this->getLayout()->createBlock('vti_shipmentreport/adminhtml_sales_shipmentdetail_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
