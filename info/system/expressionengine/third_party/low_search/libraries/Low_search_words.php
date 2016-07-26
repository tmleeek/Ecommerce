<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Search Words class, for handling words based on language
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2015, Low
 */
class Low_search_words {

	/**
	 * Language to use
	 *
	 * @var        string
	 * @access     private
	 */
	private $_lang;

	/**
	 * Inflection rules
	 *
	 * @var        array
	 * @access     private
	 */
	private $_rules;

	/**
	 * Stemmer class
	 *
	 * @var        object
	 * @access     private
	 */
	private $stemmer;

	/**
	 * Base file path
	 *
	 * @var        string
	 * @access     private
	 */
	private $_path;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @param      string
	 * @return     void
	 */
	public function __construct($lang = NULL)
	{
		// Set path
		$this->_path = PATH_THIRD.LOW_SEARCH_PACKAGE.'/i18n/';

		// Optionally set language
		if ($lang) $this->set_language($lang);
	}

	// --------------------------------------------------------------------

	/**
	 * Set language to work with
	 *
	 * @access     public
	 * @param      string
	 * @return     void
	 */
	public function set_language($lang = 'en')
	{
		$this->_set_inflection_rules($lang);
		$this->_load_stemmer($lang);
		$this->_lang = $lang;
	}

	/**
	 * Sets the inflection rules based on given language
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _set_inflection_rules($lang)
	{
		// --------------------------------------
		// Compose filename
		// --------------------------------------

		$file = $this->_path.$lang.'/inflection_rules.php';

		// --------------------------------------
		// Check custom inflection rules in config file
		// --------------------------------------

		if (($rules = ee()->config->item('low_search_inflection_rules')) &&
			isset($rules[$lang]))
		{
			$this->_rules = $rules[$lang];
		}

		// --------------------------------------
		// Check our own location
		// --------------------------------------

		elseif (file_exists($file))
		{
			$this->_rules = include $file;
		}

		// --------------------------------------
		// Set rules to NULL if unknown
		// --------------------------------------

		else
		{
			$this->_rules = NULL;
		}
	}

	/**
	 * Load the Stemmer class
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _load_stemmer($lang)
	{
		// --------------------------------------
		// Local cache to see if we checked things
		// --------------------------------------

		static $classes = array();

		// --------------------------------------
		// Defaults
		// --------------------------------------

		$file   = $this->_path.$lang.'/stemmer.php';
		$class  = "Low_search_{$lang}_stemmer";
		$method = 'stem';

		// --------------------------------------
		// Does the class exist already?
		// --------------------------------------

		if (isset($classes[$lang]))
		{
			$this->stemmer = $classes[$lang] ? new $class : FALSE;
			return;
		}

		// --------------------------------------
		// Check config file
		// --------------------------------------

		if (($s = ee()->config->item('low_search_stemmers')) &&
			isset($s[$lang]) && is_array($s[$lang]) && count($s[$lang]) == 3)
		{
			list($file, $class, $method) = $s[$lang];
		}

		// --------------------------------------
		// Does the file exist?
		// --------------------------------------

		$ok = FALSE; // Initiate cache value

		if (file_exists($file))
		{
			include $file;

			if (class_exists($class) && is_callable(array($class, $method)))
			{
				$this->stemmer = new $class;
				$this->stemmer->low_search_stem_method = $method;

				// We have an OK class!
				$ok = TRUE;
			}
		}

		// --------------------------------------
		// Save to cache
		// --------------------------------------

		$classes[$lang] = $ok;
	}

	// --------------------------------------------------------------------

	/**
	 * Return the stem of a word
	 */
	public function stem($word)
	{
		$stem = NULL;

		if ($this->stemmer)
		{
			$method = $this->stemmer->low_search_stem_method;
			$stem   = $this->stemmer->$method($word);
		}

		return $stem;
	}

	// --------------------------------------------------------------------

	/**
	 * Return a plural string
	 */
	public function plural($word)
	{
		return $this->_inflect($word, 'plural');
	}

	/**
	 * Return a singular string
	 */
	public function singular($word)
	{
		return $this->_inflect($word, 'singular');
	}

