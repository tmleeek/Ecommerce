/*!
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2015, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 2.0
 * @filesource
 */
!function(e){"use strict";/**
 * This file always runs dead last.
 *
 * We use it to initialize optional modules
 * that are loaded by our libraries. For example,
 * the table library loads up the table plugin in
 * a datasource is used.
 *
 * That plugin is ultimately bound here.
 */
// ------------------------------------------------------------------------
// Apply ee_table and ee_toggle_all to any tables that want it
e("table").each(function(){var t;e(this).data("table_config")&&(t=e(this).data("table_config"),e.isPlainObject(t)||(t=e.parseJSON(t)),e(this).table(t)),
// Apply ee_toggle_all only if it's loaded
jQuery().toggle_all&&e(this).toggle_all()}),EE.registered===!1&&!function(e,t){e("#mainMenu").length&&e("body").prepend('<div class="nanner"><p>This copy of ExpressionEngine is <strong>unregistered</strong>. Please visit the <a href="#" onclick="location.href=EE.BASE + \'&C=admin_system&M=software_registration\'">Software Registration</a> page to enter your license information.</p></div>')}(jQuery,this)}(jQuery);