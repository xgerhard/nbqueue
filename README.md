# nbqueue

A basic queue system for Nightbot. The usage is pretty straight forward, `!join` and `!leave` for users to enter or leave the queue, the user can check their position in the queue with `!position`. Moderators can `!q open` or `!q close` the queue and pick a user from the queue with `!q next`.

The command supports multiple queues, but there can only be one active. Only the active channel is joinable. Moderators can manage queues by using `!q add $queueName` or `!q del $queueName`, to switch to a different queue use `!q set $queueName`. To empty the queue a moderator can use `!q clear`, or remove one user by using `!q remove $position`, where $position is the position of the user in that specific queue (this position can be found by using `!list`).

## User commands
* __join $message__ - Joines the current queue with optional message "$message", this message is displayed when the user will be picked from the queue
* __leave__ - Leaves the current queue
* __position__ - Displays the position from the user in the current active queue
* __list__ - Displays a link to a webpage with the full list of users in the queue
* __info__ - Displays the current queue information, if the queue is open and how many people are in it
* __who $x__ - Displays the next $x people that are in the queue

# Moderator commands
* __open__ - Opens the current queue
* __close__ - Closes the current queue
* __next $number $userlevel__ - Picks and removes the first $number users from the queue, with userlevel $userlevel. The $number is optional (default 1), the $userlevel is optional (default everyone)
* __random $number $userlevel__ - Picks and removes $number random users from the queue, with userlevel $userlevel. The $number is optional (default 1), the $userlevel is optional (default everyone)
* __clear__ - Clears the current queue
* __add $name__ - Creates a new queue with the name $name
* __del $name__ - Deletes the queue with name $name (the default queue can't be deleted)
* __set $name__ - Will set the queue with name $name as active queue
* __remove $position__  - WWill remove the user with position $position from the queue
* __ul $userlevel__ - Will set the required userlevel for the active queue (available userlevels: everyone, subscriber, regular, twitch_vip, moderator, owner)
* __promote $position__  - Will promote the user with position $position to first position of the queue (next person)
* __setlimit $number__ - Will set a limit of users that can join the current active queue
* __adduser $name__ - Will add user $username to the current active queue (Twitch only)

# Installation
Sign in with Nightbot here, the installer will add the selected commands to Nightbot: https://nbq.gerhard.dev/install/auto

## Improvements?
* In the current system the user can only join the active queue. Since the system supports multiple queues, maybe add a parameter to join another queue (if they are open). However there will need to be a good syntax for it first, since everything after !join is currently saved as message, for example to store their gamertag. Maybe something like `!join "$queue" $message` - `!join "fortnite" xgerhard`.
* When a user is picked, the user will be removed from the database. If the user is not there, the user will lose the spot. Maybe add an option to skip a user, not sure how to handle this yet database wise.
* Nightbot has a minimum of 5 sec cooldown on commands, if a big chat will spam !join - some messages will be ignored, the only way a user knows if he/she joined succesfull is if they get a response from Nightbot with a success message. This might be spammy if alot of users want to enter, however if I dont send the confirmation people wouldn't know if they entered.
* People will be able to join a queue through Discord and Twitch at the same time, maybe add a setting to set a main platform and ignore all others? 


## Development
1. Get the repository: `git clone https://github.com/xgerhard/nbqueue`
2. From the nbqueue folder run `composer install`
3. Rename `.env.example` to `.env` and set your database details
4. Run `php artisan migrate` to install the required tables
5. Normally the app only accepts requests made by Nightbot. If your `APP_DEBUG` value is set to `true` in your `.env` file the app will manually add the Nightbot headers, you can set the virtual user and channel [here](app/Http/Controllers/CommandController.php)
6. Start a local webserver from the public folder: `php -S localhost:8080`

Now you'll be able to run chat commands directly through the browser by setting the `q` parameter in the url, for example:
- `http://localhost:8080/?q=join%20test%20message` will run `!q join test message`
- `http://localhost:8080/?q=next%202` will run `!q next 2`
- `http://localhost:8080/?q=list` will run `!q list`
