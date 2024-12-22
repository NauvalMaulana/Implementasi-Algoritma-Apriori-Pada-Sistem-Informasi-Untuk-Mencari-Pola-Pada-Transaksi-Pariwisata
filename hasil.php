<?php
include_once "database.php";
include_once "fungsi.php";
include_once "mining.php";

if (isset($_GET['hapus'])) {
    $id_process = $_GET['hapus'];
    $db_object = new database();
    $sql_delete = "DELETE FROM process_log WHERE id = $id_process";
    if ($db_object->db_query($sql_delete)) {
        $pesan_success = "Data berhasil dihapus";
    } else {
        $pesan_error = "Data gagal dihapus";
    }
}
?>
<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
            <div class="page-header">
                <h1 class="text-success fw-bold">
                    Hasil
                </h1>
            </div><!-- /.page-header -->
            <?php
            //object database class
            $db_object = new database();

            $pesan_error = $pesan_success = "";
            if (isset($_GET['pesan_error'])) {
                $pesan_error = $_GET['pesan_error'];
            }
            if (isset($_GET['pesan_success'])) {
                $pesan_success = $_GET['pesan_success'];
            }

            $sql = "SELECT * FROM process_log ORDER BY id DESC";
            $query = $db_object->db_query($sql);
            $jumlah = $db_object->db_num_rows($query);
            //session_start();
            ?>

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

                                if ($jumlah == 0) {
                                    echo "Data kosong...";
                                } else {
                                ?>
                                    <table class='table table-bordered table-striped  table-hover'>
                                        <tr>
                                            <th>No</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Min Support</th>
                                            <th>Min Confidence</th>
                                            <th>Actions</th>
                                            <th>Pdf</th>
                                        </tr>
                                        <?php
                                        $no = 1;
                                        while ($row = $db_object->db_fetch_array($query)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . format_date($row['start_date']) . "</td>";
                                            echo "<td>" . format_date($row['end_date']) . "</td>";
                                            echo "<td>" . $row['min_support'] . "</td>";
                                            echo "<td>" . $row['min_confidence'] . "</td>";
                                            $view = "<a href='index.php?menu=view_rule&id_process=" . $row['id'] . "' class='btn btn-info btn-xs'>View rule</a>";
                                            $delete = "<a href='index.php?menu=hasil&hapus=" . $row['id'] . "' onclick='return confirm(\"Yakin ingin menghapus data ini?\")' class='btn btn-danger btn-xs'>Hapus</a>";
                                            echo "<td>" . $view . " | " . $delete . "</td>";
                                            echo "<td>";
                                            echo "<a href='export_pdf.php?id_process=" . $row['id'] . "' class='btn btn-app btn-light btn-xs' target='blank'>
                                                <i class='ace-icon fa fa-print bigger-160'></i>
                                                Print
                                            </a>";
                                            echo "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                        ?>
                                    </table>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.page-content -->
    </div>
</div><!-- /.main-content -->
<script>
        $(document).ready(function() {
            $('#dataTable10').DataTable();

        });
    </script>
    <!-- Tautan ke jQuery -->
