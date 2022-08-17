<h1>Social Wall Settings</h1>
<?php settings_errors();?>
<form method="post" action="options.php">
<?php

settings_fields('wp_social_wall');
do_settings_sections('social-wall');
submit_button();

?>
</form>