	/**
	 * Is term countable?
	 *
	 * @access     public
	 * @param      string
	 * @return     bool
	 */
	public function is_countable($word)
	{
		return ! in_array(ee()->low_multibyte->strtolower($word), $this->_get_rules('uncountable'));
	}

	/**
	 * Inflect a given word to the given type
	 *
	 * @access     public
	 * @param      string
	 * @param      string
	 * @return     string
	 */
	public function inflect($word, $type)
	{
		// If we have no rules, bail out
		if (empty($this->_rules) || ! in_array($type, array('singular', 'plural'))) return NULL;

		// Term should be countable
		if ( ! $this->is_countable($word)) return $word;

		// Get irregular rules
		$rules = $this->_get_rules('irregular');

		// Swap if singular
		if ($type == 'singular') $rules = array_flip($rules);

		// Check for irregular singular forms
		foreach ($rules AS $pattern => $result)
		{
			$pattern = "/{$pattern}\$/iu";

			if (preg_match($pattern, $word))
			{
				return preg_replace($pattern, $result, $word);
			}
		}

		// Get singular or plural rules rules
		foreach ($this->_get_rules($type) AS $pattern => $result)
		{
			if (preg_match($pattern, $word))
			{
				return preg_replace($pattern, $result, $word);
			}
		}

		// Fallback
		return $word;
	}

	/**
	 * Get inflection rules
	 *
	 * @access     private
	 * @param      string
	 * @return     array
	 */
	private function _get_rules($name)
	{
		return isset($this->_rules[$name])
			? $this->_rules[$name]
			: array();
	}

	// --------------------------------------------------------------------

	/**
	 * Is this word a valid word for the lexicon
	 *
	 * @access     public
	 * @param      string
	 * @return     bool
	 */
	public function is_valid($str)
	{
		// No digits and at least 3 characters long
		return ! (ee()->low_multibyte->strlen(trim($str)) < 3 || preg_match('/\d/', $str));
	}

	/**
	 * Clean a string for lexicon use:
	 * No tags, entities, non-word chacacters, or double/trailing spaces
	 * Optionally remove ignore words
	 *
	 * @access     public
	 * @param      string
	 * @param      bool
	 * @return     string
	 */
	public function clean($str, $ignore = FALSE)
	{
		static $words;

		if (is_null($words))
		{
			$words = ee()->low_search_settings->ignore_words();
			$words = array_map('preg_quote', $words);
		}

		$str = preg_replace('/<br\s?\/?>/iu', ' ', $str);
		$str = strip_tags($str);
		$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
		$str = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $str);
		$str = ee()->low_multibyte->strtolower($str);

		if ($ignore === TRUE && $words)
		{
			$str = preg_replace('/\b('.implode('|', $words).')\b/iu', '', $str);
		}

		$str = preg_replace('/\s{2,}/', ' ', $str);
		$str = trim($str);

		return $str;
	}

	/**
	 * Remove diacritics
	 *
	 * @access     public
	 * @param      string
	 * @return     string
	 */
	public function remove_diacritics($str)
	{
		static $chars;

		// --------------------------------------
		// Get translation array from native foreign_chars.php file
		// --------------------------------------

		if ( ! $chars && file_exists(APPPATH.'config/foreign_chars.php'))
		{
			// This contains a map of accented character numbers and their translations
			include APPPATH.'config/foreign_chars.php';

			if (isset($foreign_characters) && is_array($foreign_characters))
			{
				foreach ($foreign_characters AS $k => $v)
				{
					$chars[low_chr($k)] = $v;
				}
			}
		}

		// --------------------------------------
		// Remove diacritics from the given string
		// --------------------------------------

		if ($chars)
		{
			$str = strtr($str, $chars);
		}

		return $str;
	}

	/**
	 * Get array of dirty words from given words
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function get_dirty($words, $site = NULL)
	{
		return ee()->low_search_word_model->get_dirty($words, $this->_lang, $site);
	}

	/**
	 * Get array of similar sounding words from given words
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function get_sounds($words, $site = NULL)
	{
		return ee()->low_search_word_model->get_sounds($words, $this->_lang, $site);
	}
}
// End of file Low_search_words.php