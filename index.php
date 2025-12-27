<?php
// KONFIGURASI PENCARIAN
$q = "";
$sql_where = "";

if (isset($_GET['submit']) && !empty($_GET['q'])) {
    $q = $_GET['q'];
    $sql_where = " WHERE nama LIKE '%{$q}%'";
}

$title = 'Data Barang';

include_once 'koneksi.php';

// HITUNG TOTAL DATA
$sql_count = "SELECT COUNT(*) as total FROM data_barang";
$sql = "SELECT * FROM data_barang";

if (!empty($sql_where)) {
    $sql .= $sql_where;
    $sql_count .= $sql_where;
}

$result_count = mysqli_query($conn, $sql_count);
if ($result_count) {
    $row_count = mysqli_fetch_assoc($result_count);
    $total_data = $row_count['total'];
} else {
    $total_data = 0;
}

// KONFIGURASI PAGINATION
$per_page = 5;
$num_page = ceil($total_data / $per_page);

if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
    if ($page < 1) $page = 1;
    if ($page > $num_page && $num_page > 0) $page = $num_page;
} else {
    $page = 1;
}

$offset = ($page - 1) * $per_page;
$sql .= " LIMIT {$per_page} OFFSET {$offset}";
$result = mysqli_query($conn, $sql);

include_once 'header.php';
?>

<!-- TOMBOL TAMBAH BARANG -->
<a href="tambah_barang.php" class="btn btn-large">+ Tambah Barang</a>

<!-- FORM PENCARIAN -->
<div class="form-cari">
    <form action="" method="get">
        <label for="q">Cari Barang: </label>
        <input type="text" 
               id="q" 
               name="q" 
               class="input-q" 
               value="<?php echo htmlspecialchars($q); ?>" 
               placeholder="Masukkan nama barang...">
        <input type="submit" name="submit" value="Cari" class="btn btn-primary">
    </form>
</div>

<?php if ($total_data > 0): ?>
<!-- TABEL DATA DENGAN GAMBAR -->
<table>
    <thead>
        <tr>
            <th>Gambar</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Harga Jual</th>
            <th>Harga Beli</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = $offset + 1;
        while($row = mysqli_fetch_assoc($result)): 
            // Format harga sesuai contoh gambar
            $harga_jual = number_format($row['harga_jual'], 2, ',', '.');
            $harga_beli = number_format($row['harga_beli'], 2, ',', '.');
        ?>
        <tr>
            <td style="text-align: center; padding: 5px;">
                <?php 
                $gambar_path = "gambar/" . $row['gambar'];
                if (!empty($row['gambar']) && file_exists($gambar_path)): ?>
                    <img src="<?php echo $gambar_path; ?>" 
                         alt="<?php echo htmlspecialchars($row['nama']); ?>" 
                         style="width:50px; height:50px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                <?php else: ?>
                    <div style="width:50px; height:50px; background:#f0f0f0; border-radius:4px; 
                                display:flex; align-items:center; justify-content:center; margin:0 auto;">
                        <span style="color:#999; font-size:10px;">No Image</span>
                    </div>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['nama']); ?></td>
            <td><?php echo htmlspecialchars($row['kategori']); ?></td>
            <td style="text-align: right;"><?php echo $harga_jual; ?></td>
            <td style="text-align: right;"><?php echo $harga_beli; ?></td>
            <td style="text-align: center;"><?php echo $row['stok']; ?></td>
            <td style="white-space: nowrap;">
                <button class="btn" style="background:#4CAF50; color:white; padding:4px 12px; margin-right:5px; border:none; border-radius:3px;">Edit</button>
                <button class="btn" style="background:#f44336; color:white; padding:4px 12px; border:none; border-radius:3px;">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- PAGINATION -->
<div class="pagination-container">
    <ul class="pagination">
        <!-- TOMBOL PREVIOUS -->
        <li>
            <?php if ($page > 1): ?>
                <?php 
                    $prev_link = "?page=" . ($page - 1);
                    if (!empty($q)) $prev_link .= "&q=" . urlencode($q);
                ?>
                <a href="<?php echo $prev_link; ?>">&laquo; Prev</a>
            <?php else: ?>
                <a class="disabled">&laquo; Prev</a>
            <?php endif; ?>
        </li>
        
        <!-- NOMOR HALAMAN -->
        <?php for ($i = 1; $i <= $num_page; $i++): ?>
            <?php 
                $link = "?page={$i}";
                if (!empty($q)) $link .= "&q=" . urlencode($q);
                $class = ($page == $i) ? 'active' : '';
            ?>
            <li><a class="<?php echo $class; ?>" href="<?php echo $link; ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        
        <!-- TOMBOL NEXT -->
        <li>
            <?php if ($page < $num_page): ?>
                <?php 
                    $next_link = "?page=" . ($page + 1);
                    if (!empty($q)) $next_link .= "&q=" . urlencode($q);
                ?>
                <a href="<?php echo $next_link; ?>">Next &raquo;</a>
            <?php else: ?>
                <a class="disabled">Next &raquo;</a>
            <?php endif; ?>
        </li>
    </ul>
    
    <!-- INFO PAGINATION -->
    <p style="margin-top: 10px; color: #7f8c8d;">
        Halaman <?php echo $page; ?> dari <?php echo $num_page; ?> | 
        Total: <?php echo $total_data; ?> barang
    </p>
</div>

<?php else: ?>
<!-- JIKA TIDAK ADA DATA -->
<div class="no-data">
    <p>❌ Tidak ada data barang ditemukan.</p>
    <?php if (!empty($q)): ?>
        <p>Kata kunci: "<strong><?php echo htmlspecialchars($q); ?></strong>"</p>
        <a href="?" style="color: #3498db;">Tampilkan semua data</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php 
mysqli_close($conn);
include_once 'footer.php'; 
?>