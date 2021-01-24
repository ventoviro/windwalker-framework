<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

/**
 * Html Cleaner object.
 *
 * Forked from the php input filter library by: Daniel Morris <dan@rootcube.com>
 * Original Contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
 *
 * @since  2.0
 */
class HtmlCleaner
{
    const USE_WHITE_LIST = 0;

    const USE_BLACK_LIST = 1;

    const ONLY_ESSENTIAL = 0;

    /**
     * The array of permitted tags (white list).
     *
     * @var    array
     * @since  2.0
     */
    public $tagsArray = [];

    /**
     * The array of permitted tag attributes (white list).
     *
     * @var    array
     * @since  2.0
     */
    public $attrArray = [];

    /**
     * The method for sanitising tags: WhiteList method = 0 (default), BlackList method = 1
     *
     * @var    integer
     * @since  2.0
     */
    public $tagsMethod = self::USE_WHITE_LIST;

    /**
     * The method for sanitising attributes: WhiteList method = 0 (default), BlackList method = 1
     *
     * @var    integer
     * @since  2.0
     */
    public $attrMethod = self::USE_WHITE_LIST;

    /**
     * A flag for XSS checks. Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
     *
     * @var    integer
     * @since  2.0
     */
    public $xssAuto;

    /**
     * The list of the default blacklisted tags.
     *
     * @var    array
     * @since  2.0
     */
    public $tagBlacklist = [
        'applet',
        'body',
        'bgsound',
        'base',
        'basefont',
        'embed',
        'frame',
        'frameset',
        'head',
        'html',
        'id',
        'iframe',
        'ilayer',
        'layer',
        'link',
        'meta',
        'name',
        'object',
        'script',
        'style',
        'title',
        'xml',
    ];

    /**
     * The list of the default blacklisted tag attributes. All event handlers implicit.
     *
     * @var    array
     * @since   2.0
     */
    public $attrBlacklist = [
        'action',
        'background',
        'codebase',
        'dynsrc',
        'lowsrc',
    ];

    /**
     * Constructor for inputFilter class. Only first parameter is required.
     *
     * @param   array   $tagsArray  List of user-defined tags
     * @param   array   $attrArray  List of user-defined attributes
     * @param   integer $tagsMethod WhiteList method = 0, BlackList method = 1
     * @param   integer $attrMethod WhiteList method = 0, BlackList method = 1
     * @param   integer $xssAuto    Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
     *
     * @since   2.0
     */
    public function __construct(
        $tagsArray = [],
        $attrArray = [],
        $tagsMethod = self::USE_BLACK_LIST,
        $attrMethod = self::USE_BLACK_LIST,
        $xssAuto = 1
    ) {
        // Make sure user defined arrays are in lowercase
        $tagsArray = array_map('strtolower', (array) $tagsArray);
        $attrArray = array_map('strtolower', (array) $attrArray);

        // Assign member variables
        $this->tagsArray = $tagsArray;
        $this->attrArray = $attrArray;
        $this->tagsMethod = $tagsMethod;
        $this->attrMethod = $attrMethod;
        $this->xssAuto = $xssAuto;
    }

    /**
     * Function to determine if contents of an attribute are safe
     *
     * @param   array $attrSubSet A 2 element array for attribute's name, value
     *
     * @return  boolean  True if bad code is detected
     *
     * @since   2.0
     */
    public static function isBadAttribute(array $attrSubSet): bool
    {
        $attrSubSet[0] = strtolower($attrSubSet[0]);
        $attrSubSet[1] = strtolower($attrSubSet[1]);

        return (
            ((str_contains($attrSubSet[1], 'expression')) && ($attrSubSet[0]) === 'style')
            || (str_contains($attrSubSet[1], 'javascript:'))
            || (str_contains($attrSubSet[1], 'behaviour:'))
            || (str_contains($attrSubSet[1], 'vbscript:'))
            || (str_contains($attrSubSet[1], 'mocha:'))
            || (str_contains($attrSubSet[1], 'livescript:'))
        );
    }

    /**
     * Internal method to iteratively remove all unwanted tags and attributes
     *
     * @param   string $source Input string to be 'cleaned'
     *
     * @return  string  'Cleaned' version of input parameter
     *
     * @since   2.0
     */
    public function remove(string $source): ?string
    {
        $loopCounter = 0;

        // Iteration provides nested tag protection
        while ($source != $this->cleanTags($source)) {
            $source = $this->cleanTags($source);
            $loopCounter++;
        }

        return $source;
    }

