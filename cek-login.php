<?php
session_start(); // harus ada di bagian paling atas kode
$path_to_root = "";
include $path_to_root . 'database.php';

// Object database class
$db = new database();

$user = strip_tags(trim($_POST['username']));
$pass = strip_tags(trim($_POST['password']));

$sql = get_sql_login_admin_page($user, $pass);
$result = $db->db_query($sql);
$num_rows = $db->db_num_rows($result);

if ($num_rows > 0) {
    $rows = $db->db_fetch_array($result);

    unset($_POST); // hapus post form
    $_SESSION['apriori_penjualan_id'] = $rows['id']; // mengisi session
    $_SESSION['apriori_penjualan_username'] = $rows['username'];
    $_SESSION['apriori_penjualan_level'] = $rows['level'];

    $level_name = ($_SESSION['apriori_penjualan_level'] == 1) ? "admin" : "kepala";
    $_SESSION['apriori_penjualan_level_name'] = $level_name;
    $_SESSION['apriori_penjualan_key'] = sha1(date("Y-m-d H:i:s") . $rows['id']);
    $_SESSION['apriori_penjualan_last_login'] = date("d-m-Y H:i:s");
    header("Location: index.php");
} else {
    $_SESSION['login_error'] = 'Username atau password salah';
    header("Location: login.php");
}

/**
 * query get login 
 * @param string $user username
 * @param string $pass password
 * @return string
 */
function get_sql_login_admin_page($user, $pass) {
    $sql = "SELECT * FROM users WHERE username = '" . $user . "' AND password = MD5('" . $pass . "')";
    return $sql;
}
?>
