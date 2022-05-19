=== WP Ultimo ===
Contributors: aanduque, freemius
Requires at least: 5.3
Tested up to: 5.9.3
Requires PHP: 7.1.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Complete Network Solution.

== Description ==

WP Ultimo

The Complete Network Solution.

== Installation ==

1. Upload 'wp-ultimo' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the step by step Wizard to set the plugin up

== Changelog ==

Version 2.0.13 - Released on 2022-05-06

* Improvement: Set the first period in period selector as pre-selected if durations is not added before, preventing the mixing of products with different period if using the selector;
* Improvement: Removed verification for existing signatures in v1, reducing the chance of false negatives as the need to run migration process;
* Fixed: Stripe card check causing error with multi step forms wen using the product slug in url;
* Fixed: Product variations not being identified and not setting the price on cart overview;
* Fix: Get the correct address field in form to set the cart regardless of multi step or single step form type;
* Fix: Stripe checkout not working on Firefox;
* Fix: Delete option on broadcast admin list table;
* Fix: Broadcast list table causing fatal due error on customers column;
* Fix: Email footer with subsite url on link instead of main site;

Version 2.0.12 - Released on 2022-04-25

* Added: Created the wu_before_form_submitted javascript filter to allow bypass the checkout form before submit;
* Added: Sunrise.php data on WP Ultimo system info page;
* Improvement: Define COOKIE_DOMAIN constant on domain mapped sites to prevent cookie related errors;
* Improvement: Search models by hash improving the UX on admin forms and preventing errors when creating sites for customers on network admin dashboard;
* Fixed: Added a default setup to use with Stripe Portal to prevent errors when not configured on Stripe account;
* Fixed: Makes sure if card is valid before start the account creation process using Stripe Gateway;
* Fixed: Check if auth_redirect and wp_set_auth_cookie functions exist before define;
* Fixed: Template previewer error when product is not selected in checkout form;
* Fixed: Error on remaining sites calculation if limitation is not set;
* Fixed: Check if there's domain options available on create site form;
* Fixed: Prevent errors with variable types defined on limits;
* Fixed: Correct check the status of a pending site for a membership on thank you page;
* Fixed: Billing address not being saved on multi step checkout form when not in last step;
* Fixed: Gutenberg blocks not being loaded;
* Fixed: Login page not working on blocked sites;
* Fixed: Error on some checkout processes due pending site check;
* Internal: Improved our test structure with cypress to help catching error before releases;

Version 2.0.11 - Released on 2022-04-09

