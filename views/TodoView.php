<?php
// views/TodoView.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// $todos diasumsikan disediakan oleh controller
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>🎨 Todo Task Manager — Biru Laut Tua & Teal</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Palet Warna Kustom: Teal Gelap (#008080) dan Biru Laut Tua (#4682B4) */
        :root {
            --bs-primary: #008080; /* Teal Gelap/Primer */
            --bs-primary-rgb: 0, 128, 128;
            --bs-info: #4682B4; /* 💡 Biru Laut Tua/Steel Blue/Aksen Info */
            --bs-info-rgb: 70, 130, 180;
            --bs-success: #38a745; /* Tetap hijau untuk Sukses */
            --bs-danger: #dc3545; /* Merah untuk Bahaya/Hapus */
            --bs-warning: #FFD700; /* Emas */
            --bs-light: #f8f9fa;
            --bs-dark: #343a40;
            --bs-body-bg: #f5f5f5; /* Background Abu-abu Sangat Muda */
            --bs-heading-color: #343a40;
        }

        body {
            background-color: var(--bs-body-bg);
            color: var(--bs-dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Override untuk menggunakan warna kustom */
        .btn-primary { background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .btn-primary:hover { background-color: #006666; border-color: #005050; }
        .text-primary { color: var(--bs-primary) !important; }
        .bg-primary { background-color: var(--bs-primary) !important; }
        
        /* 💡 Penyesuaian untuk warna Biru Laut Tua: Teks kembali putih */
        .btn-info { background-color: var(--bs-info); border-color: var(--bs-info); color: white !important; }
        .btn-info:hover { background-color: #3A6B97; border-color: #3A6B97; color: white !important; }
        .btn-outline-info { color: var(--bs-info); border-color: var(--bs-info); }
        .btn-outline-info:hover { background-color: var(--bs-info); color: white; }


        /* HEADER: Tombol Tambah dan Judul di Satu Baris Besar */
        .app-header {
            background: linear-gradient(135deg, #e0f2f1 0%, #ffffff 100%);
            border-bottom: 2px solid var(--bs-primary);
            padding: 2rem 0;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        /* Styling Kartu Todo yang Sangat Membulat */
        .todo-card {
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        .todo-card:hover { 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08); 
            transform: translateY(-4px); 
        }

        /* AKSEn Status Sudut (Biru Laut Tua untuk Belum Selesai) */
        .card-status-accent {
            position: absolute;
            top: 0;
            right: 0;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            color: white; /* Teks putih untuk kontras dengan Biru Laut Tua */
            clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%);
            border-bottom-left-radius: 1.5rem;
            z-index: 10;
        }
        .status-finished {
            background-color: var(--bs-success);
            color: white;
        }
        .status-unfinished {
            background-color: var(--bs-info); /* Biru Laut Tua */
        }
        
        .truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .card-badge { font-size: .75rem; padding: .3rem .7rem; font-weight: 700; border-radius: 0.5rem; }
        .modal-content { border-radius: 1rem; }
        .form-control.rounded-pill { padding-left: 1.5rem; }
    </style>
</head>
<body>
<div class="container py-5">
    
    <div class="app-header d-flex flex-column flex-md-row justify-content-between align-items-md-center align-items-start px-4 mb-4 shadow-sm">
        <div>
            <h1 class="fw-bolder text-dark mb-1">Task <span class="text-primary">Manager</span> 📋</h1>
            <p class="text-secondary lead mb-0">Kelola tugas harianmu</p>
        </div>
        <button type="button" class="btn btn-info rounded-pill shadow-lg fw-bold mt-3 mt-md-0" data-bs-toggle="modal" data-bs-target="#modalAdd">
            ✨ Tambah Tugas Baru
        </button>
    </div>
    
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger rounded-3 border-0 shadow-sm"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success rounded-3 border-0 shadow-sm"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php
        $currentFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    ?>

    <div class="row mb-4 g-3">
        <form class="col-md-9" method="GET" action="index.php">
            <input type="hidden" name="action" value="index">
            <div class="input-group">
                <input type="search" name="q" class="form-control rounded-pill border-primary" placeholder="🔍 Cari judul atau deskripsi..." value="<?= htmlspecialchars($q) ?>">
                <button class="btn btn-primary rounded-pill ms-2" type="submit">Cari</button>
                <a class="btn btn-outline-secondary rounded-pill ms-1" href="index.php">Reset</a>
            </div>
        </form>

        <div class="col-md-3 d-flex justify-content-md-end">
             <button type="button" class="btn btn-outline-primary rounded-pill w-100" onclick="window.location.href='index.php'">🔄 Refresh List</button>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="form-label mb-0 fw-bold text-secondary me-2">Lihat Berdasarkan:</span>
        <div class="btn-group" role="group" aria-label="filter">
            <a class="btn btn-sm <?= $currentFilter==='all' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill" href="index.php?filter=all<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">Semua</a>
            <a class="btn btn-sm <?= $currentFilter==='finished' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill ms-2" href="index.php?filter=finished<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">Selesai</a>
            <a class="btn btn-sm <?= $currentFilter==='unfinished' ? 'btn-info text-white' : 'btn-outline-info' ?> rounded-pill ms-2" href="index.php?filter=unfinished<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">Belum Selesai</a>
        </div>
    </div>

    <hr class="mb-4">

    <?php if (!empty($todos)): ?>
        <div id="todoGrid" class="row g-4">
            <?php foreach ($todos as $todo):
                // LOGIKA INI TIDAK DIHILANGKAN
                $finished = ($todo['is_finished'] === 't' || $todo['is_finished'] === '1' || $todo['is_finished'] === true || $todo['is_finished'] == 1);
                $safeTitle = htmlspecialchars($todo['title']);
                $safeDesc = htmlspecialchars($todo['description']);
                $cardBg = $finished ? 'bg-white' : 'bg-light';
            ?>
                <div class="col-md-6 col-lg-4 todo-item" data-id="<?= (int)$todo['id'] ?>">
                    <div class="card todo-card shadow-sm h-100 <?= $cardBg ?>">
                        
                        <div class="card-status-accent <?= $finished ? 'status-finished' : 'status-unfinished' ?>">
                            <?= $finished ? '✓' : '...' ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column pt-5"> 
                            <span class="badge <?= $finished ? 'bg-success' : 'bg-info text-white' ?> card-badge mb-3 align-self-start">
                                <?= $finished ? '✅ Selesai' : '⏳ Segera' ?>
                            </span>

                            <h5 class="card-title mb-2 fw-bold text-dark"><?= $safeTitle ?></h5>

                            <p class="card-text text-secondary truncate-2 mb-3 flex-grow-1">
                                <?= nl2br($safeDesc ?: '— Tidak ada deskripsi —') ?>
                            </p>

                            <div class="small text-muted border-top pt-2 mt-auto mb-3">
                                <span class="me-3">🗓️ Dibuat: <?= date('d M Y', strtotime($todo['created_at'])) ?></span>
                                <span>✏️ Diubah: <?= date('d M Y', strtotime($todo['updated_at'])) ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-info" data-id="<?= (int)$todo['id'] ?>" data-action="detail">Detail</button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary"
                                        data-action="edit"
                                        data-id="<?= (int)$todo['id'] ?>"
                                        data-title="<?= htmlspecialchars($todo['title'], ENT_QUOTES) ?>"
                                        data-description="<?= htmlspecialchars($todo['description'], ENT_QUOTES) ?>"
                                        data-finished="<?= $finished ? '1' : '0' ?>">
                                        Ubah
                                    </button>
                                </div>
                                <a href="index.php?action=delete&id=<?= (int)$todo['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Yakin ingin menghapus todo ini?')">
                                    <small>🗑️ Hapus</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center shadow-sm py-4 rounded-3 border-0">🎉 Belum ada tugas sesuai kriteria. Silakan tambahkan tugas baru!</div>
    <?php endif; ?>

</div>

<div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" action="index.php?action=create" method="POST" id="formAdd">
      <div class="modal-header bg-primary text-white border-0 rounded-top-4">
        <h5 class="modal-title">Tambah Tugas Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control rounded-3" required>
          </div>
          <div class="mb-3">
              <label class="form-label fw-bold">Deskripsi</label>
              <textarea name="description" class="form-control rounded-3" rows="4"></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary rounded-pill">Tambah</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" action="index.php?action=update" method="POST" id="formEdit">
      <input type="hidden" name="id" id="editId">
      <div class="modal-header bg-primary text-white border-0 rounded-top-4">
        <h5 class="modal-title">Ubah Detail Tugas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
              <input type="text" name="title" id="editTitle" class="form-control rounded-3" required>
          </div>
          <div class="mb-3">
              <label class="form-label fw-bold">Deskripsi</label>
              <textarea name="description" id="editDescription" class="form-control rounded-3" rows="4"></textarea>
          </div>
          <div class="form-check mt-3">
              <input type="checkbox" name="is_finished" id="editIsFinished" class="form-check-input" value="1">
              <label for="editIsFinished" class="form-check-label fw-bold">Tandai sebagai selesai</label>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary rounded-pill">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-dark text-white border-0 rounded-top-4">
        <h5 class="modal-title" id="detailTitle">Detail Tugas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="lead" id="detailDescription"></p>
        <hr>
        <div class="small text-secondary">
            <div class="fw-bold" id="detailStatus"></div>
            <div id="detailCreated"></div>
            <div id="detailUpdated"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // LOGIKA DELEGATED CLICK HANDLING UNTUK EDIT/DETAIL TIDAK DIHILANGKAN
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        if (!action) return;

        if (action === 'edit') {
            // Read safe data attributes
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title') || '';
            const description = btn.getAttribute('data-description') || '';
            const finished = btn.getAttribute('data-finished') === '1';

            // Populate edit form
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editDescription').value = description;
            document.getElementById('editIsFinished').checked = finished;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
            e.preventDefault();
        }

        if (action === 'detail') {
            const id = btn.getAttribute('data-id');
            // LOGIKA FETCH UNTUK DETAIL TIDAK DIHILANGKAN
            fetch('index.php?action=detail&id=' + encodeURIComponent(id))
                .then(r => {
                    if (!r.ok) throw new Error('Network response was not ok');
                    return r.json();
                })
                .then(todo => {
                    document.getElementById('detailTitle').innerText = 'Detail Tugas: ' + (todo.title || '(Tanpa judul)');
                    document.getElementById('detailDescription').innerText = todo.description || '(Tidak ada deskripsi)';
                    document.getElementById('detailStatus').innerText = 'Status: ' + (todo.is_finished ? '✅ Selesai' : '⏳ Belum selesai');
                    document.getElementById('detailCreated').innerText = 'Dibuat: ' + new Date(todo.created_at).toLocaleString();
                    document.getElementById('detailUpdated').innerText = 'Terakhir diupdate: ' + new Date(todo.updated_at).toLocaleString();
                    new bootstrap.Modal(document.getElementById('modalDetail')).show();
                })
                .catch(() => alert('Gagal memuat detail todo. Pastikan API berfungsi dengan baik.'));
            e.preventDefault();
        }
    });

    // Setup Sortable (drag & drop) (LOGIKA SORTABLEJS DAN AJAX REORDER TIDAK DIHILANGKAN)
    const grid = document.getElementById('todoGrid');
    if (grid) {
        new Sortable(grid, {
            draggable: '.todo-item',
            handle: '.todo-card',
            animation: 150,
            onEnd: function () {
                const ids = Array.from(grid.querySelectorAll('.todo-item')).map(el => el.getAttribute('data-id'));
                // LOGIKA FETCH REORDER TIDAK DIHILANGKAN
                fetch('index.php?action=reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order: ids })
                })
                .then(r => r.json())
                .then(resp => {
                    if (!resp.success) alert('Gagal menyimpan urutan.');
                })
                .catch(() => alert('Error saat menyimpan urutan.'));
            }
        });
    }

    // Client-side small validation for Add form (LOGIKA VALIDASI TIDAK DIHILANGKAN)
    document.getElementById('formAdd')?.addEventListener('submit', function (e) {
        const title = this.querySelector('[name=title]').value.trim();
        if (!title) {
            e.preventDefault();
            alert('Judul tidak boleh kosong.');
        }
    });
});
</script>
</body>
</html>