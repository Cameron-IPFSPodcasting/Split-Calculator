# Podcasting 2.0 Split Calculator
This is a Split Calculator to demonstrate Podcasting 2.0 Boost calculations based on the [PodcastIndex Specification](https://github.com/Podcastindex-org/podcast-namespace/blob/main/value/value.md). The concept is to strictly follow the specification. Issues can be for bugs or discussion of topics not found in the specification. This calculator will be updated to follow the spec. It is not intended to implement fixes that aren't first outlined in the spec.

### Demo
Demo available at https://ipfspodcasting.net/SplitCalc.php

### Install
To "install", simply copy the PHP file to any capable web server.

### Usage
You can enter a boost amount, change the fee flag, edit shares, and adjust the time slider to activate VTS (and edit VTS start, duation, etc.). The page will adjust splits, normalize percentages, and calculate sats.

Remote time splits (VTS) can be followed to calculate recursive splits by clicking "Split Inception!" in an active VTS.

![inception](https://github.com/Cameron-IPFSPodcasting/Split-Calculator/assets/103131615/c22f3755-2f7f-4427-9d72-00d01db14616)

URL accepts the following variables...

    feed = Feed Guid
    item = Item Guid
    sec = Play position in seconds
    sats = Inital Boost amount to calculate

Example https://ipfspodcasting.net/SplitCalc.php?feed=413fd8ca-3450-59eb-a249-073a6b530089&item=d78ef6cc-b827-4a21-90de-a5ed126f9142&sec=2400&sats=10000

