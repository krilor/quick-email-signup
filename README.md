# Quick Email Signup
The simplest possible implementation of a Wordpress user signup form only having an email field. I guess the use case for this is a simple, super-low-barrier call to action for Wordpress-based SaaS signup.

I simple terms, all this plugin does is:

1. Give you a shortcode,`[quick-email-signup]`,that you can use on any page to render a signup form with one field.
2. When a user inputs an email, the plugin will fire a ajax request to validate the email, and if it works, create the user.
3. When the user is created, it fires an action `qe_signup_user_created`, which can be used to do whatever you want with the new user, like sending a `wp_mail()`.
4. If a user exists, then a separate action (`qe_signup_user_exists`) and filter (`qe_signup_user_exists_message`) can be use. Intended for handling [Passwordless Auth](https://bitbucket.org/cozmoslabs/passwordless-auth). This means that the form can also be used as a _login_ form.

## Other info

* Default user role is `subscriber`. Can be filtered using `qe_signup_default_role` filter
* Nickname is set to email name. I.e. for `test@email.com`, nickname will be `test`. Can be filtered using `qe_signup_nickname`.
* Password is generated as 12 characters with special characters
* username = email
* No styling added. Form is located in `div.quick-email-signup`.
* No settings provided. Use filters and actions.
* Success and error messages are displayed to user.
* WP nonce used to combat bots.

## Any questions?

Raise a ticket :)
