<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `last_counter` " . 'int(10)' . " COLLATE 'utf8_general_ci' NOT NULL DEFAULT '0';");

$installer->endSetup();
