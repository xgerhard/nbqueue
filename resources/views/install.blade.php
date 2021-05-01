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
    <h1>Nightbot queue, automatic installation:</h1>

    @if(isset($success) && $success === true)
        <p>
            Installation complete, thanks for trying out the queue system!
        </p>
    @else
        @if(isset($errors) && !empty($errors))
        <div class="alert alert-warning">
            <ul>
            @foreach($errors as $error)
                <li><strong>Error:</strong> {{$error}}</li>
            @endforeach
            </ul>
        </div>
        @endif

        @if(isset($auth_url))
            <p>
                If you sign in with Nightbot we can automaticly add the commands for you. After you login you will be redirected back to this webpage and select/customize the commands you would like to install.
            </p>
            <a class="btn btn-primary" href="{{$auth_url}}" role="button">Sign in with Nightbot</a>
        @else
            <p>
                All commands but the !q command are optional, please uncheck every command you wish <b>not</b> to add to your Nightbot.<br/>
                You can rename the commands as you like.<br/>        
            </p>
            @if(isset($commands) && !empty($commands))
            <form action="" method="POST">
                @csrf
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Command code</th>
                            <th scope="col">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($commands as $title => $command)
                        <tr>
                            <td>
                            <input type="checkbox" name="command[{{$title}}][enable]" checked="checked" @if(isset($command['main_command'])) disabled @endif /></td>
                            <td>@if(isset($command['main_command'])) {{$command['name']}} @else <input type="text" name="command[{{$title}}][name]" value="{{$command['name']}}"/> @endif </td>
                            <td>{{$command['description']}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p>
                    <b>Note:</b> Commands in your Nightbot that already exist with command codes from the table above will be <b>overwritten</b>, please double check to make sure no commands will be lost. <br/>
                    <br/><input type="checkbox" name="agreement" required/> I double checked the commands names, existing commands <b>will</b> be overwritten.
                </p>
                <input class="btn btn-primary" type="submit" value="Install commands">
            </form>
            @endif
        @endif
    @endif
</div>
</body>
</html>