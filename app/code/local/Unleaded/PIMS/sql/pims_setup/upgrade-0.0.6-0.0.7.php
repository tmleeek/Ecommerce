<?php

$installer = $this;
$installer->startSetup();

// We need to give a source model too all multi selects that use the text table that do not
// have a source model
$query = 'UPDATE eav_attribute SET source_model = "eav/entity_attribute_source_table" '
	. 'WHERE frontend_input = "multiselect" AND backend_type = "text" '
	. 'AND source_model IS NULL;';
$installer->run($query);

$installer->endSetup();