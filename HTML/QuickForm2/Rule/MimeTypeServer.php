<?php
/**
 * Rule checking that uploaded file is of the correct MIME type using server side extension
 */
class HTML_QuickForm2_Rule_MimeTypeServer extends HTML_QuickForm2_Rule_MimeType
{
    /**
     * Validates the owner element
     *
     * @throws Exception
     * @return   bool    whether uploaded file's MIME type is correct
     */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if (!isset($value['error']) || UPLOAD_ERR_NO_FILE == $value['error']) {
            return true;
        }

        $allowed_mime_types = $this->getConfig();

        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $value['tmp_name']);
        finfo_close($finfo);

        return is_array($allowed_mime_types) ? in_array($mime_type, $allowed_mime_types) :
            $mime_type == $allowed_mime_types;
    }
}
?>