<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command(
    'minimon:ping'
)->everyThirtySeconds();

Schedule::command(
    'minimon:monitor'
)->everyMinute();

Schedule::command(
    'minimon:hosts'
)->everyMinute();