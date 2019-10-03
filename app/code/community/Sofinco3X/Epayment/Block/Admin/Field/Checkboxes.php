<?php
/**
 * Sofinco3X Epayment module for Magento
 *
 * Feel free to contact Sofinco3X at support@paybox.com for any
 * question.
 *
 * LICENSE: This source file is subject to the version 3.0 of the Open
 * Software License (OSL-3.0) that is available through the world-wide-web
 * at the following URI: http://opensource.org/licenses/OSL-3.0. If
 * you did not receive a copy of the OSL-3.0 license and are unable
 * to obtain it through the web, please send a note to
 * support@paybox.com so we can mail you a copy immediately.
 *
 *
 * @version   3.0.6
 * @author    BM Services <contact@bm-services.com>
 * @copyright 2012-2017 Sofinco3X
 * @license   http://opensource.org/licenses/OSL-3.0
 * @link      http://www.paybox.com/
 */

class Sofinco3X_Epayment_Block_Admin_Field_Checkboxes extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getOptionHtmlAttributes()
    {
        return array('type', 'name', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled');
    }

    protected function _optionToHtml($option, Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId().'_'.Mage::helper('core')->escapeHtml($option['value']);

        $html = '<li><input id="'.$id.'"';
        foreach ($this->_getOptionHtmlAttributes() as $attribute) {
            if ($value = $element->getDataUsingMethod($attribute, $option['value'])) {
                if ($attribute == 'name') {
                    $value .= '[]';
                }

                $html .= ' '.$attribute.'="'.$value.'"';
            }
        }

        $html .= ' value="'.$option['value'].'" />'
            . ' <label for="'.$id.'">' . $option['label'] . '</label></li>'
            . "\n";
        return $html;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setValue(explode(',', $element->getValue()));
        $values = $element->getValues();

        if (!$values) {
            return '';
        }

        $name = $element->getDataUsingMethod('name', 'NONE');
        $html = '<input type="hidden" name="'.$name.'[]" value="NONE"/>';
        $html  .= '<ul class="checkboxes" id="'.$this->escapeHtml($element->getHtmlId()).'">';
        foreach ($values as $value) {
            $html.= $this->_optionToHtml($value, $element);
        }

        $html .= '</ul>'. $this->getAfterElementHtml();

        return $html;
    }
}
