<h1>WP Cache Purger Settings</h1>
<?php settings_errors();?>
<form method="post" action="options.php">
<?php

settings_fields('wp_cache_purger');
do_settings_sections('wp-cache-purger');
submit_button();

?>
</form>
