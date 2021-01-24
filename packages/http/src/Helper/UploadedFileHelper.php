<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Helper;

/**
 * The UploadedFileHelper class.
 *
 * @since  3.0
 *
 * todo: Support php8 types hint
 */
class UploadedFileHelper
{
    /**
     * Get Upload error message.
     *
     * @see  http://php.net/manual/en/features.file-upload.errors.php#89374
     *
     * @param   integer $code
     *
     * @return  string
     */
    public static function getUploadMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
            UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
            UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file was uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "File upload stopped by extension",
            default => "Unknown upload error",
        };
    }
}
