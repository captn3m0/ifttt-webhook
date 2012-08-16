ifttt-webhook
=============

A webhook middleware for the ifttt.com service

#How To Use
1. Change your ifttt.com wordpress server to <http://ifttt.captnemo.in>.
2. You can use any username/password combination you want. ifttt will accept the authentication irrespective of what details you enter here. These details will be passed along by the webhook as well, so that you may use these as your authentication medium, perhaps.
3. Create a recipe in ifttt which would post to your "wordpress channel". In the "Tags" field, use the webhook url that you want to use.

![Screenshot of a channel](http://i.imgur.com/5FaU1.png "Sample Channel for use as a webhook")

#How It Works
ifttt uses wordpress-xmlrpc to communicate with the wordpress blog. We present a fake-xmlrpc interface on the webadress, which causes ifttt to be fooled into thinking of this as a genuine wordpress blog. The only action that ifttt allows for wordpress are posting, which are instead used for powering webhooks. All the other fields (title, description, categories) along with the username/password credentials are passed along by the webhook.

#Why
There has been a lot of call for a ifttt-webhook. I had asked about it pretty early on, but ifttt has yet to create such a channel. It was fun to build and will allow me to hookup ifttt with things like [partychat][pc], [github](gh) and many other awesome services for which ifttt is yet to build a channel.

#Payload
The following information is passed along by the webhook in the raw body of the post request in json encoded format.

    {
    	user: "username specified in ifttt",
    	password: "password specified in ifttt",
    	title: "title generated for the recipe in ifttt",
    	categories:['array','of','categories','passed'],
    	description:"Body of the blog post as created in ifttt recipe"
    }

To get the data from the POST request, you can use any of the following:

    $data = file_get_contents('php://input'); #php
    data = JSON.parse(request.body.read) #ruby-sinatra

#Licence
The code is licenced under GPL. Some portions of the code are from wordpress itself. You should host this on your own server, instead of using this.

#Custom Use
Just clone the git repo to some place, and use that as the wordpress installation location in ifttt.com channel settings.
