<?php
/**
 * @var ComponentAttributes $attributes
 */

use Windwalker\Edge\Component\ComponentAttributes;

?>
@props(['type' => 'info', 'message' => 'unknown message'])

<div id="foo" {!! $attributes->merge(['class' => 'alert alert-' . $type]) !!}>
    Foo Component: {{ $type }} - Message: {{ $message }}

    {!! $slot() !!}
</div>
