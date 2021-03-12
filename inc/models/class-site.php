<?php
/**
 * The Site model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use \WP_Ultimo\Models\Base_Model;
use \WP_Ultimo\Objects\Limitations;
use \WP_Ultimo\Database\Sites\Site_Type;
use \WP_Ultimo\UI\Template_Previewer;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Site model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Site extends Base_Model {

	use Traits\Limitable, \WP_Ultimo\Traits\WP_Ultimo_Site_Deprecated;

	/**  DEFAULT WP_SITE COLUMNS */

	/**
	 * Title of the site.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $title;

	/**
	 * The site description.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $description;

	/**
	 * Blog ID. Should be accessed via id.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $blog_id;

	/**
	 * Network ID for this site.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $site_id = 1;

	/**
	 * Domain name used by this site.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $domain;

	/**
	 * Path of the site. Used when in sub-directory mode.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $path;

	/**
	 * Alias for WP CLI support.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $site_path;

	/**
	 * Date when the site was registered.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $registered;

	/**
	 * Date of the last update on this site.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $last_updated;

	/**
	 * Is this a public site?
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $public = true;

	/**
	 * Is this an archived site?
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $archived;

	/**
	 * Is this a site with mature content?
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $mature;

	/**
	 * Is this an spam site?
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $spam;

	/**
	 * Is this site deleted?
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $deleted;

	/**
	 * ID of the language being used on this site.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $lang_id;

	/**
	 * Holds the ID of the customer that owns this site.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $customer_id;

	/**
	 * Holds the ID of the membership associated with this site, if any.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $membership_id;

	/**
	 * Local membership cache.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Membership
	 */
	private $_membership;

	/**
	 * The site template id used to create this site.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $template_id;

	/**
	 * Duplication arguments.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	private $duplication_arguments = array();

	/**
	 * The site type of this particular site.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type;

	/**
	 * ID of the featured image being used on this product.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $feature_image_id;

	/**
	 * Categories
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $categories;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Sites\\Site_Query';

	/**
	 * Extra information about this site.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $extra_information;

	/**
	 * Keeps form date from the signup form.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $transient;

	/**
	 * Prepare data before it is stored into the database.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function prepare_extra_information_to_save() {

		$name_array  = wu_request('name');
		$value_array = wu_request('value');

		if (!empty($name_array) && !empty($value_array)) {

			$length_extra_information = max(count($name_array), count($value_array));
			$extra_information        = array();

			for ($i = 0; $i < $length_extra_information; $i++) {

				$extra_information[] = array(
					'name'  => $name_array[$i],
					'value' => $value_array[$i]
				);

			} // end for;

			$this->set_extra_information($extra_information);

		} // end if;

	} // end prepare_extra_information_to_save;

	/**
	 * Get the visits for this particular sites.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_visits_count() {

		$visits_manager = new \WP_Ultimo\Objects\Visits($this->get_id());

		return $visits_manager->get_visit_total('first day of this month', 'last day of this month');

	} // end get_visits_count;

	/**
	 * Set the categories for the site.
	 *
	 * @since 2.0.0
	 *
	 * @param array $categories The categories.
	 * @return void
	 */
	public function set_categories($categories) {

		$this->meta['wu_categories'] = $categories;

		$this->categories = $categories;

	} // end set_categories;

	/**
	 * Get the list of categories.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_categories() {

		if ($this->categories === null) {

			$this->categories = $this->get_meta('wu_categories', array());

		} // end if;

		return $this->categories;

	} // end get_categories;

	/**
	 * Get featured image ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_featured_image_id() {

		if ($this->feature_image_id === null) {

			return $this->get_meta('wu_featured_image_id');

		} // end if;

		return $this->feature_image_id;

	} // end get_featured_image_id;
	/**
	 * Get featured image url.
	 *
	 * @since 2.0.0
	 * @param string $size The size of the image to retrieve.
	 * @return string
	 */
	public function get_featured_image($size = 'wu-thumb-medium') {

		if ($this->get_type() === 'external') {

			return wu_get_asset('wp-ultimo-screenshot.png');

		} // end if;

		is_multisite() && switch_to_blog(wu_get_main_site_id());

		$image_attributes = wp_get_attachment_image_src($this->get_featured_image_id(), $size);

		is_multisite() && restore_current_blog();

		if ($image_attributes) {

			return $image_attributes[0];

		} // end if;

		return wu_get_asset('site-placeholder-image.png', 'img');

	} // end get_featured_image;

	/**
	 * Set featured image ID.
	 *
	 * @since 2.0.0
	 * @param int $image_id Holds the ID of the featured image.
	 * @return void
	 */
	public function set_featured_image_id($image_id) {

		$this->meta['wu_featured_image_id'] = $image_id;

		$this->feature_image_id = $image_id;

	} // end set_featured_image_id;

	/**
	 * Get the preview URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_url() {

		return Template_Previewer::get_instance()->get_preview_url($this->get_id());

	} // end get_preview_url;

	/**
	 * Get the preview URL attrs.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_url_attrs() {

		$is_enabled = Template_Previewer::get_instance()->get_setting('enabled');

		$href = 'href="%s" target="_blank"';

		if (!$is_enabled) {

			return sprintf($href, $this->get_active_site_url());

		} // end if;

		$onclick = 'onclick="window.open(\'%s\')"';

		return sprintf($onclick, $this->get_preview_url());

	} // end get_preview_url_attrs;

	/**
	 * Get blog ID. Should be accessed via id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_id() {

		return $this->get_blog_id();

	} // end get_id;

	/**
	 * Get blog ID. Should be accessed via id..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_blog_id() {

		return $this->blog_id;

	} // end get_blog_id;

	/**
	 * Set blog ID. Should be accessed via id..
	 *
	 * @since 2.0.0
	 * @param int $blog_id Blog ID. Should be accessed via id.
	 * @return void
	 */
	public function set_blog_id($blog_id) {

		$this->blog_id = $blog_id;

	} // end set_blog_id;

	/**
	 * Get network ID for this site..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_site_id() {

		return $this->site_id;

	} // end get_site_id;

	/**
	 * Set network ID for this site..
	 *
	 * @since 2.0.0
	 * @param int $site_id Network ID for this site.
	 * @return void
	 */
	public function set_site_id($site_id) {

		$this->site_id = $site_id;

	} // end set_site_id;

	/**
	 * Get title of the site..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return $this->title;

	} // end get_title;

	/**
	 * Set title of the site..
	 *
	 * @since 2.0.0
	 * @param string $title Title of the site.
	 * @return void
	 */
	public function set_title($title) {

		$this->title = $title;

	} // end set_title;

	/**
	 * Alias to get name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->get_title();

	} // end get_name;

	/**
	 * Alias to set title.
	 *
	 * @since 2.0.0
	 * @param string $title Title of the site.
	 * @return void
	 */
	public function set_name($title) {

		$this->set_title($title);

	} // end set_name;

	/**
	 * Gets the site description.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		if ($this->description) {

			return $this->description;

		} // end if;

		return get_blog_option($this->get_id(), 'blogdescription');

	} // end get_description;

	/**
	 * Sets the site description.
	 *
	 * @todo This is not yet persistent.
	 *
	 * @since 2.0.0
	 * @param string $description The site description.
	 * @return void
	 */
	public function set_description($description) {

		$this->description = $description;

	} // end set_description;

	/**
	 * Get domain name used by this site..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_domain() {

		return $this->domain;

	} // end get_domain;

	/**
	 * Set domain name used by this site..
	 *
	 * @since 2.0.0
	 * @param string $domain Domain name used by this site.
	 * @return void
	 */
	public function set_domain($domain) {

		$this->domain = $domain;

	} // end set_domain;

	/**
	 * Get path of the site. Used when in sub-directory mode..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_path() {

		return trim($this->path, '/');

	} // end get_path;

	/**
	 * Set path of the site. Used when in sub-directory mode..
	 *
	 * @since 2.0.0
	 * @param string $path Path of the site. Used when in sub-directory mode.
	 * @return void
	 */
	public function set_path($path) {

		$this->path = $path;

	} // end set_path;

	/**
	 * Get date when the site was registered..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_registered() {

		return $this->registered;

	} // end get_registered;

	/**
	 * Proxy for a common API.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_registered() {

		return $this->get_registered();

	} // end get_date_registered;

	/**
	 * Set date when the site was registered..
	 *
	 * @since 2.0.0
	 * @param string $registered Date when the site was registered.
	 * @return void
	 */
	public function set_registered($registered) {

		$this->registered = $registered;

	} // end set_registered;

	/**
	 * Get date of the last update on this site.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_last_updated() {

		return $this->last_updated;

	} // end get_last_updated;

	/**
	 * Proxy to last_updated.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_modified() {

		return $this->get_last_updated();

	} // end get_date_modified;

	/**
	 * Set date of the last update on this site..
	 *
	 * @since 2.0.0
	 * @param string $last_updated Date of the last update on this site.
	 * @return void
	 */
	public function set_last_updated($last_updated) {

		$this->last_updated = $last_updated;

	} // end set_last_updated;

	/**
	 * Get is this a public site?.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function get_public() {

		return $this->public;

	} // end get_public;

	/**
	 * Set is this a public site?.
	 *
	 * @since 2.0.0
	 * @param bool $public Is this a public site.
	 * @return void
	 */
	public function set_public($public) {

		$this->public = $public;

	} // end set_public;

	/**
	 * Get is this an archived site.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_archived() {

		return $this->archived;

	} // end is_archived;

	/**
	 * Set is this an archived site?.
	 *
	 * @since 2.0.0
	 * @param bool $archived Is this an archived site.
	 * @return void
	 */
	public function set_archived($archived) {

		$this->archived = $archived;

	} // end set_archived;

	/**
	 * Get is this a site with mature content.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_mature() {

		return $this->mature;

	} // end is_mature;

	/**
	 * Set is this a site with mature content?.
	 *
	 * @since 2.0.0
	 * @param bool $mature Is this a site with mature content.
	 * @return void
	 */
	public function set_mature($mature) {

		$this->mature = $mature;

	} // end set_mature;

	/**
	 * Get is this an spam site.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_spam() {

		return $this->spam;

	} // end is_spam;

	/**
	 * Set is this an spam site?.
	 *
	 * @since 2.0.0
	 * @param bool $spam Is this an spam site.
	 * @return void
	 */
	public function set_spam($spam) {

		$this->spam = $spam;

	} // end set_spam;

	/**
	 * Get is this site deleted.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_deleted() {

		return $this->deleted;

	} // end is_deleted;

	/**
	 * Set is this site deleted?.
	 *
	 * @since 2.0.0
	 * @param bool $deleted Is this site deleted.
	 * @return void
	 */
	public function set_deleted($deleted) {

		$this->deleted = $deleted;

	} // end set_deleted;

	/**
	 * Get iD of the language being used on this site.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_lang_id() {

		return $this->lang_id;

	} // end get_lang_id;

	/**
	 * Set iD of the language being used on this site.
	 *
	 * @since 2.0.0
	 * @param int $lang_id ID of the language being used on this site.
	 * @return void
	 */
	public function set_lang_id($lang_id) {

		$this->lang_id = $lang_id;

	} // end set_lang_id;

	/**
	 * Get extra information.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_information() {

		if ($this->extra_information === null) {

			return $this->get_meta('wu_site_extra_information');

		} // end if;

		return $this->extra_information;

	} // end get_extra_information;

	/**
	 * Set extra information.
	 *
	 * @since 2.0.0
	 * @param string $extra_information Holds extra information.
	 * @return void
	 */
	public function set_extra_information($extra_information) {

		$this->meta['wu_site_extra_information'] = $extra_information;

	} // end set_extra_information;

	/** LIMITATIONS TO CHECK ***************************/

	/**
	 * Check if we are already above the post quota.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return boolean
	 */
	public function is_post_above_limit($post_type) {
		/*
		 * Calculate post count based on all different status
		 */
		$post_count = $this->get_post_count($post_type);

		// Get the allowed quota
		$quota = $this->get_quota($post_type);

		/**
		 * Checks if a given post type is allowed on this plan
		 * Allow plugin developers to filter the return value
		 *
		 * @since 1.7.0
		 * @param bool If the post type is disabled or not
		 * @param WU_Plan Plan of the current user
		 * @param int User id
		 */
		return apply_filters('wu_limits_is_post_above_limit', $quota > 0 && $post_count >= $quota);

	} // end is_post_above_limit;

	/**
	 * Get the post count for this site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type The post type to check against.
	 * @return int
	 */
	public static function get_post_count($post_type) {

		$count = 0;

		$post_count = wp_count_posts($post_type);

		/**
		 * Allow plugin developers to change which post status should be counted
		 * By default, published and private posts are counted
		 *
		 * @since 1.9.1
		 * @param array $post_status The list of post statuses
		 * @param string $post_type  The post type slug
		 * @return array New array of post status
		 */
		$post_statuses = apply_filters('wu_post_count_statuses', array('publish', 'private'), $post_type);

		foreach ($post_statuses as $post_status) {

			if (isset($post_count->{$post_status})) {

				$count += (int) $post_count->{$post_status};

			} // end if;

		} // end foreach;

		/**
		 * Allow plugin developers to change the count total
		 *
		 * @since 1.9.1
		 * @param int $count The total post count
		 * @param object $post_counts WordPress object return by the wp_count_posts fn
		 * @param string $post_type  The post type slug
		 * @return int New total
		 */
		return apply_filters('wu_post_count', $count, $post_count, $post_type);

	} // end get_post_count;

	/**
	 * Get holds the ID of the customer that owns this site..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_customer_id() {

		if ($this->customer_id === null) {

			$this->customer_id = $this->get_meta('wu_customer_id');

		} // end if;

		return $this->customer_id;

	} // end get_customer_id;

	/**
	 * Set holds the ID of the customer that owns this site..
	 *
	 * @since 2.0.0
	 * @param int $customer_id Holds the ID of the customer that owns this site.
	 * @return void
	 */
	public function set_customer_id($customer_id) {

		$this->meta['wu_customer_id'] = $customer_id;

		$this->customer_id = $customer_id;

	} // end set_customer_id;

	/**
	 * Gets the customer object associated with this membership.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Customer;
	 */
	public function get_customer() {

		return wu_get_customer($this->get_customer_id());

	} // end get_customer;

	/**
	 * Checks if a given customer should have access to this site options.
	 *
	 * @since 2.0.0
	 *
	 * @param int $customer_id The customer id to check.
	 * @return boolean
	 */
	public function is_customer_allowed($customer_id = false) {

		if (current_user_can('manage_network')) {

			return true;

		} // end if;

		if (!$customer_id) {

			$customer = WP_Ultimo()->currents->get_customer();

			$customer_id = $customer ? $customer->get_id() : 0;

		} // end if;

		$allowed = abs($customer_id) === abs($this->get_customer_id());

		return apply_filters('wu_site_is_customer_allowed', $allowed, $customer_id, $this);

	} // end is_customer_allowed;

	/**
	 * Get holds the ID of the membership associated with this site, if any..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_membership_id() {

		if ($this->membership_id === null) {

			$this->membership_id = $this->get_meta('wu_membership_id');

		} // end if;

		return $this->membership_id;

	} // end get_membership_id;

	/**
	 * Set holds the ID of the membership associated with this site, if any..
	 *
	 * @since 2.0.0
	 * @param int $membership_id Holds the ID of the membership associated with this site, if any.
	 * @return void
	 */
	public function set_membership_id($membership_id) {

		$this->meta['wu_membership_id'] = $membership_id;

		$this->membership_id = $membership_id;

	} // end set_membership_id;

	/**
	 * Checks if this site has a membership.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_membership() {

		return !empty($this->get_membership());

	} // end has_membership;

	/**
	 * Checks if the site has a product.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_product() {

		return $this->has_membership() && $this->get_membership()->has_product();

	} // end has_product;

	/**
	 * Gets the membership object associated with this membership.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Membership;
	 */
	public function get_membership() {

		if ($this->_membership !== null) {

			return $this->_membership;

		} // end if;

		if (function_exists('wu_get_membership')) {

			$this->_membership = wu_get_membership($this->get_membership_id());

			return $this->_membership;

		} // end if;

		global $wpdb;

		$table_name = "{$wpdb->base_prefix}wu_memberships";

		$membership_id = $this->get_membership_id();

		if (!$membership_id) {

			return false;

		} // end if;

		$query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d LIMIT 1", $membership_id); // phpcs:ignore

		$results = $wpdb->get_row($query); // phpcs:ignore

		if (!$results) {

			return false;

		} // end if;

		$this->_membership = new \WP_Ultimo\Models\Membership($results);

		return $this->_membership;

	} // end get_membership;

	/**
	 * Returns the plan that created this site.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product
	 */
	public function get_plan() {

		if ($this->has_membership()) {

			return $this->get_membership()->get_plan();

		} // end if;

		return false;

	} // end get_plan;

	/**
	 * Get template ID.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function get_template_id() {

		if ($this->template_id === null) {

			$this->template_id = $this->get_meta('wu_template_id');

		} // end if;

		return $this->template_id;

	} // end get_template_id;

	/**
	 * Set the template ID.
	 *
	 * @since 2.0.0
	 * @param boolean $template_id If this site is a template or not.
	 * @return void
	 */
	public function set_template_id($template_id) {

		$this->meta['wu_template_id'] = abs($template_id);

		$this->template_id = $template_id;

	} // end set_template_id;

	/**
	 * Gets the site object associated with this membership.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Site;
	 */
	public function get_template() {

		return wu_get_site($this->get_template_id());

	} // end get_template;

	/**
	 * Returns the default duplication arguments.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_default_duplication_arguments() {

		return array(
			'keep_users' => true,
			'copy_files' => true,
			'public'     => true,
		);

	} // end get_default_duplication_arguments;

	/**
	 * Get duplication arguments..
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_duplication_arguments() {

		$args = wp_parse_args($this->duplication_arguments, $this->get_default_duplication_arguments());

		return $args;

	} // end get_duplication_arguments;

	/**
	 * Set duplication arguments..
	 *
	 * @since 2.0.0
	 * @param array $duplication_arguments Duplication arguments.
	 * @return void
	 */
	public function set_duplication_arguments($duplication_arguments) {

		$this->duplication_arguments = $duplication_arguments;

	} // end set_duplication_arguments;

	/**
	 * Get the site type of this particular site..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		if ($this->get_id() && is_main_site($this->get_id())) {

			return 'main';

		} // end if;

		if ($this->type === null) {

			$type = $this->get_meta('wu_type');

			$this->type = $type ? $type : 'default';

		} // end if;

		return $this->type;

	} // end get_type;

	/**
	 * Set the site type of this particular site..
	 *
	 * @since 2.0.0
	 * @param string $type The site type of this particular site.
	 * @return void
	 */
	public function set_type($type) {

		$this->meta = (array) $this->meta;

		$this->meta['wu_type'] = $type;

		$this->type = $type;

	} // end set_type;

	/**
	 * Returns the active site URL, which can be a mapped domain.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_active_site_url() {

		if (!$this->get_id()) {

			return $this->get_site_url();

		} // end if;

		$domains = wu_get_domains(array(
			'primary'       => true,
			'blog_id'       => $this->get_id(),
			'stage__not_in' => \WP_Ultimo\Models\Domain::INACTIVE_STAGES,
			'number'        => 1,
		));

		if (!empty($domains)) {

			$domain = current($domains);

			return ($domain->is_secure() ? 'https://' : 'http://') . $domain->get_domain();

		} // end if;

		return $this->get_site_url();

	} // end get_active_site_url;

	/**
	 * Returns the original URL for the blog.
	 *
	 * This is useful when we need to know the original URL, without
	 * mapping applied.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_site_url() {

		$url = esc_url(sprintf($this->get_domain() . '/' . $this->get_path()));

		return $url;

	} // end get_site_url;

	/**
	 * Checks if this model was already saved to the database.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function exists() {

		return !empty($this->blog_id);

	} // end exists;

	/**
	 * Override te constructor due to this being a native table.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $object Object containing the parameters.
	 */
	public function __construct($object = null) {

		parent::__construct($object);

		$details = get_blog_details($this->get_blog_id());

		if ($details && $this->title === null) {

			$this->set_title($details->blogname);

		} // end if;

		/*
		 * Quick fix for WP CLI, since it uses the --path arg to do other things.
		 */
		if (!$this->path) {

			$this->path = $this->site_path;

		} // end if;

		$object = (object) $object;

	} // end __construct;

	/**
	 * Gets the form data saved at the time of the site creation.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_transient() {

		if ($this->transient === null) {

			$this->transient = $this->get_meta('wu_transient');

		} // end if;

		return $this->transient;

	} // end get_transient;

	/**
	 * Holds the form data at the time of registration.
	 *
	 * @since 2.0.0
	 * @param array $transient Form data.
	 * @return void
	 */
	public function set_transient($transient) {

		$this->meta['wu_transient'] = $transient;

		$this->transient = $transient;

	} // end set_transient;

	/**
	 * Returns the Label for a given type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type_label() {

		$type = new Site_Type($this->get_type());

		return $type->get_label();

	} // end get_type_label;

	/**
	 * Gets the classes for a given class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type_class() {

		$type = new Site_Type($this->get_type());

		return $type->get_classes();

	} // end get_type_class;

	/**
	 * Adds magic methods to return options.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Method name.
	 * @param array  $args List of arguments.
	 * @throws \BadMethodCallException Throws exception when method is not found.
	 * @return mixed
	 */
	public function __call($name, $args) {

		if (strpos($name, 'get_option_') !== false) {

			$option = str_replace('get_option_', '', $name);

			return get_blog_option($this->get_id(), $option, false);

		} // end if;

		throw new \BadMethodCallException(__CLASS__ . "::$name()");

	} // end __call;

	/**
	 * Checks if this is the primary site of the customer.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_customer_primary_site() {

		$customer = $this->get_customer();

		if (!$customer) {

			return false;

		} // end if;

		$user_id = $customer->get_user_id();

		if (!$user_id) {

			return false;

		} // end if;

		$primary_site_id = get_user_option('primary_blog', $user_id);

		return abs($primary_site_id) === abs($this->get_id());

	} // end is_customer_primary_site;

	/**
	 * Delete the model from the database.
	 *
	 * @since 2.0.0
	 *
	 * @return WP_Error|bool
	 */
	public function delete() {

		if (!$this->get_id()) {

			return new \WP_Error("wu_{$this->model}_delete_unsaved_item", __('Item not found.', 'wp-ultimo'));

		} // end if;

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param Base_Model $this The object instance.
		 */
		do_action("wu_{$this->model}_pre_delete", $this);

		$result = (bool) wp_delete_site($this->get_id());

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param bool       $result True if the object was successfully deleted.
		 * @param Base_Model $this   The object instance.
		 */
		do_action("wu_{$this->model}_post_delete", $result, $this);

		return $result;

	} // end delete;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		/**
		 * In order to be backwards compatible here, we'll have to do some crazy stuff,
		 * like overload the form session with the meta data saved on the pending site.
		 */
		if (has_filter('wu_search_and_replace_on_duplication')) {

			$transient = $this->get_transient();

			$session = wu_get_session('signup');

			$session->set('form', $transient);

			$session->commit();

		} // end if;

		$data = get_object_vars($this);

		unset($data['_original']);

		$data_unserialized = $data;

		$saved = true;

		$this->prepare_extra_information_to_save();

		if (!$this->exists()) {

			$network = get_network();

			$domain = $this->get_domain() ? $this->get_domain() : $network->domain;

			$network_id = $this->get_site_id() ? $this->get_site_id() : get_current_network_id();

			$user_id = get_current_user_id();

			$customer = wu_get_customer($this->get_customer_id());

			if ($customer) {

				$user_id = $customer->get_user_id();

				$email = $customer->get_email_address();

			} // end if;

			/*
			 * Decide if we need to duplicate this site, or create a new one.
			 */
			if ($this->get_template()) {

				$saved = \WP_Ultimo\Helpers\Site_Duplicator::duplicate_site($this->get_template_id(), $this->get_title(), array(
					'email'  => $email,
					'path'   => $this->get_path(),
					'domain' => $domain,
					'meta'   => $this->meta,
				));

			} else {

				$saved = wpmu_create_blog($domain, $this->get_path(), $this->get_title(), $user_id, $this->meta, $network_id);

				if ($saved && $this->get_public()) {

					$site_id = $saved;

					wp_update_site($site_id, array(
						'public' => $this->get_public(),
					));

				} // end if;

				/**
				 * Fires after a site is created for the first time.
				 *
				 * @since 2.0.0
				 *
				 * @param array      $data The object data that will be stored.
				 * @param Base_Model $this The object instance.
				 */
				do_action('wu_site_created', $data, $this);

			} // end if;

			wu_enqueue_async_action('wu_async_take_screenshot', array(
				'site_id' => $saved,
			), 'site');

		} else {

			$saved = wp_update_site($this->get_id(), $this->to_array());

		} // end if;

		if (!is_wp_error($saved)) {

			$this->blog_id = $saved;

			foreach ($this->meta as $key => $value) {

				update_site_meta($saved, $key, $value);

			} // end foreach;

		} else {

			return $saved;

		} // end if;

		/**
		 * Handles membership
		 */
		$membership = $this->get_membership();

		if ($membership) {

			$customer_id = $membership->get_customer_id();

			$this->set_customer_id($customer_id);

		} // end if;

		/**
		 * Handles customers
		 */
		$customer = $this->get_customer();

		if ($customer) {

			$role = wu_get_setting('default_role', 'administrator');

			update_site_meta($this->get_id(), 'wu_customer_id', $customer->get_id());

			$user_id = $customer->get_user_id();

			add_user_to_blog($this->get_id(), $user_id, $role);

		} // end if;

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param array      $model The model slug.
		 * @param array      $data The object data that will be stored, serialized.
		 * @param array      $data_unserialized The object data that will be stored.
		 * @param Base_Model $this The object instance.
		 */
		do_action('wu_model_post_save', $this->model, $data, $data_unserialized, $this);

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param array      $data The object data that will be stored.
		 * @param Base_Model $this The object instance.
		 */
		do_action("wu_{$this->model}_post_save", $data, $this);

		if (isset($session)) {

			$session->destroy();

		} // end if;

		return $this;

	} // end save;

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		$search_result = $this->to_array();

		$search_result['siteurl'] = $this->get_active_site_url();

		return $search_result;

	} // end to_search_results;

	/**
	 * Returns a list of sites based on the type.
	 *
	 * Type can be customer_owned or template.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type Type to return. Can be customer_owned or template.
	 * @return array
	 */
	public static function get_all_by_type($type = 'customer_owned') {

		global $wpdb;

		if ($type === 'pending') {

			$table_name = "{$wpdb->base_prefix}wu_membershipmeta";

			$sql = "SELECT meta_value FROM {$table_name} WHERE meta_key = 'pending_site' ORDER BY meta_id DESC"; // phpcs:ignore

			$results = array_column($wpdb->get_results($sql), 'meta_value'); // phpcs:ignore

			$results = array_map(function($item) {

				$pending_site = unserialize($item);

				$pending_site->set_type('pending');

				return $pending_site;

			}, $results);

			return $results;

		} // end if;

		$query = array(
			'meta_query' => array(
				array(
					'key'   => 'wu_type',
					'value' => $type,
				),
			),
		);

		return static::query($query);

	} // end get_all_by_type;

	/**
	 * Get the list of all Site Template Categories.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_all_categories() {

		global $wpdb;

		$cache = wp_cache_get('site_categories', 'sites');

		if (is_array($cache)) {

			return $cache;

		} // end if;

		$query = "SELECT DISTINCT meta_value FROM {$wpdb->base_prefix}blogmeta WHERE meta_key = %s";

		$results = $wpdb->get_results($wpdb->prepare($query, 'wu_categories'), ARRAY_A); // phpcs:ignore

		$all_arrays = array_column($results, 'meta_value');

		$all_arrays = array_map('maybe_unserialize', $all_arrays);

		$all_arrays = array_merge(...$all_arrays);

		$all_arrays = array_filter($all_arrays);

		$all_arrays = array_unique($all_arrays);

		$final_array = array_combine($all_arrays, $all_arrays);

		wp_cache_set('site_categories', $final_array, 'sites');

		return $final_array;

	} // end get_all_categories;

} // end class Site;
