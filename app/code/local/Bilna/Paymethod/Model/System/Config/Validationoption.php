<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Validationoption
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Validationoption {
    public function toOptionArray() {
        return array (
            array ('value' => 'hours', 'label' => 'Hours'),
            array ('value' => 'minutes', 'label' => 'Minutes')
        );
    }
}
