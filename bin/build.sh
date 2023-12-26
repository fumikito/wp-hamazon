#!/usr/bin/env bash

set -e

# Build files
composer install --no-dev --prefer-dist
php bin/replacer.php

# NPM packages.
npm install
npm run package

# Make Readme
echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php
