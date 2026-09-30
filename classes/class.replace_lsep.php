<?php

namespace monotone;

/**
 * Class Replace_Lsep
 *
 * The L-SEP character can appear in text copied from certain applications or platforms,
 * causing layout issues. This class automatically replaces any L-SEP characters with a
 * replacement string to ensure proper formatting.
 */
class Replace_Lsep {
	var $lsep = "\u{2028}";
	var $replace = '<br>';

	function __construct() {
		add_action('save_post', [$this, 'replace_l_sep_content'], 10, 3);
		add_action('update_post_meta', [$this, 'replace_l_sep_meta'], 10, 4);
		add_action('acf/update_value', [$this, 'replace_l_sep_acf'], 10, 3);
	}

	/*
	 * Detect and replace the L-SEP character in post content, title, and excerpt with replacement string.
	 * Only do this if the character is found.
	 */
	function replace_l_sep_content($post_id, $post, $update) {
		$replaced = FALSE;
		$keys     = ['post_title', 'post_content', 'post_excerpt'];
		foreach ($keys as $key) {
			$replaced_value = $this->replace_l_sep($post->$key);
			if ($replaced_value) {
				$post->$key = $replaced_value;
				$replaced   = TRUE;
			}
		}
		if ($replaced) {
			wp_update_post($post);
		}
	}

	/*
	 * Detect and replace the L-SEP character in post meta fields with replacement string.
	 * Only do this if the character is found.
	 */
	function replace_l_sep_meta($meta_id, $post_id, $meta_key, $meta_value) {
		$replaced_value = $this->replace_l_sep($meta_value);
		if ($replaced_value) {
			update_post_meta($post_id, $meta_key, $replaced_value);
		}
	}


	/*
	 * Detect and replace the L-SEP character in ACF fields with replacement string.
	 * Only do this if the character is found.
	 */
	function replace_l_sep_acf($value, $post_id, $field) {
		$replace = $this->replace_l_sep($value);
		if ($replace) {
			$value = $replace;
		}

		return $value;
	}


	/*
	 * Replace the L-SEP character with replacement string.
	 */
	function replace_l_sep($string) {
		if (is_string($string) && strpos($string, $this->lsep) !== FALSE) {
			return str_replace($this->lsep, $this->replace, $string);
		}

		return FALSE;
	}
}

new Replace_Lsep();
