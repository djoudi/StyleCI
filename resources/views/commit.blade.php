@extends(Config::get('core.default'))

@section('title', 'Commit - '.$commit->message)
@section('description', $commit->message)

@section('top')
<div class="page-heading">
    <div class="container">
        <h1>{{ $commit->repo->name }} &mdash; Commit Analysis</h1>
        <p>Here you can see the results of the analysed commit.</p>
    </div>
</div>
@stop

@section('content')
<div class="commit js-channel" data-channel="{{ $commit->repo->id }}">
    <p class="js-status" style="@if ($commit->status === 1) color:green; @elseif ($commit->status > 1) color:red; @else color:grey; @endif">
        {{ $commit->description() }}
    </p>
    <hr>
    <div class="row">
        <div class="col-sm-8">
            <h2>{{ $commit->message }}</h2>
            <span class="js-time-ago" title="{{ $commit->createdAtToISO }}">{{ $commit->timeAgo }}</span>
            <h5>{{ $commit->id }}</h5>
        </div>
        <div class="col-sm-4">
            @if ($commit->status === 2)
            <ul class="list-group">
                <a class="list-group-item" href="{{ route('commit_download_path', $commit->id) }}">
                    <i class="fa fa-cloud-download"></i> Download patch
                </a>
                <a class="list-group-item" href="{{ route('commit_diff_path', $commit->id) }}">
                    <i class="fa fa-code"></i> Open diff file
                </a>
            </ul>
            @endif
        </div>
    </div>
    @if ($commit->status === 2)
    <hr>
    @foreach ($commit->diffFiles() as $name => $file)
    <div class="panel panel-default">
        <div class="panel-heading">
            {{ $name }}
        </div>
        <div class="panel-body">
            <pre class="brush: diff">
                {{ $file }}
            </pre>
        </div>
    </div>
    @endforeach
    @endif
</div>
@stop

@section('js')
<script type="text/javascript">
    SyntaxHighlighter.defaults['toolbar'] = false;
    SyntaxHighlighter.defaults['gutter'] = false;
    SyntaxHighlighter.all();
    $(function() {
        StyleCI.Commit.RealTimeStatus();
    });
</script>
@stop
