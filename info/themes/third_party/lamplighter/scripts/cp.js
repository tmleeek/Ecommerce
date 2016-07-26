var Lamplighter = {
	// Initialize
	init: function() {
		this.toggleNotes();
	},

	// Toggle notes rows
	toggleNotes: function() {
		$('#lamplighter a.toggle').on('click', function(e) {
			e.preventDefault();
			$(this).toggleClass('open-toggle');
			$(this).html($(this).hasClass('open-toggle') ? '-' : '+');
			$(this).parent().parent().next('tr').toggleClass('open-notes');
		});
	},

};

$(document).ready(function() {
	Lamplighter.init();
});
