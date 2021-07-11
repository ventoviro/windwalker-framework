<?php

$foo = 'Foo Attr';
?>

<div>
    <h3>Class Component</h3>
    <x-foo flower="sakura" x-data :foo="$foo" @click="toGo()">
        <x-slot name="flower">Rose</x-slot>
        World
    </x-foo>

    <h3>Anonymous Component</h3>
    <x-components.foo-component flower="sakura" x-data :foo="$foo" @click="toGo()"
        type="TTT">
        <x-slot name="flower">Rose</x-slot>
        World
    </x-components.foo-component>

    <h3>Dynamic Component</h3>
    <x-dynamic-component is="foo" foo="Foo Attr">
        YOO
    </x-dynamic-component>
</div>
