<?php


$app->get("/v1/users", function ($request, $response, $args) {

    // mengambil jumlah seluruh user pengguna app
    $pgSql = new PgSql();
    $ndb = $pgSql->create($_ENV["STAG_DBuser"]);
    $sql = "SELECT count(*) jumlah FROM public.user";
    $temp =  $ndb->query($sql);
    $jumlah = $temp->fetch(\PDO::FETCH_ASSOC);
    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Counting User";
    $data["value"] = $jumlah["jumlah"];

    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});