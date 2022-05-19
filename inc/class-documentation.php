<?php
/**
 * This helper class allow us to keep our external link references
 * in one place for better control; Links are also filterable;
 *
 * @package WP_Ultimo
 * @subpackage Documentation
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This helper class allow us to keep our external link references
 * in one place for better control; Links are also filterable;
 *
 * @since 2.0.0
 */
class Documentation {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the links so we can retrieve them later
	 *
	 * @var array
	 */
	protected $links;

	/**
	 * Holds the default link
	 *
	 * @var string
	 */
	protected $default_link = 'https://help.wpultimo.com/';

	/**
	 * Set the default links.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$links = array();

		// WP Ultimo Dashboard
		$links['wp-ultimo'] = 'https://help.wpultimo.com/en/articles/4803213-understanding-the-wp-ultimo-dashboard';

		// Settings Page
		$links['wp-ultimo-settings'] = 'https://help.wpultimo.com';

		// Checkout Pages
		$links['wp-ultimo-checkout-forms']         = 'https://help.wpultimo.com/en/articles/4803465-checkout-forms';
		$links['wp-ultimo-edit-checkout-form']     = 'https://help.wpultimo.com/en/articles/4803465-checkout-forms';
		$links['wp-ultimo-populate-site-template'] = 'https://help.wpultimo.com/en/articles/4803661-pre-populate-site-template-with-data-from-checkout-forms';

		// Products
		$links['wp-ultimo-products']     = 'https://help.wpultimo.com/en/articles/4803960-managing-your-products';
		$links['wp-ultimo-edit-product'] = 'https://help.wpultimo.com/en/articles/4803960-managing-your-products';

		// Memberships
		$links['wp-ultimo-memberships']     = 'https://help.wpultimo.com/en/articles/4803989-managing-memberships';
		$links['wp-ultimo-edit-membership'] = 'https://help.wpultimo.com/en/articles/4803989-managing-memberships';

		// Payments
		$links['wp-ultimo-payments']     = 'https://help.wpultimo.com/en/articles/4804023-managing-payments-and-invoices';
		$links['wp-ultimo-edit-payment'] = 'https://help.wpultimo.com/en/articles/4804023-managing-payments-and-invoices';

		// WP Config Closte Instructions
		$links['wp-ultimo-closte-config'] = 'https://help.wpultimo.com/en/articles/4807812-setting-the-sunrise-constant-to-true-on-closte';

		// Requirements
		$links['wp-ultimo-requirements'] = 'https://help.wpultimo.com/en/articles/4829561-wp-ultimo-requirements';

		// Installer - Migrator
		$links['installation-errors'] = 'https://help.wpultimo.com/en/articles/4829568-installation-errors';
		$links['migration-errors']    = 'https://help.wpultimo.com/en/articles/4829587-migration-errors';

		// Multiple Accounts
		$links['multiple-accounts'] = 'https://help.wpultimo.com/article/303-accounts-taken-care-of-with-wp-ultimo-multiple-accounts';

		$this->links = apply_filters('wu_documentation_links_list', $links);

	} // end init;

	/**
	 * Checks if a link exists.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $slug The slug of the link to be returned.
	 * @return boolean
	 */
	public function has_link($slug) {

		return (bool) $this->get_link($slug, false);

	} // end has_link;

	/**
	 * Retrieves a link registered
	 *
	 * @since 1.7.0
	 * @param  string $slug The slug of the link to be returned.
	 * @param  bool   $return_default If we should return a default value.
	 * @return string
	 */
	public function get_link($slug, $return_default = true) {

		$default = $return_default ? $this->default_link : false;

		$link = wu_get_isset($this->links, $slug, $default);

		/**
		 * Allow plugin developers to filter the links.
		 * Not sure how that could be useful, but it doesn't hurt to have it
		 *
		 * @since 1.7.0
		 * @param string $link         The link registered
		 * @param string $slug         The slug used to retrieve the link
		 * @param string $default_link The default link registered
		 */
		return apply_filters('wu_documentation_get_link', $link, $slug, $this->default_link);

	} // end get_link;

	/**
	 * Add a new link to the list of links available for reference
	 *
	 * @since 2.0.0
	 * @param string $slug The slug of a new link.
	 * @param string $link The documentation link.
	 * @return void
	 */
	public function register_link($slug, $link) {

		$this->links[$slug] = $link;

	}  // end register_link;

} // end class Documentation;
