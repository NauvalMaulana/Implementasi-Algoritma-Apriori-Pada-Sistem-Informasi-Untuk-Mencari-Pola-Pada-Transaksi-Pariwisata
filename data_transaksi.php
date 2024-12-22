<?php
//session_start();
if (!isset($_SESSION['apriori_penjualan_id'])) {
    header("location:index.php?menu=forbidden");
    exit();
}

include_once "database.php";
include_once "fungsi.php";

$db_object = new database();
$id = $_SESSION['apriori_penjualan_id']; // ID user yang login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_manual'])) {
        if (isset($_POST['transaction_date']) && isset($_POST['produk'])) {
            $transaction_date = format_date($_POST['transaction_date']);
            $produk = str_replace([" ,", "  ,", "   ,", "    ,", ", ", ",  ", ",   ", ",    "], ",", $_POST['produk']);
            
            // Menyimpan data transaksi ke tabel transaksi
            $sql = "INSERT INTO transaksi (id, transaction_date, produk) VALUES ('$id', '$transaction_date', '$produk')";
            $db_object->db_query($sql);
            
            // Mendapatkan ID transaksi terakhir yang baru saja dimasukkan
            $last_transaction_id = $db_object->db_insert_id();
            
            // Menyimpan data itemset ke tabel itemset
            $produk_array = explode(',', $produk);
            foreach ($produk_array as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $sql_itemset = "INSERT INTO itemset (id_transaksi, produk) VALUES ('$last_transaction_id', '$item')";
                    $db_object->db_query($sql_itemset);
                }
            }
            
            echo '<script>location.replace("?menu=data_transaksi&pesan_success=Data berhasil disimpan");</script>';
        } else {
            echo '<script>location.replace("?menu=data_transaksi&pesan_error=Data tidak lengkap");</script>';
        }
    }

    if (isset($_POST['delete'])) {
        if (!empty($_POST['selected_ids'])) {
            $selected_ids = $_POST['selected_ids'];
            $ids_to_delete = implode(',', array_map('intval', $selected_ids));
            
            // Menghapus data dari tabel transaksi
            $sql = "DELETE FROM transaksi WHERE id_transaksi IN ($ids_to_delete)";
            $db_object->db_query($sql);
            
            // Menghapus data dari tabel itemset yang terkait dengan transaksi yang dihapus
            $sql_itemset = "DELETE FROM itemset WHERE id_transaksi IN ($ids_to_delete)";
            $db_object->db_query($sql_itemset);
            
            echo '<script>location.replace("?menu=data_transaksi&pesan_success=Data transaksi berhasil dihapus");</script>';
        } else {
            echo '<script>location.replace("?menu=data_transaksi&pesan_error=Tidak ada data yang dipilih untuk dihapus");</script>';
        }
    }

    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $csv_file = $_FILES['csv_file']['tmp_name'];
        $file = fopen($csv_file, 'r');
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            $transaction_date = format_date($data[0]);
            $produk = str_replace([" ,", "  ,", "   ,", "    ,", ", ", ",  ", ",   ", ",    "], ",", $data[1]);

            $sql = "INSERT INTO transaksi (id, transaction_date, produk) VALUES ('$id', '$transaction_date', '$produk')";
            $db_object->db_query($sql);

            $last_transaction_id = $db_object->db_insert_id();
            $produk_array = explode(',', $produk);
            foreach ($produk_array as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $sql_itemset = "INSERT INTO itemset (id_transaksi, produk) VALUES ('$last_transaction_id', '$item')";
                    $db_object->db_query($sql_itemset);
                }
            }
        }
        fclose($file);
        echo '<script>location.replace("?menu=data_transaksi&pesan_success=Data berhasil diimport");</script>';
    }
}

$pesan_error = $pesan_success = "";
if (isset($_GET['pesan_error'])) {
    $pesan_error = htmlspecialchars($_GET['pesan_error']);
}
if (isset($_GET['pesan_success'])) {
    $pesan_success = htmlspecialchars($_GET['pesan_success']);
}

$sql = "SELECT * FROM transaksi WHERE id = '$id'";
$query = $db_object->db_query($sql);
$jumlah = $db_object->db_num_rows($query);
?>
<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            <div class="page-header">
                <h1 class="text-success fw-bold">
                    Data Transaksi
                </h1>
            </div><!-- /.page-header -->

            <div class="row">
                <div class="col-sm-4">
                    <div class="widget-box">
                        <!--MANUAL INPUT FORM-->
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="widget-body">
                                <div class="widget-main">
                                    <div class="form-group">
                                        <label for="transaction_date">Tanggal Transaksi</label>
                                        <input type="date" id="transaction_date" name="transaction_date" required class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label for="produk">Asal Wisatawan</label>
                                        <input type="text" id="produk" name="produk" required class="form-control" />
                                    </div>
                                    <button name="submit_manual" type="submit" class="btn btn-app btn-primary btn-sm">
                                        <i class="ace-icon fa fa-plus bigger-200"></i> Tambah
                                    </button>

                                    <button name="import_csv" type="button" class="btn btn-app btn-success btn-sm" onclick="document.getElementById('csv_file').click();">
                                        <i class="ace-icon fa fa-upload bigger-200"></i> Import CSV
                                    </button>
                                    <input type="file" id="csv_file" name="csv_file" style="display:none;" onchange="this.form.submit();" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="widget-box">
                        <div class="widget-body">
                            <div class="widget-main">
                                <?php
                                if (!empty($pesan_error)) {
                                    display_error($pesan_error);
                                }
                                if (!empty($pesan_success)) {
                                    display_success($pesan_success);
                                }

                                echo "Jumlah data: " . $jumlah . "<br>";
                                if ($jumlah == 0) {
                                    echo "Data kosong...";
                                } else {
                                ?>
                                    <form method="post" action="">
                                        <table class='table table-bordered table-striped table-hover'>
                                            <tr>
                                                <th>No</th>
                                                <th>Tanggal</th>
                                                <th>Asal Wisatawan</th>
                                                <th>Pilih</th>
                                            </tr>
                                            <?php
                                            $no = 1;
                                            while ($row = $db_object->db_fetch_array($query)) {
                                                echo "<tr>";
                                                echo "<td>" . $no . "</td>";
                                                echo "<td>" . format_date2($row['transaction_date']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['produk']) . "</td>";
                                                echo "<td><input type='checkbox' name='selected_ids[]' value='" . $row['id_transaksi'] . "'></td>";
                                                echo "</tr>";
                                                $no++;
                                            }
                                            ?>
                                        </table>
                                        <button name="delete" type="submit" class="btn btn-app btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            <i class="ace-icon fa fa-trash-o bigger-200"></i> Delete
                                        </button>
                                    </form>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
