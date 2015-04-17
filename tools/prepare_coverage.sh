#!/bin/bash

composer self-update
composer require codeclimate/php-test-reporter --dev
composer install
