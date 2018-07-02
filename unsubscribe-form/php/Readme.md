# Sample unsubscribe page in PHP

![](/unsubscribe-form/php/screenshot.png)

## General guidelines when using the Intercom API with PHP
- https://github.com/thewheat/intercom-testing-api/tree/master/php

## Install dependencies
`composer install`

## Modify index.php to include configuration settings
```
$APP_ID="Your Intercom APP ID"
$TOKEN="Your access token (standard access token is sufficient) https://developers.intercom.com/reference#personal-access-tokens-1"
```

## Modify index.php to your app logic

### Way to identify user
```
"user_id" => $user_id
```

- Current implementation uses `user_id` to identify user https://docs.intercom.com/faqs-and-troubleshooting/your-users-and-leads-data-in-intercom/what-is-user-id
- If you wish to use `email`, change code to look up record by email instead

### Retrieving identifier of user
```
$user_id = "USER_ID_OF_CURRENTLY_LOGGED_IN_USER";
```

- Should be replaced with function in your app to retrieve the user_id of the record (e.g. `getCurrentUserID()`)

### Modify newsletter attributes and HTML fields
```
"newsletter1" => (@$_POST["newsletter1"] == "on"),
"newsletter2" => (@$_POST["newsletter2"] == "on")
```

```
<div><label><input type="checkbox" name="newsletter1" <?php echo ($user->custom_attributes->newsletter1 ? "checked" : "") ?> >Newsletter one</label></div>
<div><label><input type="checkbox" name="newsletter2" <?php echo ($user->custom_attributes->newsletter2 ? "checked" : "") ?> >Newsletter two</label></div>
```

- These should be changed to attributes in your Intercom app and text of actual names of newsletters in your app


## Run app
- Either move index.php (and dependencies) into a local web directory and run from there
- Or start a local server with PHP: `php -S localhost:8080`