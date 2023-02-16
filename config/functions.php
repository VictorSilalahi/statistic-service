<?php

// use Micheh\Cache\CacheUtil;
use Firebase\JWT\JWT;

function getUserId($token)
{
    $t = str_replace("Bearer ","",$token);
    $dat = JWT::decode($t, getenv("JWT_SECRET"), array('HS256'));
    return $dat->uid;
}

function getUserName($token)
{
    $t = str_replace("Bearer ","",$token);
    $dat = JWT::decode($t, getenv("JWT_SECRET"), array('HS256'));
    return $dat->sub;
}


// function makeCache() {
//     return new CacheUtil;
// }