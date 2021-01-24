<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Unicode;

/**
 * Class UnicodeHelper
 *
 * @since 2.0
 */
abstract class UnicodeHelper
{
    /**
     * latinToAscii
     *
     * @param  string  $string
     * @param  int     $case
     *
     * @note  Port of phputf8's utf8_accents_to_ascii()
     *
     * @return  mixed
     */
    public static function latinToAscii(string $string, $case = 0): mixed
    {
        static $UTF8_LOWER_ACCENTS = null;
        static $UTF8_UPPER_ACCENTS = null;

        if ($case <= 0) {
            if (is_null($UTF8_LOWER_ACCENTS)) {
                $UTF8_LOWER_ACCENTS = [
                    'à' => 'a',
                    'ô' => 'o',
                    'ď' => 'd',
                    'ḟ' => 'f',
                    'ë' => 'e',
                    'š' => 's',
                    'ơ' => 'o',
                    'ß' => 'ss',
                    'ă' => 'a',
                    'ř' => 'r',
                    'ț' => 't',
                    'ň' => 'n',
                    'ā' => 'a',
                    'ķ' => 'k',
                    'ŝ' => 's',
                    'ỳ' => 'y',
                    'ņ' => 'n',
                    'ĺ' => 'l',
                    'ħ' => 'h',
                    'ṗ' => 'p',
                    'ó' => 'o',
                    'ú' => 'u',
                    'ě' => 'e',
                    'é' => 'e',
                    'ç' => 'c',
                    'ẁ' => 'w',
                    'ċ' => 'c',
                    'õ' => 'o',
                    'ṡ' => 's',
                    'ø' => 'o',
                    'ģ' => 'g',
                    'ŧ' => 't',
                    'ș' => 's',
                    'ė' => 'e',
                    'ĉ' => 'c',
                    'ś' => 's',
                    'î' => 'i',
                    'ű' => 'u',
                    'ć' => 'c',
                    'ę' => 'e',
                    'ŵ' => 'w',
                    'ṫ' => 't',
                    'ū' => 'u',
                    'č' => 'c',
                    'ö' => 'oe',
                    'è' => 'e',
                    'ŷ' => 'y',
                    'ą' => 'a',
                    'ł' => 'l',
                    'ų' => 'u',
                    'ů' => 'u',
                    'ş' => 's',
                    'ğ' => 'g',
                    'ļ' => 'l',
                    'ƒ' => 'f',
                    'ž' => 'z',
                    'ẃ' => 'w',
                    'ḃ' => 'b',
                    'å' => 'a',
                    'ì' => 'i',
                    'ï' => 'i',
                    'ḋ' => 'd',
                    'ť' => 't',
                    'ŗ' => 'r',
                    'ä' => 'ae',
                    'í' => 'i',
                    'ŕ' => 'r',
                    'ê' => 'e',
                    'ü' => 'ue',
                    'ò' => 'o',
                    'ē' => 'e',
                    'ñ' => 'n',
                    'ń' => 'n',
                    'ĥ' => 'h',
                    'ĝ' => 'g',
                    'đ' => 'd',
                    'ĵ' => 'j',
                    'ÿ' => 'y',
                    'ũ' => 'u',
                    'ŭ' => 'u',
                    'ư' => 'u',
                    'ţ' => 't',
                    'ý' => 'y',
                    'ő' => 'o',
                    'â' => 'a',
                    'ľ' => 'l',
                    'ẅ' => 'w',
                    'ż' => 'z',
                    'ī' => 'i',
                    'ã' => 'a',
                    'ġ' => 'g',
                    'ṁ' => 'm',
                    'ō' => 'o',
                    'ĩ' => 'i',
                    'ù' => 'u',
                    'į' => 'i',
                    'ź' => 'z',
                    'á' => 'a',
                    'û' => 'u',
                    'þ' => 'th',
                    'ð' => 'dh',
                    'æ' => 'ae',
                    'µ' => 'u',
                    'ĕ' => 'e',
                    'œ' => 'oe',
                ];
            }

            $string = str_replace(array_keys($UTF8_LOWER_ACCENTS), array_values($UTF8_LOWER_ACCENTS), $string);
        }

        if ($case >= 0) {
            if (is_null($UTF8_UPPER_ACCENTS)) {
                $UTF8_UPPER_ACCENTS = [
                    'À' => 'A',
                    'Ô' => 'O',
                    'Ď' => 'D',
                    'Ḟ' => 'F',
                    'Ë' => 'E',
                    'Š' => 'S',
                    'Ơ' => 'O',
                    'Ă' => 'A',
                    'Ř' => 'R',
                    'Ț' => 'T',
                    'Ň' => 'N',
                    'Ā' => 'A',
                    'Ķ' => 'K',
                    'Ŝ' => 'S',
                    'Ỳ' => 'Y',
                    'Ņ' => 'N',
                    'Ĺ' => 'L',
                    'Ħ' => 'H',
                    'Ṗ' => 'P',
                    'Ó' => 'O',
                    'Ú' => 'U',
                    'Ě' => 'E',
                    'É' => 'E',
                    'Ç' => 'C',
                    'Ẁ' => 'W',
                    'Ċ' => 'C',
                    'Õ' => 'O',
                    'Ṡ' => 'S',
                    'Ø' => 'O',
                    'Ģ' => 'G',
                    'Ŧ' => 'T',
                    'Ș' => 'S',
                    'Ė' => 'E',
                    'Ĉ' => 'C',
                    'Ś' => 'S',
                    'Î' => 'I',
                    'Ű' => 'U',
                    'Ć' => 'C',
                    'Ę' => 'E',
                    'Ŵ' => 'W',
                    'Ṫ' => 'T',
                    'Ū' => 'U',
                    'Č' => 'C',
                    'Ö' => 'Oe',
                    'È' => 'E',
                    'Ŷ' => 'Y',
                    'Ą' => 'A',
                    'Ł' => 'L',
                    'Ų' => 'U',
                    'Ů' => 'U',
                    'Ş' => 'S',
                    'Ğ' => 'G',
                    'Ļ' => 'L',
                    'Ƒ' => 'F',
                    'Ž' => 'Z',
                    'Ẃ' => 'W',
                    'Ḃ' => 'B',
                    'Å' => 'A',
                    'Ì' => 'I',
                    'Ï' => 'I',
                    'Ḋ' => 'D',
                    'Ť' => 'T',
                    'Ŗ' => 'R',
                    'Ä' => 'Ae',
                    'Í' => 'I',
                    'Ŕ' => 'R',
                    'Ê' => 'E',
                    'Ü' => 'Ue',
                    'Ò' => 'O',
                    'Ē' => 'E',
                    'Ñ' => 'N',
                    'Ń' => 'N',
                    'Ĥ' => 'H',
                    'Ĝ' => 'G',
                    'Đ' => 'D',
                    'Ĵ' => 'J',
                    'Ÿ' => 'Y',
                    'Ũ' => 'U',
                    'Ŭ' => 'U',
                    'Ư' => 'U',
                    'Ţ' => 'T',
                    'Ý' => 'Y',
                    'Ő' => 'O',
                    'Â' => 'A',
                    'Ľ' => 'L',
                    'Ẅ' => 'W',
                    'Ż' => 'Z',
                    'Ī' => 'I',
                    'Ã' => 'A',
                    'Ġ' => 'G',
                    'Ṁ' => 'M',
                    'Ō' => 'O',
                    'Ĩ' => 'I',
                    'Ù' => 'U',
                    'Į' => 'I',
                    'Ź' => 'Z',
                    'Á' => 'A',
                    'Û' => 'U',
                    'Þ' => 'Th',
                    'Ð' => 'Dh',
                    'Æ' => 'Ae',
                    'Ĕ' => 'E',
                    'Œ' => 'Oe',
                ];
            }

            $string = str_replace(array_keys($UTF8_UPPER_ACCENTS), array_values($UTF8_UPPER_ACCENTS), $string);
        }

        return $string;
    }

