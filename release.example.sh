#!/bin/bash
git show --format="%h" --no-patch >> releaselog.txt

# sspak
rm sspak.phar -rf
wget https://silverstripe.github.io/sspak/sspak.phar
chmod +x sspak.phar

# move dumps
rm release-3.sspak -rf
mv release-2.sspak release-3.sspak
mv release-1.sspak release-2.sspak
mv release.sspak release-1.sspak

# dump
php sspak.phar save . --db pre-release.sspak


# change to current dir (important)
cd "$(dirname "$0")"

# get all the latest changes from git - just so you have them. NOT APPLIED
git fetch --all

# check you are on the right branch
git branch

# pull the latest of the current branch, may need to specify the branch - e.g. git pull origin mybranch
git pull

# install composer - CHECK FOR ERRORS! - best solution: deleted vendor/ folder (rm vendor -rf) - and install from scratch
# this may also help: remove public/resources (rm public/resources -rf)
# this may also help: remove public/_resources (rm public/_resources -rf)
composer install --no-dev

# build database - check for errors
# build database - check for errors
vendor/bin/sake dev/build
vendor/bin/sake dev/build flush=all

echo '=============================================='
echo "now open the website with ?flush=all"
echo '=============================================='
echo '=============================================='
echo '=============================================='
echo '=============================================='


# MUST OPEN FRONT-END WITH ?flush=all
