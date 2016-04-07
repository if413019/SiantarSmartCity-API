<?php

/**
 * @author Yohanes Marthin Hutabarat
 */

require_once 'include/DB_Functions.php';
$db = new DB_Functions();

// json response array
//$response = array("error" => FALSE);
$response = array();

    // get a product from products table
    $result = $db->getPengaduan();
    if ($result != null) {
        // check for empty result
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $product = array();
            $product["judul"]               = $row["judul"];
            $product["deskripsi"]           = $row["deskripsi_pengaduan"];
            $product["alamat"]              = $row["alamat_pengaduan"];
            $product["tanggal_pengaduan"]   = $row["tanggal_pengaduan"];
            $product["dinas_skpd"]          = $row["id_dinas_skpd"];

            // push single product into final response array
            array_push($response, $product);
        }
        //$response["success"] = 1; 

        // echoing JSON response
        echo json_encode($response);
        
    }else {
        // no product found
        $response["success"] = 0;
        $response["message"] = "Tidak ada pengaduan";
        // echo no users JSON
        echo json_encode($response);
    }
?>
