<?php

/*
 * Class Configuration Database
 */
class Database {

    private $servername;
    private $user_db;
    private $password_db;
    private $database;
    private $koneksi;
    

    /**
     * Constructor untuk memulai koneksi ke database
     */
    function __construct() {
        $this->load_conf_db();
        $this->connect_db();
    }

    function load_conf_db() {
        $path = dirname(__FILE__) . '/koneksi.php';
        if (file_exists($path)) {
            $conf = include $path;
            $this->servername = @$conf['host'];
            $this->database = @$conf['dbname'];
            $this->user_db = @$conf['username'];
            $this->password_db = @$conf['password'];
        }
    }
    
    /**
     * Membuat koneksi ke database
     */
    public function connect_db() {
        $this->koneksi = new mysqli($this->servername, $this->user_db, $this->password_db, $this->database);
        
        // Memeriksa koneksi
        if ($this->koneksi->connect_error) {
            die("Koneksi gagal: " . $this->koneksi->connect_error);
        }
    }

    /**
     * Mengembalikan objek koneksi
     * @return mysqli
     */
    public function get_connection() {
        return $this->koneksi;
    }

    /**
     * Eksekusi query MySQL
     * @param string $sql
     * @param array $params Optional parameters for prepared statement
     * @return mixed
     */
    function db_query($sql, $params = array()) {
        try {
            $statement = $this->koneksi->prepare($sql);

            if (!empty($params)) {
                // Bind parameters for prepared statement
                $types = ''; // Types string for bind_param
                $bind_params = array(); // Parameters array for bind_param

                foreach ($params as $param) {
                    $types .= $this->get_bind_type($param); // Determine bind type
                    $bind_params[] = &$param; // Reference to parameter for bind_param
                }

                array_unshift($bind_params, $types); // Add types as first element
                call_user_func_array(array($statement, 'bind_param'), $bind_params); // Bind parameters
            }

            $statement->execute(); // Execute prepared statement
            $result = $statement->get_result(); // Get result set from executed statement

            return $result; // Return result set
        } catch (Exception $e) {
            // Handle exception (e.g., log it, display error message, etc.)
            return false; // Return false on error
        }
    }

    /**
     * Get bind_param type for variable
     * @param mixed $var
     * @return string
     */
    private function get_bind_type($var) {
        if (is_int($var)) return 'i'; // Integer type
        elseif (is_float($var)) return 'd'; // Double type
        elseif (is_string($var)) return 's'; // String type
        else return 'b'; // Blob or unknown type
    }

    /**
     * Mendapatkan error dari koneksi MySQLi
     * @return string
     */
    function db_error() {
        return $this->koneksi->error;
    }

    /**
     * Mengambil hasil query sebagai array asosiatif atau numerik
     * @param mysqli_result $result Hasil dari fungsi mysqli_query()
     * @return array|false Array asosiatif atau numerik, atau false jika tidak ada baris lagi
     */
    function db_fetch_array($result) {
        try {
            if ($result instanceof mysqli_result) {
                return mysqli_fetch_array($result, MYSQLI_BOTH);
            } else {
                throw new Exception('Parameter harus berupa objek mysqli_result.');
            }
        } catch (Exception $e) {
            // Handle exception (e.g., log it, display error message, etc.)
            return false; // Return false or handle error as appropriate
        }
    }

    /**
     * Menghitung jumlah baris dalam hasil query
     * @param mysqli_result $result Hasil dari fungsi mysqli_query()
     * @return int Jumlah baris dalam hasil query
     */
    function db_num_rows($result) {
        try {
            if ($result instanceof mysqli_result) {
                return mysqli_num_rows($result);
            } else {
                throw new Exception('Parameter harus berupa objek mysqli_result.');
            }
        } catch (Exception $e) {
            // Handle exception (e.g., log it, display error message, etc.)
            return -1; // Return -1 or handle error as appropriate
        }
    }

    /**
     * Mendapatkan ID terakhir yang di-insert
     * @return int
     */
    function db_insert_id() {
        return mysqli_insert_id($this->koneksi);
    }

    /**
     * Menutup koneksi database
     */
    function close() {
        $this->koneksi->close();
    }

    /**
     * Cek value data pada suatu table
     * @param string $table
     * @param string $field
     * @param string $value
     * @return boolean true jika ada, false jika tidak ada
     */
    function cek_data_is_in_table($table, $field, $value) {
        $sql = "SELECT COUNT(" . $field . ") FROM " . $table . " WHERE " . $field . " = '" . $value . "'";
        $result = $this->db_query($sql);
        $num = $this->db_fetch_array($result);

        if ($num[0] > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Mendaftarkan pengguna baru
     * @param string $username
     * @param string $password
     * @return bool True jika berhasil, false jika gagal
     */
    public function register_user($username, $password) {
        // Periksa apakah username sudah ada
        if ($this->cek_data_is_in_table('login', 'username', $username)) {
            return false; // Username sudah ada
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Simpan pengguna baru ke database
        $data = array(
            'username' => $username,
            'password' => $hashed_password
        );

        return $this->insert_record('users', $data);
    }
    /**
     * Ambil data login user
     * @param type $id_login
     * @return type
     */
    function get_login_by_id($id_login){
        $sql = "SELECT * FROM login WHERE id_login = " . $id_login;
        $result = $this->db_query($sql);
        return $this->db_fetch_array($result);
    }
    /**
     * Menampilkan semua kolom dari sebuah tabel dengan kondisi opsional
     * @param string $table Nama tabel
     * @param string $where Kondisi WHERE
     * @param boolean $join Penggunaan JOIN (tidak digunakan di sini)
     * @param boolean $group_by Penggunaan GROUP BY (tidak digunakan di sini)
     * @param int $offset Offset untuk paginasi
     * @param int $limit Batasan jumlah baris yang diambil
     * @param string $order_by Kolom untuk pengurutan
     * @return mixed Hasil dari query
     */
    public function display_table_all_column($table, $where = '', $join = false, $group_by = false, $offset = 0, $limit = 0, $order_by = 'id') {
        $query = "SELECT * FROM $table";

        if ($where) {
            $query .= " WHERE $where";
        }

        if ($order_by) {
            $query .= " ORDER BY $order_by";
        }

        if ($limit > 0) {
            $query .= " LIMIT $offset, $limit";
        }

        return $this->db_query($query);
    }

    /**
     * Update record dalam tabel
     * @param string $table Nama tabel
     * @param array $data Data yang akan diperbarui (associative array)
     * @param string $where Kondisi WHERE
     * @return bool True jika berhasil, false jika gagal
     */
    public function update_record($table, $data, $where) {
        $fields = array();
        $params = array();

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        $field_list = implode(', ', $fields);
        $sql = "UPDATE $table SET $field_list WHERE $where";

        return $this->db_query($sql, $params);
    }

    /**
     * Insert record ke dalam tabel
     * @param string $table Nama tabel
     * @param array $data Data yang akan dimasukkan (associative array)
     * @return bool True jika berhasil, false jika gagal
     */
    public function insert_record($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        $params = array_values($data);

        $field_list = implode(', ', $fields);
        $placeholder_list = implode(', ', $placeholders);
        $sql = "INSERT INTO $table ($field_list) VALUES ($placeholder_list)";

        return $this->db_query($sql, $params);
    }
}
?>
