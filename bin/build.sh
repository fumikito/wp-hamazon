#!/usr/bin/env bash

set -e

# Build files
composer install --no-dev --prefer-dist
npm install
npm run package
# Make Readme
echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php
# Remove files
rm -rf ./node_modules
rm -rf ./tests
rm -rf ./bin
rm -rf ./.git
rm -rf ./.gitignore
rm -rf ./.browserslistrc
rm -rf ./.editorconfig
rm -rf ./.eslintrc
rm -rf ./.travis.yml
rm -rf phpcs.ruleset.xml
rm -rf phpunit.xml.dist
rm -rf README.md

if [ $TRAVIS_TAG ]; then
    echo $TRAVIS_TAG
fi

if [ $SVN_USER ]; then
    echo "SVN_USER exists."
fi

if [ $SVN_PASS ]; then
    echo "SVN_PASS exists."
fi
