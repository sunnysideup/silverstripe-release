#!/bin/bash

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
