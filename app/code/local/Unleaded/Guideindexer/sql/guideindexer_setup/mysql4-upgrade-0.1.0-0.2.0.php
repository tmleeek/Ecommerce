<?php

$this->startSetup()->run(" 
        TRUNCATE TABLE {$this->getTable('guideindexer')};
        ALTER TABLE {$this->getTable('guideindexer')} DROP `product_line`;
")->endSetup();
