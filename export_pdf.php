<?php
include_once "database.php";
include_once "fungsi.php";
include_once "mining.php";

if (isset($_GET['id_process'])) {
    $id_process = $_GET['id_process'];
    $db_object = new database();

    $sql = "SELECT * FROM process_log WHERE id = $id_process";
    $query = $db_object->db_query($sql);
    $process_log = $db_object->db_fetch_array($query);

    if ($process_log) {
        ?>
        <html>
        <head>
            <title>Report PDF</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .table { width: 100%; border-collapse: collapse; }
                .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
                .table th { background-color: #f2f2f2; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <h1>Report PDF</h1>
            <table class="table">
                <tr>
                    <th>Start Date</th>
                    <td><?php echo format_date2($process_log['start_date']); ?></td>
                </tr>
                <tr>
                    <th>End Date</th>
                    <td><?php echo format_date2($process_log['end_date']); ?></td>
                </tr>
                <tr>
                    <th>Min Support</th>
                    <td><?php echo $process_log['min_support']; ?></td>
                </tr>
                <tr>
                    <th>Min Confidence</th>
                    <td><?php echo $process_log['min_confidence']; ?></td>
                </tr>
            </table>
            <button class="no-print" onclick="window.print();">Print PDF</button>
        </body>
        </html>
        <?php
    }
}
?>