* Important
  * Fixed: Plugin & Theme Limitations not being loaded, leading to plugins not being hidden, or auto-activated after registration;
  * Fixed: Site duplicator now deals with plugins that have custom database tables with foreign key constraints - for example, Bookly;
  * Fixed: Incompatibility between Avada and mapped domains;
  * Fixed: Incompatibility between Avada and template previewer;
  * Fixed: Incompatibility with FluentCRM breaking the registration flow;
  * Fixed: Domain mapping issues on previous build;
  * Fixed: Payments pending on trial plans;
  * Fixed: Products with wrong duration after checkout;
  * Fixed: Sites created in double in some circumstances - specially when using Stripe & Stripe Checkout;
  * Added: A completely re-designed and re-written SSO module, built to work in a higher level of abstraction to support all current and future possible use cases; It deals natively with:
    * Security: there's a token exchange protocol that verifies both sides of the auth process;
    * Cross-scheme Auth: When we are not able to access remote cookies due to different schemes being used, we force a regular redirect flow to authenticate the customer regardless;
    * Admin Panel Access: Prevents the auth_redirect function from sending the request to wp-login.php before SSO has a chance to kick in;
    * Auth for different Domain Options: SSO no longer focuses on mapped domains only. It gets triggered anytime there's a mismatch between the target domain and the main network domain. This allows it to work with sites that were registered using different domain options offered on checkout;
    * Loading Screen: the new SSO offers a setting that adds a loading overlay when SSO is being performed on the front-end;
    * Support to Incognito Mode: most browsers prevent cookies from being set from third-parties, nowadays. Our SSO detects incognito mode and forces a full redirect, instead of trying to authenticate directly with the verify code;
  * Added: Placeholders on Thank You page snippet code editor, to pass values to conversion snippets; 
  * Added: Country classes with state and city lists to allow for more granular control over how taxes apply territorially, as well as to guarantee that valid billing address info is entered during checkout. At the moment, the following countries are supported: US, GB, BR, FR, RU, DE, NE, ES, CA, CN, ZA (this list was devised based on our current customer base, new countries can be added as requested).
  * Added: REST API field requirements and descriptions are now compiled and saved as static files at build time. This is done because we use reflection on PHPDocBlocks to generate the documentation of the fields, and comments might not be available if op_cache is enabled on production, causing the REST API to break;
  * Improvement: Add CNAME records from Cloudflare to the DNS checking results, in addition to A and AAAA;
  * Improvement: Updated DNS lib to prevent memory leaks when checking for DNS;
  * Improvement: Adds fatal error catcher when the DNS resolver runs into a memory limit error, although this no longer happens due to the above fix;
  * Improvement: Using CSS grid to lay fields on the checkout field instead of flex/float. This cleaned up the fields markup a good bit and makes it more customizable. By default, the checkout form is a two-column grid, with fields spanning the two columns;
  * Improvement: Better responsiveness on the checkout form, resulting from the use of CSS grid;
  * Improvement: Replaced the old hacky implementation of the Site URL prefix and suffix blocks (disabled inputs) with a proper flex block with a prefix and suffix element;
  * Improvement: Checkout field blocks use less opinionated HTML tags (div, instead of p) to maintain semantic value and escape the default spacing CSS rules applied to paragraphs;
* Other
  * Fixed: Negative values on sign ups by country widget;
  * Fixed: Remove the email error message on sign up validation;
  * Fixed: Taxable toggle on product update;
  * Fixed: Discount code migrator not bypassing validation rules;
  * Fixed: Error on site creation process passing the customer rules in the main site;
  * Fixed: Makes sure the auto-submittable script is only added after wu-checkout was loaded;
  * Added: Filter available templates on template viewer with selected products in checkout form;
  * Added: Option to add a page on main site to redirect customer in blocked sites; 
  * Added: Hide customer sites from network admin top bar;
  * Added: Created the wu_bypass_unset_current_user filter to allow developers to bypass the user unset on multiple account feature;
  * Added: Possibility to see and change on customer admin page,  the customer custom metadata set when user sign up.
  * Added: An public api to customer meta data that handles sign up form titles and types of fields;
  * Added: Memory trap to avoid memory limit fatal errors in some cases;
  * Added: Support for Jetpack plugin in mapped domains;
  * Added: Stripe Portal for customer payment manage;
  * Added: Option to add a custom redirect path in Login block;
  * Added: New image upload field layout with the stacked option;
  * Improvement: New field for company logo on settings;
  * Improvement: Block frontend site when a membership is not active;
  * Improvement: sunrise.php install step on WP Ultimo Install Wizard;
  * Improvement: better define of SUNRISE constant on wp-config.php on WP Ultimo Install Wizard;
  * Improvement: Better UX on thank you page, showing if the site is in creation process;
  * Improvement: Breaks the gigantic functions/helper.php file into lots of different small files with specific public apis, allowing us to make sure we only load what we really need in a sunrise time;
  * Improvement: Adds a sunrise meta file to control whether or not we need to run ultimo tasks when Ultimo is disabled or not present;
  * Improvement: First step in the direction of removing jQuery as a checkout form dependency given by dropping jQuery Migrate as a dependency;
