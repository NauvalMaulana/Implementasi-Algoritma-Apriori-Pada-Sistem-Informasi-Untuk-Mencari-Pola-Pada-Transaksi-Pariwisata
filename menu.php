<?php
$menu_active = '';
if (isset($_GET['menu'])) {
    $menu_active = $_GET['menu'];
}
?>

<div id="sidebar" class="sidebar                  responsive                    ace-save-state">
    <script type="text/javascript">
        try {
            ace.settings.loadState('sidebar')
        } catch (e) {}
    </script>

    <div class="d-flex flex-column flex-shrink-0  bg-light py-2 mt-3    ">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link  <?= ($menu_active == '') ? "active" : ""; ?>" aria-current="page">
                    Halaman Utama
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?menu=data_transaksi" class="nav-link <?= ($menu_active == 'data_transaksi') ? "active" : ""; ?>" aria-current="page">
                    Data Transaksi
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?menu=proses_apriori" class="nav-link  <?= ($menu_active == 'proses_apriori') ? "active" : ""; ?>" aria-current="page">
                    Proses Apriori
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link " aria-current="page">
                    <span class="text-danger">
                        Keluar
                    </span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
        <i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
    </div>
</div>