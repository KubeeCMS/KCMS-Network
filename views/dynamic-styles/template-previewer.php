<?php
/**
 * Dynamic styles for the template previewer.
 *
 * @since 2.0.0
 */
?>
body #switcher {
  background-color: #<?php echo $bg_color->getHex(); ?>;
	border-bottom: 5px solid <?php echo $bg_color->isDark() ? '#f9f9f9' : '#333'; ?>;
}

#template_selector {
	color: <?php echo $bg_color->isDark() ? '#dfdfdf' : '#555'; ?>;
}

.responsive a {
  color: <?php echo $bg_color->isDark() ? '#fff' : '#444'; ?>
}

.responsive a.active, .responsive a:hover {
	color: <?php echo $bg_color->isDark() ? '#fff' : '#444'; ?>
}

.select-template a, .mobile-selector a {
  background-color: #<?php echo $button_bg_color->getHex(); ?>;
  color: <?php echo $button_bg_color->isDark() ? '#fff' : '#444'; ?>;
}
