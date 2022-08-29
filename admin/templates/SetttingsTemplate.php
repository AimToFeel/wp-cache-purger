<h1>Social Wall Settings</h1>
<?php settings_errors();?>
<form method="post" action="options.php">
<?php

settings_fields('wp_social_wall');
do_settings_sections('social-wall');
submit_button();

do_action('wp_social_wall_render_token_information');

?>
</form>


<script>
  function setFacebookAuthentication(event) {
    event.preventDefault();

    FB.getLoginStatus(function(response) {
      const tokenInput = document.querySelector('input[name="wp_social_wall_facebook_token"]');
      const userIdInput = document.querySelector('input[name="wp_social_wall_facebook_user_id"]');

      if (response.authResponse) {
        tokenInput.value = response.authResponse.accessToken;
        userIdInput.value = response.authResponse.userID;
      }
    });
  }

  window.fbAsyncInit = function() {
    FB.init({
      appId: '516765780209891',
      cookie: false,
      xfbml: true,
      version: 'v14.0'
    });

    FB.AppEvents.logPageView();
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));

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

<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v14.0&appId=5238538342939990&autoLogAppEvents=1" nonce="J0Cvd48y"></script>
