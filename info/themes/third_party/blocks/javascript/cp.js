;jQuery(function($) {

var summarizers = {
	'text': function($atomContainer) {
		return $atomContainer.find('input').val();
	},
	'wygwam': function($atomContainer) {
		return $($atomContainer.find('textarea').first().val()).text();
	},
	'file': function($atomContainer) {
		return $atomContainer.find('.file_set:visible .filename').text();
	},
	'relationship': function($atomContainer) {
		var multiselect = $atomContainer.find('.multiselect-active');
		if (multiselect.length) {
			// If multi...
			var first = true;
			var text = '';
			multiselect.find('ul li').each(function() {
				if (first) {
					first = false;
				}
				else {
					text += ', ';
				}
				text += $(this).text().replace('\u00d7', '').trim();
			});
			return text;
		}
		else {
			// If single...
			return $atomContainer.find('select option:selected').text();
		}
	},
	'rte': function($atomContainer) {
		return $($atomContainer.find('textarea').val()).text();
	}
};

$('.blocksft').each(function() {

	var blocksft = $(this);
	var blocks = blocksft.find('.blocksft-blocks');

	var newBlockCount = 1;
	blocksft.on('click', '[js-newblock]', function(e) {
		e.preventDefault();
		var newbutton = $(this);
		var templateid = newbutton.attr('data-template');
		var location = newbutton.attr('data-location');
		var context = newbutton.closest('.blocksft-block');

		createBlock(templateid, location, context);
	});

	fireEvent("display", blocks.find('[data-fieldtype]'));

	function createBlock(templateid, location, context) {
		var template = blocksft.find('#' + templateid).find('.blocksft-block');
		var clone = template.clone();

		// TODO: Disable/enable all inputs?
		clone.html(clone.html().replace(/\[blocks_new_row_\d+\]/g, '[blocks_new_row_' + newBlockCount + ']'));

		switch (location) {
			case 'above':
				context.before(clone);
				break;
			case 'below':
				context.after(clone);
				break;
			case 'bottom':
				blocks.append(clone);
				break;
		}
		fireEvent("display", clone.find('[data-fieldtype]'));
		reorderFields();
		newBlockCount++;
		blocks.blocksSortable('reload');
	}

	// Set the order value for all of the fields.
	function reorderFields() {
		var order = 1;
		blocksft.find('[data-order-field]').each(function() {
			$(this).val(order);
			order++;
		});
	}

	blocks.blocksSortable({
		handle: '.blocksft-block-handle',
		forcePlaceholderSize: true
	});
	blocks.on('sortstart', function(e, ui) {
		var block = $(ui.item);
		block.find('[data-fieldtype]').each(function() {
			fireEvent('beforeSort', $(this));
		});
	});
	blocks.on('sortupdate', reorderFields);
	blocks.on('sortend', function(e, ui) {
		var block = $(ui.item);
		block.find('[data-fieldtype]').each(function() {
			fireEvent('afterSort', $(this));
		});
	});

	// Punt on a fancy re-orderer. We can figure that out later.
	blocks.on('click', 'button.move.up, button.move.down', function(e) {
		e.preventDefault();
		var button = $(this);
		var up = button.is('.up');
		var block = button.closest('.blocksft-block');

		if (up) {
			var prev = block.prev('.blocksft-block');
			if (prev.length) {
				block.find('[data-fieldtype]').each(function() {
					fireEvent('beforeSort', $(this));
				});
				prev.before(block);
				block.find('[data-fieldtype]').each(function() {
					fireEvent('afterSort', $(this));
				});
				reorderFields();
			}
		}
		else {
			var next = block.next('.blocksft-block');
			if (next.length) {
				block.find('[data-fieldtype]').each(function() {
					fireEvent('beforeSort', $(this));
				});
				next.after(block);
				block.find('[data-fieldtype]').each(function() {
					fireEvent('afterSort', $(this));
				});
				reorderFields();
			}
		}
	});

	blocks.on('click', '[js-remove]', function(e) {
		e.preventDefault();
		var button = $(this);
		var block = button.closest('.blocksft-block');

		var deletedInput = block.find('[data-deleted-field]');
		if (deletedInput.length) {
			deletedInput.val('true');
			block.addClass('deleted');
			block.find('[data-order-field]').remove();
		}
		else {
			block.remove();
		}
		reorderFields();
	});

	blocks.on('click', '[js-context]', function(e) {
		e.preventDefault();
		var button = $(this);
		var block = button.closest('.blocksft-block');

		var menu = block.find('.blocksft-contextmenu');
		menu.show();
		e.stopPropagation();

		$('html').on('click', function(e) {
			menu.hide();
		});
	});

	function collapseBlock(block) {
		block.attr('data-blockvisibility', 'collapsed');
		summarizeBlock(block);
	}

	function expandBlock(block) {
		block.attr('data-blockvisibility', 'expanded');
	}

	blocks.on('click', '[js-nextstep]', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var button = $(this);
		var multistep = button.closest('.multistep');
		var current = parseInt(multistep.attr('data-currentstep'), 10) || 1;
		multistep.attr('data-currentstep', current+1);
	});

	blocks.on('click', '[js-previousstep]', function(e) {
		e.preventDefault();
		e.stopPropagation();
		var button = $(this);
		var multistep = button.closest('.multistep');
		var current = parseInt(multistep.attr('data-currentstep'), 10) || 1;
		multistep.attr('data-currentstep', current-1);
	});

	blocks.on('click', '[js-expand]', function(e) {
		e.preventDefault();
		var button = $(this);
		var block = button.closest('.blocksft-block');

		expandBlock(block);
	});

	blocks.on('click', '[js-collapse]', function(e) {
		e.preventDefault();
		var button = $(this);
		var block = button.closest('.blocksft-block');

		collapseBlock(block);
	});

	blocks.on('mousedown', '.blocksft-header', function(e) {
		// Don't prevent default on the drag handle.
		if ($(e.target).is('.blocksft-block-handle')) {
			return;
		}

		// Prevent default so we don't highlight a bunch of stuff when double-
		// clicking.
		e.preventDefault();
	});
	blocks.on('dblclick', '.blocksft-header', function(e) {
		var block = $(this).closest('.blocksft-block');
		var visibility = block.attr('data-blockvisibility');
		switch (visibility) {
			case 'expanded':
				collapseBlock(block);
				break;
			default:
				expandBlock(block);
				break;
		}
	});

	blocks.on('click', '[js-expandall]', function(e) {
		e.preventDefault();
		blocks.find('.blocksft-block').each(function() {
			expandBlock($(this));
		});
	});

	blocks.on('click', '[js-collapseall]', function(e) {
		e.preventDefault();
		blocks.find('.blocksft-block').each(function() {
			collapseBlock($(this));
		});
	});

	function summarizeBlock(block) {
		var summarized = '';
		block.find('[data-fieldtype]').each(function() {
			var atom = $(this);
			var fieldtype = atom.attr('data-fieldtype');
			if (summarizers[fieldtype]) {
				var text = summarizers[fieldtype](atom.find('.blocksft-atomcontainer'));
				if (!/^\s*$/.test(text)) {
					summarized += ' \u2013 ' + text;
				}
			}
		});
		block.find('[js-summary]').text(summarized);
	}

	blocks.find('.blocksft-block').each(function() {
		var block = $(this);
		summarizeBlock(block);
	});
});

function fireEvent(eventName, fields) {
	fields.each(function() {
		// Some field types require this.
		window.Grid.Settings.prototype._fireEvent(eventName, $(this));
	});
}

// On occassion, Blocks will load before another field type within a
// block, and so Grid.bind will not have been called yet. So, we need to
// intercept those and initialize them as well. I'm not convinced this is
// the best way to do this, so it may need to be refined in the future.
var g = Grid;
var b = g.bind;
g.bind = function(fieldType, eventName, callback) {
	b.apply(g, [fieldType, eventName, callback]);
	if (eventName === "display") {
		fireEvent("display", $('.blocksft .blocksft-blocks [data-fieldtype="' + fieldType + '"]'));
	}
};

});
