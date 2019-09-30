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
    <h1>Nightbot queue, manual installation:</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Command</th>
                <th scope="col">Description</th>
            </tr>
        </thead>
        <tbody>
        @foreach($commands as $title => $command)
            @php
                $userLevel = isset($command['mod']) ? 'moderator' : 'everyone';
                $alias = isset($command['main_command']) ? '' : ' -a=!q';
            @endphp
            <tr>
                <td>{{$command['name']}}</td>
                <td>{{$command['description']}}</td>
            </tr>
            <tr>
                <td colspan=2>@if(isset($command['main_command']))<b>(Required)</b>@endif <code>!commands add {{$command['name']}} -ul={{$userLevel}}{{$alias}} {{$command['code']}}</code></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>