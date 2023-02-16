<?php

$app->get("/v1/transaction/{type}", function ($request, $response, $args) {

    // cari jumlah transaksi berdasarkan jenis (ppob dan non-ppob)
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];

    $dat  = array();
    $pgSql = new PgSql();
    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT count(*) jumlah, coalesce(sum(grand_total),0) gt FROM public.transaction where status='".$payment_status[$i]."'";
            $temp =  $ndb->query($sql);
            $jumlah = $temp->fetch(\PDO::FETCH_ASSOC);
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$jumlah["jumlah"], "total"=>$jumlah["gt"]));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT count(*) jumlah, coalesce(sum(grand_total) gt FROM public.transaction where status='".$payment_status[$i]."'";
            $temp =  $ndb->query($sql);
            $jumlah = $temp->fetch(\PDO::FETCH_ASSOC);
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$jumlah["jumlah"], "total"=>$jumlah["gt"]));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Counting Transaction";
    $data['transaction-type'] = $type;
    $data["data"] = $dat;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$app->get("/v1/transaction/today/{type}", function ($request, $response, $args) {

    // cari data transaksi berdasarkan jenis (ppob dan non-ppob) pada hari yg sedang berjalan
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];

    $dat  = array();
    $pgSql = new PgSql();
    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, trx_id, trx_no, sub_total, grand_total, status, paid_by, created_at FROM public.transaction where status='".$payment_status[$i]."' and created_at=CURRENT_DATE order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, guid, transaction_no, sub_total, grand_total, status, paid_by, merchant_id, created_at FROM public.transaction where status='".$payment_status[$i]."' and created_at=CURRENT_DATE and is_deleted=0 order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "guid"=>$row["guid"], "trx_no"=>$row["transaction_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "merchant_id"=>$row["merchant_id"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Sales Today";
    $data["date"] = date("l Y-m-d");
    $data['transaction-type'] = $type;
    $data["data"] = $dat;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$app->get("/v1/transaction/thisweek/{type}", function ($request, $response, $args) {

    // cari data transaksi berdasarkan jenis (ppob dan non-ppob) pada minggu yg sedang berjalan
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];

    $dat  = array();
    $pgSql = new PgSql();
    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, trx_id, trx_no, sub_total, grand_total, status, paid_by, created_at FROM public.transaction where status='".$payment_status[$i]."' and created_at >= date_trunc('week',CURRENT_DATE) order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, guid, transaction_no, sub_total, grand_total, status, paid_by, merchant_id, created_at FROM public.transaction where status='".$payment_status[$i]."' and created_at >= date_trunc('week',CURRENT_DATE) and is_deleted=0 order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "guid"=>$row["guid"], "trx_no"=>$row["transaction_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "merchant_id"=>$row["merchant_id"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Sales This Week";
    $data['transaction-type'] = $type;
    $data["data"] = $dat;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$app->get("/v1/transaction/thismonth/{type}", function ($request, $response, $args) {

    // cari data transaksi berdasarkan jenis (ppob dan non-ppob) pada bulan yg sedang berjalan
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];

    $dat  = array();
    $pgSql = new PgSql();
    $m = date("m");
    $Y = date("Y");

    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, trx_id, trx_no, sub_total, grand_total, status, paid_by, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'MM')='".$m."' and TO_CHAR(created_at, 'YYYY')='".$Y."' order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, guid, transaction_no, sub_total, grand_total, status, paid_by, merchant_id, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'MM')='".$m."' and TO_CHAR(created_at, 'YYYY')='".$Y."' and is_deleted=0 order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "guid"=>$row["guid"], "trx_no"=>$row["transaction_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "merchant_id"=>$row["merchant_id"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Sales This Month";
    $data["Month-Year"] = $m."-".$Y;
    $data['transaction-type'] = $type;
    $data["data"] = $dat;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$app->get("/v1/transaction/thisyear/{type}", function ($request, $response, $args) {

    // cari data transaksi berdasarkan jenis (ppob dan non-ppob) pada bulan yg sedang berjalan
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];

    $dat  = array();
    $pgSql = new PgSql();
    $Y = date("Y");

    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, trx_id, trx_no, sub_total, grand_total, status, paid_by, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'YYYY')='".$Y."' order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, guid, transaction_no, sub_total, grand_total, status, paid_by, merchant_id, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'YYYY')='".$Y."' and is_deleted=0 order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "guid"=>$row["guid"], "trx_no"=>$row["transaction_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "merchant_id"=>$row["merchant_id"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Sales This Year";
    $data["Year"] = $Y;
    $data['transaction-type'] = $type;
    $data["data"] = $dat;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$app->get("/v1/transaction/bymonth/{bln}/{thn}/{type}", function ($request, $response, $args) {

    // cari data transaksi berdasarkan jenis (ppob dan non-ppob) pada bulan tertentu
    $bln = $args["bln"];
    $thn = $args["thn"];
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];

    $dat  = array();
    $pgSql = new PgSql();

    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, trx_id, trx_no, sub_total, grand_total, status, paid_by, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'YYYY')='".$thn."' and TO_CHAR(created_at, 'MM')='".$bln."' order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $sql = "SELECT id, guid, transaction_no, sub_total, grand_total, status, paid_by, merchant_id, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'YYYY')='".$thn."' and TO_CHAR(created_at, 'MM')='".$bln."' and is_deleted=0 order by id desc";
            $temp =  $ndb->query($sql)->fetchAll();
            $trans = array();
            $j = 0;
            foreach ($temp as $row) {
                array_push($trans, array("id"=>$row["id"], "guid"=>$row["guid"], "trx_no"=>$row["transaction_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "merchant_id"=>$row["merchant_id"], "created_at"=>$row["created_at"] ));
                $j = $j+1;
            }            
            array_push($dat, array("state"=>$payment_status[$i], "records"=>$j, "data"=>$trans));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Sales By Month";
    $data["Month"] = $bln;
    $data["Year"] = $thn;
    $data['transaction-type'] = $type;
    $data["data"] = $dat;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});

$app->get("/v1/transaction/byyear/{thn}/{type}", function ($request, $response, $args) {

    // cari data transaksi berdasarkan jenis (ppob dan non-ppob) pada bulan tertentu
    $thn = $args["thn"];
    $type = $args["type"];

    $payment_status = ["SUBMIT", "WAITING_PAYMENT", "PENDING", "PAID", "INPROGRESS", "SUCCESS", "EXPIRED", "REFUND_PROCESSING", "REFUND", "FAILED"];
    $month_name = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "Desember"];

    $pgSql = new PgSql();

    $datYear = array();
    if ($type == "ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBppob"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $dat  = array();
            for ($j=0; $j<count($month_name); $j++) {
                $m = $j + 1;
                $b = "";
                if ($m<10) {
                    $b = "0".$b;
                }
                $sql = "SELECT id, trx_id, trx_no, sub_total, grand_total, status, paid_by, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'YYYY')='".$thn."' and TO_CHAR(created_at, 'MM')='".$b."' order by id desc";
                $temp =  $ndb->query($sql)->fetchAll();
                $trans = array();
                $k = 0;
                foreach ($temp as $row) {
                    array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                    $k = $k+1;
                }            
                array_push($dat, array("month name"=>$month_name[$j], "records"=>$k, "data"=>$trans));
            }
            array_push($datYear, array("Payment_Status"=>$payment_status[$i], "transaction-list"=>$dat));
        }
    } 
    if ($type == "non-ppob") {
        $ndb = $pgSql->create($_ENV["STAG_DBtrx"]);
        for ($i=0; $i<count($payment_status); $i++) {
            $dat  = array();
            for ($j=0; $j<count($month_name); $j++) {
                $m = $j + 1;
                $b = "";
                if ($m<10) {
                    $b = "0".$b;
                }
                $sql = "SELECT id, guid, transaction_no, sub_total, grand_total, status, paid_by, merchant_id, created_at FROM public.transaction where status='".$payment_status[$i]."' and TO_CHAR(created_at, 'YYYY')='".$thn."' and TO_CHAR(created_at, 'MM')='".$b."' and is_deleted=0 order by id desc";
                $temp =  $ndb->query($sql)->fetchAll();
                $trans = array();
                $k = 0;
                foreach ($temp as $row) {
                    array_push($trans, array("id"=>$row["id"], "trx_id"=>$row["trx_id"], "trx_no"=>$row["trx_no"], "sub_total"=>$row["sub_total"], "grand_total"=>$row["grand_total"], "status"=>$row["status"], "paid_by"=>$row["paid_by"], "created_at"=>$row["created_at"] ));
                    $k = $k+1;
                }            
                array_push($dat, array("month name"=>$month_name[$j], "records"=>$k, "data"=>$trans));
            }
            array_push($datYear, array("Payment_Status"=>$payment_status[$i], "transaction-list"=>$dat));
        }
    } 

    $ndb=null;

    $data["status"] = "ok";
    $data["stat-operation"] = "Sales By Year";
    $data["Year"] = $thn;
    $data['transaction-type'] = $type;
    $data["data"] = $datYear;

    return $response->withStatus(201)
    ->withHeader("Content-Type", "application/json")
    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});