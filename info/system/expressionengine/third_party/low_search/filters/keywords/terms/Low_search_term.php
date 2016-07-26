<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Single search term
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2015, Low
 */
class Low_search_term {

	public $raw;
	public $clean;
	public $format;

	public $exact       = FALSE;
	public $exclude     = FALSE;
	public $loose_left  = FALSE;
	public $loose_right = FALSE;

	public $original    = FALSE;

	public $sound       = FALSE;
	public $stem        = FALSE;

	/**
	 * Return the term for fulltext search
	 */
	public function get_fulltext_sql($include = TRUE)
	{
		$term = $this->clean;

		if ($this->exact) $term = '"'.$term.'"';
		if ($this->exclude) $term = '-'.$term;
		if ($this->loose_right) $term = $term.'*';
		if ($include && ! $this->exclude) $term = '+'.$term;

		return $term;
	}

	/**
	 * Return the term for fallback search
	 */
	public function get_fallback_sql()
	{
		$term = " {$this->clean} ";

		if ($this->loose_left) $term = ltrim($term);
		if ($this->loose_right) $term = rtrim($term);

		$like = $this->exclude ? 'NOT LIKE' : 'LIKE';

		return "`index_text` {$like} '%{$term}%'";
	}

	/**
	 * Return the regex pattern
	 */
	public function get_pattern()
	{
		// We use the formatted term by default
		$terms = array($this->format, $this->raw, $this->clean);
		$terms = array_values(array_unique(array_filter($terms)));

		// Add the clean term if it's different
		foreach ($terms AS &$term)
		{
			$term = preg_quote($term, '/');
		}

		// Get pattern based on the terms
		$pattern = (count($terms) == 1)
			? $terms[0]
			: '('.implode('|', $terms).')';

		// Add word-delimiter on the left side
		if ( ! $this->loose_left)
		{
			$pattern = '\b'.$pattern;
		}

		// Add word-delimiter on the right side
		if ( ! $this->loose_right)
		{
			$pattern .= '\b';
		}

		// Match full stemmed words
		if ($this->stem)
		{
			$pattern .= '\w*';
		}

		return $pattern;
	}

	/**
	 * Is this term a fulltext term?
	 */
	public function is_fulltext()
	{
		// Loop through each word in the search term
		foreach (explode(' ', $this->clean) AS $word)
		{
			// Check word length
			if (ee()->low_multibyte->strlen($word) < ee()->low_search_settings->get('min_word_length'))
			{
				return FALSE;
			}

			// Check stop words
			if (in_array($word, ee()->low_search_settings->stop_words()))
			{
				return FALSE;
			}
		}

		// Otherwise, we're OK
		return TRUE;
	}

	/**
	 * Is this term inflectable?
	 */
	public function is_inflectable()
	{
		return (!$this->exact && !$this->exclude && !$this->loose_left && !$this->loose_right &&
				$this->original && ee()->low_multibyte->strlen($this->clean) > 2);
	}

	/**
	 * Is this term stemmable?
	 */
	public function is_stemmable()
	{
		return (!$this->exact && !$this->exclude && !$this->loose_left && !$this->loose_right &&
				$this->original && ee()->low_multibyte->strlen($this->clean) > 2);
	}

}