* Internal
  * Internal: Replaced all development scripts, build tasks, and more with the internal development library called MPB;
  * Internal: Adds the Query Monitor panels to help debug issues while developing WP Ultimo core;
  * Internal: Adds the development sandbox toolkit that allows developers to run and listen to particular events in a isolated context. Useful for timing how long a given hook takes to run, or to trigger build tasks that rely on a real WordPress installation running Ultimo to work;
  * Internal: Updated node dependencies to their latest versions;
  * Internal: Switched Tailwind to JIT mode, to save precious KBs on the generated framework.css file;
  * Internal: Removed PHP Scoper as a composer dependency (it is now handled directly by MPB);
  * Internal: Removed unnecessary composer dependencies;
  * Internal: Updated composer dependencies to their latest versions;
  * Internal: Finally switched the composer version internally from v1 to v2;

Version 2.0.10 - Released on 2022-01-21

* Added: Workflow to automatically generate the [list of actions](https://github.com/next-press/wp-ultimo-hooks-and-filters/blob/main/wp-ultimo.md) and filters for the plugin;
* Improvement: Added extra checks on the field loops on checkout to prevent warnings;
* Improvement: Added checks to the customizer theme screen to prevent theme limitations from being bypassed;
* Improvement: cPanel integration adds alternative domain options to cPanel as sub-domains on site creation;
* Experiment: Added the SSO lax mode to deal with new browser restrictions;
* Fixed: Free memberships correctly being set as Lifetime, by default;
* Fixed: Product user roles not being applied to newly created sites;
* Fixed: User roles not being updated on up/downgrade;
* Fixed: User role restrictions being applied to editable_roles();
* Fixed: Incompatibility between Blocksy customizer and mapped domains on WP Ultimo;
* Fixed: Incompatibility between Brizy and mapped domains on WP Ultimo;
* Fixed: Capability names not matching with Support Agents options;
* Fixed: Broadcast notices not appearing on sub-site admin panels;
* Fixed: Discount Code use count not being increased if the cart total goes down to 0;
* Fixed: Conflict with Fluent Forms - where Ultimo broke the FF form edit UI;
* Fixed: Error migrating broadcast messages from v1 to v2;
* Fixed: Search and Replace not working on post titles in some cases;
* Fixed: Multiple Accounts replacing billing address with fake version even when it's not necessary;
* Fixed: Send customer address to Stripe;

Version 2.0.9 - Released on 2021-12-29

* Added: Hook wu_checkout_after_process_order added - required by the new AffiliateWP Integration;
* Added: Filters for class-current - wu_current_site_get_manage_url, wu_current_set_site, wu_current_set_customer. Useful for integrations and later front-end management functionality;
* Added: Template Switching capabilities;
* Improvement: cPanel integration now adds sub-domains when alternative domain names are offered on registration;
* Fixed: Selectizer templates not being loaded for Support Agents;

Version 2.0.8 - Released on 2021-12-21

* Added: Templates can be pre-selected using the URL format: /register/template/template_site_name_here;
* Added: Filter to change the /template/ portion of the pre-selected site template URL - "wu_template_selection_rewrite_rule_slug";
* Added: Adds the sv_SE and it_IT translations - thanks Annika Sjöberg and Edoardo Guzzi;
* Improvement: Updated the legacy template selection layout to use flex-box over older CSS rules;
* Improvement: Added a "cols" parameter to the wu_templates_list shortcode - with a default value of 3;
* Improvement: Caching results of plugin permissions on the same request to improve performance;
* Fixed: Dropdown and other elements of the template previewer page not working as expected;
* Fixed: Lazy loads the events default payloads via a callable - preventing errors during installation;
* Fixed: Changed the h1 tag on the legacy template selection layout to an h2 for SEO reasons;
* Fixed: Shortcode wu_pricing_table buttons now correctly select plans on the checkout form;
* Fixed: Order Summary containing some untranslatable strings - they are now part of the .pot file;
* Fixed: Product duplication not copying the limitations and other meta info;
* Fixed: Refactored the algo that decides if an install needs to run the migrator or not;

Version 2.0.7 - Released on 2021-12-14

* Added: Support widget added to the migrator error screen so customers can send the necessary info for the support team;
* Added: Domain hosting integrations are now handled and activated by the migrator automatically;
* Improvement: Condensed the Migrator checks into a single step so we can make sure transaction rollbacks are not affecting the results;
* Improvement: Clear domain stage logs when a domain name is deleted;
* Improvement: Added the skip_validation option to the wu_create_payment function - which is required for the migrator to work properly;
* Improvement: Adds dumb Mercator file and a Mercator load statement to the v2 sunrise file to increase compatibility with WP Ultimo after a rollback is performed;
* Fixed: Migrator now successfully migrates the gateway info for memberships;
* Fixed: The webhook listener endpoint for v1 now have dedicated logic to treat webhooks before handing it over to the new endpoint;
* Fixed: Stripe Checkout treating all payments as an upgrade, including the initial one;
* Fixed: DNS propagation check failing due to Cloudflare breaking the list of DNS entries regardless of Cloudflare being active;
* Fixed: Cloudflare DNS injection is only loaded when Cloudflare is active;

Version 2.0.6 - Released on 2021-12-07

* Added: Option to "emulate" post types to avoid having unnecessary plugins active on the main site;
* Improvement: Re-add deprecated Mercator hooks for backwards compatibility: mercator.mapping.created, mercator.mapping.updated, mercator.mapping.deleted;
* Fixed: Removed the 100 site template limitation on the [wu_templates_list] shortcode and the Template Selection field;
* Fixed: Selecting a template from the [wu_templates_list] starts the registration with the template pre-selected correctly;
* Fixed: cPanel Integration's step to check for a valid connection return success even when an "Access Denied" error had occurred;
* Fixed: Changing the field slug on the Checkout Form editor was creating a new field instead of modifying the existing one;
* Fixed: Added support for Elementor's render_widget ajax calls to prevent errors when saving pages that contain WP Ultimo elements;
* Fixed: Edge-case where some users were not able to install add-on after opening the "More Info" window;
* Fixed: Elementor editor not loading in some edge-case scenarios when Multiple Accounts was activated;
* Fixed: Legacy product pricing table behaving exactly like the pricing table of the previous version;
* Fixed: The main site was being marked as a site template and showing up on template selection fields - this fix only applies to newly migrated networks but added an extra check to prevent the main site from being displayed as a template option;
* Fixed: Hard-coded reference to 300 sites on the Legacy pricing table template;

Version 2.0.5 - Released on 2021-12-02

* Added: A new class Unsupported that performs security checks when v2 is first activated on a v1 network that has v1 add-ons active;
* Added: Initial version of the file with the elements public apis;
* Added: Send www. version alongside naked domain to Cloudways when new domains are added;
* Improvement: Wraps "wu_core_update" on a try/catch statement;
* Improvement: Strings used on price descriptions (day, week, month, year) are now translatable;
* Fixed: Fixed template id validation rules to prevent errors;
* Fixed: Limitation merging between plans and packages behaving as expected again;
* Fixed: Filter "wu_domain_has_correct_dns" returning the wrong base value;
* Fixed: Re-adds shortcode registration for customer-owned sites when the context requires it - such as the upgrade form;
* Fixed: Replaced the generic "Object removed successfully" with a contextualized message;
* Fixed: Templates not showing up despite being marked as available on plans;
* Fixed: Better logic on setting the active plan on the checkout form to avoid two plans from being selectable;
* Fixed: Adding our shortcodes to Elementor would break their editor after a initial save;
* Fixed: Prevents Ultimo elements from breaking Divi - still needs work to make sure element previews display correctly inside the visual editor;

Version 2.0.4 - Released on 2021-11-29

* Added: Link to resend verification email on the "Thank You" page;
* Added: Option to save checkout fields as user meta;
* Added: Option to restrict SSO calls to login pages only - on Settings → Domain Mapping;
* Added: Option to disable the Screenshot Generator on Settings → Sites;
* Added: Option to force synchronous site publication on Settings → Login and Registration;
* Improvement: General clean-up to the checkout form editor fields/steps options;
* Improvement: Performance impact of Theme Limits class greatly reduced;
* Improvement: Fetch Cloudflare DNS entries to comparison table when checking for DNS propagation;
* Improvement: Move SSO ajax calls to light ajax for 50%+ performance gains on those calls;
* Improvement: Add an option to disable the "Hover to Zoom" feature on Settings → Other Options;
* Improvement: Load block editor fields for WP Ultimo blocks with default values pre-loaded;
* Improvement: Display message when new products are created, mentioning that they need to be manually added to forms;
* Improvement: Display message when new site templates are created, mentioning that they need to be manually added to forms;
* Improvement: Better cPanel and Cloudflare integration descriptions, to make their purpose clearer;
* Improvement: Add a warning when the sunrise.php is still being loaded, even when WP Ultimo is no longer active;
* Improvement: The template selection and pricing table fields automatically submit the form when they are the only relevant fields of a checkout step;
* Improvement: Option to skip plan selection if value is pre-loaded via the URL;
* Improvement: Prevent Oxygen builder from removing default hooks - used to load styles - on the Template Previewer page;
* Improvement: Enforce validations rules for template selection and products, making these fields mandatory;
* Fixed: Confirmation email not being sent when email verification was enabled;
* Fixed: Auto-generate options for site_url, site_title, and username not working;
* Fixed: JavaScript incompatibility with FluentCRM, UIPress, and other JS-heavy plugins;
* Fixed: Cart validations for price variations passing in situations where errors should be displayed;
* Fixed: Broadcast list table breaking when products attached to a Broadcast gets delete;
* Fixed: Replaced deprecated wp_no_robots with wp_robots_no_robots, if available;
* Fixed: "Maintenance Mode Active" top-bar warning appearing on the front-end even when maintenance mode was disabled;
* Fixed: System Info, Account, and Job Queue page links being added to the footer before the installation was complete;
* Fixed: Manage Sites page search input not working;
* Fixed: Only register WP Ultimo blocks and shortcodes on sites that are not customer-owned;
* Fixed: Fatal error when duplicating site templates or publishing pending sites on certain scenarios;
* Fixed: cPanel integration not working when the port constant was omitted;
* Fixed: Removed unnecessary mock implementation of get_current_screen() from the signup-main template;
* Fixed: Domain Mapping element redirecting to /wp-admin regardless of original location after adding/removing a domain;
* Fixed: Auto-increasing discount codes "uses" count when payments that used those discount codes are received not working;
* Fixed: Unable to bulk delete, activate, and deactivate discount codes;
* Fixed: "Use this template" button on the template previewer communicates selection back to the checkout;
* Fixed: Editing the custom login page was not possible with any page builder as it redirected back to /wp-admin;
* Fixed: Fatal error when trying to locate the FpdfTpl class in certain environments, specially shared hosting;
* Fixed: Adjusted the layout to better fit the legacy template page;
* Fixed: Check for Elementor file manager instance before trying to call the clear_cache method;
* Fixed: Adding classes and an ID to a checkout form step not working;
* Fixed: Add and remove note forms not working;

Version 2.0.3 - Released on 2021-11-23

* Improvement: Remove the subdirectory/subdomain tab of the new site form depending on the install type;
* Fixed: "Install User Switching" form not available;
* Fixed: WooCommerce incompatibility with multiple accounts on login;
* Fixed: DNS checking for domains not working, keeping domains stuck on the checking dns status;

Version 2.0.2 - Released on 2021-11-22

* Fixed: "Unauthorized" error when trying to install add-ons;

Version 2.0.1 - Released on 2021-11-22

* Fixed: Trying to activate a hosting provider integration causing timeout/blank screen;
* Fixed: Selecting a new plan on checkout was not updating template list;
* Fixed: WP Ultimo and its add-ons not appearing on the main site's plugins list;

Version 2.0.0 - Released on 2021-11-21 (WP Ultimo's 5-year anniversary)
