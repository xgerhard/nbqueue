<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <h1>Queue list:</h1>
    @if(isset($queues) && !empty($queues))
    @foreach($queues as $queue)
    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#c{{$queue->id}}">Queue: <b>{{$queue->name}}</b></a>
                </h4>
            </div>
            <div id="c{{$queue->id}}" class="panel-collapse collapse">
                <ul class="list-group">
                    @if(isset($queue->users) && !empty($queue->users))
                    @foreach($queue->users as $i => $user)
                    <li class="list-group-item">{{$i+1}}. {{$user->user->displayName}}<span class="badge badge-primary badge-pill">Remove ID: {{$user->id}}</span></li>
                    @endforeach
                    @else
                    <li class="list-group-item">Queue is empty</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>
</body>
</html>