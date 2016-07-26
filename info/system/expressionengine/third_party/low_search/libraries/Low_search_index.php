<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Search Index class
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2015, Low
 */
class Low_search_index {

	/**
	 * Keep track of found fields
	 */
	private $_fields = array();

	// --------------------------------------------------------------------

	/**
	 * Build index for given entry or entries
	 *
	 * @access     public
	 * @param      mixed     int or array of ints
	 * @return     bool
	 */
	public function build_by_entry($entry_ids, $build = NULL)
	{
		// --------------------------------------
		// Force array
		// --------------------------------------

		if ( ! is_array($entry_ids))
		{
			$entry_ids = array($entry_ids);
		}

		// --------------------------------------
		// Clean up
		// --------------------------------------

		$entry_ids = array_filter($entry_ids);

		// --------------------------------------
		// Bail out if nothing given
		// --------------------------------------

		if (empty($entry_ids)) return FALSE;

		// --------------------------------------
		// Get collections for these entries
		// --------------------------------------

		$query = ee()->db->select('t.entry_id, c.collection_id')
		       ->from('channel_titles t')
		       ->join('low_search_collections c', 't.channel_id = c.channel_id')
		       ->where_in('t.entry_id', $entry_ids)
		       ->get();

		// --------------------------------------
		// No collections? Bail.
		// --------------------------------------

		if ( ! $query->num_rows()) return FALSE;

		// --------------------------------------
		// Collect in array
		// --------------------------------------

		$rows = array();

		foreach ($query->result() AS $row)
		{
			$rows[$row->collection_id][] = $row->entry_id;
		}

		// --------------------------------------
		// Call build_collection for each found
		// --------------------------------------

		foreach ($rows AS $collection_id => $entry_ids)
		{
			$this->build_by_collection($collection_id, $entry_ids);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Build given collection in batches
	 *
	 * @access     public
	 * @param      int
	 * @param      int
	 * @return     mixed     bool or int
	 */
	public function build_batch($collection_id, $start = 0, $build = NULL)
	{
		// --------------------------------------
		// None given or invalid? Bail out
		// --------------------------------------

		if ( ! ($col = ee()->low_search_collection_model->get_by_id($collection_id)))
		{
			return FALSE;
		}

		// Focus on the one
		$col = $col[$collection_id];

		// --------------------------------------
		// Get total number entry IDs for collection's channel
		// --------------------------------------

		$total = ee()->db->from('channel_titles')
		       ->where('channel_id', $col['channel_id'])
		       ->count_all_results();

		$batch = ee()->low_search_settings->get('batch_size');

		// --------------------------------------
		// Get batch entry IDs for this collection
		// --------------------------------------

		$query = ee()->db->select('entry_id')
		       ->from('channel_titles')
		       ->where('channel_id', $col['channel_id'])
		       ->order_by('entry_id')
		       ->limit($batch, $start)
		       ->get();

		$entry_ids = low_flatten_results($query->result_array(), 'entry_id');

		// --------------------------------------
		// Call build by collection, with given batch
		// --------------------------------------

		$ok = $this->build_by_collection($collection_id, $entry_ids, $build);

		// --------------------------------------
		// Return new start position or TRUE if finished
		// --------------------------------------

		if ($ok)
		{
			// New start position
			$start = $start + $batch;

			// Or TRUE if finished
			if ($start >= $total) $start = TRUE;
		}
		else
		{
			$start = FALSE;
		}

		return $start;
	}

	// --------------------------------------------------------------------

	/**
	 * Build index by given collection ID, optionally limited by given IDs
	 *
	 * @access     public
	 * @param      int
	 * @param      array
	 * @return     bool
	 */
	public function build_by_collection($collection_id, $entry_ids = array(), $build = NULL)
	{
		// --------------------------------------
		// Get collection details
		// --------------------------------------

		if ( ! ($cols = ee()->low_search_collection_model->get_by_id($collection_id)))
		{
			return FALSE;
		}

		// Focus on the one
		$col = $cols[$collection_id];

		// --------------------------------------
		// Select what from entries?
		// --------------------------------------

		$fields = array_keys($col['settings']);
		$select = array('t.entry_id');
		$field_ids = $cat_fields = array();

		foreach ($fields AS $id)
		{
			// Regular fields are numeric
			if (is_numeric($id))
			{
				$select[] = ($id == '0') ? 't.title AS field_id_0' : 'd.field_id_'.$id;
				if ($id) $field_ids[] = $id;
			}
			// Non-numeric fields are category fields
			else
			{
				// Split in group and field ID and add to cats
				list($group, $id) = explode(':', $id);
				if (is_numeric($id)) $id = 'field_id_'.$id;
				if ( ! in_array($id, $cat_fields)) $cat_fields[] = $id;
			}
		}

		// --------------------------------------
		// Get entries for this collection
		// --------------------------------------

		if (ee()->extensions->active_hook('low_search_get_index_entries') === TRUE)
		{
			$entries = ee()->extensions->call('low_search_get_index_entries', $col, $entry_ids);
		}
		else
		{
			ee()->db->select($select)
			        ->from('channel_titles t')
			        ->join('channel_data d', 't.entry_id = d.entry_id')
			        ->where('t.channel_id', $col['channel_id']);

			// Optionally limit by given entry IDs
			if (is_array($entry_ids) && ! empty($entry_ids))
			{
				ee()->db->where_in('t.entry_id', $entry_ids);
			}

			$entries = ee()->db->get()->result_array();
			$entries = low_associate_results($entries, 'entry_id');

			// Get categories for the found entries
			foreach ($this->get_entry_categories(array_keys($entries), $cat_fields) AS $key => $val)
			{
				$entries[$key] += $val;
			}
		}

		// --------------------------------------
		// Load the fields
		// --------------------------------------

		$this->_load_fields($field_ids);

		// --------------------------------------
		// Load words lib
		// --------------------------------------

		ee()->load->library('Low_search_words');

		// --------------------------------------
		// Build index for each entry
		// --------------------------------------

		// Seen words for cache
		static $seen = array();
		$index = $lexicon = array();

		// batch-insert 100 at a time
		$batch = 100;

		foreach ($entries AS $entry)
		{
			// Make sure all fields have their content (check fieldtypes)
			$entry = $this->_prep_entry($col, $entry);

			// --------------------------------------
			// Optionally build lexicon
			// --------------------------------------

			if ($col['language'] && $build != 'index')
			{
				// Get the words for the lexicon
				$words = explode(' ', implode(' ', $entry));
				$words = array_filter($words, array(ee()->low_search_words, 'is_valid'));
				$words = array_unique($words);

				// Diff 'em from the words we've already encountered or ignoring
				$words = array_diff($words, $seen);
				$words = array_diff($words, ee()->low_search_settings->ignore_words());

				// And remember what we've seen
				$seen = array_merge($seen, $words);

				// Build lexicon
				foreach ($words AS $word)
				{
					// Get clean word
					$clean = ee()->low_search_words->remove_diacritics($word);

					// Get sound of word
					$sound = soundex($word);

					// Compose row
					$data = array(
						'site_id'  => $col['site_id'],
						'word'     => $word,
						'language' => $col['language'],
						'length'   => ee()->low_multibyte->strlen($word),
						'sound'    => ($sound == '0000' ? NULL : $sound),
						'clean'    => ($word == $clean ? NULL : $clean)
					);

					// --------------------------------------
					// 'low_search_update_lexicon' hook
					// - Add additional attributes to the lexicon
					// --------------------------------------

					if (ee()->extensions->active_hook('low_search_update_lexicon') === TRUE)
					{
						$ext_data = ee()->extensions->call('low_search_update_lexicon', $data);

						if (is_array($ext_data) && ! empty($ext_data))
						{
							$data = array_merge($data, $ext_data);
						}
					}

					// Add row
					$lexicon[] = $data;
				}

				if (count($lexicon) >= $batch)
				{
					ee()->low_search_word_model->insert_ignore_batch($lexicon);
					$lexicon = array();
				}
			}

			// --------------------------------------
			// Optionally build index
			// --------------------------------------

			if ($build != 'lexicon')
			{
				// --------------------------------------
				// Apply weight to the entry
				// --------------------------------------

				$text = $this->_get_weighted_text($col, $entry);
				$text = ee()->low_search_words->remove_diacritics($text);

				// Compose data to insert
				$data = array(
					'collection_id' => $col['collection_id'],
					'entry_id'      => $entry['entry_id'],
					'site_id'       => $col['site_id'],
					'index_text'    => $text,
					'index_date'    => ee()->localize->now
				);

				// --------------------------------------
				// 'low_search_update_index' hook
				// - Add additional attributes to the index
				// --------------------------------------

				if (ee()->extensions->active_hook('low_search_update_index') === TRUE)
				{
					$ext_data = ee()->extensions->call('low_search_update_index', $data, $entry);

					if (is_array($ext_data) && ! empty($ext_data))
					{
						$data = array_merge($data, $ext_data);
					}
				}

				// --------------------------------------
				// Add data to rows for batch replace
				// --------------------------------------

				$index[] = $data;

				// --------------------------------------
				// If we're at a batch size, insert 'em
				// --------------------------------------

				if (count($index) == $batch)
				{
					ee()->low_search_index_model->replace_batch($index);

					// and reset the rows
					$index = array();
				}
			}
		}

		// --------------------------------------
		// Insert left-overs
		// --------------------------------------

		if ($lexicon)
		{
			ee()->low_search_word_model->insert_ignore_batch($lexicon);
		}

		if ($index)
		{
			ee()->low_search_index_model->replace_batch($index);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Get categories for entries
	 *
	 * @access     public
	 * @param      array
	 * @param      array
	 * @return     array
	 */
	public function get_entry_categories($entry_ids, $fields)
	{
		// --------------------------------------
		// Prep output
		// --------------------------------------

		$cats = array();

		if (empty($entry_ids) || empty($fields)) return $cats;

		// --------------------------------------
		// Get all categories plus data for given entries
		// --------------------------------------

		$query = ee()->db->select('p.entry_id, c.cat_id, c.group_id, c.cat_name, c.cat_description, d.*')
			->from('categories c')
			->join('category_field_data d', 'c.cat_id = d.cat_id', 'left')
			->join('category_posts p', 'c.cat_id = p.cat_id', 'inner')
			->where_in('p.entry_id', $entry_ids)
			->get();

		// --------------------------------------
		// Done with the query; loop through results
		// --------------------------------------

		foreach ($query->result_array() AS $row)
		{
			// Loop through each result and populate the output
			foreach ($row AS $key => $val)
			{
				// Skip non-valid fields or empty ones
				if ( ! in_array($key, $fields) || empty($val)) continue;

				// Set field to ID if applicable
				if (preg_match('/^field_id_(\d+)$/', $key, $match))
				{
					$key = $match[1];
				}

				// Use that as the key in the array to return
				$cats[$row['entry_id']]["{$row['group_id']}:{$key}"][$row['cat_id']] = $val;
			}
		}

		return $cats;
	}

	// --------------------------------------------------------------------

	/**
	 * Load fieldtypes for given field IDs -- populates $this->_fields
	 *
	 * @access     private
	 * @param      array
	 * @return     void
	 */
	private function _load_fields($field_ids)
	{
		// --------------------------------------
		// Load addon/fieldtype files
		// --------------------------------------

		ee()->load->library('addons');

		// Include EE Fieldtype class
		if ( ! class_exists('EE_Fieldtype'))
		{
			include_once (APPPATH.'fieldtypes/EE_Fieldtype'.EXT);
		}

		// --------------------------------------
		// Initiate fieldtypes var
		// --------------------------------------

		static $fieldtypes;

		// Set fieldtypes
		if ($fieldtypes === NULL)
		{
			$fieldtypes = ee()->addons->get_installed('fieldtypes');
		}

		// --------------------------------------
		// Check for ids we haven't dealt with yet
		// --------------------------------------

		$not_encountered = array_diff($field_ids, array_keys($this->_fields));

		if (empty($not_encountered)) return;

		// --------------------------------------
		// Get the details for not encountered fields
		// --------------------------------------

		$query = ee()->db->select()
		       ->from('channel_fields')
		       ->where_in('field_id', $not_encountered)
		       ->get();

		foreach ($query->result() AS $field)
		{
			// Shortcut to fieldtype
			$ftype = $fieldtypes[$field->field_type];

			// Include the file if it doesn't yet exist
			if ( ! class_exists($ftype['class']))
			{
				require $ftype['path'].$ftype['file'];
			}

			// Only initiate the fieldtypes that have the necessary method
			if (method_exists($ftype['class'], 'third_party_search_index'))
			{
				// Initiate this fieldtype
				$obj = new $ftype['class'];

				// Add settings to object
				if ($settings = @unserialize(base64_decode($field->field_settings)))
				{
					$settings = array_merge( (array) $field, $settings );
				}

				// Set this instance's settings
				$obj->settings = $settings;
			}
			else
			{
				$obj = TRUE;
			}

			// Record the field
			$this->_fields[$field->field_id] = $obj;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Make sure all the fields in the entry have their content
	 *
	 * @access     private
	 * @param      array     collection
	 * @param      array     entry
	 * @return     string
	 */
	private function _prep_entry($col, $entry)
	{
		// --------------------------------------
		// Loop through the entry's keys and fire the third_party method, if present
		// --------------------------------------

		foreach (array_keys($entry) AS $field_name)
		{
			// Skip entry_id
			if ($field_name == 'entry_id') continue;

			// --------------------------------------
			// Determine proper field id
			// --------------------------------------

			$field_id = (preg_match('/^field_id_(\d+)$/', $field_name, $match))
				? $match[1]
				: FALSE;

			// --------------------------------------
			// Fire third party thingie for this field?
			// --------------------------------------

			if ($field_id && array_key_exists($field_id, $this->_fields) && is_object($this->_fields[$field_id]))
			{
				// Extra settings per entry
				$settings = array(
					'entry_id'      => $entry['entry_id'],
					'collection_id' => $col['collection_id']
				);

				// Merge the extra settings
				$this->_fields[$field_id]->settings = array_merge(
					$this->_fields[$field_id]->settings,
					$settings
				);

				// If fieldtype exists, it will have the correct method, so call that
				$entry[$field_name] = $this->_fields[$field_id]->third_party_search_index($entry[$field_name]);
			}

			// --------------------------------------
			// Get the value for this field and force arry
			// --------------------------------------

			$val = (array) $entry[$field_name];

			// Clean up the values
			$val = array_map(array(ee()->low_search_words, 'clean'), $val);

			// And turn back into a string
			$val = implode(' | ', $val);

			// Set it to the entry's value
			$entry[$field_name] = trim($val);
		}

		// --------------------------------------
		// Filter out empty values
		// --------------------------------------

		$entry = array_filter($entry);

		// --------------------------------------
		// Return the prep'ed entry again
		// --------------------------------------

		return $entry;
	}

	// --------------------------------------------------------------------

	/**
	 * Get index text based in given entry
	 *
	 * @access     private
	 * @param      array     collection
	 * @param      array     entry
	 * @return     string
	 */
	private function _get_weighted_text($col, $entry)
	{
		// --------------------------------------
		// Init text array which will contain the index
		// and weight separator
		// --------------------------------------

		$text = array();
		$sep  = ' | ';

		// --------------------------------------
		// Loop through settings and add weight to field by repeating string
		// --------------------------------------

		foreach ($entry AS $key => $val)
		{
			// Skip entry_id
			if ($key == 'entry_id' || empty($val)) continue;

			// --------------------------------------
			// Determine proper settings ID
			// --------------------------------------

			$key = (preg_match('/^field_id_(\d+)$/', $key, $match))
				? $match[1]
				: $key;

			// --------------------------------------
			// Get weight
			// --------------------------------------

			$weight = array_key_exists($key, $col['settings'])
				? $col['settings'][$key]
				: FALSE;

			// Skip if not there
			if ( ! $weight) continue;

			// --------------------------------------
			// Apply weight and add to text
			// --------------------------------------

			$text[] = trim($sep.str_repeat($val.$sep, $weight));
		}

		// --------------------------------------
		// Return text with each field on its own line
		// --------------------------------------

		return implode(NL, $text);
	}

}
// End of file Low_search_index.php