# Server status page
![screenshot](https://status.trucksbook.eu/img/screenshot.png)

Very simple server status page written in PHP that can run on **PHP 5.4+** - even on **shared webhosting**. Because why waste your money on another server (or host on a server that you might want to do maintenance on), when you can use cheap webhosting?

## How do I install this thing?
Simply put the files on your server and access it from your browser. There will be a simple install dialog waiting for you.

You can find more info on [our wiki page](https://github.com/Pryx/server-status/wiki)

## Contributing
Anyone is welcome to make pull request with new features or security patches / bug fixes. You can find some ideas [here](https://github.com/Pryx/server-status/labels/help%20wanted).

### Translations
Any help with translations is much welcome! You can join us at https://poeditor.com/join/project/37SpmJtyOm. You can even add your own language. Just let me know when you're done and I'm going to include the language in the next release.

[List of contributors](https://github.com/Pryx/server-status/wiki/contributors)

## FAQ

### Does it actually run somewhere?
Yes it does! This is basically debranded version of https://status.trucksbook.eu/. 

### Do you have a demo page?
Yes we have! Head over to https://demo.status.sajdl.com/admin and try the admin interface for yourself.
Login details:
```
email: sysadmin@example.com
password: Ss123456
```
Please note that changes are reverted every hour.

### Can I somehow pull status info from Server status programatically?
Yes you can! As of [9f7e15f](https://github.com/Pryx/server-status/commit/9f7e15fcd1d900108cbb0b3cad4bdc5ecf8b741b) we added API to pull status data... And more APIs are coming! Results are encoded in JSON format which should be pretty easy to use in any common programming language.

### Why does this project exist?
It was written as a school project where we had to create a website. I went with this because I found the lack of good looking, easy to install and use status page rather weird. Therefore my goal (as stated above) was to create a simplistic status page that could run almost anywhere. The code is not the nicest or cleanest and it definitely doesn't have that many features, but hey, it works!

### I want feature XY!
You can write an issue and I will try to take a look when I get some time *OR* you can actually make a fork as the code it GNU licensed. Pull requests are most welcome!

___

If you like this project, buy us a coffee!

<a href="https://www.buymeacoffee.com/Pryx"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" target="_blank"></a>
