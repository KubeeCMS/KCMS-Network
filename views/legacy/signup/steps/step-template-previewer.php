<?php
/**
 * This is the template used for the Template Previewer.
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-template-previewer.php.
 *
 * HOWEVER, on occasion WP Ultimo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NextPress
 * @package     WP_Ultimo/Views
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
} // end if;

global $current_site;

/**
 * Template Selection Bar
 *
 * @since 1.6.0
 */

$suffix = WP_Ultimo()->min;

wp_enqueue_script('wu-template-preview', wu_get_asset('wu-template-preview.js', 'js'), array('jquery'), WP_Ultimo()->version);
wp_enqueue_style('dashicons');

/** Gets the categories and Template objects */
list($categories, $templates) = WU_Site_Templates::prepare_site_templates();

/**
 * Default URL
 */
$selected_template = new WU_Site_Template($_GET['template-preview']);

/**
 * Check if this is a site template
 */
if (!$selected_template->is_template) {

	/**
	 * We need to check to see if this is a user checking out his own site
   *
	 * @since 1.7.4
	 */
	$subscription = wu_get_current_subscription();

	if (!$subscription || !in_array($_GET['template-preview'], $subscription->get_sites_ids())) {

		wp_die(__('This template is not available', 'wp-ultimo'));

	} // end if;

} // end if;

/**
 * Get the Colors to be using
 */
$bg_color         = wu_color(wu_get_setting('top-bar-bg-color'));
$button_bg_color  = wu_color(wu_get_setting('top-bar-button-bg-color'));
$button_bg_darker = wu_color($button_bg_color->darken(4));

/*
+
 * Get the Logo
 */
$logo_url = wu_get_setting('top-bar-use-logo')

? WU_Settings::get_logo('full', false, 'top-bar-logo')
: WU_Settings::get_logo();

?>
<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1">

<?php wp_head(); ?>

<style>
html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

/**
* Tetse
*/

html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
	margin: 0;
	padding: 0;
	border: 0;
	font-size: 100%;
	font: inherit;
	vertical-align: baseline;
}

article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {
	display: block;
}

body {
	line-height: 1;
    background: #333;
}

ol, ul {
	list-style: none;
}

blockquote, q {
	quotes: none;
}

blockquote:before, blockquote:after,
q:before, q:after {
	content: '';
	content: none;
}

table {
	border-collapse: collapse;
	border-spacing: 0;
}

body {
	overflow: hidden;
	font-family: 'Open Sans', sans-serif;
	font-weight: 400;
	color: #555;
    font-size: 13px;
}

a {
text-decoration: none;
color: #fff;
}

#switcher {
	height: 62px;
	padding: 10px 30px;
    background-color: #<?php echo $bg_color->getHex(); ?>;
	border-bottom: 5px solid <?php echo $bg_color->isDark() ? '#f9f9f9' : '#333'; ?>;
	z-index: 99999;
	position: fixed;
	width: 100%;
    box-sizing: border-box;
}

#theme_list {
    position: relative;
}

#template_selector {
	width: 200px;
	display: block;
	padding: 10px 9px;
	color: <?php echo $bg_color->isDark() ? '#dfdfdf' : '#555'; ?>;
    border-radius:2px;
    font-weight: 700;
    margin-top: 3px;
    background: rgba(0,0,0,.2);
}

#template_selector:hover {
    /* color: #F0F0F0; */
}

#theme_dropdown_list {
	border-radius: 10px;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	padding: 20px;
}

.center {
	margin: auto;
	width: 98%;
	/* padding: 11px 0; */
}

.center ul li {
	display: inline;
	float: left;
}

.center ul li ul {
    display: none;
    height: 250px;
    margin-left: 0;
    overflow-x: hidden;
    overflow-y: auto;
    position: static;
    width: 200px;
    z-index: -1;
}

.center ul li ul li {
	background: #2b2b2b;
	border-bottom: 1px solid #414141;
}