    /**
     * strtolower
     *
     * @param  string  $str
     *
     * @return  string
     */
    public static function strtolower(string $str): bool|string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str);
        }

        return static::utf8strtolower($str);
    }

    /**
     * utf8strtolower
     *
     * @param  string  $string
     *
     * @note  Port of phputf8's utf8_strtolower()
     *
     * @return  string|bool
     */
    public static function utf8strtolower(string $string): bool|string
    {
        static $UTF8_UPPER_TO_LOWER = null;

        if (is_null($UTF8_UPPER_TO_LOWER)) {
            $UTF8_UPPER_TO_LOWER = [
                0x0041 => 0x0061,
                0x03A6 => 0x03C6,
                0x0162 => 0x0163,
                0x00C5 => 0x00E5,
                0x0042 => 0x0062,
                0x0139 => 0x013A,
                0x00C1 => 0x00E1,
                0x0141 => 0x0142,
                0x038E => 0x03CD,
                0x0100 => 0x0101,
                0x0490 => 0x0491,
                0x0394 => 0x03B4,
                0x015A => 0x015B,
                0x0044 => 0x0064,
                0x0393 => 0x03B3,
                0x00D4 => 0x00F4,
                0x042A => 0x044A,
                0x0419 => 0x0439,
                0x0112 => 0x0113,
                0x041C => 0x043C,
                0x015E => 0x015F,
                0x0143 => 0x0144,
                0x00CE => 0x00EE,
                0x040E => 0x045E,
                0x042F => 0x044F,
                0x039A => 0x03BA,
                0x0154 => 0x0155,
                0x0049 => 0x0069,
                0x0053 => 0x0073,
                0x1E1E => 0x1E1F,
                0x0134 => 0x0135,
                0x0427 => 0x0447,
                0x03A0 => 0x03C0,
                0x0418 => 0x0438,
                0x00D3 => 0x00F3,
                0x0420 => 0x0440,
                0x0404 => 0x0454,
                0x0415 => 0x0435,
                0x0429 => 0x0449,
                0x014A => 0x014B,
                0x0411 => 0x0431,
                0x0409 => 0x0459,
                0x1E02 => 0x1E03,
                0x00D6 => 0x00F6,
                0x00D9 => 0x00F9,
                0x004E => 0x006E,
                0x0401 => 0x0451,
                0x03A4 => 0x03C4,
                0x0423 => 0x0443,
                0x015C => 0x015D,
                0x0403 => 0x0453,
                0x03A8 => 0x03C8,
                0x0158 => 0x0159,
                0x0047 => 0x0067,
                0x00C4 => 0x00E4,
                0x0386 => 0x03AC,
                0x0389 => 0x03AE,
                0x0166 => 0x0167,
                0x039E => 0x03BE,
                0x0164 => 0x0165,
                0x0116 => 0x0117,
                0x0108 => 0x0109,
                0x0056 => 0x0076,
                0x00DE => 0x00FE,
                0x0156 => 0x0157,
                0x00DA => 0x00FA,
                0x1E60 => 0x1E61,
                0x1E82 => 0x1E83,
                0x00C2 => 0x00E2,
                0x0118 => 0x0119,
                0x0145 => 0x0146,
                0x0050 => 0x0070,
                0x0150 => 0x0151,
                0x042E => 0x044E,
                0x0128 => 0x0129,
                0x03A7 => 0x03C7,
                0x013D => 0x013E,
                0x0422 => 0x0442,
                0x005A => 0x007A,
                0x0428 => 0x0448,
                0x03A1 => 0x03C1,
                0x1E80 => 0x1E81,
                0x016C => 0x016D,
                0x00D5 => 0x00F5,
                0x0055 => 0x0075,
                0x0176 => 0x0177,
                0x00DC => 0x00FC,
                0x1E56 => 0x1E57,
                0x03A3 => 0x03C3,
                0x041A => 0x043A,
                0x004D => 0x006D,
                0x016A => 0x016B,
                0x0170 => 0x0171,
                0x0424 => 0x0444,
                0x00CC => 0x00EC,
                0x0168 => 0x0169,
                0x039F => 0x03BF,
                0x004B => 0x006B,
                0x00D2 => 0x00F2,
                0x00C0 => 0x00E0,
                0x0414 => 0x0434,
                0x03A9 => 0x03C9,
                0x1E6A => 0x1E6B,
                0x00C3 => 0x00E3,
                0x042D => 0x044D,
                0x0416 => 0x0436,
                0x01A0 => 0x01A1,
                0x010C => 0x010D,
                0x011C => 0x011D,
                0x00D0 => 0x00F0,
                0x013B => 0x013C,
                0x040F => 0x045F,
                0x040A => 0x045A,
                0x00C8 => 0x00E8,
                0x03A5 => 0x03C5,
                0x0046 => 0x0066,
                0x00DD => 0x00FD,
                0x0043 => 0x0063,
                0x021A => 0x021B,
                0x00CA => 0x00EA,
                0x0399 => 0x03B9,
                0x0179 => 0x017A,
                0x00CF => 0x00EF,
                0x01AF => 0x01B0,
                0x0045 => 0x0065,
                0x039B => 0x03BB,
                0x0398 => 0x03B8,
                0x039C => 0x03BC,
                0x040C => 0x045C,
                0x041F => 0x043F,
                0x042C => 0x044C,
                0x00DE => 0x00FE,
                0x00D0 => 0x00F0,
                0x1EF2 => 0x1EF3,
                0x0048 => 0x0068,
                0x00CB => 0x00EB,
                0x0110 => 0x0111,
                0x0413 => 0x0433,
                0x012E => 0x012F,
                0x00C6 => 0x00E6,
                0x0058 => 0x0078,
                0x0160 => 0x0161,
                0x016E => 0x016F,
                0x0391 => 0x03B1,
                0x0407 => 0x0457,
                0x0172 => 0x0173,
                0x0178 => 0x00FF,
                0x004F => 0x006F,
                0x041B => 0x043B,
                0x0395 => 0x03B5,
                0x0425 => 0x0445,
                0x0120 => 0x0121,
                0x017D => 0x017E,
                0x017B => 0x017C,
                0x0396 => 0x03B6,
                0x0392 => 0x03B2,
                0x0388 => 0x03AD,
                0x1E84 => 0x1E85,
                0x0174 => 0x0175,
                0x0051 => 0x0071,
                0x0417 => 0x0437,
                0x1E0A => 0x1E0B,
                0x0147 => 0x0148,
                0x0104 => 0x0105,
                0x0408 => 0x0458,
                0x014C => 0x014D,
                0x00CD => 0x00ED,
                0x0059 => 0x0079,
                0x010A => 0x010B,
                0x038F => 0x03CE,
                0x0052 => 0x0072,
                0x0410 => 0x0430,
                0x0405 => 0x0455,
                0x0402 => 0x0452,
                0x0126 => 0x0127,
                0x0136 => 0x0137,
                0x012A => 0x012B,
                0x038A => 0x03AF,
                0x042B => 0x044B,
                0x004C => 0x006C,
                0x0397 => 0x03B7,
                0x0124 => 0x0125,
                0x0218 => 0x0219,
                0x00DB => 0x00FB,
                0x011E => 0x011F,
                0x041E => 0x043E,
                0x1E40 => 0x1E41,
                0x039D => 0x03BD,
                0x0106 => 0x0107,
                0x03AB => 0x03CB,
                0x0426 => 0x0446,
                0x00DE => 0x00FE,
                0x00C7 => 0x00E7,
                0x03AA => 0x03CA,
                0x0421 => 0x0441,
                0x0412 => 0x0432,
                0x010E => 0x010F,
                0x00D8 => 0x00F8,
                0x0057 => 0x0077,
                0x011A => 0x011B,
                0x0054 => 0x0074,
                0x004A => 0x006A,
                0x040B => 0x045B,
                0x0406 => 0x0456,
                0x0102 => 0x0103,
                0x039B => 0x03BB,
                0x00D1 => 0x00F1,
                0x041D => 0x043D,
                0x038C => 0x03CC,
                0x00C9 => 0x00E9,
                0x00D0 => 0x00F0,
                0x0407 => 0x0457,
                0x0122 => 0x0123,
            ];
        }

        $uni = utf8_to_unicode($string);

        if (!$uni) {
            return false;
        }

        $cnt = count($uni);

        for ($i = 0; $i < $cnt; $i++) {
            if (isset($UTF8_UPPER_TO_LOWER[$uni[$i]])) {
                $uni[$i] = $UTF8_UPPER_TO_LOWER[$uni[$i]];
            }
        }

        return static::fromUnicode($uni);
    }

    /**
     * fromUnicode
     *
     * @param  array  $arr
     *
     * @note  Port of phputf8's utf8_from_unicode()
     *
     * @return  bool|string
     */
    public static function fromUnicode(array $arr): bool|string
    {
        ob_start();

        foreach (array_keys($arr) as $k) {
            # ASCII range (including control chars)
            if (($arr[$k] >= 0) && ($arr[$k] <= 0x007f)) {
                echo chr($arr[$k]);
                # 2 byte sequence
            } else {
                if ($arr[$k] <= 0x07ff) {
                    echo chr(0xc0 | ($arr[$k] >> 6));
                    echo chr(0x80 | ($arr[$k] & 0x003f));
                    # Byte order mark (skip)
                } else {
                    if ($arr[$k] == 0xFEFF) {
                        // nop -- zap the BOM

                        # Test for illegal surrogates
                    } else {
                        if ($arr[$k] >= 0xD800 && $arr[$k] <= 0xDFFF) {
                            // found a surrogate
                            trigger_error(
                                'utf8_from_unicode: Illegal surrogate ' .
                                'at index: ' . $k . ', value: ' . $arr[$k],
                                E_USER_WARNING
                            );

                            return false;
                            # 3 byte sequence
                        } else {
                            if ($arr[$k] <= 0xffff) {
                                echo chr(0xe0 | ($arr[$k] >> 12));
                                echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
                                echo chr(0x80 | ($arr[$k] & 0x003f));
                                # 4 byte sequence
                            } else {
                                if ($arr[$k] <= 0x10ffff) {
                                    echo chr(0xf0 | ($arr[$k] >> 18));
                                    echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
                                    echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
                                    echo chr(0x80 | ($arr[$k] & 0x3f));
                                } else {
                                    trigger_error(
                                        'utf8_from_unicode: Codepoint out of Unicode range ' .
                                        'at index: ' . $k . ', value: ' . $arr[$k],
                                        E_USER_WARNING
                                    );

                                    // out of range
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }

        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * toUnicode
     *
     * @param  string  $str
     *
     * @note  Port of phputf8's utf8_to_unicode()
     *
     * @return  bool
     */
    public static function toUnicode(string $str): bool
    {
        $mState = 0; // cached expected number of octets after the current octet
        // until the beginning of the next UTF8 character sequence
        $mUcs4  = 0; // cached Unicode character
        $mBytes = 1; // cached expected number of octets in the current sequence

        $out = [];

        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $in = ord($str[$i]);

            if ($mState === 0) {
                // When mState is zero we expect either a US-ASCII character or a
                // multi-octet sequence.
                if (0 === (0x80 & ($in))) {
                    // US-ASCII, pass straight through.
                    $out[]  = $in;
                    $mBytes = 1;
                } elseif (0xC0 === (0xE0 & ($in))) {
                    // First octet of 2 octet sequence
                    $mUcs4  = ($in);
                    $mUcs4  = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 === (0xF0 & ($in))) {
                    // First octet of 3 octet sequence
                    $mUcs4  = ($in);
                    $mUcs4  = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 === (0xF8 & ($in))) {
                    // First octet of 4 octet sequence
                    $mUcs4  = ($in);
                    $mUcs4  = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 === (0xFC & ($in))) {
                    /* First octet of 5 octet sequence.
                    *
                    * This is illegal because the encoded codepoint must be either
                    * (a) not the shortest form or
                    * (b) outside the Unicode range of 0-0x10FFFF.
                    * Rather than trying to resynchronize, we will carry on until the end
                    * of the sequence and let the later error handling code catch it.
                    */
                    $mUcs4  = ($in);
                    $mUcs4  = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC === (0xFE & ($in))) {
                    // First octet of 6 octet sequence, see comments for 5 octet sequence.
                    $mUcs4  = ($in);
                    $mUcs4  = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    /* Current octet is neither in the US-ASCII range nor a legal first
                     * octet of a multi-octet sequence.
                     */
                    trigger_error(
                        'utf8_to_unicode: Illegal sequence identifier ' .
                        'in UTF-8 at byte ' . $i,
                        E_USER_WARNING
                    );

                    return false;
                }
            } elseif (0x80 === (0xC0 & ($in))) {
                // When mState is non-zero, we expect a continuation of the multi-octet
                // sequence

                // Legal continuation.
                $shift = ($mState - 1) * 6;
                $tmp   = $in;
                $tmp   = ($tmp & 0x0000003F) << $shift;
                $mUcs4 |= $tmp;

                /**
                 * End of the multi-octet sequence. mUcs4 now contains the final
                 * Unicode codepoint to be output
                 */
                if (0 === --$mState) {
                    /*
                    * Check for illegal sequences and codepoints.
                    */
                    // From Unicode 3.1, non-shortest form is illegal
                    if (
                        ((2 === $mBytes) && ($mUcs4 < 0x0080)) ||
                        ((3 === $mBytes) && ($mUcs4 < 0x0800)) ||
                        ((4 === $mBytes) && ($mUcs4 < 0x10000)) ||
                        (4 < $mBytes) ||
                        // From Unicode 3.2, surrogate characters are illegal
                        (($mUcs4 & 0xFFFFF800) === 0xD800) ||
                        // Codepoints outside the Unicode range are illegal
                        ($mUcs4 > 0x10FFFF)
                    ) {
                        trigger_error(
                            'utf8_to_unicode: Illegal sequence or codepoint ' .
                            'in UTF-8 at byte ' . $i,
                            E_USER_WARNING
                        );

                        return false;
                    }

                    if (0xFEFF !== $mUcs4) {
                        // BOM is legal but we don't want to output it
                        $out[] = $mUcs4;
                    }

                    //initialize UTF8 cache
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                }
            } else {
                /**
                 *((0xC0 & (*in) != 0x80) && (mState != 0))
                 * Incomplete multi-octet sequence.
                 */
                trigger_error(
                    'utf8_to_unicode: Incomplete multi-octet ' .
                    '   sequence in UTF-8 at byte ' . $i,
                    E_USER_WARNING
                );

                return false;
            }
        }
    }
}
