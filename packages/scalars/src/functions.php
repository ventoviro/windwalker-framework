<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker {

    use Windwalker\Scalars\ArrayObject;
    use Windwalker\Scalars\StringObject;

    /**
     * str
     *
     * @param  mixed        $string
     * @param  null|string  $encoding
     *
     * @return  StringObject
     */
    function str($string = '', ?string $encoding = StringObject::ENCODING_UTF8): StringObject
    {
        return StringObject::wrap($string, $encoding);
    }

    /**
     * collect
     *
     * @param  array  $data
     *
     * @return  ArrayObject
     *
     * @since  3.5
     */
    function arr($data = []): ArrayObject
    {
        return new ArrayObject($data);
    }
}
