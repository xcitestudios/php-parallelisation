#!/bin/bash

git clone https://github.com/xcitestudios/php-parallelisation.git
cd php-parallelisation
composer install
phing docs
rm -rf ../docs
mv docs ../
cd ../
rm -rf php-parallelisation
