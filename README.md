Yii2 Arduino Control
============================

Realtime Web Arduino control by WebSocket. Also available Ajax control. Built on Yii2

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      servers/            contains websocket servers-handlers
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Install via Git

~~~
git clone https://github.com/CyanoFresh/yii2-arduino-control.git arduino
cd arduino
composer install
~~~

Configure the app in `config/params.php`. Then start WebSocket Server by command in console:

~~~
php yii server
~~~

**NOTES:**
- Check and edit the other files in the `config/` directory to customize your application as required.