.center ul li ul li:hover {
    background: #414141;
}

.center ul li ul li a {
	display: block;
	padding: 10px;
	font-weight:bold;
	min-width: 198px;
	color:#DFDFDF;
}

.center ul li ul li a:hover {
    color:#F0F0F0;
}

#iframe {
	margin: 0 auto;
	display: block;
	margin-top: 62px;

    -webkit-box-shadow: 0px 10px 20px 0px rgba(0,0,0,0.4);
    -moz-box-shadow: 0px 10px 20px 0px rgba(0,0,0,0.4);
    box-shadow: 0px 10px 20px 0px rgba(0,0,0,0.4);
}

#iframe iframe {
    height: 100%;
    width: 100%;
}

.logo {
    float: left;
    margin-right: 40px;
    height: 35px;
    /* background: #fff; */
    /* margin-top: -8px; */
}

.logo a {
    display: inline-block;
    overflow: hidden;
    height: 35px;
}

.logo img {
	opacity: 1;
    max-height: 100%;
    margin-top: 2px;
}

.logo img:hover {
	opacity: 0.7;
}

.links {
    float: right;
    /* margin-top: -7px; */
    /* width: 210px; */
}

.responsive {
    float: left;
    margin-left: 14px;
    padding: 7px 0;
    margin-top: 3px;
}

.responsive a {
    opacity: 0.5;
    float: left;
    margin: 0 6px;
    color: <?php echo $bg_color->isDark() ? '#fff' : '#444'; ?>
}

.responsive a {
	width: 20px;
	height: 20px;
}

.responsive a.tabletlandscape,
.responsive a.mobilelandscape {
  -moz-transform: scaleY(-1) rotate(-90deg);
  -o-transform: scaleY(-1) rotate(-90deg);
  -webkit-transform: scaleY(-1) rotate(-90deg);
  transform: scaleY(-1) rotate(-90deg);
}

.responsive a.active, .responsive a:hover {
	opacity:1;
	color: <?php echo $bg_color->isDark() ? '#fff' : '#444'; ?>

}

.select-template a, .mobile-selector a {
    padding: 12px;
    float: left;
	border-radius: 2px;
    font-weight: bold;
    border: none;
    text-transform: uppercase;
    text-align: center;
}

.select-template a, .mobile-selector a {
    transition: linear all 100ms;
    background-color: #<?php echo $button_bg_color->getHex(); ?>;
    color: <?php echo $button_bg_color->isDark() ? '#fff' : '#444'; ?>;
    text-decoration: none;
}

.select-template a:hover, .mobile-selector a:hover {
    background-color: #1a1a1a;
    color: #FFFFFF;
}

.select-template a img {
	vertical-align: middle;
	margin-right: 5px;
	margin-top: -3px;
}

.mobile-selector {
    /* width: 100%; */
    /* height: 60px; */
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    display: none;
    z-index: 999;
    -webkit-overflow-scrolling: auto;
}

.mobile-selector a {
    /* float: none; */
    width: 100%;
    -webkit-box-shadow: 0px 7px 34px -5px rgba(0,0,0,0.75);
    -moz-box-shadow: 0px 7px 34px -5px rgba(0,0,0,0.75);
    box-shadow: 0px 7px 34px -5px rgba(0,0,0,0.75);
}

img.preview {
    display: none;
    position: absolute;
    z-index:999;
    top: 8px;
    left: 242px;
    background: #2b2b2b ;
    padding: 7px;
    border-radius: 3px;
    -webkit-box-shadow: 0px 2px 5px rgba(0,0,0,0.7);
    box-shadow: 0px 2px 5px rgba(0,0,0,0.7);
    -moz-opacity: 0;
    -webkit-opacity: 0;
    opacity: 0;
    width: 500px;
    max-width: 500px;
}

.center ul li ul li:hover img.preview {
    display: inline;
    opacity:1;
}

