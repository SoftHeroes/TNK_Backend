<?php

use App\Models\Betting;
use Illuminate\Support\Facades\DB;

function followBetCount($followToUserID, $followerID, $fromDate, $fromTime)
{
    $bettingRef = new Betting();
    return $bettingRef->getFollowBetsByUser($followToUserID, $followerID, $fromDate, $fromTime)->count(DB::raw('1'));
}

function totalFollowWinAmount($followToUserID, $followerID, $fromDate, $fromTime)
{
    $bettingRef = new Betting();
    return $bettingRef->getFollowBetsByUser($followToUserID, $followerID, $fromDate, $fromTime, [1])->sum('rollingAmount');
}

function totalFollowLoseAmount($followToUserID, $followerID, $fromDate, $fromTime)
{
    $bettingRef = new Betting();
    return $bettingRef->getFollowBetsByUser($followToUserID, $followerID, $fromDate, $fromTime, [0])->sum('betAmount');
}
