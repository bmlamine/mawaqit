#!/bin/bash

omxplayer $1 &
sleep $2
killall omxplayer.bin