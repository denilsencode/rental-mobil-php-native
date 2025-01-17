<?php
session_start(); // Mulai session
require 'koneksi.php';

// Notifikasi
if (isset($_SESSION['message'])) {
    echo "<div class='alert alert-{$_SESSION['message_type']} alert-dismissible fade show' role='alert'>
            {$_SESSION['message']}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
    unset($_SESSION['message_type']);
}

// Create
if (isset($_POST['tambah'])) {
    $nama_mobil = $_POST['nama_mobil'] ?? '';
    $merk = $_POST['merk'] ?? '';
    $harga_sewa = $_POST['harga_sewa'] ?? 0;

    if (empty($nama_mobil) || empty($merk) || empty($harga_sewa)) {
        $_SESSION['message'] = "Semua field harus diisi!";
        $_SESSION['message_type'] = "danger";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO mobil (nama_mobil, merk, harga_sewa) VALUES (?, ?, ?)");
            $stmt->execute([$nama_mobil, $merk, $harga_sewa]);
            $_SESSION['message'] = "Data mobil berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Gagal menambahkan data mobil: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
    header("Location: index.php");
    exit();
}

// Update
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? '';
    $nama_mobil = $_POST['nama_mobil'] ?? '';
    $merk = $_POST['merk'] ?? '';
    $harga_sewa = $_POST['harga_sewa'] ?? 0;

    if (empty($nama_mobil) || empty($merk) || empty($harga_sewa)) {
        $_SESSION['message'] = "Semua field harus diisi!";
        $_SESSION['message_type'] = "danger";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE mobil SET nama_mobil = ?, merk = ?, harga_sewa = ? WHERE id = ?");
            $stmt->execute([$nama_mobil, $merk, $harga_sewa, $id]);
            $_SESSION['message'] = "Data mobil berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Gagal memperbarui data mobil: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
    header("Location: index.php");
    exit();
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'] ?? '';
    try {
        $stmt = $pdo->prepare("DELETE FROM mobil WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Data mobil berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Gagal menghapus data mobil: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("Location: index.php");
    exit();
}

// Sewa Mobil
if (isset($_GET['sewa'])) {
    $id = $_GET['sewa'] ?? '';
    try {
        // Update status mobil menjadi 'Disewa'
        $stmt = $pdo->prepare("UPDATE mobil SET status = 'Disewa' WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Mobil berhasil disewa!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Gagal menyewa mobil: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("Location: index.php");
    exit();
}

// Read
$stmt = $pdo->query("SELECT * FROM mobil");
$mobil = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Mobil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Rental Mobil</h1>

        <!-- Form Tambah Mobil -->
        <form method="POST" class="mb-4">
            <h2>Tambah Mobil</h2>
            <div class="mb-3">
                <label for="nama_mobil" class="form-label">Nama Mobil</label>
                <input type="text" class="form-control" id="nama_mobil" name="nama_mobil" required>
            </div>
            <div class="mb-3">
                <label for="merk" class="form-label">Merk</label>
                <input type="text" class="form-control" id="merk" name="merk" required>
            </div>
            <div class="mb-3">
                <label for="harga_sewa" class="form-label">Harga Sewa</label>
                <input type="number" step="0.01" class="form-control" id="harga_sewa" name="harga_sewa" required>
            </div>
            <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
        </form>

        <!-- Tabel Daftar Mobil -->
        <h2>Daftar Mobil</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Mobil</th>
                    <th>Merk</th>
                    <th>Harga Sewa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mobil as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['id']) ?></td>
                        <td><?= htmlspecialchars($m['nama_mobil']) ?></td>
                        <td><?= htmlspecialchars($m['merk']) ?></td>
                        <td><?= number_format($m['harga_sewa'], 2) ?></td>
                        <td>
                            <?php if ($m['status'] === 'Tersedia'): ?>
                                <span class="badge bg-success"><?= htmlspecialchars($m['status']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= htmlspecialchars($m['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($m['status'] === 'Tersedia'): ?>
                                <a href="index.php?sewa=<?= $m['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin menyewa mobil ini?')">Sewa</a>
                            <?php endif; ?>
                            <a href="#editModal<?= $m['id'] ?>" class="btn btn-warning btn-sm" data-bs-toggle="modal">Edit</a>
                            <a href="index.php?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $m['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit Mobil</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <div class="mb-3">
                                            <label for="nama_mobil" class="form-label">Nama Mobil</label>
                                            <input type="text" class="form-control" id="nama_mobil" name="nama_mobil" value="<?= htmlspecialchars($m['nama_mobil']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="merk" class="form-label">Merk</label>
                                            <input type="text" class="form-control" id="merk" name="merk" value="<?= htmlspecialchars($m['merk']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="harga_sewa" class="form-label">Harga Sewa</label>
                                            <input type="number" step="0.01" class="form-control" id="harga_sewa" name="harga_sewa" value="<?= htmlspecialchars($m['harga_sewa']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>