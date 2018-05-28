# nbqueue

A basic queue system for Nightbot.

## User commands
* __join $message__ - Joines the current queue with optional message "$message", this message is displayed when the user will be picked from the queue
* __leave__ - Leaves the current queue
* __position__ - Displays the position from the user in the current active queue
* __list__ - Displays a link to a webpage with the full list of users in the queue
* __info__ - Displays the current queue information, if the queue is open and how many people are in it

# Moderator commands
* __open__ - Opens the current queue
* __close__ - Closes the current queue
* __next $x__ - Picks and removes the first $x users from the queue, the $x is optional
* __clear__ - Clears the current queue
* __add $name__ - Creates a new queue with the name $name
* __del $name__ - Deletes the queue with name $name (the default queue can't be deleted)
* __set $name__ - Will set the queue with name $name as active queue.
* __remove $id__ - Will remove the user with id $id from the queue

# Installation
Sign in with Nightbot here, the installer will add the selected commands to Nightbot: https://dev.2g.be/twitch/nbqueue/public/install/auto

## Improvements?
* In the current system the user can only join the active queue. Since the system supports multiple queues, maybe add a parameter to join another queue (if they are open). However there will need to be a good syntax for it first, since everything after !join is currently saved as message, for example to store their gamertag. Maybe something like '!join "$queue" $message' - '!join "fortnite" xgerhard'.
* When a user is picked, the user will be removed from the database. If the user is not there, the user will lose the spot. Maybe add an option to skip a user, not sure how to handle this yet database wise.
* The queue system currently identifies users by their Id's provided in the Nightbot headers, this means that the only way to join a queue is by typing the join command, a moderator can't add users, which might be a good thing?
* Nightbot has a minimum of 5 sec cooldown on commands, if a big chat will spam !join - some messages will be ignored, the only way a user knows if he/she joined succesfull is if they get a response from Nightbot with a success message. This might be spammy if alot of users want to enter, however if I dont send the confirmation people wouldn't know if they entered.
* People will be able to join a queue through Discord and Twitch at the same time, maybe add a setting to set a main platform and ignore all others? 
