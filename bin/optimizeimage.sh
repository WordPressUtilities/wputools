#!/bin/bash

echo "# Optimize Image"
cd "${_SCRIPTSTARTDIR}";
function wputools__optimize_image() {
    local _IMAGE_FILE="$1"
    local RESPONSE;
    local ERROR;
    local MESSAGE;
    local COMPRESSED_URL;
    local _EXTENSION;
    local _IMAGE_FILE_TMP;
    local _IMAGE_FILE_OLD;
    local _FILE_SIZE_BEFORE;
    local _FILE_SIZE_AFTER;

    if [[ -z "$_IMAGE_FILE" ]]; then
        echo "Usage: wputools optimizeimage <_IMAGE_FILE>"
        return 0;
    fi

    if [[ ! -f "${_IMAGE_FILE}" ]]; then
        echo "Error: File '${_IMAGE_FILE}' does not exist."
        return 0;
    fi

    if [[ -z "$WPUTOOLS_TINYPNG_API_KEY" ]]; then
        echo "Error: WPUTOOLS_TINYPNG_API_KEY is not set. Please set it in your environment."
        return 0;
    fi

    _EXTENSION="${_IMAGE_FILE##*.}";
    _EXTENSION=$(echo "$_EXTENSION" | tr '[:upper:]' '[:lower:]')
    if [[ "$_EXTENSION" != "png" && "$_EXTENSION" != "jpg" && "$_EXTENSION" != "jpeg" ]]; then
        echo "Error: Unsupported image format '$_EXTENSION'. Only PNG, JPG, and JPEG are supported."
        return 0;
    fi

    # Upload image to TinyPNG
    RESPONSE=$(curl -s --user api:"$WPUTOOLS_TINYPNG_API_KEY" \
        --data-binary @"$_IMAGE_FILE" \
        --header "Content-Type: image/${_EXTENSION}" \
        https://api.tinify.com/shrink)

    # Check for error
    if echo "$RESPONSE" | grep -q '"error"'; then
        ERROR=$(echo "$RESPONSE" | grep -o '"error"[^,}]*' | sed 's/.*: *"//' | sed 's/"$//')
        MESSAGE=$(echo "$RESPONSE" | grep -o '"message"[^}]*' | sed 's/.*: *"//' | sed 's/"$//')
        echo "API error: $ERROR - $MESSAGE"
        exit 3
    fi

    # Extract output URL
    COMPRESSED_URL=$(echo "$RESPONSE" | grep -o '"url":"[^"]*' | sed 's/"url":"//')

    _IMAGE_FILE_TMP="${_IMAGE_FILE%.*}_compressed.${_EXTENSION}";
    _IMAGE_FILE_OLD="${_IMAGE_FILE}.old";

    # Download compressed image
    curl -s "$COMPRESSED_URL" -o "$_IMAGE_FILE_TMP"

    # Check if the file was created
    if [[ ! -f "$_IMAGE_FILE_TMP" ]]; then
        echo "Error: Compressed image file was not created."
        return 0;
    fi

    # Get file sizes before and after compression
    _FILE_SIZE_BEFORE=$(du -h "$_IMAGE_FILE" | cut -f1);
    _FILE_SIZE_AFTER=$(du -h "$_IMAGE_FILE_TMP" | cut -f1);

    # Backup the original image and move the compressed image to the original file name
    mv "$_IMAGE_FILE" "$_IMAGE_FILE_OLD";
    mv "$_IMAGE_FILE_TMP" "$_IMAGE_FILE";
    echo "Image compressed and saved to: $_IMAGE_FILE"
    echo "Original image backed up to: $_IMAGE_FILE_OLD"
    echo "- Size before: $_FILE_SIZE_BEFORE";
    echo "- Size after: $_FILE_SIZE_AFTER";

}

wputools__optimize_image "$@";
