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

get '/' do
  erb :form
end

post '/' do
  mode = params['mode'] || ENV["MODE"]
  # MODE values: 
  #  - user
  #  - lead (will use email provided to find an existing lead and will create one if not found)
  #  - lead_in_browser (treats the existing browser session as the same lead)

  body = params['message'];
  email= params['email'];
  name = params['name'];
  visitor_or_lead_user_id = params["visitor_or_lead_user_id"];

  init_intercom
  from = nil
  begin
    if mode == "user" 
      # update existing user
      user = @intercom.users.create(name: name, email: email);
      from = { type: "user", id: user.id };
    elsif mode == "lead"
      # create lead if it doesn't exist
      # if multiple leads exist, just pick first one
      existing_leads = @intercom.contacts.find_all(email: email)
      if existing_leads.count == 0
        lead = @intercom.leads.create(name: name, email: email);
      else
        lead = existing_leads.first;
        lead.name = name;
        @intercom.contacts.save(lead)
      end
      from = { type: "contact", id: lead.id };
    elsif mode == "lead_in_browser"
      lead = nil;

      # check if lead exists, if doesn't, it means they are a visitor and we need to convert them to a lead
      begin
        lead = @intercom.contacts.find(user_id: visitor_or_lead_user_id)
      rescue Intercom::IntercomError => exception
        if exception.http_code == 404
          visitor = @intercom.visitors.find(user_id: visitor_or_lead_user_id)
          @intercom.visitors.convert(visitor)
          lead = visitor
        else           
          @error = "Could not verify if we need to convert visitor to lead. [#{exception.http_code}] #{exception}"
        end
      end
      unless lead.nil?
        lead.name = name
        lead.email = email
        @intercom.contacts.save(lead)
        from = { type: "contact", id: lead.id }
      end 
    end
    puts from
    unless from.nil?
      @intercom.messages.create({
        message_type: "inapp",
        body: body,
        from: from
      });
      @msg = "Message sent!"
    end
  rescue Exception => exception
   @error = "Unknown error: <pre>#{exception}</pre>";
  end

  erb :form
end
