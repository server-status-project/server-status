# Server Status (Official)
## This is the official Server Status Project.
![License](https://img.shields.io/github/license/Pryx/server-status.svg) ![Current release](https://img.shields.io/badge/version-2-blue) 
![Beta-Build](https://img.shields.io/badge/latest_beta-Developmet_Beta_7-black)
![Stability](https://img.shields.io/badge/master_stability-Stable-red)
![Build](https://img.shields.io/badge/build-success-green)
<!--
## What does **contributor beta** mean?
It means the beta that is heavily unstable that is meant for contributors to make changes and use as a recovery point.
### Other Beta Types
#### Development Beta
##### This beta has some bugs that are noticeable and is sometimes unstable. Best for new contributors
#### Public Beta
##### This beta has some bugs that are not really noticeable and mostly exist as bugs. Best for trying the new features before updating!
-->
Very simple server status page written in PHP that can run on **PHP 5.5+** - even on **shared webhosting** even without shell access. Because why waste your money on another server (or host on a server that you might want to do maintenance on), when you can use cheap webhosting? And as a cherry on top - it works even without javascript!

## How do I install this thing?
Simply put the files on your server and access it from your browser. There will be a simple install dialog waiting for you.
If you prefer you can install manually by filling the info in config.php.template and renaming it to config.php.
As this **does not** run installation scripts you need to head to your install directory and run create-server-config.php and then delete it.
If you don't want to allow php to access your files or you have permission issues, use the following instructions.
### FOR IIS:
// Rename IISWebConfig to web.config
### FOR Apache and Nginx
// Rename ApacheHtaccess to .htaccess


You can find more info on [our wiki page](https://github.com/Pryx/server-status/wiki)

## Contributing
Anyone is welcome to make pull request with new features or security patches / bug fixes.

You may create a pull request anytime or you can join our discord here(https://discord.gg/Wgxnxz4)

### Translations
Any help with translations is much welcome! You can join us at https://poeditor.com/join/project/37SpmJtyOm. You can even add your own language. Just let me know when you're done and I'm going to include the language in the next release.

[List of contributors](https://github.com/Pryx/server-status/wiki/contributors)

### Does it actually run somewhere?
Yes it does! 
https://status.trucksbook.eu/ (Trucksbook)
https://status.theskyfallen.com (The Skyfallen Productions Company)
https://status.ecidsf.com/ (ECIDSF)
https://status.otuts.eu/ (OTUTS)
And many more!

## FAQ

### My Translations are not working. What to do?
1. Open your shell
2. Type this command 'sudo nano /etc/locale.gen'
3. Uncomment all the languages you want.
4. Save with 'Ctrl+X'
5. Run 'sudo locale-gen'
6. Restart apache 'sudo service apache2 restart'
7. Enjoy!
### Do you have a demo page?
Yes we have! Head over to https://demo.status.sajdl.com/admin and try the admin interface for yourself.
Login details:
```
email: sysadmin@example.com
password: Ss123456
```
Please note that changes are reverted every hour.

### I noticed there is a new release. How do I update?
Updating server status is fairly straightforward. Download your config.php from the server. Delete all files. Upload the new release with config.php you downloaded earlier. You need to manually run install scripts. For that head to your domain and run create-server-config.php deleting it afterwards.
If you don't want to allow php to access your files or you have permission issues, use the following instructions.
#### FOR IIS:
// Rename IISWebConfig to web.config
#### FOR Apache and Nginx
// Rename ApacheHtaccess to .htaccess

### Is there any way to do this automatically?
We are working on it but it is not yet included. Stay tuned!

### Can I somehow pull status info from Server status programatically?
Yes you can! As of [9f7e15f](https://github.com/Pryx/server-status/commit/9f7e15fcd1d900108cbb0b3cad4bdc5ecf8b741b) we added API to pull status data... And more APIs are coming! Results are encoded in JSON format which should be pretty easy to use in any common programming language.

### Why does this project exist?
It was written as a school project where we had to create a website. I went with this because I found the lack of good looking, easy to install and use status page rather weird. Therefore my goal (as stated above) was to create a simplistic status page that could run almost anywhere. The code is not the nicest or cleanest and it definitely doesn't have that many features, but hey, it works!

### Feature Request
You can write an issue and I will try to take a look when I get some time *OR* you can actually make a fork as the code it GNU licensed. Pull requests are most welcome!

___

If you like this project, buy us a coffee!

<a href="https://www.buymeacoffee.com/Pryx"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" target="_blank"></a>
