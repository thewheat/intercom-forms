require 'sinatra'
require 'intercom'
require 'dotenv'

Dotenv.load

DEBUG = ENV["DEBUG"] || nil

set :show_exceptions, true

def init_intercom
  if @intercom.nil? then
    @intercom = Intercom::Client.new(token: ENV["TOKEN"])
  end
end

def init_variables
  @msg = "";
  @error = "";
end

def find_user
  @user = @intercom.users.find({"user_id" => @current_user[:user_id]})
end

def get_current_logged_in_user
  @current_user = {user_id: "13"}
end

def process_update
  get_current_logged_in_user
  init_intercom
end

def unsubcribe_page
  init_intercom
  get_current_logged_in_user
  find_user
  erb :form  
end
get '/update_subscriptions' do
  unsubcribe_page
end
get '/unsubscribe_all' do
  unsubcribe_page
end
get '/optin' do
  unsubcribe_page
end
get '/' do
  unsubcribe_page
end


post '/update_subscriptions' do
  process_update
  @user = @intercom.users.create(user_id: @current_user[:user_id], custom_attributes: {
      "newsletter1" => (params["newsletter1"] == "on"),
      "newsletter2" => (params["newsletter2"] == "on")
    });
  @msg = "Subscription updated!";
  erb :form
end

post '/unsubscribe_all' do
  process_update
  @user = @intercom.users.create(user_id: @current_user[:user_id], unsubscribed_from_emails: true);
  @msg = "Unsubscribed from emails!";
  erb :form
end

post '/optin' do
  process_update
  @user = @intercom.users.create(user_id: @current_user[:user_id], unsubscribed_from_emails: false);
  @msg = "Resubscribed!";
  erb :form
end

