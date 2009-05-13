#!/bin/sh
find ./app/libraries/javascript/ -name '*.js' | xargs -n 1 grep $*

