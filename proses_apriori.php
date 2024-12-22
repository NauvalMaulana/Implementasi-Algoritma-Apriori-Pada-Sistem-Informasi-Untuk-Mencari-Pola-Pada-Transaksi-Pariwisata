<?php
if (!isset($_SESSION['apriori_penjualan_id'])) {
    header("location:index.php?menu=forbidden");
    exit();
}

include_once "database.php";
include_once "fungsi.php";
include_once "mining.php";
include_once "display_mining.php";
?>
<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            <div class="page-header">
                <h1>Proses Apriori</h1>
            </div>

            <?php
            $db_object = new database();

            $pesan_error = $pesan_success = "";
            if (isset($_GET['pesan_error'])) {
                $pesan_error = $_GET['pesan_error'];
            }
            if (isset($_GET['pesan_success'])) {
                $pesan_success = $_GET['pesan_success'];
            }

            if (isset($_POST['submit'])) {
                $can_process = true;
                if (empty($_POST['min_support']) || empty($_POST['min_confidence'])) {
                    $can_process = false;
            ?>
                    <script>
                        location.replace("?sidebar=proses_apriori&pesan_error=Min Support dan Min Confidence harus diisi");
                    </script>
                <?php
                }
                if (!is_numeric($_POST['min_support']) || !is_numeric($_POST['min_confidence'])) {
                    $can_process = false;
                ?>
                    <script>
                        location.replace("?sidebar=proses_apriori&pesan_error=Min Support dan Min Confidence harus diisi angka");
                    </script>
                <?php
                }

                if ($can_process) {
                    $tgl = explode(" - ", $_POST['range_tanggal']);
                    $start = format_date($tgl[0]);
                    $end = format_date($tgl[1]);

                    if (isset($_POST['id_process'])) {
                        $id_process = $_POST['id_process'];
                        reset_hitungan($db_object, $id_process);

                        $field = array(
                            "start_date" => $start,
                            "end_date" => $end,
                            "min_support" => $_POST['min_support'],
                            "min_confidence" => $_POST['min_confidence']
                        );
                        $where = array("id" => $id_process);
                        $query = $db_object->update_record("process_log", $field, $where);
                    } else {
                        $field_value = array(
                            "start_date" => $start,
                            "end_date" => $end,
                            "min_support" => $_POST['min_support'],
                            "min_confidence" => $_POST['min_confidence']
                        );
                        $query = $db_object->insert_record("process_log", $field_value);
                        $id_process = $db_object->db_insert_id();
                    }
                ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group ml-3">
                                            <label>Tanggal: </label>
                                            <div class="input-group mb-2">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                </div>
                                                <input type="text" class="form-control" name="range_tanggal" id="id-date-range-picker-1" placeholder="Date range" value="<?php echo isset($_POST['range_tanggal']) ? $_POST['range_tanggal'] : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group col-auto">
                                            <input name="search_display" type="submit" value="Search" class="btn btn-primary mb-2">
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <input name="min_support" type="text" class="form-control" placeholder="Min Support" value="<?php echo $_POST['min_support']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <input name="min_confidence" type="text" class="form-control" placeholder="Min Confidence" value="<?php echo $_POST['min_confidence']; ?>">
                                        </div>
                                        <input type="hidden" name="id_process" value="<?php echo $id_process; ?>">
                                        <div class="form-group">
                                            <input name="submit" type="submit" value="Proses" class="btn btn-success">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php
                    // Get the input values
                    $min_support_input = $_POST['min_support'];
                    $min_confidence_input = $_POST['min_confidence'];
                    $range_tanggal = $_POST['range_tanggal'];

                    // Output the absolute minimum support value
                    echo "Min Support Absolut: " . $min_support_input;
                    echo "<br>";

                    // Get the total number of transactions within the specified date range
                    $sql = "SELECT COUNT(*) FROM transaksi WHERE transaction_date BETWEEN '$start' AND '$end'";
                    $res = $db_object->db_query($sql);
                    $num = $db_object->db_fetch_array($res);
                    $total_transactions = $num[0];

                    // Calculate and output the relative minimum support value
                    $minSupportRelatif = ($min_support_input / $total_transactions) * 100;
                    echo "Min Support Relatif: " . $minSupportRelatif;
                    echo "<br>";

                    // Output the minimum confidence value
                    echo "Min Confidence: " . $min_confidence_input;
                    echo "<br>";

                    // Output the date range
                    echo "Start Date: " . $range_tanggal;
                    echo "<br>";

                    // Start calculating confidence
                    // Get the support counts from itemsets
                    $sql_itemset1 = "SELECT COUNT(*) AS support1 FROM itemset1";
                    $res_itemset1 = $db_object->db_query($sql_itemset1);
                    $support1 = $db_object->db_fetch_array($res_itemset1)[0];

                    $sql_itemset2 = "SELECT COUNT(*) AS support2 FROM itemset2";
                    $res_itemset2 = $db_object->db_query($sql_itemset2);
                    $support2 = $db_object->db_fetch_array($res_itemset2)[0];

                    if ($support1 > 0) {
                        // Calculate confidence
                        $confidence = ($support2 / $support1) * 100;
                        echo "Confidence: " . $confidence . "%";
                    } else {
                        echo "Tidak cukup data untuk menghitung confidence.";
                    }

                    // Call mining_process function
                    $result = mining_process($db_object, $_POST['min_support'], $_POST['min_confidence'], $start, $end, $id_process);
                    if ($result) {
                        $successMessage = "Proses mining selesai";
                        echo "
                        <div class='modal fade' id='successModal' tabindex='-1' role='dialog' aria-labelledby='successModalLabel' aria-hidden='true'>
                            <div class='modal-dialog' role='document'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='successModalLabel'>Success</h5>
                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                    </div>
                                    <div class='modal-body'>
                                        $successMessage
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                        <button type='button' class='btn btn-primary' onclick='printResult()'>Print</button>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>";

                        echo "
                        <script>
                            $(document).ready(function() {
                                $('#successModal').modal('show');
                            });

                            function printResult() {
                                var printContents = document.getElementById('resultContent').innerHTML;
                                var originalContents = document.body.innerHTML;
                                document.body.innerHTML = printContents;
                                window.print();
                                document.body.innerHTML = originalContents;
                                location.reload();
                            }
                        </script>";

                        echo "<div id='resultContent' style='display:none;'>";
                        echo "<h2>Hasil Apriori</h2>";
                        echo "Min Support Absolut: " . $min_support_input . "<br>";
                        echo "Min Support Relatif: " . $minSupportRelatif . "<br>";
                        echo "Min Confidence: " . $min_confidence_input . "<br>";
                        echo "Confidence: " . $confidence . "%<br>";
                        echo "</div>";
                    } else {
                        $errorMessage = "Gagal mendapatkan aturan asosiasi";
                        echo "
                        <div class='modal fade' id='errorModal' tabindex='-1' role='dialog' aria-labelledby='errorModalLabel' aria-hidden='true'>
                            <div class='modal-dialog' role='document'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='errorModalLabel'>Error</h5>
                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                    </div>
                                    <div class='modal-body'>
                                        $errorMessage
                                    </div>
                                    <div class='modal-footer'>
                                        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>";

                        echo "
                        <script>
                            $(document).ready(function() {
                                $('#errorModal').modal('show');
                            });
                        </script>";
                    }
                }
            } else {
                $where = "1=1";
                if (isset($_POST['range_tanggal'])) {
                    $tgl = explode(" - ", $_POST['range_tanggal']);
                    $start = format_date($tgl[0]);
                    $end = format_date($tgl[1]);
                    $where = "transaction_date BETWEEN '$start' AND '$end'";
                }
                $sql = "SELECT * FROM transaksi WHERE $where";
                $query = $db_object->db_query($sql);
                $jumlah = $db_object->db_num_rows($query);
                ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group ml-3">
                                <label>Tanggal: </label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                    <input type="text" class="form-control" name="range_tanggal" id="id-date-range-picker-1" placeholder="Date range" value="<?php echo isset($_POST['range_tanggal']) ? $_POST['range_tanggal'] : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group col-auto">
                                <input name="search_display" type="submit" value="Search" class="btn btn-primary mb-2">
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="form-group">
                                <input name="min_support" type="text" class="form-control" placeholder="Min Support">
                            </div>
                            <div class="form-group">
                                <input name="min_confidence" type="text" class="form-control" placeholder="Min Confidence">
                            </div>
                            <div class="form-group">
                                <input name="submit" type="submit" value="Proses" class="btn btn-success">
                            </div>
                        </div>
                    </div>
                </form>

                <?php
                if (!empty($pesan_error)) {
                    display_error($pesan_error);
                }
                if (!empty($pesan_success)) {
                    display_success($pesan_success);
                }

                if ($jumlah == 0) {
                    echo "Data kosong...";
                } else {
                ?>
                    <div class="container-fluid mt-3">
                        <h1 class="h3 mb-2 text-gray-800">Data Penjualan</h1>
                        <p class="mb-4"></p>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Data Transaksi Penjualan</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <?php
                                    echo "Jumlah data: " . $jumlah . "<br>";
                                    if ($jumlah == 0) {
                                        echo "Data kosong...";
                                    } else {
                                    ?>
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Asal Wisatawan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                while ($row = $db_object->db_fetch_array($query)) {
                                                    echo "<tr>";
                                                    echo "<td>" . $no . "</td>";
                                                    echo "<td>" . $row['transaction_date'] . "</td>";
                                                    echo "<td>" . $row['produk'] . "</td>";
                                                    echo "</tr>";
                                                    $no++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
    <style>
                                    body { font-family: Arial, sans-serif; }
                                    .table { width: 100%; border-collapse: collapse; }
                                    .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
                                    .table th { background-color: #f2f2f2; }
                                    @media print {
                                        .no-print { display: none; }
                                    }
                                  </style>
                                  <head>
                                  <!-- Link Font Awesome -->
                                  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-xxxxxxx" crossorigin="anonymous" />
                                  </head>

                                   
                                    <button class="no-print" onclick="window.print();">
                                    </i> Print PDF  <i class='ace-icon fa fa-print bigger-250'></i>
                                    </button>

</div>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