    /**
     * Internal method to strip a string of certain tags
     *
     * @param   string $source Input string to be 'cleaned'
     *
     * @return  string  'Cleaned' version of input parameter
     *
     * @since   2.0
     */
    protected function cleanTags(string $source): ?string
    {
        // First, pre-process this for illegal characters inside attribute values
        $source = $this->escapeAttributeValues($source);

        // In the beginning we don't really have a tag, so everything is postTag
        $preTag = null;
        $postTag = $source;
        $currentSpace = false;

        // Setting to null to deal with undefined variables
        $attr = '';

        // Is there a tag? If so it will certainly start with a '<'.
        $tagOpen_start = strpos($source, '<');

        while ($tagOpen_start !== false) {
            // Get some information about the tag we are processing
            $preTag .= substr($postTag, 0, $tagOpen_start);
            $postTag = substr($postTag, $tagOpen_start);
            $fromTagOpen = substr($postTag, 1);
            $tagOpen_end = strpos($fromTagOpen, '>');

            // Check for mal-formed tag where we have a second '<' before the first '>'
            $nextOpenTag = (strlen($postTag) > $tagOpen_start) ? strpos($postTag, '<', $tagOpen_start + 1) : false;

            if (($nextOpenTag !== false) && ($nextOpenTag < $tagOpen_end)) {
                // At this point we have a mal-formed tag -- remove the offending open
                $postTag = substr($postTag, 0, $tagOpen_start) . substr($postTag, $tagOpen_start + 1);
                $tagOpen_start = strpos($postTag, '<');
                continue;
            }

            // Let's catch any non-terminated tags and skip over them
            if ($tagOpen_end === false) {
                $postTag = substr($postTag, $tagOpen_start + 1);
                $tagOpen_start = strpos($postTag, '<');
                continue;
            }

            // Do we have a nested tag?
            $tagOpen_nested = strpos($fromTagOpen, '<');

            if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
                $preTag .= substr($postTag, 0, ($tagOpen_nested + 1));
                $postTag = substr($postTag, ($tagOpen_nested + 1));
                $tagOpen_start = strpos($postTag, '<');
                continue;
            }

            // Let's get some information about our tag and setup attribute pairs
            $tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
            $currentTag = substr($fromTagOpen, 0, $tagOpen_end);
            $tagLength = strlen($currentTag);
            $tagLeft = $currentTag;
            $attrSet = [];
            $currentSpace = strpos($tagLeft, ' ');

            // Are we an open tag or a close tag?
            if (substr($currentTag, 0, 1) === '/') {
                // Close Tag
                $isCloseTag = true;
                [$tagName] = explode(' ', $currentTag);
                $tagName = substr($tagName, 1);
            } else {
                // Open Tag
                $isCloseTag = false;
                [$tagName] = explode(' ', $currentTag);
            }

            /*
             * Exclude all "non-regular" tagnames
             * OR no tagname
             * OR remove if xssauto is on and tag is blacklisted
             */
            if (!preg_match('/^[a-z][a-z0-9]*$/i', $tagName) || !$tagName
                || (\in_array(strtolower($tagName), $this->tagBlacklist, true) && $this->xssAuto)) {
                $postTag = substr($postTag, $tagLength + 2);
                $tagOpen_start = strpos($postTag, '<');

                // Strip tag
                continue;
            }

            /*
             * Time to grab any attributes from the tag... need this section in
             * case attributes have spaces in the values.
             */
            while ($currentSpace !== false) {
                $attr = '';
                $fromSpace = substr($tagLeft, ($currentSpace + 1));
                $nextEqual = strpos($fromSpace, '=');
                $nextSpace = strpos($fromSpace, ' ');
                $openQuotes = strpos($fromSpace, '"');
                $closeQuotes = strpos(substr($fromSpace, ($openQuotes + 1)), '"') + $openQuotes + 1;

                $startAtt = '';
                $startAttPosition = 0;

                // Find position of equal and open quotes ignoring
                if (preg_match('#\s*=\s*\"#', $fromSpace, $matches, PREG_OFFSET_CAPTURE)) {
                    $startAtt = $matches[0][0];
                    $startAttPosition = $matches[0][1];
                    $closeQuotes = strpos(
                        substr($fromSpace, ($startAttPosition + strlen($startAtt))),
                        '"'
                    ) + $startAttPosition + strlen($startAtt);
                    $nextEqual = $startAttPosition + strpos($startAtt, '=');
                    $openQuotes = $startAttPosition + strpos($startAtt, '"');
                    $nextSpace = strpos(substr($fromSpace, $closeQuotes), ' ') + $closeQuotes;
                }

                // Do we have an attribute to process? [check for equal sign]
                if ($fromSpace !== '/' && (($nextEqual && $nextSpace && $nextSpace < $nextEqual) || !$nextEqual)) {
                    if (!$nextEqual) {
                        $attribEnd = strpos($fromSpace, '/') - 1;
                    } else {
                        $attribEnd = $nextSpace - 1;
                    }

                    // If there is an ending, use this, if not, do not worry.
                    if ($attribEnd > 0) {
                        $fromSpace = substr($fromSpace, $attribEnd + 1);
                    }
                }

                if (strpos($fromSpace, '=') !== false) {
                    // If the attribute value is wrapped in quotes we need to grab the substring from
                    // the closing quote, otherwise grab until the next space.
                    if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes + 1)), '"') !== false)) {
                        $attr = substr($fromSpace, 0, $closeQuotes + 1);
                    } else {
                        $attr = substr($fromSpace, 0, (int) $nextSpace);
                    }
                } else // No more equal signs so add any extra text in the tag into the attribute array [eg. checked]
                {
                    if ($fromSpace !== '/') {
                        $attr = substr($fromSpace, 0, (int) $nextSpace);
                    }
                }

                // Last Attribute Pair
                if (!$attr && $fromSpace !== '/') {
                    $attr = $fromSpace;
                }

                // Add attribute pair to the attribute array
                $attrSet[] = $attr;

                // Move search point and continue iteration
                $tagLeft = substr($fromSpace, strlen($attr));
                $currentSpace = strpos($tagLeft, ' ');
            }

            // Is our tag in the user input array?
            $tagFound = in_array(strtolower($tagName), $this->tagsArray);

            // If the tag is allowed let's append it to the output string.
            if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) {
                // Reconstruct tag with allowed attributes
                if (!$isCloseTag) {
                    // Open or single tag
                    $attrSet = $this->cleanAttributes($attrSet);
                    $preTag .= '<' . $tagName;

                    for ($i = 0, $count = count($attrSet); $i < $count; $i++) {
                        $preTag .= ' ' . $attrSet[$i];
                    }

                    // Reformat single tags to XHTML
                    if (strpos($fromTagOpen, '</' . $tagName)) {
                        $preTag .= '>';
                    } else {
                        $preTag .= ' />';
                    }
                } else // Closing tag
                {
                    $preTag .= '</' . $tagName . '>';
                }
            }

            // Find next tag's start and continue iteration
            $postTag = substr($postTag, ($tagLength + 2));
            $tagOpen_start = strpos($postTag, '<');
        }

        // Append any code after the end of tags and return
        if ($postTag !== '<') {
            $preTag .= $postTag;
        }

        return $preTag;
    }

    /**
     * Internal method to strip a tag of certain attributes
     *
     * @param   array $attrSet Array of attribute pairs to filter
     *
     * @return  array  Filtered array of attribute pairs
     *
     * @since   2.0
     */
    protected function cleanAttributes(array $attrSet):array
    {
        $newSet = [];

        $count = count($attrSet);

        // Iterate through attribute pairs
        for ($i = 0; $i < $count; $i++) {
            // Skip blank spaces
            if (!$attrSet[$i]) {
                continue;
            }

            // Split into name/value pairs
            $attrSubSet = explode('=', trim($attrSet[$i]), 2);

            // Take the last attribute in case there is an attribute with no value
            $attrSubSet_0 = explode(' ', trim($attrSubSet[0]));
            $attrSubSet[0] = array_pop($attrSubSet_0);

            // Remove all "non-regular" attribute names
            // AND blacklisted attributes
            if ((!preg_match('/[a-z]*$/i', $attrSubSet[0]))
                || ($this->xssAuto && (in_array(strtolower($attrSubSet[0]), $this->attrBlacklist, true)
                        || (strpos($attrSubSet[0], 'on') === 0)))) {
                continue;
            }

            // XSS attribute value filtering
            if (isset($attrSubSet[1])) {
                // Trim leading and trailing spaces
                $attrSubSet[1] = trim($attrSubSet[1]);

                // Strips unicode, hex, etc
                $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);

                // Strip normal newline within attr value
                $attrSubSet[1] = preg_replace('/[\n\r]/', '', $attrSubSet[1]);

                // Strip double quotes
                $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);

                // Convert single quotes from either side to doubles
                // (Single quotes shouldn't be used to pad attr values)
                if ((strpos($attrSubSet[1], "'") === 0)
                    && (substr($attrSubSet[1], strlen($attrSubSet[1]) - 1, 1) === "'")) {
                    $attrSubSet[1] = substr($attrSubSet[1], 1, -1);
                }

                // Strip slashes
                $attrSubSet[1] = stripslashes($attrSubSet[1]);
            } else {
                continue;
            }

            // Autostrip script tags
            if (self::isBadAttribute($attrSubSet)) {
                continue;
            }

            // Is our attribute in the user input array?
            $attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray, true);

            // If the tag is allowed lets keep it
            if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) {
                // Does the attribute have a value?
                if (empty($attrSubSet[1]) === false) {
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
                } elseif ($attrSubSet[1] === "0") {
                    // Special Case
                    // Is the value 0?
                    $newSet[] = $attrSubSet[0] . '="0"';
                } else {
                    // Leave empty attributes alone
                    $newSet[] = $attrSubSet[0] . '=""';
                }
            }
        }

        return $newSet;
    }

    /**
     * Try to convert to plaintext
     *
     * @note    This method will be removed once support for PHP 5.3 is discontinued.
     *
     * @param   string $source The source string.
     *
     * @return  string  Plaintext string
     *
     * @since   2.0
     */
    public function decode(string $source):string
    {
        return html_entity_decode($source, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape < > and " inside attribute values
     *
     * @param   string $source The source string.
     *
     * @return  string  Filtered string
     *
     * @since   2.0
     */
    protected function escapeAttributeValues(string $source):string
    {
        $alreadyFiltered = '';
        $remainder = $source;
        $badChars = ['<', '"', '>'];
        $escapedChars = ['&lt;', '&quot;', '&gt;'];

        // Process each portion based on presence of =" and "<space>, "/>, or ">
        // See if there are any more attributes to process
        while (preg_match('#<[^>]*?=\s*?(\"|\')#s', (string) $remainder, $matches, PREG_OFFSET_CAPTURE)) {
            // Get the portion before the attribute value
            $quotePosition = $matches[0][1];
            $nextBefore = $quotePosition + strlen($matches[0][0]);

            // Figure out if we have a single or double quote and look for the matching closing quote
            // Closing quote should be "/>, ">, "<space>, or " at the end of the string
            $quote = substr($matches[0][0], -1);
            $pregMatch = ($quote === '"') ? '#(\"\s*/\s*>|\"\s*>|\"\s+|\"$)#' : "#(\'\s*/\s*>|\'\s*>|\'\s+|\'$)#";

            // Get the portion after attribute value
            if (preg_match($pregMatch, substr($remainder, $nextBefore), $matches, PREG_OFFSET_CAPTURE)) {
                // We have a closing quote
                $nextAfter = $nextBefore + $matches[0][1];
            } else {
                // No closing quote
                $nextAfter = strlen($remainder);
            }

            // Get the actual attribute value
            $attributeValue = substr($remainder, $nextBefore, $nextAfter - $nextBefore);

            // Escape bad chars
            $attributeValue = str_replace($badChars, $escapedChars, $attributeValue);
            $attributeValue = $this->stripCssExpressions($attributeValue);
            $alreadyFiltered .= substr($remainder, 0, $nextBefore) . $attributeValue . $quote;
            $remainder = substr($remainder, $nextAfter + 1);
        }

        // At this point, we just have to return the $alreadyFiltered and the $remainder
        return $alreadyFiltered . $remainder;
    }

    /**
     * Remove CSS Expressions in the form of <property>:expression(...)
     *
     * @param   string $source The source string.
     *
     * @return  string  Filtered string
     *
     * @since   2.0
     */
    protected function stripCssExpressions(string $source):string
    {
        // Strip any comments out (in the form of /*...*/)
        $test = preg_replace('#\/\*.*\*\/#U', '', $source);

        // Test for :expression
        if (!stripos($test, ':expression')) {
            // Not found, so we are done
            $return = $source;
        } else {
            // At this point, we have stripped out the comments and have found :expression
            // Test stripped string for :expression followed by a '('
            if (preg_match_all('#:expression\s*\(#', $test, $matches)) {
                // If found, remove :expression
                $test = str_ireplace(':expression', '', $test);
                $return = $test;
            }
        }

        return $return;
    }

    /**
     * getTagsMethod
     *
     * @return  int
     */
    public function getTagsMethod():int
    {
        return $this->tagsMethod;
    }

    /**
     * setTagsMethod
     *
     * @param   int $tagsMethod
     *
     * @return  HtmlCleaner  Return self to support chaining.
     */
    public function setTagsMethod(int $tagsMethod):static
    {
        $this->tagsMethod = $tagsMethod;

        return $this;
    }

    /**
     * getAttrMethod
     *
     * @return  int
     */
    public function getAttrMethod():int
    {
        return $this->attrMethod;
    }

    /**
     * setAttrMethod
     *
     * @param   int $attrMethod
     *
     * @return  HtmlCleaner  Return self to support chaining.
     */
    public function setAttrMethod(int $attrMethod):static
    {
        $this->attrMethod = $attrMethod;

        return $this;
    }

    /**
     * getXssAuto
     *
     * @return  int
     */
    public function getXssMethod():int
    {
        return $this->xssAuto;
    }

    /**
     * setXssAuto
     *
     * @param   int $xssAuto
     *
     * @return  HtmlCleaner  Return self to support chaining.
     */
    public function setXssMethod(int $xssAuto):static
    {
        $this->xssAuto = $xssAuto;

        return $this;
    }
}
