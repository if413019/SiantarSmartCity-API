<?php

/**
 * @author Yohanes Marthin Hutabarat
 */

require_once 'include/DB_Functions.php';
$db = new DB_Functions();

// json response array
$response = array("error" => FALSE);

if (isset($_POST['judul']) && isset($_POST['deskripsi']) && isset($_POST['alamat'])) {

    // receiving the post params
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $alamat = $_POST['alamat'];
    $image = $_POST['image'];
    $masyarakat = 1;
    $desa = 1;
    $dinas_skpd = 1;

    // check if user is already existed with the same email
    if ($db->isPengaduanExisted($judul)) {
        // user already existed
        $response["error"] = TRUE;
        $response["error_msg"] = "Pengaduan sudah terdaftar sebelumnya dengan judul: " . $judul;
        echo json_encode($response);
    } else {
        // create a new 
        $pengaduan = $db->storePengaduan($judul, $deskripsi, $alamat, $masyarakat, $desa, $dinas_skpd, $image);
        if ($pengaduan) {
            // user stored successfully
            $response["error"] = FALSE;
            $response["pengaduan"]["id"] = $pengaduan["id_pengaduan"];
            $response["pengaduan"]["judul"] = $pengaduan["judul"];
            $response["pengaduan"]["deskripsi"] = $pengaduan["deskripsi_pengaduan"];
            $response["pengaduan"]["tanggal"] = $pengaduan["tanggal_pengaduan"];
            $response["pengaduan"]["masyarakat"] = $pengaduan["id_masyarakat"];
            echo json_encode($response);
        } else {
            // user failed to store
            $response["error"] = TRUE;
            $response["error_msg"] = "Unknown error occurred in store New Pengaduan!";
            echo json_encode($response);
        }
    }
} else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters (name, email or password) is missing!";
    echo json_encode($response);
}
?>

