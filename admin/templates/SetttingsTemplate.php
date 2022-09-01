<h1>Social Wall Settings</h1>
<?php settings_errors();?>
<form method="post" action="options.php">
<?php

settings_fields('wp_social_wall');
do_settings_sections('wp-social-wall');
submit_button();

do_action('wp_social_wall_render_token_information');

?>
</form>

<script>
   (() => {
    const twitterLoginButton = document.getElementById('twitter-login-button');
    const apiToken = document.getElementById('api-token');

    twitterLoginButton.addEventListener('click', (event) => {
      event.preventDefault();

      const { token } = apiToken.dataset;

      if (!token) {
        return;
      }

      fetch('https://api.wp-social-wall.feelgoodtechnology.nl/twitter/register', {
        method: 'POST',
        mode: 'cors',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': token
        }
      }).then(async (response) => {
        const payload = await response.json();

        location.href = payload.redirectUrl;
      });
    });
   })();
</script>
