<?php

function IsAuthEnv()
{
    if (config('app.env') == 'production') { // production
        return TRUE;
    }
    if (config('app.env') == 'dev') { // Dev acceptance testing
        return FALSE;
    }
    if (config('app.env') == 'uat') { // User acceptance testing
        return FALSE;
    }
    if (config('app.env') == 'local') { // local
        return FALSE;
    }
    if (config('app.env') == 'testing') { //testing environment for php unit test cases
        return FALSE;
    }
    if (config('app.env') == 'uattest') { //testing environment for php unit test cases
        return FALSE;
    }
    if (config('app.env') == 'demo') { //testing environment for php unit test cases
        return FALSE;
    }
    if (config('app.env') == 'Perf_BE_01' || config('app.env') == 'Perf_BE_02' || config('app.env') == 'Perf_DB_01' || config('app.env') == 'Perf_SU_01') { //testing environment for php unit test cases
        return FALSE;
    }
    return TRUE; //by default setting it true.
}

function getCurrentTimeStamp()
{
    date_default_timezone_set(config('app.timezone'));
    return microtime(true);
}

function microtimeToDateTime($microtime, $needMicroTime = true, $format = 'Y-m-d H:i:s')
{
    $explodeData = explode(".", $microtime);
    $dateTimeValue = count($explodeData) >= 1 ? $explodeData[0] : null;
    $microTimeValue = count($explodeData) >= 2 ? $explodeData[1] : null;

    if ($needMicroTime) {
        return date($format . '.', $dateTimeValue) . $microTimeValue;
    } else {
        return date($format, $dateTimeValue);
    }
}

function millisecondsBetweenMicrotime($microTimeOne, $microTimeTwo, $abs = true)
{

    $milliseconds = ($microTimeOne - $microTimeTwo) * 1000;

    if ($abs) {
        $milliseconds = ($milliseconds < 0) ? $milliseconds * -1 : $milliseconds;
    }

    return intval($milliseconds);
}

function isEmpty($Data, $checkArrayCount = true)
{
    if ($Data === null)
        return true;
    if (gettype($Data) == 'string') {
        if (trim($Data) == "") {
            return true;
        } elseif (trim($Data) == 'NULL') {
            return true;
        }
    } elseif (gettype($Data) == 'int') {
        return !isset($Data);
    } elseif (gettype($Data) == 'array') {
        if ($checkArrayCount) {
            if (isset($Data) == 1 && count($Data) == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    return false;
}

//To find the date time between two time objects
function timeDiffBetweenTwoDateTimeObjects($dateTimeOne, $dateTimeTwo = null)
{
    if (isEmpty($dateTimeTwo)) {
        $dateTimeTwo = microtimeToDateTime(getCurrentTimeStamp(), false);
    }

    $dateTimeObjectOne = new DateTime($dateTimeOne);

    $dateTimeObjectTwo = new DateTime($dateTimeTwo);

    $dateDiff = $dateTimeObjectTwo->diff($dateTimeObjectOne);

    return $dateDiff;
}

function associativeArrayToHtmlString($array)
{
    $str = "";
    foreach ($array as $key => $value) {
        $str = $str . '<h3><span style="color: #ff0000;"><strong>' . $key . ': ' . $value[0] . '</strong></span></h3><br>';
    }
    return $str;
}
