<?php
/**
 * Penambahan field include_in_megamenu di EAV untuk Catalog Category
 */
$installer = $this;
$installer->startSetup();

try {
    // uncomment the line below if you already have include_in_megamenu EAV
    //$installer->removeAttribute('catalog_category', 'include_in_megamenu');

    $installer->addAttribute('catalog_category', 'include_in_megamenu', array (
        'group' => 'General Information',
        'type' => 'int',
        'input' => 'select',
        'source'  => 'eav/entity_attribute_source_boolean',
        'label' => 'Include in Mega Menu',
        'required' => true,
        'default' => 0,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    ));

    // To populate the default data, go to shell/bilna and run:
    // php customizationCatalogCategory.php
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();
