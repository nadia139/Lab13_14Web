<?php
session_start();

// CEK APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Auto logout setelah 30 menit
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    header('Location: logout.php?timeout=1');
    exit();
}

// Update waktu aktivitas
$_SESSION['login_time'] = time();

include_once 'koneksi.php';

// KONFIGURASI PENCARIAN & PAGINATION
$q = "";
$sql_where = "";

if (isset($_GET['submit']) && !empty($_GET['q'])) {
    $q = $_POST['q'] ?? $_GET['q'];
    $sql_where = " WHERE nama LIKE '%{$q}%'";
}

// HITUNG TOTAL DATA
$sql_count = "SELECT COUNT(*) as total FROM data_barang";
$sql = "SELECT * FROM data_barang";

if (!empty($sql_where)) {
    $sql .= $sql_where;
    $sql_count .= $sql_where;
}

$result_count = mysqli_query($conn, $sql_count);
$total_data = 0;
if ($result_count) {
    $row_count = mysqli_fetch_assoc($result_count);
    $total_data = $row_count['total'];
}

// PAGINATION
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Data Barang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        
        /* HEADER */
        .header {
            background: #333;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info span {
            background: #444;
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-logout:hover {
            background: #c82333;
        }
        
        /* MAIN CONTENT */
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        /* SEARCH FORM */
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn-search {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* TABLE */
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        
        .page-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .page-link:hover {
            background: #f8f9fa;
        }
        
        .page-link.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .page-info {
            text-align: center;
            margin-top: 10px;
            color: #666;
        }
        
        /* FOOTER */
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            margin-top: 40px;
            border-top: 1px solid #ddd;
        }
        
        /* NO DATA */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <h1>📊 Dashboard Data Barang</h1>
        <div class="user-info">
            <span>👤 <?php echo $_SESSION['username']; ?> (Admin)</span>
            <a href="logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- SEARCH FORM -->
        <div class="search-box">
            <form action="" method="get" class="search-form">
                <input type="text" 
                       name="q" 
                       value="<?php echo htmlspecialchars($q); ?>"
                       placeholder="Cari nama barang...">
                <button type="submit" name="submit" class="btn-search">🔍 Cari</button>
            </form>
        </div>
        
        <?php if ($total_data > 0): ?>
        <!-- TABLE -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Harga Beli</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <?php 
                            $gambar_path = "gambar/" . $row['gambar'];
                            if (!empty($row['gambar']) && file_exists($gambar_path)): 
                            ?>
                                <img src="<?php echo $gambar_path; ?>" 
                                     alt="<?php echo htmlspecialchars($row['nama']); ?>"
                                     class="product-img">
                            <?php else: ?>
                                <div style="width:50px; height:50px; background:#f0f0f0; 
                                            border-radius:5px; display:flex; align-items:center; 
                                            justify-content:center;">
                                    <span style="color:#999; font-size:12px;">No Image</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                        <td>Rp <?php echo number_format($row['harga_jual'], 2, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($row['harga_beli'], 2, ',', '.'); ?></td>
                        <td><?php echo $row['stok']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- PAGINATION -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($q) ? '&q=' . urlencode($q) : ''; ?>"
                   class="page-link">‹ Prev</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $num_page; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($q) ? '&q=' . urlencode($q) : ''; ?>"
                   class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $num_page): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($q) ? '&q=' . urlencode($q) : ''; ?>"
                   class="page-link">Next ›</a>
            <?php endif; ?>
        </div>
        
        <div class="page-info">
            Halaman <?php echo $page; ?> dari <?php echo $num_page; ?> | 
            Total <?php echo $total_data; ?> barang
        </div>
        
        <?php else: ?>
        <!-- NO DATA -->
        <div class="no-data">
            <h3>📭 Tidak ada data ditemukan</h3>
            <?php if (!empty($q)): ?>
                <p>Kata kunci: "<?php echo htmlspecialchars($q); ?>"</p>
                <a href="dashboard.php">Tampilkan semua data</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- FOOTER -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Sistem Data Barang | Login: <?php echo $_SESSION['username']; ?></p>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>