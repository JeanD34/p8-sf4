# To-do List

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/29619d0122614401be96c403530b3df5)](https://www.codacy.com/app/JeanD34/p8-sf4?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=JeanD34/p8-sf4&amp;utm_campaign=Badge_Grade)[![Build Status](https://travis-ci.com/JeanD34/p8-sf4.svg?branch=master)](https://travis-ci.com/JeanD34/p8-sf4)


OpenClassrooms project for "PHP / Symfony" course.

The objective is to improve an existing application build with Symfony 3.1.6 and Bootstrap 3.3.7.

## Build With

- Symfony 4.3.3
- Bootstrap 3.3.7

## Installation

1 - Clone or download the project

```https://github.com/JeanD34/p8-sf4.git```

2 - Update your database identifiers in project/.env

```DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name```

3 - Install composer -> [Composer installation doc](https://getcomposer.org/download/)

4 - Run composer.phar to install dependencies

```php bin/console composer.phar update```

5 - Create the database

```php bin/console doctrine:database:create```

6 - Create the database table/schema

```php bin/console doctrine:schema:update```

7 - Load fixtures

```php bin/console doctrine:fixtures:load```

## Usage

Login link :

```/login```

An user account is already available, use it to test the application :

```
"username" : "User",
"password" : "User340!"
```

An admin account is already available, use it to test the application :

```
"username" : "Admin",
"password" : "Admin34!"
```

An super admin account is already available, use it to test the application :

```
"username" : "SuperAdmin",
"password" : "SuperAdmin34!"
```

## Tests

1 - Update the test database identifiers in project/
phpunit.xml.dist

```<env name="DATABASE_URL" value="DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db__test_name"/>```

2 - Run tests

```php bin/phpunit```

