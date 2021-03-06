<?php

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {
		parent::__construct();
		
		//$this->_removeButton('reset'); //remove reset button
		//$this->_removeButton('delete'); //remove delete button
		
		$this->_objectId = 'id';
		$this->_blockGroup = 'staticarea';
		$this->_controller = 'adminhtml_manage';
	
		//$this->_updateButton('save', 'label', Mage::helper('staticarea')->__('Save Static Area'));
		//$this->_updateButton('delete', 'label', Mage::helper('staticarea')->__('Delete Static Area'));
	
		if($this->getRequest()->getParam('id')) {
            $this->_addButton('addcontent', array(
                'label' => $this->__('Add Content'),
                'onclick' => 'bilnaisAddContent()',
                'class' => 'add',
                'id' => 'bilna-add-content'
            ), 0);
        }

		$this->_addButton('saveandcontinueedit', array(
				'label' => Mage::helper('staticarea')->__('Save And Continue Edit'),
				'onclick' => 'bilnaStaticareaSaveAndContinueEdit()',
				'class' => 'save',
				'id' => 'bilnastaticarea-save-and-continue'
		), -200);
	
		$this->_formScripts[] = "
        function bilnaisAddContent() {
            staticarea_tabsJsTabs.tabs[1].show();
            bilnaISAjaxForm.showForm(".$this->getRequest()->getParam('id').");
        }
        function bilnastaticarea_prepareForm() {

        }
        function bilnaStaticareaSaveAndContinueEdit() {
            if($('edit_form').action.indexOf('continue/1/')<0){
                $('edit_form').action += 'continue/1/';
            }
            if($('edit_form').action.indexOf('continue_tab/')<0){
                $('edit_form').action += 'continue_tab/'+staticarea_tabsJsTabs.activeTab.name+'/';
			}                
            bilnastaticarea_prepareForm();
            editForm.submit($('edit_form').action+'back/edit/');
        }

        if(bilnaISSettings)
            bilnaISSettings.setOption('imagesAjaxFormUrl', '{$this->getUrl('staticarea/adminhtml_manage/ajaxform')}');
	
        
        ";
	}
	
	public function getHeaderText() {
		if (Mage::registry('staticarea_data') && Mage::registry('staticarea_data')->getId()) {
			return Mage::helper("staticarea")->__("Edit Static Area '%s'", $this->htmlEscape(Mage::registry("staticarea_data")->getpromo_title()));
		} else {
			return Mage::helper('staticarea')->__('Add Static Area');
		}
	}

	protected function _prepareLayout() {
	    parent::_prepareLayout();
	    if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
	        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
	    }
	}
	
}
