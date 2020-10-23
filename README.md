# SHERPA Knowledge Base

## Requirements

Please note that this might not be the most current information and codebase files could prove to be the most reliable source of information. These are the versions that are currently being used for development and production environments:

* PHP version 7.3 (7.4 should also work just fine)
* Laravel version 8
* Node.js version 14 (used in development for building static assets)
* MySQL version 8 (5.7+ should also be just fine)
* Apache web server (Nginx is also suitable, please check Laravel documentation for configuration)

## Installation

Please read the [Laravel Documentation](https://laravel.com/docs/8.x) for information about server requirements and getting the framework running.

Please check the **composer.json** for Laravel version requirement. the version being used might not be the latest available release of the framework.

Copy .env.example file to .env and fill in all the necessary configurations. Environment should be configured for production with initial portion of configurations declaring that and disabling debugging. This could be used an a basis:

```
APP_NAME="SHERPA Knowledge Base"
APP_ENV=production
APP_KEY=<SOME-UNIQUE-KEY>
APP_DEBUG=false
APP_URL=https://<SOME_ADDRESS>
```

In addition to that you will need to configure the database connection, mailer, and reCAPTCHA. Other configurations should be suitable and redis is not being used, at least for now.

Make sure that the database has been configured properly before running database migrations. Go to the application home catalog and run these commands in order to setup the application (make sure you use the code from **production** branch outside of development):

```
composer install --no-dev
php artisan key:generate
php artisan migrate
php artisan db:seed
```

This should get the packages installed, generate new secret key and update the .env file, run all the database migrations and create all the required tables, fill the database with some of the data presets (categories, roles and some other data).

It mght also be a good idea to [cache configuration](https://laravel.com/docs/8.x/configuration#configuration-caching) to improve performance. **Please note that caches would need to be reset after updates!**

Once that is done, you would need to create an account and assign it an administrator role. After that all the user management could be done through the user management UI. That would include creating new user account and assigning roles. This command should get the job done (please replace the arguments with correct values):

```
php artisan auth:create-admin {name} {email} {password}
```

Log in to the application and create any additional accounts that are needed. One additional step would be setting up a scheduler according to the [documentation](https://laravel.com/docs/8.x/scheduling#starting-the-scheduler). Running it every minute might not be needed as the only job that is currently present is the daily sending of email to Language Experts.

## Development

Follow the installation instructions without adding the **--no-dev** to composer command. Make sure that Node.js runtime with NPM packkage manager is present and run `npm install`. You will be able to run all the commands that are outlined in **package.json** file as soon as the dependencies are installed. The ones that make most sense are `npm run watch` and `npm run prod`. The first one will watch for changes and rebuild static assets during development and the second one will create a production build.

Development is done on any branch except for **production**, which is a special branch that has stable code ready for use in the live environment. This branch should also include the latest build of static accets (styles, scripts and possibly some others).