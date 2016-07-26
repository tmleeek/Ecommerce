<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(

    // Additional Key => Value pairs go here

    'undefined' => 'You haven\'t entered your path to the Magento root directory yet. Please do that below.',
    'label_mage' => 'Server path to Magento root directory',
    'help_mage' => 'Can be either absolute server path, or a relative path from your ExpressionEngine root directory',
    'label_store' => 'Magento Store Code',
    'help_run_code' => 'Magento Run Code',
    'help_run_type' => 'Magento Run Type',
    'run_type_website' => 'Website',
    'run_type_store' => 'Store View',
    'label_exclude' => 'Select templates to exclude from Magento processing',
    'help_exclude' => 'Templates that are excluded will not be able to use Magento variables or code.<br/>This will also prevent issues with templates embedded into Magento pages.',
    'label_blocks' => 'Which Magento blocks do you want to be available?',
    'help_blocks' => 'Block contents will be available as <code>{mage:block_name}</code> variables in your templates',
    'label_custom' => 'Additional blocks you would like to load.',
    'help_custom' => 'Comma-separated list of block names (not aliases) you want to load. Blocks must be defined in the <code>&lt;expressionengine_integratee&gt;</code> layout handler in Magento.',

    // END
    ''=>''

);