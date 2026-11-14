#!/bin/bash


###################################
## Initial checks
###################################

if [[ -f "wp-config.php" || -f "../wp-config.php" ]]; then
    bashutilities_message "This script should not be run on an installed WordPress site." 'error';
    return;
fi;

###################################
## Tools
###################################

function wputools__update_quick_cleanup {
    # Only stash+drop if there is something to discard, otherwise drop would remove an older stash
    if [ -n "$(git status --porcelain)" ]; then
        git add .;
        git stash;
        git stash drop;
    fi
    git checkout "$_main_branch" && git pull;
}

###################################
## Main vars
###################################

# Main branch is "main" or "master" depending on the project
_main_branch=$(wputools_get_main_branch);

# Get current version
_current_version=$(_WPCLICOMMAND core version);

if [ -z "$_current_version" ]; then
    bashutilities_message "No WordPress install found here." 'error';
    return;
fi

_wp_lang=$(wputools_get_wp_locale);

###################################
## WordPress Update Script
###################################

# Get latest patch of the current branch (e.g. 6.8.x) from WordPress.org
_wp_branch=$(echo "$_current_version" | cut -d. -f1,2 | sed 's/\./\\./')
_latest_version=$(curl -s https://api.wordpress.org/core/stable-check/1.0/ | tr ',' '\n' | grep -oE "\"$_wp_branch(\.[0-9]+)?\"" | tr -d '"' | sort -V | tail -1)

if [ -z "$_latest_version" ]; then
    bashutilities_message "Could not fetch latest version from WordPress.org." 'error';
    return;
fi

_branch_name="update-wp-$(date +%Y-%m-%d)-$_latest_version"

# Compare versions and update if necessary
if [ "$_current_version" != "$_latest_version" ]; then

    # Cleanup any existing changes and switch to the main branch before updating
    if ! wputools__update_quick_cleanup; then
        bashutilities_message "Could not switch to an up-to-date $_main_branch branch." 'error';
        return;
    fi

    # Create a branch if it doesn't already exist
    if ! { git checkout -b "$_branch_name" 2>/dev/null || git checkout "$_branch_name"; }; then
        bashutilities_message "Could not switch to branch $_branch_name." 'error';
        return;
    fi
    if ! _WPCLICOMMAND core download --skip-content --force --version="$_latest_version" --locale="$_wp_lang"; then
        bashutilities_message "Download failed." 'error';
        wputools__update_quick_cleanup;
        git branch -D "$_branch_name";
        return
    fi
    bashutilities_message "Updating WordPress from $_current_version to $_latest_version..." 'notice';
else
    bashutilities_message "WordPress is already up to date ($_current_version)." 'notice';
    return;
fi

###################################
## User Confirmation
###################################

# Ask the user if they want to continue
echo "You are about to push a WordPress update from $_current_version to $_latest_version on branch $_branch_name with locale $_wp_lang."
_continue_update=$(bashutilities_get_yn "Do you want to continue?" 'y');
if [ "$_continue_update" != "y" ]; then
    bashutilities_message "Update aborted." 'error';
    wputools__update_quick_cleanup;
    git branch -D "$_branch_name";
    return;
fi

###################################
## Commit and Push Update
###################################

# Commit the update
git add .
if ! git commit -m "Update WordPress from $_current_version to $_latest_version"; then
    bashutilities_message "Commit failed. Staying on branch $_branch_name." 'error';
    return;
fi

# Push the branch to the remote repository
bashutilities_message "pushing branch $_branch_name to remote repository..." 'notice';
if ! git push origin "$_branch_name"; then
    bashutilities_message "Push failed. Staying on branch $_branch_name." 'error';
    return;
fi

# Open the merge request creation page
if git open --help >/dev/null 2>&1; then
    _repo_url=$(git open --print | sed -E 's#/(-/)?tree/.*##')
    open "$_repo_url/-/merge_requests/new?merge_request%5Bsource_branch%5D=$_branch_name&merge_request%5Btarget_branch%5D=$_main_branch"
fi

# Go back to the main branch
wputools__update_quick_cleanup;
