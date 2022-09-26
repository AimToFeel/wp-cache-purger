<h1>WP Social Wall Settings</h1>
<?php settings_errors();?>
<form method="post" action="options.php">
<?php

settings_fields('wp_social_wall');
do_settings_sections('wp-social-wall');
submit_button();

do_action('wp_social_wall_render_token_information');

?>
</form>
