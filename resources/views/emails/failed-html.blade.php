@extends(Config::get('core.email'))

@section('content')
<p>The coding style analysis of the commit "{{ $commit }}" on "{{ $repo }}" revealed problems.</p>
<p>Click <a href="{{ $link }}">here</a> to see the details.</p>
@stop
