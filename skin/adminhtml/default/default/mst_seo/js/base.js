document.observe("dom:loaded", function(){
	var fieldLabelsFirst = document.getElementsByClassName("m-seo-settings-field-level-1");
	for (var i = 0; i < fieldLabelsFirst.length; i++) {
		fieldLabelsFirst[i].parentNode.classList.add("m-seo-settings-first-padding");
	}
	var fieldLabelsSecond = document.getElementsByClassName("m-seo-settings-field-level-2");
	for (var i = 0; i < fieldLabelsSecond.length; i++) {
		fieldLabelsSecond[i].parentNode.classList.add("m-seo-settings-second-padding");
	}
	var fieldLabelsArrow = document.getElementsByClassName("m-seo-settings-field-arrow");
	for (var i = 0; i < fieldLabelsArrow.length; i++) {
		fieldLabelsArrow[i].parentNode.classList.add("m-seo-settings-field-arrow-add");
	}
 });
