#!/bin/bash

wputools_echo_message "# RESIZE BIG IMAGE";

###################################
## Resize an attachment original to the big image threshold
###################################

function wputools__resize_big_image(){
    local _ID="${1}";
    local _MAGICK;
    local _INFOS;
    local _FILE;
    local _WIDTH;
    local _HEIGHT;
    local _THRESHOLD;
    local _HAS_ORIGINAL;
    local _EXTENSION;
    local _FILE_BACKUP;
    local _FILE_TMP;
    local _FILE_SIZE_BEFORE;

    if [[ ! "${_ID}" =~ ^[0-9]+$ ]]; then
        echo "Usage: wputools resizebigimage <attachment_id>";
        return 0;
    fi

    if command -v magick &> /dev/null; then
        _MAGICK="magick";
    elif command -v convert &> /dev/null; then
        _MAGICK="convert";
    else
        echo "Error: ImageMagick is not available (magick or convert).";
        return 0;
    fi

    # Only the last line is read: plugins could output notices before it.
    _INFOS=$(_WPCLICOMMAND eval '
$id = '"${_ID}"';
$file = get_attached_file($id);
if (!wp_attachment_is_image($id) || !$file || !is_file($file)) {
    return;
}
$size = getimagesize($file);
if (!$size) {
    return;
}
$meta = wp_get_attachment_metadata($id);
$threshold = (int) apply_filters("big_image_size_threshold", 2560, array($size[0], $size[1]), $file, $id);
echo implode("\t", array($file, $size[0], $size[1], $threshold > 0 ? $threshold : 2560, empty($meta["original_image"]) ? 0 : 1));
' | tail -n 1);

    IFS=$'\t' read -r _FILE _WIDTH _HEIGHT _THRESHOLD _HAS_ORIGINAL <<< "${_INFOS}";

    if [[ -z "${_THRESHOLD}" ]]; then
        echo "Error: Attachment '${_ID}' is not a readable image";
        return 0;
    fi

    if [[ "${_HAS_ORIGINAL}" == '1' ]]; then
        echo "Error: Attachment '${_ID}' already has a scaled version.";
        return 0;
    fi

    _EXTENSION="${_FILE##*.}";
    _EXTENSION=$(echo "${_EXTENSION}" | tr '[:upper:]' '[:lower:]');
    if [[ "${_EXTENSION}" != "png" && "${_EXTENSION}" != "jpg" && "${_EXTENSION}" != "jpeg" && "${_EXTENSION}" != "webp" ]]; then
        echo "Error: Unsupported image format '${_EXTENSION}'. Only PNG, JPG, JPEG, and WEBP are supported.";
        return 0;
    fi

    if [[ "${_WIDTH}" -le "${_THRESHOLD}" && "${_HEIGHT}" -le "${_THRESHOLD}" ]]; then
        echo "Nothing to do: ${_WIDTH}x${_HEIGHT} is within ${_THRESHOLD}x${_THRESHOLD}.";
        return 0;
    fi

    _FILE_BACKUP="${_FILE}.bak";
    if [[ -e "${_FILE_BACKUP}" ]]; then
        echo "Error: Backup '${_FILE_BACKUP}' already exists.";
        return 0;
    fi

    # The copy gives the resized file the same owner and permissions as the original.
    _FILE_TMP="${_FILE%.*}-resizebigimage-tmp.${_EXTENSION}";
    cp -p "${_FILE}" "${_FILE_TMP}" || return 1;
    if ! "${_MAGICK}" "${_FILE}" -auto-orient -resize "${_THRESHOLD}x${_THRESHOLD}>" "${_FILE_TMP}"; then
        rm -f "${_FILE_TMP}";
        echo "Error: Image could not be resized. Original file was not modified.";
        return 1;
    fi

    _FILE_SIZE_BEFORE=$(du -h "${_FILE}" | cut -f1);
    if ! cp -p "${_FILE}" "${_FILE_BACKUP}"; then
        rm -f "${_FILE_TMP}";
        echo "Error: Backup could not be created. Original file was not modified.";
        return 1;
    fi
    mv "${_FILE_TMP}" "${_FILE}" || return 1;

    # Thumbnail names can change by 1px after resizing: old ones may still be used in contents.
    if ! _WPCLICOMMAND media regenerate "${_ID}" --yes --skip-delete; then
        echo "Error: Thumbnails could not be regenerated. To restore the original:";
        echo "mv \"${_FILE_BACKUP}\" \"${_FILE}\"";
        return 1;
    fi

    echo "Image resized: ${_FILE}";
    echo "- Size before: ${_FILE_SIZE_BEFORE}";
    echo "- Size after: $(du -h "${_FILE}" | cut -f1)";
    echo "Original image backed up. Delete it after checking the result:";
    echo "rm \"${_FILE_BACKUP}\"";
}
wputools__resize_big_image "$@";
