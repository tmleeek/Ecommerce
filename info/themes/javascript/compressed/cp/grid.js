!function(t){/**
 * Grid Namespace
 */
var i=window.Grid={
// Event handlers stored here, direct access outside only from
// Grid.Publish class
_eventHandlers:[],/**
	 * Binds an event to a fieldtype
	 *
	 * Available events are:
	 * 'display' - When a row is displayed
	 * 'remove' - When a row is deleted
	 * 'beforeSort' - Before sort starts
	 * 'afterSort' - After sort ends
	 * 'displaySettings' - When settings form is displayed
	 *
	 * @param	{string}	fieldtypeName	Class name of fieldtype so the
	 *				correct cell object can be passed to the handler
	 * @param	{string}	action			Name of action
	 * @param	{func}		func			Callback function for event
	 */
bind:function(t,i,e){void 0==this._eventHandlers[i]&&(this._eventHandlers[i]=[]),
// Each fieldtype gets one method per handler
this._eventHandlers[i][t]=e}};/**
 * Grid Publish class
 *
 * @param	{string}	field		Field ID of table to instantiate as a Grid
 */
i.Publish=function(i,e){this.root=t(i),this.blankRow=this.root.find("tr.blank_row"),this.emptyField=this.root.find("tr.empty_field"),this.rowContainer=this.root.find(".grid_row_container"),this.settings=void 0!==e?e:EE.grid_field_settings[i.id],this.init(),this.eventHandlers=[]},i.Publish.prototype={init:function(){this._bindSortable(),this._bindAddButton(),this._bindDeleteButton(),this._toggleRowManipulationButtons(),this._fieldDisplay(),
// Store the original row count so we can properly increment new
// row placeholder IDs in _addRow()
this.original_row_count=this._getRows().size(),
// Disable input elements in our blank template container so they
// don't get submitted on form submission
this.blankRow.find(":input").attr("disabled","disabled")},/**
	 * Allows rows to be reordered
	 */
_bindSortable:function(){var i=this;this.rowContainer.sortable({axis:"y",// Only allow vertical dragging
containment:"parent",// Contain to parent
handle:"td.grid_handle",// Set drag handle
cancel:"td.grid_sort_cancel",// Do not allow sort on this handle
items:"tr.grid_row",// Only allow these to be sortable
sort:EE.sortable_sort_helper,// Custom sort handler
helper:function(i,e){var n=e.children(),o=e.clone();return o.children().each(function(i){
// Set helper cell sizes to match the original sizes
t(this).width(n.eq(i).width())}),o},
// Fire 'beforeSort' event on sort start
start:function(t,e){i._fireEvent("beforeSort",e.item)},
// Fire 'afterSort' event on sort stop
stop:function(t,e){i._fireEvent("afterSort",e.item)}})},/**
	 * Adds rows to a Grid field based on the fields minimum rows setting
	 * and how many rows already exist
	 */
_addMinimumRows:function(){
// Figure out how many rows we need to add
var t=this._getRows().size(),i=this.settings.grid_min_rows-t;
// Add the needed rows
for(
// Show empty field message if field is empty and no rows are needed
0==t&&0==i&&this.emptyField.show();i>0;)this._addRow(),i--},/**
	 * Toggles the visibility of the Add button and Delete buttons for rows
	 * based on the number of rows present and the max and min rows settings
	 */
_toggleRowManipulationButtons:function(){var t=this._getRows().size();if(""!==this.settings.grid_max_rows){var i=this.root.find(".grid_button_add");
// Show add button if row count is below the max rows setting
i.toggle(t<this.settings.grid_max_rows)}if(""!==this.settings.grid_min_rows){var e=this.root.find(".grid_button_delete");
// Show delete buttons if the row count is above the min rows setting
e.toggle(t>this.settings.grid_min_rows)}
// Do not allow sortable to run when there is only one row, otherwise
// the row becomes detached from the table and column headers change
// width in a fluid-column-width table
this.rowContainer.find("td.grid_handle").toggleClass("grid_sort_cancel",1==t)},/**
	 * Returns current number of data rows in the Grid field
	 *
	 * @return	{int}	Number of rows
	 */
_getRows:function(){return this.rowContainer.find("tr.grid_row").not(this.blankRow.add(this.emptyField))},/**
	 * Binds click listener to Add button to insert a new row at the bottom
	 * of the field
	 */
_bindAddButton:function(){var t=this;this.root.find(".grid_button_add, .grid_link_add").on("click",function(i){i.preventDefault(),t._addRow()})},/**
	 * Inserts new row at the bottom of our field
	 */
_addRow:function(){
// Clone our blank row
el=this.blankRow.clone(),el.removeClass("blank_row"),
// Increment namespacing on inputs
this.original_row_count++,el.html(el.html().replace(RegExp("new_row_[0-9]{1,}","g"),"new_row_"+this.original_row_count)),
// Enable inputs
el.find(":input").removeAttr("disabled"),
// Append the row to the end of the row container
this.rowContainer.append(el),
// Make sure empty field message is hidden
this.emptyField.hide(),
// Hide/show delete buttons depending on minimum row setting
this._toggleRowManipulationButtons(),
// Fire 'display' event for the new row
this._fireEvent("display",el)},/**
	 * Binds click listener to Delete button in row column to delete the row
	 */
_bindDeleteButton:function(){var i=this;this.root.on("click",".grid_button_delete",function(e){e.preventDefault(),row=t(this).parents("tr.grid_row"),
// Fire 'remove' event for this row
i._fireEvent("remove",row),
// Remove the row
row.remove(),i._toggleRowManipulationButtons(),
// Show our empty field message if we have no rows left
0==i._getRows().size()&&i.emptyField.show()})},/**
	 * Called after main initialization to fire the 'display' event
	 * on pre-exising rows
	 */
_fieldDisplay:function(){var i=this;setTimeout(function(){i._getRows().each(function(){i._fireEvent("display",t(this))}),i._addMinimumRows()},500)},/**
	 * Fires event to fieldtype callbacks
	 *
	 * @param	{string}		action	Action name
	 * @param	{jQuery object}	row		jQuery object of affected row
	 */
_fireEvent:function(e,n){
// If no events regsitered, don't bother
if(void 0!==i._eventHandlers[e])
// For each fieldtype binded to this action
for(var o in i._eventHandlers[e])
// Find the sepecic cell(s) for this fieldtype and send each
// to the fieldtype's event hander
n.find('td[data-fieldtype="'+o+'"]').each(function(){i._eventHandlers[e][o](t(this))})}},/**
 * Grid Settings class
 */
i.Settings=function(i){this.root=t("#grid_settings"),this.settingsScroller=this.root.find("#grid_col_settings_container"),this.settingsContainer=this.root.find("#grid_col_settings_container_inner"),this.colTemplateContainer=t("#grid_col_settings_elements"),this.blankColumn=this.colTemplateContainer.find(".grid_col_settings"),this.settings=i,this.init()},i.Settings.prototype={init:function(){this._bindResize(),this._bindSortable(),this._bindAddButton(),this._bindCopyButton(),this._bindDeleteButton(),this._bindDeleteButton(),this._toggleDeleteButtons(),this._bindColTypeChange(),this._bindSubmit(),this._highlightErrors(),
// If this is a new field, bind the automatic column title plugin
// to the first column
this._bindAutoColName(this.root.find('div.grid_col_settings[data-field-name^="new_"]')),
// Fire displaySettings event
this._settingsDisplay(),
// Disable input elements in our blank template container so they
// don't get submitted on form submission
this.colTemplateContainer.find(":input").attr("disabled","disabled")},/**
	 * Upon page load, we need to resize the settings container to match the
	 * width of the page, minus the width of the labels on the left, and also
	 * need to resize the column container to fit the number of columns we have
	 */
_bindResize:function(){var i=this;t(document).ready(function(){i._resizeSettingsContainer(),
// Resize settings container on window resize
t(window).resize(function(){i._resizeSettingsContainer()}),
// Resize when Grid is selected from field type dropdown
t("#field_type").change(function(){"grid"==t(this).val()&&i._resizeSettingsContainer()}),
// Now, resize the inner container to fit the number of columns
// we have ready on page load
i._resizeColContainer()})},/**
	 * Resizes the scrollable settings container to fit within EE's settings
	 * table; this is called on page load and window resize
	 */
_resizeSettingsContainer:function(){
// First need to set container smaller so that it's not affecting the
// root with; for example, if the user makes the window width smaller,
// the root with won't change if the settings scroller container doesn't
// get smaller, thus the container runs off the page
this.settingsScroller.width(500),this.settingsScroller.width(this.root.width()-this.root.find("#grid_col_settings_labels").width())},/**
	 * Resizes column container based on how many columns it contains
	 *
	 * @param	{boolean}	animated	Whether or not to animate the resize
	 */
_resizeColContainer:function(t){this.settingsContainer.animate({width:this._getColumnsWidth()},1==t?400:0)},/**
	 * Calculates total width the columns in the container should take up,
	 * plus a little padding for the Add button
	 *
	 * @return	{int}	Calculated width
	 */
_getColumnsWidth:function(){var t=this.root.find(".grid_col_settings");
// 75px of extra room for the add button
return t.size()*t.width()+75},/**
	 * Allows columns to be reordered
	 */
_bindSortable:function(){this.settingsContainer.sortable({axis:"x",// Only allow horizontal dragging
containment:"parent",// Contain to parent
handle:"div.grid_data_type",// Set drag handle to the top box
items:".grid_col_settings",// Only allow these to be sortable
sort:EE.sortable_sort_helper})},/**
	 * Binds click listener to Add button to insert a new column at the end
	 * of the columns
	 */
_bindAddButton:function(){var t=this;this.root.find(".grid_button_add").on("click",function(i){i.preventDefault(),t._insertColumn(t._buildNewColumn())})},/**
	 * Binds click listener to Copy button in each column to clone the column
	 * and insert it after the column being cloned
	 */
_bindCopyButton:function(){var i=this;this.root.on("click","a.grid_col_copy",function(e){e.preventDefault();var n=t(this).parents(".grid_col_settings");i._insertColumn(
// Build new column based on current column
i._buildNewColumn(n),
// Insert AFTER current column
n)})},/**
	 * Binds click listener to Delete button in each column to delete the column
	 */
_bindDeleteButton:function(){var i=this;this.root.on("click",".grid_button_delete",function(e){e.preventDefault();var n=t(this).parents(".grid_col_settings");
// Only animate column deletion if we're not deleting the last column
n.index()==t("#grid_settings .grid_col_settings:last").index()?(n.remove(),i._resizeColContainer(!0),i._toggleDeleteButtons()):n.animate({opacity:0},200,function(){
// Clear HTML before resize animation so contents don't
// push down bottom of column container while resizing
n.html(""),n.animate({width:0},200,function(){n.remove(),i._resizeColContainer(!0),i._toggleDeleteButtons()})})})},/**
	 * Looks at current column count, and if there are multiple columns,
	 * shows the delete buttons; otherwise, hides delete buttons if there is
	 * only one column
	 */
_toggleDeleteButtons:function(){var t=this.root.find(".grid_col_settings").size(),i=this.root.find(".grid_button_delete");i.toggle(t>1)},/**
	 * Inserts a new column after a specified element
	 *
	 * @param	{jQuery Object}	column		Column to insert
	 * @param	{jQuery Object}	insertAfter	Element to insert the column
	 *				after; if left blank, defaults to last column
	 */
_insertColumn:function(i,e){var n=t("#grid_settings .grid_col_settings:last");
// Default to inserting after the last column
void 0==e&&(e=n),
// If we're inserting a column in the middle of other columns,
// animate the insertion so it's clear where the new column is
e.index()!=n.index()&&i.css({opacity:0}),i.insertAfter(e),this._resizeColContainer(),this._toggleDeleteButtons(),
// If we are inserting a column after the last column, scroll to
// the end of the column container
e.index()==n.index()&&
// Scroll container to the very end
this.settingsScroller.animate({scrollLeft:this._getColumnsWidth()},700),i.animate({opacity:1},400),
// Bind automatic column name
this._bindAutoColName(i),
// Fire displaySettings event
this._fireEvent("displaySettings",t(".grid_col_settings_custom > div",i))},/**
	 * Binds ee_url_title plugin to column label box to auto-populate the
	 * column name field; this is only applied to new columns
	 *
	 * @param	{jQuery Object}	el	Column to bind ee_url_title to
	 */
_bindAutoColName:function(i){i.each(function(i,e){t("input.grid_col_field_label",e).bind("keyup keydown",function(){t(this).ee_url_title(t(e).find("input.grid_col_field_name"),!0)})})},/**
	 * Builts new column from scratch or based on an existing column
	 *
	 * @param	{jQuery Object}	el	Column to base new column off of, when
	 *				copying an existing column for example; if left blank,
	 *				defaults to blank column
	 * @return	{jQuery Object}	New column element
	 */
_buildNewColumn:function(i){i=void 0==i?this.blankColumn.clone():this._cloneWithFormValues(i),i.find('input[name$="\\[name\\]"]').attr("value","");
// Need to make sure the new column's field names are unique
var e="new_"+t(".grid_col_settings",this.root).size();
// Make sure inputs are enabled if creating blank column
return i.html(i.html().replace(RegExp("(new_|col_id_)[0-9]{1,}","g"),e)),i.attr("data-field-name",e),i.find(":input").removeAttr("disabled").removeClass("grid_settings_error"),i},/**
	 * Binds change listener to the data type columns dropdowns of each column
	 * so we can load the correct settings form for the selected fieldtype
	 */
_bindColTypeChange:function(){var i=this;this.root.on("change",".grid_data_type .grid_col_select",function(e){
// New, fresh settings form
var n=i.colTemplateContainer.find(".grid_col_settings_custom_field_"+t(this).val()+":last").clone();
// Enable inputs
n.find(":input").removeAttr("disabled");var o=t(this).parents(".grid_col_settings").find(".grid_col_settings_custom");
// Namespace fieldnames for the current column
n.html(n.html().replace(RegExp("(new_|col_id_)[0-9]{1,}","g"),o.data("fieldName"))),
// Find the container holding the settings form, replace its contents
o.html(n),
// Fire displaySettings event
i._fireEvent("displaySettings",n)})},/**
	 * Binds to form submission to pass along the entire HTML for the last
	 * row in the Grid settings table for easy repopulated upon form
	 * validation failing
	 */
_bindSubmit:function(){var i=this;this.root.parents("form").submit(function(){
// Remove existing validation error classes
t(".grid_col_settings_section input[type=text]").removeClass("grid_settings_error"),grid_html=i._cloneWithFormValues(i.root.parent("#grid_settings_container")),t("<input/>",{type:"hidden",name:"grid_html",value:'<div id="grid_settings_container">'+grid_html.html()+"</div>"}).appendTo(i.root)})},/**
	 * Clones an element and copies over any form input values because
	 * normal cloning won't handle that
	 *
	 * @param	{jQuery Object}	el	Element to clone
	 * @return	{jQuery Object}	Cloned element with form fields populated
	 */
_cloneWithFormValues:function(i){var e=i.clone();return i.find(":input:enabled").each(function(){
// Find the new input in the cloned column for editing
var i=e.find(":input[name='"+t(this).attr("name")+"']:enabled");t(this).is("select")?i.find("option").removeAttr("selected").filter('[value="'+t(this).val()+'"]').attr("selected","selected"):"checkbox"==t(this).attr("type")?
// .prop('checked', true) doesn't work, must set the attribute
i.attr("checked",t(this).attr("checked")):"radio"==t(this).attr("type")?i.removeAttr("selected").filter("[value='"+t(this).val()+"']").attr("checked",t(this).attr("checked")):t(this).is("textarea")?i.html(t(this).val()):
// .val('new val') doesn't work, must set the attribute
i.attr("value",t(this).val())}),e},/**
	 * Called after main initialization to fire the 'display' event
	 * on pre-exising columns
	 */
_settingsDisplay:function(){var i=this;this.root.find(".grid_col_settings").each(function(){
// Fire displaySettings event
i._fireEvent("displaySettings",t(".grid_col_settings_custom > div",this))})},/**
	 * Fires event to fieldtype callbacks
	 *
	 * @param	{string}		action	Action name
	 * @param	{jQuery object}	el		jQuery object of affected element
	 */
_fireEvent:function(e,n){var o=n.data("fieldtype");
// If no events regsitered, don't bother
void 0!==i._eventHandlers[e]&&void 0!=i._eventHandlers[e][o]&&i._eventHandlers[e][o](t(n))},/**
	 * If there are fields with form validation errors in our settings
	 * object, highlight them
	 */
_highlightErrors:function(){void 0!=this.settings.error_fields&&t.each(this.settings.error_fields,function(i,e){t('input[name="'+e+'"]').addClass("grid_settings_error")})}},/**
 * Public method to instantiate Grid field
 */
EE.grid=function(t,e){return new i.Publish(t,e)},/**
 * Public method to instantiate Grid settings
 */
EE.grid_settings=function(t){return new i.Settings(t)},"undefined"!=typeof _&&"undefined"!==EE.grid_cache&&_.each(EE.grid_cache,function(t){i.bind.apply(i,t)})}(jQuery);