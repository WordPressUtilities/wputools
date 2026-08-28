#!/bin/bash


###################################
## Initial checks
###################################

if [[ -f "wp-config.php" || -f "../wp-config.php" ]]; then
    echo "This script should not be run on an installed WordPress site."
    return;
fi;

###################################
## Tools
###################################

function maintainer_update_wp_cleanup {
    git add .;
    git stash;
    git stash clear;
    git checkout "$_main_branch";
    git pull;
}

###################################
## Main vars
###################################

# Main branch is "main" or "master" depending on the project
git rev-parse --verify --quiet main >/dev/null && _main_branch="main" || _main_branch="master"

# Get current version from wp-includes/version.php
_current_version=$(grep "\$wp_version =" wp-includes/version.php | awk -F"'" '{print $2}')

if [ -z "$_current_version" ]; then
    echo "No WordPress install found here."
    return;
fi

_wp_lang=$(grep "\$wp_local_package =" wp-includes/version.php | awk -F"'" '{print $2}')
if [ -z "$_wp_lang" ]; then
    _wp_lang="en_US"
fi

###################################
## WordPress Update Script
###################################

# Get latest patch of the current branch (e.g. 6.8.x) from WordPress.org
_wp_branch=$(echo "$_current_version" | cut -d. -f1,2 | sed 's/\./\\./')
_latest_version=$(curl -s https://api.wordpress.org/core/stable-check/1.0/ | tr ',' '\n' | grep -o "\"$_wp_branch[0-9.]*\"" | tr -d '"' | sort -V | tail -1)

if [ -z "$_latest_version" ]; then
    echo "Could not fetch latest version from WordPress.org."
    return;
fi

_branch_name="update-wp-$(date +%Y-%m-%d)-$_latest_version"

# Compare versions and update if necessary
if [ "$_current_version" != "$_latest_version" ]; then

    # Cleanup any existing changes and switch to the main branch before updating
    maintainer_update_wp_cleanup;

    # Create a branch if it doesn't already exist
    git checkout -b "$_branch_name" 2>/dev/null || git checkout "$_branch_name";
    if ! wp core download --skip-content --force --version="$_latest_version" --locale="$_wp_lang"; then
        echo "Download failed."
        maintainer_update_wp_cleanup;
        git branch -D "$_branch_name";
        return
    fi
    echo "Updating WordPress from $_current_version to $_latest_version..."
else
    echo "WordPress is already up to date ($_current_version).";
    return;
fi

###################################
## User Confirmation
###################################

# Ask the user if they want to continue
echo "You are about to push a WordPress update from $_current_version to $_latest_version on branch $_branch_name with locale $_wp_lang."
read -p "Do you want to continue? (y/n) " _continue_update
if [ "$_continue_update" != "y" ]; then
    echo "Update aborted."
    maintainer_update_wp_cleanup;
    git branch -D "$_branch_name";
    return;
fi

###################################
## Commit and Push Update
###################################

# Commit the update
git add .
git commit -m "Update WordPress from $_current_version to $_latest_version"

# Push the branch to the remote repository
echo "pushing branch $_branch_name to remote repository..."
git push origin "$_branch_name";

# Open the merge request creation page
_repo_url=$(git open --print | sed -E 's#/(-/)?tree/.*##')
open "$_repo_url/-/merge_requests/new?merge_request%5Bsource_branch%5D=$_branch_name&merge_request%5Btarget_branch%5D=$_main_branch"

# Go back to the main branch
maintainer_update_wp_cleanup;
