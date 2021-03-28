Symfony Demo Application with Bref 🚀
=====================================

The "Symfony Demo Application" is a reference application created to show how
to develop applications following the [Symfony Best Practices][1].

This is a slightly modified version meant to showcase how to make Symfony run 
on AWS Lambda with [Bref](https://bref.sh).

Requirements
------------

  * PHP 7.2.9 or higher;
  * PDO-PGSQL PHP extension enabled;
  * docker compose;
  * and the [usual Symfony application requirements][2].

Usage
-----

If you have[installed Symfony][4] binary, run these commands:

```bash
$ cd my_project/
$ docker-compose up -d
$ symfony console doctrine:migration:migrate --no-interaction
$ symfony console doctrine:fixtures:load --no-interaction
$ symfony serve
```

Then access the application in your browser at the given URL (<https://localhost:8000> by default).

If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`
to use the built-in PHP web server or [configure a web server][3] like Nginx or
Apache to run the application. Also use `php bin/console` instead of `symfony console`.

Tests
-----

Execute this command to run tests:

```bash
$ cd my_project/
$ ./bin/phpunit
```

[1]: https://symfony.com/doc/current/best_practices.html
[2]: https://symfony.com/doc/current/reference/requirements.html
[3]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[4]: https://symfony.com/download
