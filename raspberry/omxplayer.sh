#!/bin/bash

omxplayer $1 &
sleep "$(($2 + 0))"
killall omxplayer.bin
