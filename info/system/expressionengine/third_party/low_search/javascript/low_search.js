/**
 * Low Search JS file
 *
 * @package        low_search
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2015, Low
 */

// Make sure LOW namespace is valid
if (typeof LOW == 'undefined') var LOW = new Object;

(function($){

// --------------------------------------
// Language lines
// --------------------------------------

var lang = function(str) {
	return (typeof $.LOW_lang[str] == 'undefined') ? str : $.LOW_lang[str];
}

// --------------------------------------
// Create Low Index object
// --------------------------------------

LOW.Index = function(cell) {

	var self  = this,
		$cell = $(cell),
		$link = $cell.find('a'),
		$bar  = $cell.find('.index-options'),
		url   = location.href.replace(/(method=)collections/i, '$1build'),
		vars  = {},
		total = $cell.data('total'),
		colId = $cell.data('collection'),
		EE280 = (typeof EE.CSRF_TOKEN != 'undefined'),
		csrf_token_name   = (EE280 ? 'CSRF_TOKEN' : 'XID'),
		csrf_token_header = (EE280 ? 'X-CSRF-TOKEN' : 'X-EEXID');

	// Does this index also contain a lexicon?
	this.lexicon = $cell.data('lexicon');

	// Add csrf_token to vars
	vars[csrf_token_name] = EE[csrf_token_name];

	// Other default vars to send:
	vars.start = 0;
	vars.collection_id = colId;

	// Perform Ajax Call for this batch
	var buildBatch = function(rebuild) {
		// Set rebuild var accordingly
		vars.rebuild = rebuild ? 'yes' : false;
		// Data to post
		$.post(url, vars, respond, 'text').fail(respond);
	};

	// Handle Ajax response from the server
	var respond = function(data, status, xhr) {

		// String responses
		if (typeof data == 'string') {

			// Only 'true' or '[digit]' are valid
			return (data.match(/^(true|\d+)$/))
				? update($.parseJSON(data), xhr)
				: showError(data, xhr);

		}

		// If data is an object, it's actually an xhr
		if (typeof data == 'object') {

			var response = data.responseText;

			if (response.match(/^\{/)) {
				response = $.parseJSON(response);
				response = response.error || response.toString();
			}

			return showError(response, data);

		}

		alert('Well, this is awkward.');

	};

	// Function to execute after each Ajax call
	var update = function(start, xhr) {
		var width, text, done;

		if (start === true) {
			done  = true;
			width = 'auto';
			text  = lang('done');
		} else {
			done  = false;
			width = (start / total * 100) + '%';
			text  = start + ' / ' + total;
		}

		// Update progress bar with new info
		$bar.css('width', width).text(text);

		if (done) {

			// If we're done, set the class
			$cell.addClass('done');

			// And trigger oncomplete
			self.oncomplete();

		} else {

			// Set new csrf_token
			var csrf_token_value = xhr.getResponseHeader(csrf_token_header) || null;
			if (csrf_token_value) vars[csrf_token_name] = csrf_token_value;

			// Set new start value
			vars.start = start;

			// And build the next batch
			buildBatch(false);
		}

	};

	// Show error message when building borks
	var showError = function(response, xhr) {
		// Format the cell correctly
		$cell.removeClass('loading').addClass('error');

		// Basic text
		$bar.css('width', 'auto').text('An error occurred building the index.');

		// Add response-link?
		if (response) {
			// Div for dialog
			var $response = $('<div/>').html(response);

			// Add span to view response in an alert
			var $view = $('<span/>').text('View response').on('click', function(){
				$response.dialog({
					modal: true,
					title: xhr.status+': '+xhr.statusText,
					width: '50%'
				});
			}).css('cursor','pointer');

			$bar.append($view);
		}
	};

	// Set the build type
	this.setType = function(type) {
		vars.build = type;
	};

	// Callable build function to trigger the build
	this.build = function(rebuild){
		if ($cell.hasClass('done')) return self.oncomplete();
		// Don't build if we're building a lexicon, but don't have one
		if (vars.build != 'index' && !self.lexicon) return self.oncomplete();
		// Remove tick, display feedback message
		$cell.addClass('loading');
		// Update text
		$bar.text('0 / ' + total);
		// Call function
		buildBatch(rebuild);
	};

	// Add event to link to trigger rebuild
	$link.click(function(event){
		event.preventDefault();
		// remember what we're building
		self.oncomplete = function(){};
		self.setType($(this).data('build'));
		self.build(event.altKey);
	});

	this.oncomplete = function(){};

	return this;
};

// --------------------------------------
// Controller for collections/indexes
// --------------------------------------

LOW.Collections = function() {

	var index = [];

	$('td.low-index').each(function(){
		index.push(new LOW.Index(this));
	});

	$('.low-build-all a').click(function(event){
		event.preventDefault();
		var $cell = $(this).parent(),
			build = $(this).data('build');
		$cell.text(lang('working'));
		$(index).each(function(i){
			var next = index[i + 1];
			index[i].oncomplete = function(){
				if (next) {
					next.setType(build);
					next.build();
				} else {
					$cell.text(lang('done'));
				}
			};
		});
		index[0].setType(build);
		index[0].build();
	});
};

$(LOW.Collections);

// --------------------------------------
// Collection Settings
// --------------------------------------

LOW.CollectionSettings = function() {

	// Show search fields in Edit Collection screen
	var show_fields = function() {
		var val = $('#collection_channel').val();
		$('.low-search-collection-settings').addClass('hidden');
		if (val) {
			$('#low-search-channel-'+val).removeClass('hidden');
			if ( ! $('#collection_id').val()) {
				$('#collection_label').val(EE.low_search_channels[val].channel_title);
				$('#collection_name').val(EE.low_search_channels[val].channel_name);
			}
		}
	};

	show_fields();
	$('#collection_channel').change(show_fields);
};

$(LOW.CollectionSettings);

// ------------------------------------------
// Sortable shortcuts
// ------------------------------------------

LOW.Sortcuts = function(){
	var $table = $('#low-search-shortcuts');

	if ( ! $table.length) return;

	var $cols = $table.find('col'),
		$tbody = $table.find('tbody');

	// Set styles for TDs, so row stays same wit
	$tbody.find('tr').each(function(){
		$(this).find('td').each(function(i){
			var w = $($cols.get(i)).css('width');
			$(this).css('width', w);
		});
	});

	// Callback function after sorting
	var sorted = function(event, ui) {
		var order = [];

		$tbody.find('tr').each(function(i){
			var $tr = $(this),
				i = i + 1;

			// Correct zebra striping
			$tr.removeClass((i % 2 ? 'even' : 'odd'));
			$tr.addClass((i % 2 ? 'odd' : 'even'));

			// Add to orders
			order.push($tr.data('id'));
		});

		// Compose URL for Ajax call
		var url = location.href.replace('method=shortcuts', 'method=order_shortcuts');

		// Post it
		$.post(url, {
			'XID': EE.XID,
			'order': order
		});
	};

	// Make the rows sortable
	$tbody.sortable({
		axis: 'y',
		containment: $('#mainContent'),
		items: 'tr',
		handle: '.drag-handle',
		update: sorted
	});
};

$(LOW.Sortcuts);

// ------------------------------------------
// Shortcut Parameters
// ------------------------------------------

LOW.Params = function(){
	var $tmpl = $('#parameter-template'),
		$add  = $('#parameters button');

	var addFilter = function(event, key, val) {
		// Clone the filter template and remove the id
		var $newFilter = $tmpl.clone().removeAttr('id');

		// If a key is given, set it
		if (key) $newFilter.find('.param-key').val(key);

		// If a val is given, set it
		if (val) $newFilter.find('.param-val').val(val);

		// Add it just above the add-button
		$add.before($newFilter);

		// If it's a click event, slide down the new filter,
		// Otherwise just show it
		if (event) {
			event.preventDefault();
			$newFilter.slideDown(100);
		} else {
			$newFilter.show();
		}

		$newFilter.find('.param-key').focus();
	};

	// If we have reorder fields pre-defined, add them to the list
	if (typeof LOW_Search_parameters != 'undefined') {
		for (var i in LOW_Search_parameters) {
			addFilter(null, i, LOW_Search_parameters[i]);
		}
	}

	// Enable the add-button
	$add.click(addFilter);

	// Enable all future remove-buttons
	$('#parameters').delegate('button.remove', 'click', function(event){
		event.preventDefault();
		$(this).parent().remove();
	});
};

$(LOW.Params);

// ------------------------------------------
// Search Log
// ------------------------------------------

LOW.SearchLog = function() {
	var $cells = $('td.params'),
		$th = $('#params-header'),
		open = false;

	$cells.each(function(){
		var $td   = $(this),
			$more = $('<span>&hellip;</span>')
			$lis  = $td.find('li');

		if ($lis.length > 1) {
			$lis.first().append($more);
			$td.on('click', function(){
				$td.toggleClass('open');
			}).addClass('has-more');
		}
	});

	$th.on('click', function(){
		var method = open ? 'removeClass' : 'addClass';
		$cells.filter('.has-more')[method]('open');
		open = ! open;
	});
};

$(LOW.SearchLog);

// ------------------------------------------
// Tabs object
// ------------------------------------------

LOW.Tabs = function(el) {

	var self   = this,
		$el    = $(el),
		$pages = $el.find('.low-tab'),
		$tabs  = $(),
		names  = $el.data('names'),
		_class = 'active';

	var toggle = function(event) {

		event.preventDefault();

		// Which tab is this?
		var i = $(this).data('index'),
			prev = 'low-tab-active-' + self.active,
			next = 'low-tab-active-' + i;

		// Deactivate all
		$tabs.removeClass(_class);
		$pages.removeClass(_class);
		$el.removeClass(prev);

		// Activate one
		$tabs.eq(i).addClass(_class);
		$pages.eq(i).addClass(_class);
		$el.addClass(next);

		// Remember which is active
		// and fire onchange event
		self.active = i;
		self.onchange();

	};

	// Build tab for each page
	$pages.each(function(i){
		var $page = $(this),
			$name = $page.find(names),
			title = $name.first().text(),
			$link = $('<a href="#"/>').attr('data-index', i).text(title),
			$tab  = $('<li/>').append($link);

		// If page is active, make tab active too
		if ($page.hasClass(_class)) {
			$tab.addClass(_class);
			self.active = i;
		}

		// This is the change event
		$link.click(toggle);

		$name.remove();

		$tabs = $tabs.add($tab);
	});

	// Create the tabs themselves
	$('<ul/>').addClass('low-tab-links').append($tabs).prependTo($el);

	$el.addClass('low-tab-active-' + self.active);

	// Onchange event handler
	this.onchange = function(){};

	this.change = function(i){
		$tabs.eq(i).find('a').click();
	};

	return this;
};


// ------------------------------------------
// Lexicon object
// ------------------------------------------

LOW.Lexicon = function() {
	var $el     = $('#low-lexicon'),
		$tabs   = $el.find('.low-tabs'),
		$form   = $el.find('form'),
		$input  = $form.find('input[type="text"]'),
		$status = $el.find('.low-status'),
		$target = $el.find('.low-dynamic-content'),
		names   = ['find', 'add'],
		tabs;

	// Initiate tabs and alter input name onchange
	if ($tabs.length) {
		tabs = new LOW.Tabs($tabs.get(0));
		tabs.onchange = function() {
			$input.attr('name', names[this.active]);
			$input.focus();
		};
		$input.focus();
	}

	// Update status numbers
	var updateStatus = function(txt) {
		$status.text(txt);
	};

	// Do something after form was submitted
	var updateTarget = function(data) {
		$target.html('');
		if (data.status) updateStatus(data.status);
		if (data.found) createLinks(data.found);
	};

	var addLink = function(word)  {
		var $a = $('<a href="#"/>').text('Add '+word+'?').appendTo($target);
		$a.click(function(event){
			event.preventDefault();
			tabs.change(1);
			$form.submit();
		});
	};

	var createLinks = function(words) {
		// Containing element
		var $p = $('<p/>').addClass('low-found-words').appendTo($target);

		// Loop through words
		for (var i in words) {

			// Get single word
			var word = words[i];

			// Create link and append
			$('<a href="#"/>').attr('data-lang', word.language).text(word.word).appendTo($p);

			// Add space
			$p.append(' ');
		}
	};

	// Submit form via ajax
	$form.submit(function(event){

		// Cancel submit!
		event.preventDefault();

		// Message
		$target.html(lang('working'));

		// Submit form via Ajax, show result in target
		$.post(this.action, $(this).serialize(), updateTarget, 'json');

	});

	// Delete words from lexicon via ajax
	$target.delegate('a', 'click', function(event){
		event.preventDefault();
		var $el = $(this),
			word = {
				language: $el.data('lang'),
				remove: $el.text()
			};

		$.post(location.href, word, function(data){
			if (data.status) updateStatus(data.status);
			$el.remove();
		}, 'json');
	});

};

$(LOW.Lexicon);

// ------------------------------------------
// Find & Replace functions
// ------------------------------------------

LOW.FindReplace = function(){

	var $el = $('#low-find-replace'),
		$tabs = $el.find('.low-tabs'),
		$form = $el.find('form'),
		$target = $el.find('.low-dynamic-content'),
		$keywords = $el.find('#low-keywords'),
		tabs;

	// Initiate tabs and alter input name onchange
	if ($tabs.length) {
		tabs = new LOW.Tabs($tabs.get(0));
	}

	// Get dialog element
	var $dialog   = $('#low-dialog');

	// Define BoxSection object: to (de)select all checkboxes that belong to the section
	var BoxSection = function(el) {
		var $el     = $(el),
			$boxes  = $el.find(':checkbox'),
			$toggle = $el.find('h4 span');

		// Add toggle function to channel header
		$toggle.click(function(event){
			event.preventDefault();
			var $unchecked = $el.find('input:not(:checked)');

			if ($unchecked.length) {
				$unchecked.attr('checked', true);
			} else {
				$boxes.attr('checked', false);
			}
		});
	};

	// Channel / field selection options
	$form.find('fieldset').each(function(){

		// Define local variables
		var $self      = $(this),
			$sections  = $self.find('div.low-boxes'),
			$allBoxes  = $self.find('input[name]'),
			$selectAll = $self.find('input.low-select-all');

		// Init channel object per one channel found in main element
		$sections.each(function(){
			new BoxSection(this);
		});

		// Enable the (de)select all checkbox
		$selectAll.change(function(){
			var check = ($selectAll.attr('checked') ? true : false);
			$allBoxes.attr('checked', check);
		});
	});

	// Show preview of find & replace action
	$form.submit(function(event){

		// Don't believe the hype!
		event.preventDefault();

		// Validate keywords
		if ( ! $keywords.val()) {
			$.ee_notice(lang('no_keywords_given'),{type:"error",open:true});
			return;
		}

		// Validate field selection
		if ( ! $form.find('input[name^="fields"]:checked').length) {
			$.ee_notice(lang('no_fields_selected'),{type:"error",open:true});
			return;
		}

		// Turn on throbber, empty out target
		$.ee_notice.destroy();
		$target.html(lang('working'));

		// Submit form via Ajax, show result in Preview
		$.post(this.action, $(this).serialize(), function(data){ $target.html(data); });
	});

	// (de)select all checkboxes in preview table
	$target.delegate('.low-select-all', 'change', function(){
		$target.find('tbody :checkbox').attr('checked', this.checked);
	});

	// Form submission after previewing
	$target.delegate('form', 'submit', function(event){

		// Don't believe the hype!
		event.preventDefault();

		// Set local vars
		var $this = $(this);

		// Validate checked entries, destroy notice if okay
		if ( ! $this.find('tbody :checked').length) {
			$.ee_notice(lang('no_entries_selected'),{type:"alert",open:true});
			return;
		}

		// Show message in preview
		$.ee_notice.destroy();
		$target.html(lang('working'));

		// Submit form via Ajax, show result in Preview
		$.post(this.action, $this.serialize(), function(data){ $target.html(data); });
	});
};

$(LOW.FindReplace);

// ------------------------------------------
// Replace Log
// ------------------------------------------

LOW.ReplaceLog = function(){

	var $dialog = $('#low-dialog');

	// Replace log: open details in dialog
	$('.low-show-dialog').click(function(event){

		// Don't follow the link
		event.preventDefault();

		// Load details via Ajax, then show in dialog
		$('#low-dialog').load(this.href, function(){
			$dialog.dialog({
				modal: true,
				title: $('#breadCrumb .last').text(),
				width: '50%'
			});
		});
	});
};

$(LOW.ReplaceLog);

// ------------------------------------------
// Settings
// ------------------------------------------

// Settings
$(function(){
	// Toggle hilite title settings
	$('#excerpt_hilite').change(function(){
		var method = $(this).val() ? 'slideDown' : 'slideUp';
		$('#title_hilite')[method](150);
	});
});


})(jQuery);

// --------------------------------------
