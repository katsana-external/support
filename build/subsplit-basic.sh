#!/bin/sh

if [ -d .subsplit ]; then
    git subsplit update
else
    git subsplit init git@github.com:orchestral/support.git
fi

git subsplit publish --heads="master 3.0" --no-tags src/Facades:git@github.com:orchestral/support-facades.git
git subsplit publish --heads="master 3.0" --no-tags src/Providers:git@github.com:orchestral/support-providers.git
git subsplit publish --heads="master 2.1 2.2 3.0" --no-tags src/Support:git@github.com:orchestral/support-core.git
