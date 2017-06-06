# Sample contact form code in Ruby

## General guidelines when using the Intercom API with Ruby
- https://github.com/thewheat/intercom-testing-api/tree/master/ruby

## Install dependencies
`bundle install`

## Create `.env` file with your configuration

```
APP_ID="Your Intercom APP ID"
TOKEN="Your access token (standard access token needed for listing leads via email for mode=lead) https://developers.intercom.com/reference#personal-access-tokens-1"
MODE=lead_in_browser # Possible values user,lead,lead_in_browser
```

## Run app
`ruby app.rb`

## Run app while development and auto reload on changes
`rerun ruby app.rb`