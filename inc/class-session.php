<?php
/**
 * Session Handler
 *
 * Other than interact with the PHP Session directly, we decided
 * to use a wrapper to make sure things are more or less error proof,
 * despite of how strange PHP sessions can be at times.
 *
 * At the moment, this wrapper encapsulates the implementation of the
 * Aura/Session package.
 *
 * As a nice bonus, Aura's implementation adds a flash layer that can be used
 * to persist error messages across page requests, what can be super useful.
 *
 * Other than that, we don't recommend using sessions in WordPress and for WP Ultimo
 * in general. We use it to keep track of multi-step signup flows so we don't need to
 * rely on saving sensitive data on WordPress transients.
 *
 * @see https://github.com/auraphp/Aura.Session
 *
 * @author Arindo Duque <arindo@wpultimo.com>
 * @package WP_Ultimo
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Session Class.
 */
class Session {

	/**
	 * The instance of the session manager.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Dependencies\Aura\Session\Session
	 */
	protected $session_manager;

	/**
	 * The instance of the current segment of the session.
	 *
	 * For practical terms, this is the same as a key under the session manager.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Dependencies\Aura\Session\Segment
	 */
	protected $segment;

	/**
	 * Constructs the manager and returns a section using the real name.
	 *
	 * @since 2.0.0
	 *
	 * @param string $realm_name The segment to add elements to.
	 */
	public function __construct($realm_name) {

		$session_factory = new \WP_Ultimo\Dependencies\Aura\Session\SessionFactory;

		$this->session_manager = $session_factory->newInstance($_COOKIE);

		$this->segment = $this->session_manager->getSegment($realm_name);

	} // end __construct;

	/**
	 * Gets the value of a session key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to retrieve.
	 * @return mixed
	 */
	public function get($key) {

		return $this->segment->get($key);

	} // end get;

	/**
	 * Set the value of a session key.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The value of the key to set.
	 * @param mixed  $value The value.
	 * @return bool
	 */
	public function set($key, $value) {

		return $this->segment->set($key, $value);

	} // end set;

	/**
	 * Appends values to a given key, instead of replacing it.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The value of the key to set.
	 * @param array  $values Additional array values.
	 * @return bool
	 */
	public function add_values($key, $values) {

		$current_values = (array) $this->segment->get($key);

		return $this->segment->set($key, array_merge($current_values, $values));

	} // end add_values;

	/**
	 * Set a flash message.
	 *
	 * Flash messages are persistent messages that are only valid for the next
	 * request. This is useful for displaying error messages and such.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The value of the key to set.
	 * @param mixed  $value The value.
	 * @return bool
	 */
	public function set_flash($key, $value) {

		return $this->segment->setFlashNow($key, $value);

	} // end set_flash;

	/**
	 * Returns the content of a flash message.
	 *
	 * @since 2.0.0
	 * @param string $key The key to retrieve.
	 * @return mixed
	 */
	public function get_flash($key) {

		return $this->segment->getFlash($key);

	} // end get_flash;

	/**
	 * Writes to the session and closes the connection.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function commit() {

		return $this->session_manager->commit();

	} // end commit;

	/**
	 * Clears the current session.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function clear() {

		return $this->session_manager->clear();

	} // end clear;

	/**
	 * Destroys the session. Equivalent to session_destroy();
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function destroy() {

		return $this->session_manager->destroy();

	} // end destroy;

} // end class Session;
