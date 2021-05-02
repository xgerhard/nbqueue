# User commands

---

- [Basic info](#basic-info)
- [Join](#join)
- [Leave](#leave)
- [Position](#position)
- [List](#list)
- [Info](#info)
- [Who](#who)

<a name="basic-info"></a>
## Basic info

All commands are being processed through the `!q` command. Streamers can setup separate commands for each action, these will be added as alias of the `!q` command. In the examples below the following separate commands will be shown: `!join`, `!leave`, `!position`, `!list`, `!info`, `!who`. These commands might vary per channel, or not exist at all, the `!q` command will always be avaiable.<br/><br/>

<b>Note:</b> Inside the usage blocks, words starting with `$` are variables, read the info above the usage block on how to use them.

<a name="join"></a>
## Join

Join the current active queue.<br/>
<b>Optional:</b> A message can be provided, this message will be shown when the user is selected from the queue.
<br/><br/>
Usage:
```html
!q join $message
```
Or if the streamer has setup a custom command, for example `!join`
```html
!join $message
```
<br/><br/>
Example:
```html
xgerhard: !q join
Nightbot: @xgerhard: Successfully joined queue, your position is #1
```

<a name="leave"></a>
## Leave

Leave the current active queue.
<br/><br/>
Usage:
```html
!q leave
```
Or if the streamer has setup a custom command, for example `!leave`
```html
!leave
```
<br/>
Example:
```html
xgerhard: !q leave
Nightbot: @xgerhard: Successfully removed from queue
```

<a name="position"></a>
## Position

Check your position in the current active queue.
<br/><br/>
Usage:
```html
!q position
```
Or if the streamer has setup a custom command, for example `!position`
```html
!position
```
<br/>
Example:
```html
xgerhard: !q position
Nightbot: @xgerhard: Your current position in queue is: #1
```

<a name="list"></a>
## List

Get a link to a webpage to view all queues and users in the queue.
<br/><br/>
Usage:
```html
!q list
```
Or if the streamer has setup a custom command, for example `!list`
```html
!list
```
<br/>
Example:
```html
xgerhard: !q list
Nightbot: @xgerhard: Full list of users and queues can be found here: https://nbq.gerhard.dev/146
```

<a name="info"></a>
## Info

Get information (name, status, amount of users) of the current active queue.
<br/><br/>
Usage:
```html
!q info
```
Or if the streamer has setup a custom command, for example `!info`
```html
!info
```
<br/>
Example:
```html
xgerhard: !q info
Nightbot: @xgerhard: Current queue: "default", the queue is currently open for "everyone" and contains 6 users. Available queues: [default, test]
```

<a name="who"></a>
## Who

Display the next X users in the queue.<br/>
<b>Optional:</b> By default the next 5 users will be shown, you can provide a number after the command, to get that amount of users.
<br/><br/>
Usage:
```html
!q who $number
```
Or if the streamer has setup a custom command, for example `!who`
```html
!who $number
```
<br/>
Example:
```html
xgerhard: !q who 2
Nightbot: @xgerhard: Next 2 persons in the queue: @xgerhard, @NotXgerhard
```