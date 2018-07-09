<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

//---------------------------------------------------------------
/**
 * UTF-8 aware alternative to str_split
 * Convert a string to an array
 * Note: requires utf8_strlen to be loaded.
 *
 * @param string UTF-8 encoded
 * @param int number to characters to split string by
 *
 * @return string characters in string reverses
 *
 * @see     http://www.php.net/str_split
 * @see     utf8_strlen
 */
function utf8_str_split($str, $split_len = 1)
{
    if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1) {
        return false;
    }

    $len = utf8_strlen($str);
    if ($len <= $split_len) {
        return [$str];
    }

    preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);

    return $ar[0];
}
