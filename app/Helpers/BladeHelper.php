<?php

function getDay()
{
    return [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday"
    ];
}

function closeDayConverter(String $value, array $listData = []) // $value = "0,6" and $value = "0"
{
    $splitValue = explode(',', $value);

    foreach ($splitValue as $split) {
        $value = str_replace($split, $listData[(int) $split], $value);
    }
    return $value;
}

function minutesToTime($inputMinutes)
{
    $inputSeconds = $inputMinutes * 60;
    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;
    $secondsInAMonth = 30 * $secondsInADay;
    $secondsInAYear = 12 * $secondsInAMonth;

    $years = floor($inputSeconds / $secondsInAYear);

    $monthSeconds = $inputSeconds % $secondsInAYear;
    $months = floor($monthSeconds / $secondsInAMonth);

    $daySeconds = $monthSeconds % $secondsInAMonth;
    $days = floor($daySeconds / $secondsInADay);

    $hourSeconds = $daySeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    $Y = (int) $years > 0 ? (int) $years . ' '. __('adminPanel.year').',' : '';
    $M = (int) $months > 0 ? (int) $months . ' '. __('adminPanel.month').',' : '';
    $d = (int) $days > 0 ? (int) $days .' '. __('adminPanel.day').',' : '';
    $h = (int) $hours > 0 ? (int) $hours . ' '. __('adminPanel.hour').',' : '';
    $m = (int) $minutes > 0 ? (int) $minutes . ' '. __('adminPanel.minute').',' : '';
    $s = (int) $seconds > 0 ? (int) $seconds . ' '. __('adminPanel.second') : '';

    return $Y . $M . $d . $h . $m . $s;
}


function getAccessStatus($value)
{
    if ($value == 0) $accessStatus = 'read';
    elseif ($value == 1)  $accessStatus = 'read/write';
    elseif ($value == 2)  $accessStatus = 'read/write/delete';
    else $accessStatus = 'hide';
    return $accessStatus;
}
