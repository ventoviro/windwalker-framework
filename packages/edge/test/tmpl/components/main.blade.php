<h2>Hello</h2>

@component('tmpl.components.hello')
    @slot('title')
        <strong>Notice</strong>
    @endslot

    <p>Hello World</p>
@endcomponent
