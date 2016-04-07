<?php

/**
 * @author Yohanes Marthin Hutabarat
 */

class DB_Functions {

    private $conn;

    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }

    // destructor
    function __destruct() {
        
    }

    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $nomor_ktp, $nomor_telepon, $alamat, $username, $email, $password) {
        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt

        $stmt = $this->conn->prepare("INSERT INTO user(username, auth_key, password_hash, email, unique_id, created_at, updated_at) VALUES(?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssss", $name, $salt, $encrypted_password, $email, $uuid);
        $result = $stmt->execute();
        $stmt->close();

        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return $user;
        } else {
            return false;
        }
    }

    /**
     * Get user by email and password
     */
    public function getUserByUsernameAndPassword($email, $password) {

        $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ?");

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // verifying user password
            $salt = $user['auth_key'];
            $encrypted_password = $user['password_hash'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $stmt = $this->conn->prepare("SELECT email from user WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }

    /**
     * Check user is existed or not
     */
    public function isPengaduanExisted($judul) {
        $stmt = $this->conn->prepare("SELECT judul from pengaduan WHERE judul = ?");

        $stmt->bind_param("s", $judul);

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }

    /**
     * Storing new Pengaduan
     * returns Pengaduan details
     */
    public function storePengaduan($judul, $deskripsi, $alamat, $masyarakat, $desa, $dinas_skpd, $image) {
        
        $isVerified = 1;
        $uuid = uniqid('', true); 
        $path = "uploads/$uuid.png";
        if($image == null){
            $stmt = $this->conn->prepare("INSERT INTO pengaduan(judul, deskripsi_pengaduan, alamat_pengaduan, isverified, tanggal_pengaduan, id_masyarakat, id_desa, id_dinas_skpd) VALUES(?, ?, ?, ?, NOW(), ?, ?,?,?)");
            $stmt->bind_param("ssssiii", $judul, $deskripsi, $alamat, $isVerified, $masyarakat, $desa, $dinas_skpd);
        }
        else{
            $stmt = $this->conn->prepare("INSERT INTO pengaduan(judul, deskripsi_pengaduan, alamat_pengaduan, isverified, tanggal_pengaduan, id_masyarakat, id_desa, id_dinas_skpd, gambar) VALUES(?, ?, ?, ?, NOW(), ?, ?,?,?)");
            $stmt->bind_param("ssssiiis", $judul, $deskripsi, $alamat, $isVerified, $masyarakat, $desa, $dinas_skpd, $path);
        }        
        $result = $stmt->execute();
        $stmt->close();

        // check for successful store
        if ($result) {
            if($image != null){
                file_put_contents($path, base64_decode($image));
            }

            $stmt = $this->conn->prepare("SELECT * FROM pengaduan WHERE judul = ?");
            $stmt->bind_param("s", $judul);
            $stmt->execute();
            $pengaduan = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $pengaduan;
        } else {
            return false;
        }
    }



    public function getSkpdById($id){
        $stmt = $this->conn->prepare("SELECT * FROM dinas_skpd WHERE id_dinas_skpd = ?");
        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
            $skpd = $stmt->get_result()->fetch_assoc();
            $stmt->close();
                return $skpd;
        } else {
            return NULL;
        }
    }
    /**
     * Getting All Pengaduan
     * returns array of pengaduan
     */
    public function getPengaduan() {
        $arrayofpengaduan;
        $stmt = $this->conn->prepare("SELECT * FROM pengaduan ORDER BY id_pengaduan DESC");
        $stmt->execute();
        $arrayofpengaduan = $stmt->get_result();
        $stmt->close();
        return $arrayofpengaduan;
    }

    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

}

?>
