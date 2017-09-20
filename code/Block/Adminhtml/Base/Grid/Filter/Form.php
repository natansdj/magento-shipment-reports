<?php
/**
 * @category    VTI
 * @package     VTI_ShipmentReport
 * @version     1.0.0
 *
 */

/**
 * Class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Filter_Form
 */
class VTI_ShipmentReport_Block_Adminhtml_Base_Grid_Filter_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('vti_shipmentreport');
        $actionUrl = $this->getUrl('*/*/index');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'vti_shipmentreport_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));

        $fieldset->addField('from', 'date', array(
            'name' => 'from',
            'format' => $dateFormatIso,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => $helper->__('From'),
            'title' => $helper->__('From'),
            'class' => 'validate-date validate-date-range date-range-report_filter-from',
            'required' => true
        ));

        $fieldset->addField('to', 'date', array(
            'name' => 'to',
            'format' => $dateFormatIso,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => $helper->__('To'),
            'title' => $helper->__('To'),
            'class' => 'validate-date validate-date-range date-range-report_filter-to',
            'required' => true
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _initFormValues()
    {
        $data = $this->getFilterData()->getData();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }


}
