# PHP-CWS-Sample

A sample application utilizing EVO Snap*'s **Commerce Web Services** library, PHP-CWS-lib

For more information about Commerce Web Services, please see:
[EVO Snap* CWS Docs](https://docs.evosnap.com/commerce-web-services/cws-overview/cws-overview-what-is-commerce-web-services/) 

You will need Composer in your computer to run the example.
[Get Composer](https://getcomposer.org/doc/00-intro.md) first in case you don't have it yet.

After getting this example, you should run Composer from the example folder:

```$bash
# composer install
```

Composer will download EVO Snap* CWS library for you. Then you will need to make a copy of
"config-original.php" to "config.php", then fill the new file out with your merchant profile
information. If you do not have credentials, browse to
http://www.evosnap.com/develop-with-snap/ and request them to us.

Now you are ready to transact with Commerce Web Services!

## Configuring the Application

Copy the "config-template.php" file to "config.php" and fill in that file with the details
provided to you by your Solutions Engineer.  Once that's done you'll be able to run the
application.

## Running the Application

The application is intended to be run on the command line in order to cut out a lot of noise.

It is recommended you get a terminal emulator application that supports colored output.
"cmder" is an example of a terminal emulator that supports colored output.

The application uses Tracy's "dump()" function to dump colorized output at each step, showing
the entire objects being sent back and forth.

To run the application, just run the "runme.php" file:

```$bash
# php runme.php
```

The application will then run through the following transaction samples...

- *Getting Service Information*, useful for configuring your application directly from the
  features reported by EVO Snap*'s platform
- *Authorize & Capture*, in a combined call
- *Authorize* a transaction, without capturing it immediately
- *Capture* the previously authorized transaction
- *Undo* the Capture()d transaction
- *ReturnById* the first AuthorizeAndCapture()d transaction
- *Authorize via Token*, utilizing the auto-generated token from the original
  AuthorizeAndCapture()d transaction