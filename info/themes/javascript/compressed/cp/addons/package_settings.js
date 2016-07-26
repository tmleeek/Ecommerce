!function(n){var e={init:function(){this.extension_enable_verify()},/**
	 * If extensions are disabled, show the user a confirmation, if they click
	 * OK, add a hidden input to the form so we enable extensions on submit,
	 * otherwise don't submit the form.
	 * @return void
	 */
extension_enable_verify:function(){EE.extensions_disabled&&n("#mainContent form").submit(function(e){
// Add a hidden input to enable extensions
return confirm(EE.extensions_disabled_warning)?(n(this).append(n("<input>",{type:"hidden",name:"enable_extensions",value:"yes"})),!0):!1})}};e.init()}(jQuery);