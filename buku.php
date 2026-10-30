<?php
    $host = "localhost";
    $user = "root";
    $password = "";
    $db = "perpus";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Koneksi gagal: " . $e->getMessage());
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $aksi = $_POST["aksi"] ?? "tambah";

        try {
            if ($aksi === "hapus") {
                $id = (int) ($_POST["id"] ?? 0);
                $stmt = $conn->prepare("DELETE FROM buku WHERE id = :id");
                $stmt->execute([":id" => $id]);

                header("Location: buku.php?status=deleted");
                exit;
            }

            $id      = (int) ($_POST["id"] ?? 0);
            $judul   = trim($_POST["judul"] ?? "");
            $penulis = trim($_POST["penulis"] ?? "");
            $tahun   = (int) ($_POST["tahun"] ?? 0);
            $stok    = (int) ($_POST["stok"] ?? 0);

            // validasi sisi server
            if (strlen($judul) < 3 || $penulis === "" || $tahun < 1900 || $stok < 0) {
                header("Location: buku.php?status=error");
                exit;
            }

            if ($aksi === "edit") {
                $sql = "UPDATE buku
                        SET judul = :judul, penulis = :penulis, tahun_terbit = :tahun, stok = :stok
                        WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":judul"   => $judul,
                    ":penulis" => $penulis,
                    ":tahun"   => $tahun,
                    ":stok"    => $stok,
                    ":id"      => $id
                ]);

                header("Location: buku.php?status=updated");
                exit;
            }

            // default: tambah
            $sql = "INSERT INTO buku (judul, penulis, tahun_terbit, stok)
                    VALUES (:judul, :penulis, :tahun, :stok)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ":judul"   => $judul,
                ":penulis" => $penulis,
                ":tahun"   => $tahun,
                ":stok"    => $stok
            ]);

            header("Location: buku.php?status=success");
            exit;

        } catch (PDOException $e) {
            error_log($e->getMessage());
            header("Location: buku.php?status=error");
            exit;
        }
    }

    // ===== MODE EDIT: ambil data buku yang mau diedit =====
    $editData = null;
    if (isset($_GET["edit"])) {
        $stmt = $conn->prepare("SELECT * FROM buku WHERE id = :id");
        $stmt->execute([":id" => (int) $_GET["edit"]]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    $modeEdit = $editData !== null;

    // ===== DATA UNTUK TABEL =====
    $buku = $conn->query("SELECT * FROM buku ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $status = $_GET["status"] ?? "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Daftar Buku</title>
    <style>
        :root {
            --ungu-tua: #6b5b95;
            --ungu: #b8a9e0;
            --ungu-muda: #ece6fa;
            --ungu-hover: #a393d4;
        }
        body { background-color: #faf8ff; }

        .navbar-ungu { background-color: var(--ungu); }
        .navbar-ungu .navbar-brand { color: #3d2f66; font-weight: 600; }

        .btn-ungu {
            background-color: var(--ungu);
            border-color: var(--ungu);
            color: #3d2f66;
        }
        .btn-ungu:hover, .btn-ungu:focus {
            background-color: var(--ungu-hover);
            border-color: var(--ungu-hover);
            color: #2b2048;
        }
        .btn-outline-ungu {
            border-color: var(--ungu);
            color: var(--ungu-tua);
        }
        .btn-outline-ungu:hover {
            background-color: var(--ungu);
            color: #3d2f66;
        }

        h2 { color: var(--ungu-tua); }
        .form-control:focus {
            border-color: var(--ungu);
            box-shadow: 0 0 0 .2rem rgba(184, 169, 224, .35);
        }
        .box-ungu { background-color: var(--ungu-muda); border-color: var(--ungu) !important; }
        .table thead th { background-color: var(--ungu-muda); color: var(--ungu-tua); }
        .table-hover tbody tr:hover { background-color: #f3eefc; }
        .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: #f8f5fe; }
        footer { background-color: var(--ungu-muda); color: var(--ungu-tua); }
        .list-group-item a { color: var(--ungu-tua); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-ungu">
        <div class="container-fluid">
            <a class="navbar-brand" href="buku.php">Website Praktik</a>
            <form class="d-flex" role="search">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-ungu" type="submit">Search</button>
            </form>
        </div>
    </nav>

    <div class="container-fluid my-4">
        <div class="row">
            <aside class="col-md-3">
                <div class="box-ungu border rounded p-3">
                    <h2>Menu</h2>
                    <ul class="list-group">
                        <li class="list-group-item"><a href="dashboard.html" class="text-decoration-none fw-bold">Dashboard</a></li>
                        <li class="list-group-item"><a href="buku.php" class="text-decoration-none">Daftar Buku</a></li>
                        <li class="list-group-item">Form Peminjaman</li>
                    </ul>
                </div>
            </aside>

            <main class="col-md-9">
                <!-- ===== FORM TAMBAH / EDIT ===== -->
                <section>
                    <h2><?= $modeEdit ? "Form Edit Buku" : "Form Tambah Buku" ?></h2>

                    <form method="POST" action="buku.php" id="form-buku" novalidate>
                        <input type="hidden" name="aksi" value="<?= $modeEdit ? 'edit' : 'tambah' ?>">
                        <input type="hidden" name="id" value="<?= $modeEdit ? (int) $editData['id'] : '' ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="judul" class="form-label">Judul Buku</label>
                                <input type="text" class="form-control" name="judul" id="judul" required minlength="3"
                                       value="<?= $modeEdit ? htmlspecialchars($editData['judul']) : '' ?>">
                                <div class="invalid-feedback">Minimal 3 karakter</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="penulis" class="form-label">Penulis</label>
                                <input type="text" class="form-control" name="penulis" id="penulis" required
                                       value="<?= $modeEdit ? htmlspecialchars($editData['penulis']) : '' ?>">
                                <div class="invalid-feedback">Isi Penulis</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="terbit" class="form-label">Tahun Terbit</label>
                                <input type="number" class="form-control" min="1900" max="<?= date('Y') ?>" name="tahun" id="terbit" required
                                       value="<?= $modeEdit ? (int) $editData['tahun_terbit'] : '' ?>">
                                <div class="invalid-feedback">Isi Tahun Terbit (1900 - <?= date('Y') ?>)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control" min="0" step="1" name="stok" id="stok" required
                                       value="<?= $modeEdit ? (int) $editData['stok'] : '' ?>">
                                <div class="invalid-feedback">Stok tidak boleh kosong</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ungu">
                            <?= $modeEdit ? "Simpan Perubahan" : "Tambah Buku" ?>
                        </button>
                        <?php if ($modeEdit): ?>
                            <a href="buku.php" class="btn btn-outline-ungu">Batal</a>
                        <?php endif; ?>
                    </form>
                </section>

                <!-- ===== DAFTAR BUKU ===== -->
                <section class="border rounded p-3 mt-4">
                    <?php
                        if ($status === "success") {
                            echo '<div class="alert alert-success">Buku berhasil ditambahkan!</div>';
                        } elseif ($status === "updated") {
                            echo '<div class="alert alert-info">Buku berhasil diperbarui!</div>';
                        } elseif ($status === "deleted") {
                            echo '<div class="alert alert-warning">Buku berhasil dihapus.</div>';
                        } elseif ($status === "error") {
                            echo '<div class="alert alert-danger">Terjadi kesalahan, data tidak tersimpan.</div>';
                        }
                    ?>
                    <h2>Daftar Buku</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Tahun Terbit</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="daftar-buku">
                                <?php if (count($buku) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada data buku.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($buku as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($row["judul"]) ?></td>
                                        <td><?= htmlspecialchars($row["penulis"]) ?></td>
                                        <td><?= htmlspecialchars($row["tahun_terbit"]) ?></td>
                                        <td><?= htmlspecialchars($row["stok"]) ?></td>
                                        <td class="text-nowrap">
                                            <a href="buku.php?edit=<?= (int) $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>

                                            <form method="POST" action="buku.php" class="d-inline"
                                                  onsubmit="return confirm('Yakin mau menghapus buku &quot;<?= htmlspecialchars(addslashes($row['judul'])) ?>&quot;?');">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <footer class="text-center">
        <hr class="m-0">
        <p class="p-3 mb-0">&copy; 2026 Website Praktik. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // validasi form di sisi browser (munculkan pesan merah kalau belum valid)
        const formBuku = document.getElementById("form-buku");
        formBuku.addEventListener("submit", function (event) {
            if (!formBuku.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            formBuku.classList.add("was-validated");
        });
    </script>
</body>
</html>