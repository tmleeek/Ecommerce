<?php

namespace EEBlocks\Adapter;

class RelationshipContentTypeAdapter
{
	private $fieldtype;

	public function setFieldtype($fieldtype)
	{
		$this->fieldtype = $fieldtype;
	}

	public function display_field($data) {
		// This is a little bit hacky, but here goes. When we call
		// `display_field` on the relationship field type to create a new
		// atom, the relationship field sets a bunch of where clauses on an
		// SQL query. How it generates these where clauses, when it does it in
		// Blocks, ends up pulling in an actual value. So a new block ends up
		// having the value of the most recently created relationship, instead
		// of being blank. If instead we set some dummy default values to 0,
		// that query won't return any values, and the new block will be
		// empty, like it should be.
		if ($this->fieldtype->settings['grid_row_id'] === null) {
			$this->fieldtype->settings['col_id'] = 0;
			$this->fieldtype->settings['grid_field_id'] = 0;
			$this->fieldtype->settings['grid_row_id'] = 0;
		}
		return $this->fieldtype->display_field($data);
	}
}
