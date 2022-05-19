<?php
/**
 * A trait to handle notable models.
 *
 * @package WP_Ultimo
 * @subpackage Models\Traits
 * @since 2.0.0
 */

namespace WP_Ultimo\Models\Traits;

use \WP_Ultimo\Objects\Note;

/**
 * Singleton trait.
 */
trait Notable {

	/**
	 * The notes saved.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Objects\Note[]
	 */
	protected $notes;

	/**
	 * Get all the notes saved for this model.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Note[]
	 */
	public function get_notes() {

		if ($this->notes === null) {

			$this->notes = get_metadata($this->get_meta_data_table_name(), $this->get_id(), 'wu_note', false);

		} // end if;

		return $this->notes;

	} // end get_notes;

	/**
	 * Adds a new note to this model.
	 *
	 * @since 2.0.0
	 *
	 * @param array|\WP_Ultimo|Objects\Note $note The note to add.
	 * @return bool
	 */
	public function add_note($note) {

		if (!is_a($note, 'Note')) {

			$note = new Note($note);

		} // end if;

		$status = $note->validate();

		if (is_wp_error($status)) {

			return $status;

		} // end if;

		$status = add_metadata($this->get_meta_data_table_name(), $this->get_id(), 'wu_note', $note, false);

		return $status;

	} // end add_note;

	/**
	 * Remove all notes related to this model.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function clear_notes() {

		$status = delete_metadata($this->get_meta_data_table_name(), $this->get_id(), 'wu_note', '', true);

		return $status;

	} // end clear_notes;

	/**
	 * Remove one note related to this model.
	 *
	 * @since 2.0.0
	 *
	 * @param string $note_id The Note ID.
	 *
	 * @return bool
	 */
	public function delete_note($note_id) {

		$model = $this->model;

		$notes = $this->get_notes();

		$mid = false;

		foreach ($notes as $note) {

			if ($note->note_id && $note->note_id === $note_id) {

				global $wpdb;

				$prefix = $wpdb->base_prefix;

				$table_name = "{$prefix}wu_{$model}meta";

				$column_name = "wu_{$model}_id";

				if ($model === 'site') {

					$table_name = "{$wpdb->base_prefix}blogmeta";

					$column_name = 'blog_id';

				} // end if;

				$mid = $wpdb->get_row($wpdb->prepare("SELECT meta_id FROM $table_name WHERE $column_name = %d AND meta_key = %s AND meta_value = %s", $this->get_id(), 'wu_note', maybe_serialize($note)), ARRAY_A); // phpcs:ignore

			} // end if;

		} // end foreach;

		if (!$mid) {

			return false;

		} // end if;

		$status = delete_metadata_by_mid("wu_{$model}", $mid['meta_id']);

		if ($model === 'site') {

			$status = delete_metadata_by_mid('blog', $mid['meta_id']);

		} // end if;

		return $status;

	} // end delete_note;

	/**
	 * Returns the meta data meta table.
	 *
	 * This is redundant, but it is better than changing the access of the original method.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_meta_data_table_name() {

		$query_class = new $this->query_class;

		// Maybe apply table prefix
		$table = !empty($query_class->prefix)
		? "{$query_class->prefix}_{$query_class->item_name}"
		: $query_class->item_name;

		return $table;

	} // end get_meta_data_table_name;

} // end trait Notable;
