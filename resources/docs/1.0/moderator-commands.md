# Moderator commands

---

- [Basic info](#basic-info)
- [Open](#open)
- [Close](#close)
- [Add](#add)
- [Del](#del)
- [Set](#set)
- [Next](#next)
- [Clear](#clear)
- [Random](#random)
- [Adduser](#adduser)
- [Remove](#remove)
- [Promote](#promote)
- [Setlimit](#setlimit)
- [UL](#ul)

<a name="basic-info"></a>
## Basic info

All commands are being processed through the `!q` command. Streamers can setup separate commands for each action, these will be added as alias of the `!q` command.
<br/><br/>
<b>Note:</b> Inside the usage blocks, words starting with `$` are variables, read the info above the usage block on how to use them.

<a name="open"></a>
## Open

Opens the queue.
<br/><br/>
Usage:
```html
!q open
```
Example:
```html
xgerhard: !q open
Nightbot: @xgerhard: Queue is now open
```

<a name="close"></a>
## Close

Closes the queue.
<br/><br/>
Usage:
```html
!q close
```
Example:
```html
xgerhard: !q close
Nightbot: @xgerhard: Queue is now closed
```
<a name="add"></a>
## Add

Adds a new queue.
<br/><br/>
Usage:
```html
!q add $queueName
```
Example:
```html
xgerhard: !q add Call of Duty
Nightbot: @xgerhard: Successfully added queue "Call of Duty"
```

<a name="del"></a>
## Del

Deletes a queue.
<br/><br/>
Usage:
```html
!q del $queueName
```
Example:
```html
xgerhard: !q del Call of Duty
Nightbot: @xgerhard: Successfully deleted queue "Call of Duty"
```

<a name="set"></a>
## Set

Set a queue as active queue (only one queue can be active/joined).
<br/><br/>
Usage:
```html
!q set $queueName
```
Example:
```html
xgerhard: !q set Call of Duty
Nightbot: @xgerhard: Queue "Call of Duty" is now the active queue
```

<a name="next"></a>
## Next

Pick the next X users in the queue.<br/>
<b>Optional:</b> By default the first person will be picked. You can provide a number after the command, to get that amount of users.<br/>
<b>Optional:</b> If a UserLevel is provided, the next user(s) with that UserLevel (or higher) will be picked.
<br/><br/>
Usage:
```html
!q next $amount $userLevel
```
Example:
```html
xgerhard: !q next 2
Nightbot: @xgerhard: Next 2 persons in the queue: @Xgerhard, @NotXgerhard
```

<a name="clear"></a>
## Clear

Clears the queue.
<br/><br/>
Usage:
```html
!q clear
```
Example:
```html
xgerhard: !q clear
Nightbot: @xgerhard: Successfully cleared queue
```

<a name="random"></a>
## Random

Randomly picks the next X users in the queue.<br/>
<b>Optional:</b> By default one person will be picked. You can provide a number after the command, to get that amount of users.<br/>
<b>Optional:</b> If a UserLevel is provided, random user(s) with that UserLevel (or higher) will be picked.
<br/><br/>
Usage:
```html
!q random $amount $userLevel
```
Example:
```html
xgerhard: !q random 2
Nightbot: @xgerhard: Next 2 persons in the queue: @NotXgerhard, @xgerhard
```

<a name="adduser"></a>
## Adduser

Manually add a user to the queue.<br/>
<b>Note:</b> this is a Twitch only feature.
<br/><br/>
Usage:
```html
!q adduser $username
```
Example:
```html
xgerhard: !q adduser xgerhard
Nightbot: @xgerhard: Successfully added "xgerhard" to queue, position #1
```

<a name="remove"></a>
## Remove

Remove a user from the queue, by their position.<br/>
Use the `!q list` command to view the users queue position.
<br/><br/>
Usage:
```html
!q remove $position
```
Example:
```html
xgerhard: !q remove 1
Nightbot: @xgerhard: Successfully removed user from queue
```

<a name="promote"></a>
## Promote

Promote a user to the first position of the queue, by their current position<br/>
Use the `!q list` command to view the users current queue position.
<br/><br/>
Usage:
```html
!q promote $positon
```
Example:
```html
xgerhard: !q promote 2
Nightbot: @xgerhard: Successfully promoted NotXgerhard to first position of the queue
```

<a name="setlimit"></a>
## Setlimit

Set a limit of users that can join the queue.<br/>
Use `!q setlimit 0` to reset the limit (unlimited).

<br/><br/>
Usage:
```html
!q setlimit $number
```
Example:
```html
xgerhard: !q setlimit 10
Nightbot: @xgerhard: Successfully set the queue limit to: 10
```

<a name="ul"></a>
## UL

Set a specific UserLevel to the queue, users below this UserLevel won't be able to join the queue.<br/>
Available UserLevels from high to low:
- owner
- moderator
- vip
- regular
- subscriber
- everyone

Use `!q setlimit everyone` to reset the UserLevel (everyone can enter).

<br/><br/>
Usage:
```html
!q ul $userLevel
```
Example:
```html
xgerhard: !q ul subscriber
Nightbot: @xgerhard: Successfully set the UserLevel to "subscriber"
```