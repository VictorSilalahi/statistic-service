<?php

$app->get("/v1/products/{cid}", function ($request, $response, $args) {

    // mengambil jumlah outlets dari tiap jenis outlet category
    $cid = $args["cid"];

    // Nilai cid
    // 1 : Hotel
    // 2 : Cafe & Resto
    // 3 : Spa

    $pgSql = new PgSql();
    $ndb = $pgSql->create($_ENV["STAG_DBproduct"]);
    $sql = "SELECT count(*) jumlah FROM public.outlet where category_id=".$cid;
    $temp =  $ndb->query($sql);
    $jumlah = $temp->fetch(\PDO::FETCH_ASSOC);
    $sql = "SELECT category_name nama FROM public.outlet_category where id=".$cid;
    $temp =  $ndb->query($sql);
    $nama = $temp->fetch(\PDO::FETCH_ASSOC);
    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Counting Outlets";
    $data['outlet-name'] = $nama["nama"];
    $data["value"] = $jumlah["jumlah"];

    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

});

$app->get("/v1/countproducts/{type}", function ($request, $response, $args) {

    // mengambil jumlah product dari ppob dan non-ppob
    $type = $args["type"];

    // Nilai type :  ppob dan non-ppob

    $dat  = array();
    $pgSql = new PgSql();
    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        $sql = "select count( distinct product_id ) jumlah from public.product";
        $temp =  $ndb->query($sql);
        $jumlah = $temp->fetch(\PDO::FETCH_ASSOC);
        array_push($dat, array("records"=>$jumlah["jumlah"]));
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBproduct"]);
        $sql = "SELECT count( distinct guid ) jumlah from public.product";
        $temp =  $ndb->query($sql);
        $jumlah = $temp->fetch(\PDO::FETCH_ASSOC);
        array_push($dat, array("records"=>$jumlah["jumlah"]));
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Total products in ".$type;
    $data['product-category'] = $type;
    $data["value"] = $jumlah["jumlah"];

    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

});