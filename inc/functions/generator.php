<?php
/**
 * Generator Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Generate CSV file
 *
 * @param  string $file_name File name.
 * @param  array  $data Content.
 * @return void
 */
function wu_generate_csv($file_name, $data = array()) {

	$fp = fopen('php://output', 'w');

	if ($fp && $data) {

		header('Content-Type: text/csv; charset=utf-8');

		header('Content-Disposition: attachment; filename="' . $file_name . '.csv"');

		header('Pragma: no-cache');

		header('Expires: 0');

		foreach ($data as $data_line) {

			if (is_array($data_line)) {

				fputcsv($fp, array_values($data_line));

			} elseif (is_object($data_line)) {

				fputcsv($fp, array_values(get_object_vars($data_line)));

			} // end if;

		} // end foreach;

	} // end if;

} // end wu_generate_csv;
