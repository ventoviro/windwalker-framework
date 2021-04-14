<?php
$foo = 'Foo Attr';
?>

<div>
    Hello Tags

    <x-foo flower="sakura" x-data :foo="$foo" @click="toGo()">
        <x-slot name="flower">Rose</x-slot>
        World
    </x-foo>

    <x-components.foo-component flower="sakura" x-data :foo="$foo" @click="toGo()"
        type="TTT">
        <x-slot name="flower">Rose</x-slot>
        World
    </x-components.foo-component>
</div>
