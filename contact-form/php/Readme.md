# Sample contact form code in PHP

## General guidelines when using the Intercom API with PHP
- https://github.com/thewheat/intercom-testing-api/tree/master/php

## Install dependencies
`composer install`

## Modify form.php file to include configuration settings
$APP_ID="Your Intercom APP ID"
TOKEN="Your access token (standard access token needed for listing leads via email for mode=lead) https://developers.intercom.com/reference#personal-access-tokens-1"
MODE=lead_in_browser # Possible values user,lead,lead_in_browser
```

## Run app
- Either move form.php (and dependencies) into a local web directory and run from there
- Or start a local server with PHP: `php -S localhost:8080`