.center ul li ul li span {
    float: right;
    font-size: 11px;
	font-weight:bold;
    background: #21759B;
    border-radius: 2px;
    padding: 3px 8px;
    color: #ffffff;
    margin-top: -2px;
    min-width: 40px;
    text-align: center;
}

@media (min-width: 768px) and (max-width: 979px) {
	.share,
	.responsive{
		display:none;
	}

	img.preview {
		width: 360px;
	}
}

@media (max-width: 767px) {



	.share,
	.responsive{
		display:none;
	}

	img.preview {
		width: 300px;
		left: 191px;
	}

	#template_selector {
		width: 160px;
	}

	.center ul li ul li a {
		min-width: 190px;
	}

	.center ul li ul {
		width: 168px;
	}

	.logo {
		margin-right: 10px;
	}

	.select-template a {
		padding: 9px 15px 8px 8px;
	}

	.close a {
		padding: 10px 5px;
		margin-left: 6px;
	}

	.links {
		font-size: 11px;
	}
}


@media (max-width: 480px) {
    .slimScrollDiv,
	#template_selector {
		width: 100% !important;
	}

    #switcher {
        height: 96px;
    }

    #iframe {
        margin-top: 0;
        /* margin-bottom: 40px; */
        position: fixed;
        right: 0;
        bottom: 0px;
        left: 0;
        top: 96px;
        -webkit-overflow-scrolling: touch;
        overflow-y: scroll;
    }

	img.preview {
		display: none !important;
	}

	.center {
		width: 95%;
	}

	.select-template a {
		padding: 7px 4px 7px 5px;
	}

	.close a {
		padding: 10px 5px;
		margin-left: 6px;
	}

    .logo {
        text-align: center;
        width: 100%;
    }

	.links {
		/* font-size: 0; */
        display: none;
		width:60px;
	}

	.select-template a img, .close a img {
		margin-right: 0;
	}

    .mobile-selector {
        display: block;
    }

    #theme_list_selector {
        display: block;
        width: 100%;
        overflow: hidden;
    }

    .center ul li ul {
        float: none;
        width: 100%;
    }
    .center ul li {
        width: 100%;
    }
}

</style>

<script type="text/javascript">

/**
 * Prevent CORS
 */
document.domain = "<?php echo str_replace('www.', '', $current_site->domain); ?>";

// create the cookie
wuCreateCookie('wuTemplate', false);

function is_iOS() {

  window.addEventListener('touchstart', {}); // in top window


  var iDevices = [
  'iPad Simulator',
  'iPhone Simulator',
  'iPod Simulator',
  'iPad',
  'iPhone',
  'iPod'
  ];

  if (!!navigator.platform) {
    while (iDevices.length) {
      if (navigator.platform === iDevices.pop()) { return true; }
    }
  }

  return false;

} // end is IOS;

(function($) {
  $(document).ready(function() {

    /**
    * Send anti-cors message
    */
    // get reference to window inside the iframe
    var wn = document.getElementById('iframe').contentWindow;
    // postMessage arguments: data to send, target origin
    wn.postMessage('Hello to iframe from parent!', '://' + document.domain);

    /**
     * Template button, should select the template and close the window
     */
    $('#action-select, #action-select2').on('click', function() {
      wuCreateCookie('wuTemplate', $('#template-selector').val());
      window.close();
    });

    /**
     * Fix on the iPhone previews
     * @since 2.0.0
     */
    $('#iframe iframe').load(function() {
      if (is_iOS()) {
        $('#iframe iframe').contents().find('body').addClass('wu-fix-safari-preview').css({
          "position": "fixed",
          "top": 0,
          "right": 0,
          "bottom": 0,
          "left": 0,
          "overflow-y": "scroll",
          "-webkit-overflow-scrolling": "touch",
        });
      }
    });

  });
})(jQuery);
</script>

</head>
<body>

