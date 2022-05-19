<?php
/**
 * The Domain model for the Domain Mappings.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;
use WP_Ultimo\Domain_Mapping\Helper;
use WP_Ultimo\Database\Domains\Domain_Stage;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domain model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Domain extends Base_Model {

	/**
	 * Blog ID of the site associated with this domain.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $blog_id;

	/**
	 * The domain name mapped.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $domain = '';

	/**
	 * Is this domain active?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $active = true;

	/**
	 * Is this a primary_domain? Requests to other mapped domains will resolve to the primary.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $primary_domain = false;

	/**
	 * Should this domain be forced to be used only on HTTPS?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $secure = false;

	/**
	 * Stages of domain mapping
	 *
	 * - checking-dns
	 * - checking-ssl-cert
	 * - done
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $stage = 'checking-dns';

	/**
	 * Date when this was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * List of stages that should force the domain to an inactive status.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	const INACTIVE_STAGES = array(
		'checking-dns',
		'checking-ssl-cert',
		'failed',
	);

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Domains\\Domain_Query';

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		$id = $this->get_id();

		return array(
			'blog_id'        => 'required|integer',
			'domain'         => "required|domain|unique:\WP_Ultimo\Models\Domain,domain,{$id}",
			'stage'          => 'required|in:checking-dns,checking-ssl-cert,done-without-ssl,done,failed|default:checking-dns',
			'active'         => 'default:1',
			'secure'         => 'default:0',
			'primary_domain' => 'default:0',
		);

	} // end validation_rules;

	/**
	 * Returns the domain address mapped.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_domain() {

		return $this->domain;

	} // end get_domain;

	/**
	 * Sets the domain of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param string $domain Your Domain name. You don't need to put http or https in front of your domain in this field. e.g: example.com.
	 * @return void
	 */
	public function set_domain($domain) {

		$this->domain = $domain;

	} // end set_domain;

	/**
	 * Gets the URL with schema and all.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path The path to add to the end of the url.
	 * @return string
	 */
	public function get_url($path = '') {

		$schema = $this->is_secure() ? 'https://' : 'http://';

		return sprintf('%s%s/%s', $schema, $this->get_domain(), $path);

	} // end get_url;

	/**
	 * Get the ID of the corresponding site.
	 *
	 * @access public
	 * @since  2.0
	 * @return int
	 */
	public function get_blog_id() {

		if (abs($this->blog_id) === 0) {

			return '';

		} // end if;

		return (int) $this->blog_id;

	} // end get_blog_id;

	/**
	 * Sets the blog_id of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param int $blog_id The blog ID attached to this domain.
	 * @return void
	 */
	public function set_blog_id($blog_id) {

		$this->blog_id = $blog_id;

	} // end set_blog_id;

	/**
	 * Get the ID of the corresponding site.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_site_id() {

		return $this->get_blog_id();

	} // end get_site_id;

	/**
	 * Get the site object for this particular mapping.
	 *
	 * @since 2.0.0
	 * @return WP_Site|false
	 */
	public function get_site() {

		/**
		 * In a domain mapping environment, the user is not yet logged in.
		 * This means that we can't use BerlinDB, unfortunately, as it uses the user caps
		 * to decide which fields to make available.
		 *
		 * To bypass this limitation, we use the default WordPress function on those cases.
		 */
		if (!function_exists('current_user_can')) {

			return \WP_Site::get_instance($this->get_blog_id());

		} // end if;

		return wu_get_site($this->get_blog_id());

	} // end get_site;

	/**
	 * Check if this particular mapping is active.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_active() {

		if ($this->has_inactive_stage()) {

			return false;

		} // end if;

		return (bool) $this->active;

	} // end is_active;

	/**
	 * Sets the active state of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $active Set this domain as active (true), which means available to be used, or inactive (false).
	 * @return void
	 */
	public function set_active($active) {

		$this->active = $active;

	} // end set_active;

	/**
	 * Check if this is a primary domain.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_primary_domain() {

		return (bool) $this->primary_domain;

	} // end is_primary_domain;

	/**
	 * Sets the primary_domain state of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $primary_domain Define true to set this as primary domain of a site, meaning it's the main url, or set false.
	 * @return void
	 */
	public function set_primary_domain($primary_domain) {

		$this->primary_domain = $primary_domain;

	} // end set_primary_domain;

	/**
	 * Check if we should use this domain securely (via HTTPS).
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_secure() {

		return (bool) $this->secure;

	} // end is_secure;

	/**
	 * Sets the secure state of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $secure If this domain has some SSL security or not.
	 * @return void
	 */
	public function set_secure($secure) {

		$this->secure = $secure;

	} // end set_secure;

	/**
	 * Get the stage in which this domain is in at the moment.
	 *
	 * This is used to check the stage of the domain lifecycle.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_stage() {

		return $this->stage;

	} // end get_stage;

	/**
	 * Sets the stage of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param string $stage The state of the domain model object. Can be one of this options: checking-dns, checking-ssl-cert, done-without-ssl, done and failed.
	 * @return void
	 */
	public function set_stage($stage) {

		$this->stage = $stage;

	} // end set_stage;

	/**
	 * Check if this domain is on a inactive stage.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_inactive_stage() {

		return in_array($this->get_stage(), self::INACTIVE_STAGES, true);

	} // end has_inactive_stage;

	/**
	 * Returns the Label for a given stage level.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_stage_label() {

		$type = new Domain_Stage($this->get_stage());

		return $type->get_label();

	} // end get_stage_label;

	/**
	 * Gets the classes for a given stage level.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_stage_class() {

		$type = new Domain_Stage($this->get_stage());

		return $type->get_classes();

	} // end get_stage_class;

	/**
	 * Get date when this was created.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Set date when this was created.
	 *
	 * @since 2.0.0
	 * @param string $date_created Date when the domain was created. If no date is set, the current date and time will be used.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Check if the domain is correctly set-up in terms of DNS resolution.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_correct_dns() {

		global $current_site;

		$domain_url = $this->get_domain();

		$network_ip_address = Helper::get_network_public_ip();

		$results = \WP_Ultimo\Managers\Domain_Manager::dns_get_record($domain_url);

		$domains_and_ips = array_column($results, 'data');

		if (in_array($current_site->domain, $domains_and_ips, true)) {

			return true;

		} // end if;

		if (in_array($network_ip_address, $domains_and_ips, true)) {

			return true;

		} // end if;

		$result = false;

		/**
		 * Allow plugin developers to add new checks in order to define the results.
		 *
		 * @since 2.0.4
		 * @param bool $result the current result.
		 * @param self $this The current domain instance.
		 * @param array $domains_and_ips The list of domains and IPs found on the DNS lookup.
		 * @return bool If the DNS is correctly setup or not.
		 */
		$result = apply_filters('wu_domain_has_correct_dns', $result, $this, $domains_and_ips);

		return $result;

	} // end has_correct_dns;

	/**
	 * Checks if the current domain has a valid SSL certificate that covers it.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_valid_ssl_certificate() {

		return Helper::has_valid_ssl_certificate($this->get_domain());

	} // end has_valid_ssl_certificate;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * Needs to override the parent implementation
	 * to clear the cache.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		$new_domain = $this->exists();

		$before_changes = clone $this;

		$results = parent::save();

		if (is_wp_error($results) === false) {

			if ($new_domain) {

				if (has_action('mercator.mapping.created')) {

					$deprecated_args = array(
						$this,
					);

					/**
					 * Deprecated: Mercator created domain.
					 *
					 * @since 2.0.0
					 * @param self The domain object after saving.
					 * @param self The domain object before the changes.
					 * @return void.
					 */
					do_action_deprecated('mercator.mapping.created', $deprecated_args, '2.0.0', 'wu_domain_post_save');

				} // end if;

			} else {

				if (has_action('mercator.mapping.updated')) {

					$deprecated_args = array(
						$this,
						$before_changes,
					);

					/**
					 * Deprecated: Mercator updated domain.
					 *
					 * @since 2.0.0
					 * @param self The domain object after saving.
					 * @param self The domain object before the changes.
					 * @return void.
					 */
					do_action_deprecated('mercator.mapping.updated', $deprecated_args, '2.0.0', 'wu_domain_post_save');

				} // end if;

			} // end if;

			/*
			 * Resets cache.
			 *
			 * This will make sure the list of domains gets rebuild
			 * after a change is made.
			 */
			wp_cache_flush();

		} // end if;

		return $results;

	} // end save;

	/**
	 * Delete the model from the database.
	 *
	 * @since 2.0.0
	 *
	 * @return WP_Error|bool
	 */
	public function delete() {

		$results = parent::delete();

		if (is_wp_error($results) === false && has_action('mercator.mapping.deleted')) {

			$deprecated_args = array(
				$this,
			);

			/**
			 * Deprecated: Mercator Deleted domain.
			 *
			 * @since 2.0.0
			 * @param self The domain object just deleted.
			 * @return void.
			 */
			do_action_deprecated('mercator.mapping.deleted', $deprecated_args, '2.0.0', 'wu_domain_post_delete');

		} // end if;

		/*
		 * Delete log file.
		 */
		wu_log_clear("domain-{$this->get_domain()}");

		wu_log_add("domain-{$this->get_domain()}", __('Domain deleted and logs cleared...', 'wp-ultimo'));

		return $results;

	} // end delete;

	/**
	 * Get mapping by site ID
	 *
	 * @since 2.0.0
	 *
	 * @param int|stdClass $site Site ID, or site object from {@see get_blog_details}.
	 * @return Domain|WP_Error|null Mapping on success, WP_Error if error occurred, or null if no mapping found.
	 */
	public static function get_by_site($site) {

		global $wpdb;

		// Allow passing a site object in
		if (is_object($site) && isset($site->blog_id)) {

			$site = $site->blog_id;

		} // end if;

		if (!is_numeric($site)) {

			return new \WP_Error('wu_domain_mapping_invalid_id');

		} // end if;

		$site = absint($site);

		// Check cache first
		$mappings = wp_cache_get('id:' . $site, 'domain_mapping');

		if ($mappings === 'none') {

			return false;

		} // end if;

		if (!empty($mappings)) {

			return static::to_instances($mappings);

		} // end if;

		// Cache missed, fetch from DB
		// Suppress errors in case the table doesn't exist
		$suppress = $wpdb->suppress_errors();

		$domain_table = "{$wpdb->base_prefix}wu_domain_mappings";

		$mappings = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $domain_table . ' WHERE blog_id = %d ORDER BY primary_domain DESC, active DESC, secure DESC', $site)); //phpcs:ignore

		$wpdb->suppress_errors($suppress);

		if (!$mappings) {

			wp_cache_set('id:' . $site, 'none', 'domain_mapping');

			return false;

		} // end if;

		wp_cache_set('id:' . $site, $mappings, 'domain_mapping');

		return static::to_instances($mappings);

	} // end get_by_site;

	/**
	 * Gets mappings by domain names
	 *
	 * Note: This is used in sunrise, so unfortunately, we can't use the Query model.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $domains Domain names to search for.
	 * @return object
	 */
	public static function get_by_domain($domains) {

		global $wpdb;

		$domains = (array) $domains;

		// Check cache first
		$not_exists = 0;

		foreach ($domains as $domain) {

			$data = wp_cache_get('domain:' . $domain, 'domain_mappings');

			if (!empty($data) && $data !== 'notexists') {

				return new static($data);

			} elseif ($data === 'notexists') {

				$not_exists++;

			} // end if;

		} // end foreach;

		if ($not_exists === count($domains)) {

			// Every domain we checked was found in the cache, but doesn't exist
			// so skip the query
			return null;

		} // end if;

		$placeholders = array_fill(0, count($domains), '%s');

		$placeholders_in = implode(',', $placeholders);

		// Prepare the query
		$query = "SELECT * FROM {$wpdb->wu_dmtable} WHERE domain IN ($placeholders_in) AND active = 1 ORDER BY primary_domain DESC, active DESC, secure DESC LIMIT 1";

		$query = $wpdb->prepare($query, $domains); // phpcs:ignore

		// Suppress errors in case the table doesn't exist
		$suppress = $wpdb->suppress_errors();

		$mapping = $wpdb->get_row($query); // phpcs:ignore

		$wpdb->suppress_errors($suppress);

		if (empty($mapping)) {

			// Cache that it doesn't exist
			foreach ($domains as $domain) {

				wp_cache_set('domain:' . $domain, 'notexists', 'domain_mappings');

			} // end foreach;

			return null;

		} // end if;

		wp_cache_set('domain:' . $mapping->domain, $mapping, 'domain_mappings');

		return new static($mapping);

	} // end get_by_domain;

} // end class Domain;
