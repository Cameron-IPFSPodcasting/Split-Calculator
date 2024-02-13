# Podcasting 2.0 Split Calculator
This is a Split Calculator to demonstrate Podcasting 2.0 Boost calculations

Demo available at https://ipfspodcasting.net/SplitCalc.php

You can enter a boost amount, change the fee flag, edit shares, and adjust the time slider to activate VTS (and edit VTS start, duation, etc.). The page will adjust splits, normalize percentages, and calculate sats according to the [PodcastIndex Specification](https://github.com/Podcastindex-org/podcast-namespace/blob/main/value/value.md).

URL accepts the following variables...

    feed = Feed Guid
    item = Item Guid
    sec = Play position in seconds
    sats = Inital Boost amount to calculate

Example https://ipfspodcasting.net/SplitCalc.php?feed=413fd8ca-3450-59eb-a249-073a6b530089&item=d78ef6cc-b827-4a21-90de-a5ed126f9142&sec=2400&sats=10000