<div id="switcher">
    <div class="center">
        <div class="logo">
            <a href="<?php echo network_home_url(); ?>" target="_blank">
                <img src="<?php echo $logo_url; ?>">
            </a>
        </div>

        <ul id="theme_list_selector">
            <li id="theme_list">

                <?php if ($selected_template) : ?>
                    <a id="template_selector" href="#">
					<?php echo $selected_template->blogname; ?>
                        <span style="float: right; margin-top:  -3px" class="dashicons dashicons-arrow-down-alt2"></span>
                    </a>
                <?php else : ?>
                    <a id="template_selector" href="#">
					<?php _e('Select template...', 'wp-ultimo'); ?>
                        <span style="float: right; margin-top:  -3px" class="dashicons dashicons-arrow-down-alt2"></span>
                    </a>
                <?php endif; ?>


                <ul id="test1a">

                    <?php foreach ($templates as $template) : ?>
                        <li>
                            <a href="<?php echo WU_Site_Hooks::get_template_preview_url($template['id']); ?>">
						<?php echo $template['name']; ?>
                            </a>
                            <img alt="" class="preview" src="<?php echo $template['screenshot'][0]; ?>">
                        </li>
                    <?php endforeach; ?>

                </ul>
            </li>
        </ul>

        <?php if (wu_get_setting('top-bar-enable-resize')) : ?>
            <div class="responsive">
                <a href="#" class="desktop active dashicons-before dashicons-desktop" title="<?php _e('View Desktop Version', 'wp-ultimo'); ?>"></a>
                <a href="#" class="tabletlandscape dashicons-before dashicons-tablet" title="<?php _e('View Tablet Landscape (1024x768)', 'wp-ultimo'); ?>"></a>
                <a href="#" class="tabletportrait dashicons-before dashicons-tablet" title="<?php _e('View Tablet Portrait (768x1024)', 'wp-ultimo'); ?>"></a>
                <a href="#" class="mobilelandscape dashicons-before dashicons-smartphone" title="<?php _e('View Mobile Landscape (480x320)', 'wp-ultimo'); ?>"></a>
                <a href="#" class="mobileportrait dashicons-before dashicons-smartphone" title="<?php _e('View Mobile Portrait (320x480)', 'wp-ultimo'); ?>"></a>
            </div>
        <?php endif; ?>

    </div>

    <?php if (!isset($_GET['switching'])) : ?>
    <ul class="links">
        <?php if (isset($_GET['cs'])) : ?>
            <li class="select-template">
                <a id="action-select" href="#"><?php echo wu_get_setting('top-bar-button-text'); ?> &rarr;</a>
            </li>
        <?php else : ?>
            <li class="select-template">
                <a id="action-select-link" href="<?php echo WU_Signup()->get_register_url( network_site_url('wp-signup.php') )."?template_id=$selected_template->id"; ?>"><?php echo wu_get_setting('top-bar-button-text'); ?> &rarr;</a>
            </li>
        <?php endif; ?>
    </ul>
    <?php endif; ?>

    <input type="hidden" id="template-selector" value="<?php echo esc_attr($_GET['template-preview']); ?>" />

</div>

<?php if (!isset($_GET['switching'])) : ?>
<div class="mobile-selector">
    <?php if (isset($_GET['cs'])) : ?>
        <a id="action-select2" href="#"><?php echo wu_get_setting('top-bar-button-text'); ?> &rarr;</a>
    <?php else : ?>

        <a id="action-select-link" href="<?php echo WU_Signup()->get_register_url( network_site_url('wp-signup.php') )."?template_id=$selected_template->id"; ?>"><?php echo wu_get_setting('top-bar-button-text'); ?> &rarr;</a>

    <?php endif; ?>
</div>
<?php endif; ?>

<!-- <iframe id="iframe" src="<?php echo add_query_arg('is_wu_template_preview', '1', get_home_url($selected_template->id)); ?>" width="100%" height="100%"/> -->

</body>

</html>

<?php
// end the exhibition
exit; ?>
