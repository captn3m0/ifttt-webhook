ifttt-webhook
=============

A webhook middleware for the ifttt.com service

#Official IFTTT Webhook: Maker Channel
There is now an official channel for IFTTT that supports webhooks (as actions and triggers). You can find it here: [Maker Channel](https://ifttt.com/maker).

#How To Use
1. Change your ifttt.com wordpress server to <http://ifttt.captnemo.in>.
2. You can use any username/password combination you want. ifttt will accept the authentication irrespective of what details you enter here. These details will be passed along by the webhook as well, so that you may use these as your authentication medium, perhaps.
3. Create a recipe in ifttt which would post to your "wordpress channel". In the "Tags" field, use the webhook url that you want to use.

![Connecting to ifttt-webhook](http://i.imgur.com/RA0Jb.png "You can type in any username/password you want")

Any username/password combination will be accepted, and passed through to the webhook url. A blank password is considered valid, but ifttt invalidates a blank username.

![Screenshot of a channel](http://i.imgur.com/kPpufmZ.png "Sample Channel for use as a webhook")

Make sure that the url you specify accepts POST requests. (Use [requestbin][rb] for debugging.) The url is only picked up from the description field, and all other fields are passed through to the webhook url. The post status should ideally be set to "Publish Immediately", but anything else should work as well.

#How It Works
ifttt uses wordpress-xmlrpc to communicate with the wordpress blog. We present a fake-xmlrpc interface on the webadress, which causes ifttt to be fooled into thinking of this as a genuine wordpress blog. The only action that ifttt allows for wordpress is posting, which is used for powering webhooks. All the other fields (title, categories) along with the username/password credentials are passed along by the webhook. Do not use the "Create a photo post" action for wordpress, as ifttt manually adds a `<img>` tag in the description pointing to what url you pass. Its better to pass the url in clear instead (using body field).

#Why
There has been a lot of [call](http://blog.jazzychad.net/2012/08/05/ifttt-needs-webhooks-stat.html) for a ifttt-webhook. I had asked about it pretty early on, but ifttt has yet to create such a channel. It was fun to build and will allow me to hookup ifttt with things like [partychat][pc], [github][gh] and many other awesome services for which ifttt is yet to build a channel. You can build a postmarkapp.com like email-to-webhook service using ifttt alone. Wordpress seems to be the only channel on ifttt that supports custom domains, and hence can be used as a middleware. The ifttt-webhook also propogates errors on connecting to the webhook back to ifttt. This means that an Internal Server Error will be recognized as an error by ifttt, and reported as such. You won't be getting any debug information from this side (ifttt doesn't show that in logs), so debug on your webhook side by proper logging.

#Payload
The following information is passed along by the webhook in the raw body of the post request in json encoded format.

```json
    {
  "user" : "username specified in ifttt",
  "password" : "password specified in ifttt",
  "title" : "title generated for the recipe in ifttt",
  "categories" : ["array","of","categories","passed"]
    }
```

To get the data from the POST request, you can use any of the following:

```php
    $data = json_decode(file_get_contents('php://input')); //php
    data = JSON.parse(request.body.read) #ruby-sinatra
```
#Licence
Licenced under GPL. Some portions of the code are from wordpress itself. You should probably host this on your own server, instead of using `ifttt.captnemoin`. I recommend using [Heroku](http://heroku.com) for excellent php hosting. ([Heroku does supports PHP](http://stackoverflow.com/questions/13334052/does-heroku-support-php))

#Custom Use
Just clone the git repo to some place, and use that as the wordpress installation location in ifttt.com channel settings.

#Changelog
- Shifted URL from Tags field to Description(Body) field. (Oct 26 2013)
- Shifted `ifttt.captnemo.in` to Heroku (Oct 7 2013)

[pc]: http://partychat-hooks.appspot.com/ "Partychat Hooks"
[gh]: https://help.github.com/articles/post-receive-hooks/ "Github Post receive hooks"
[rb]: http://requestb.in/