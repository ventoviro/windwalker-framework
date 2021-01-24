<html>
<body>
{{ $test }}

{!! $escape !!}

{{{ $yoo }}}

{{-- Comment --}}

@foreach($a as $k => $v)
<li>{{ $v }}</li>
@endforeach
</body>
</html>
