<?php
/**
 * Site Functions
 *
 * Public APIs to load and deal with WP Ultimo sites.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Site
 * @version     2.0.0
 */

/**
 * Returns the current site.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Site
 */
function wu_get_current_site() {

	return new \WP_Ultimo\Models\Site(get_blog_details());

} // end wu_get_current_site;

/**
 * Returns the site object
 *
 * @since 2.0.0
 *
 * @param int $id The id of the site.
 * @return \WP_Ultimo\Models\Site
 */
function wu_get_site($id) {

	return \WP_Ultimo\Models\Site::get_by_id($id);

} // end wu_get_site;

/**
 * Gets a site based on the hash.
 *
 * @since 2.0.0
 *
 * @param string $hash The hash for the payment.
 * @return \WP_Ultimo\Models\Site|false
 */
function wu_get_site_by_hash($hash) {

	return \WP_Ultimo\Models\Site::get_by_hash($hash);

} // end wu_get_site_by_hash;

/**
 * Queries sites.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Site[]
 */
function wu_get_sites($query = array()) {

	if (!empty($query['search'])) {

		$domain_ids = wu_get_domains(array(
			'number' => -1,
			'search' => '*' . $query['search'] . '*',
			'fields' => array('blog_id'),
		));

		$domain_ids = array_column($domain_ids, 'blog_id');

		if (!empty($domain_ids)) {

			$query['blog_id__in'] = $domain_ids;

			unset($query['search']);

		} // end if;

	} // end if;

	return \WP_Ultimo\Models\Site::query($query);

} // end wu_get_sites;

/**
 * Returns the list of Site Templates..
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_site_templates() {

	return \WP_Ultimo\Models\Site::get_all_by_type('site_template');

} // end wu_get_site_templates;

/**
 * Parses a URL and breaks it into different parts
 *
 * @since 2.0.0
 *
 * @param string $domain The domain to break up.
 * @return object
 */
function wu_handle_site_domain($domain) {

	global $current_site;

	if (strpos($domain, 'http') === false) {

		$domain = "https://{$domain}";

	} // end if;

	$parsed = parse_url($domain);

	return (object) $parsed;

} // end wu_handle_site_domain;

/**
 * Creates a new site.
 *
 * @since 2.0.0
 *
 * @param array $site_data Site data.
 * @return \WP_Error|\WP_Ultimo\Models\Product
 */
function wu_create_site($site_data) {

	$current_site = get_current_site();

	$site_data = wp_parse_args($site_data, array(
		'domain'                => $current_site->domain,
		'path'                  => '/',
		'title'                 => false,
		'type'                  => false,
		'template_id'           => false,
		'duplication_arguments' => false,
		'public'                => true,
	));

	$site = new \WP_Ultimo\Models\Site($site_data);

	$site->set_public($site_data['public']);

	$saved = $site->save();

	return is_wp_error($saved) ? $saved : $site;

} // end wu_create_site;

/**
 * Returns the correct domain/path combination when creating a new site.
 *
 * @since 2.0.0
 *
 * @param string $path_or_subdomain The site path.
 * @return object Object with a domain and path properties.
 */
function wu_get_site_domain_and_path($path_or_subdomain = '/') {

	global $current_site;

	$path_or_subdomain = trim($path_or_subdomain, '/');

	$d = new \stdClass;

	if (is_multisite() && is_subdomain_install()) {

		$d->domain = "{$path_or_subdomain}.{$current_site->domain}";

		$d->path = '/';

		return $d;

	} // end if;

	$d->domain = $current_site->domain;

	$d->path = "/{$path_or_subdomain}";

	return $d;

} // end wu_get_site_domain_and_path;
