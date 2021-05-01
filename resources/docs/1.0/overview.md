# Overview

---

- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Supported platforms](#supported-platforms)
- [Installation](#installation)

<a name="introduction"></a>
## Introduction

Hi there 👋, NBQueue is a queue system for Nightbot. Easily setup and manage queues for your channel & viewers. Some usage examples: viewergames, song requests, mario level queues.

<a name="requirements"></a>
## Requirements

NBQueue is made for Nightbot, how to setup Nightbot:
1. Login at: [Nightbot.tv](https://Nightbot.tv)
2. Hit `Join channel` top right of the Nightbot dashboard

If you encounter problems setting up Nightbot, please visit their community forum: [community.nightdev.com](https://community.nightdev.com/t/nightbot-troubleshooting/24239).

<a name="features"></a>
## Features
NBQueue usage is pretty straight forward, users have commands to join, leave a queue, check their position, see the next X people in the queue, or get a list to the queue webpage. For a full list of user commands and examples, see: [User commands](/{{route}}/{{version}}/user-commands)
<br/><br/>
NBQueue also has commands to moderate the queues, moderators can add, delete, open, close queues, promote a user to the front of the queue, or pick the next X user(s). For a full list of moderator commands and examples, see: [Moderator commands](/{{route}}/{{version}}/moderator-commands)

<a name="Supported platforms"></a>
## Supported platforms

NBQueue will work on all the platforms Nightbot is available on, currently Nightbot is available for:
- Twitch.tv
- Youtube gaming
- Discord (Sign in with one of the above platforms and connect your Discord server here: [nightbot.tv/integrations](https://nightbot.tv/integrations))

<a name="Installation"></a>
## Installation

To install the NBQueue system we have to create Nightbot commands. Only one command is required to be installed, the `!q` command, this command communicates with the NBQueue API. All other commands are optional and will work through the `!q` command using the Nightbot alias feature.
<br/><br/>
There's two methods to install the NBQueue system, automatic or manually. The automatic method is recommended, since it's more secure and easier. During the installation a token will be placed inside the url, which is used inside the `!q` command, this token is channel specific and should be kept private. By installing the command manually through chat this token could be leaked, and could potentially give someone access to moderator queue actions.
<br/><br/>
- [Automatic installation (Recommended)](https://nbq.gerhard.dev/install/auto)
- [Manual installation (Warning: Only use if you add the commands through the Nightbot dashboard)](https://nbq.gerhard.dev/install/manual)