# Sample unsubscribe page in Javascript

![](/unsubscribe-form/javascript/screenshot.png)

## Modify index.html to include configuration settings
```
var APP_ID = 'Your Intercom App ID';
```

## Modify index.html to your app logic

### Way to identify user
```
window.intercomSettings= {app_id: APP_ID, user_id: USER_ID};
```

- Current implementation uses the [logged in user install](https://docs.intercom.com/install-on-your-product-or-site/quick-install/install-intercom-on-your-website-for-logged-out-visitors) and identifiers the user via `user_id` https://docs.intercom.com/faqs-and-troubleshooting/your-users-and-leads-data-in-intercom/what-is-user-id
- If you wish to use `email` change code to look up record by email instead

### Retrieving identifier of user
```
var USER_ID = 'USER_ID_OF_CURRENTLY_LOGGED_IN_USER';
```

- Should be replaced with function in your app to retrieve the user_id of the record (e.g. `getCurrentUserID()`)

### Modify newsletter attributes and HTML fields
```
"newsletter1": document.getElementById("newsletter1").checked,
"newsletter2": document.getElementById("newsletter2").checked
```

```
<div><label><input type="checkbox" id="newsletter1">Newsletter one</label></div>
<div><label><input type="checkbox" id="newsletter2">Newsletter two</label></div>
```

- These should be changed to attributes in your Intercom app and text of actual names of newsletters in your app


## Run app
- Open index.html in a browser