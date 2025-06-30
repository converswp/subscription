#!/bin/bash

# Create release directory
mkdir -p release/subscription

# Copy all files except development files
cp -r assets builds includes src templates composer.json index.php readme.txt subscription.php release/subscription/

# Change to release directory
cd release/subscription

# Run composer install without dev dependencies
composer install --no-dev --optimize-autoloader

# Run yarn if package.json exists
if [ -f "package.json" ]; then
    yarn install --production
fi

# Remove development files
rm -rf .git/
rm -f .editorconfig
rm -f .gitignore
rm -f phpcs.xml
rm -f README.md
rm -f composer.lock
rm -f yarn.lock

# Go back to root
cd ../

# Create zip file
cd release
zip -r subscription.zip subscription

# Clean up
# rm -rf subscription

echo "Release package has been created at release/subscription.zip" 