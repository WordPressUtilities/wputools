#!/bin/bash

echo "# Optimize Image"
cd "${_SCRIPTSTARTDIR}";
function wputools__optimize_image() {
    local _IMAGE_FILE="$1"
    local RESPONSE;
    local ERROR;
    local MESSAGE;
    local COMPRESSED_URL;

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

    # Upload image to TinyPNG
    RESPONSE=$(curl -s --user api:"$WPUTOOLS_TINYPNG_API_KEY" \
        --data-binary @"$_IMAGE_FILE" \
        --header "Content-Type: image/png" \
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

    # Download compressed image
    curl -s "$COMPRESSED_URL" -o "$_IMAGE_FILE"

    echo "Image compressed and saved to: $_IMAGE_FILE"

}

wputools__optimize_image "$@";
