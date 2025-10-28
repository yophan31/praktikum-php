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
    <title>🌊 Todo List — Modern App</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Palet Warna Kustom: Biru Laut (#007BFF) dan Kuning Cerah/Emas (#FFC107) */
        :root {
            --bs-primary: #007BFF; /* Biru Laut */
            --bs-primary-rgb: 0, 123, 255;
            --bs-secondary: #6c757d;
            --bs-success: #28a745;
            --bs-danger: #dc3545;
            --bs-warning: #FFC107; /* Kuning Cerah */
            --bs-light: #f8f9fa;
            --bs-dark: #343a40;
            --bs-body-bg: #f0f4f8; /* Background Lebih Lembut */
            --bs-heading-color: #343a40;
        }

        body {
            background-color: var(--bs-body-bg);
            color: var(--bs-dark);
        }
        
        /* Override untuk menggunakan warna kustom */
        .btn-primary { background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
        .text-primary { color: var(--bs-primary) !important; }

        /* Styling Kartu Todo yang Lebih Baik */
        .todo-card {
            cursor: grab;
            user-select: none;
            transition: all 0.3s ease;
            border-left: 5px solid; /* Aksen warna status */
            border-radius: 0.5rem;
        }
        .todo-card:active { cursor: grabbing; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2) !important; transform: translateY(-3px); }
        .todo-card:hover { box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important; }

        /* Aksen Border Card Sesuai Status */
        .border-success { border-left-color: var(--bs-success) !important; } /* Selesai */
        .border-warning { border-left-color: var(--bs-warning) !important; } /* Belum Selesai */

        .truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .card-badge { font-size: .7rem; padding: .3rem .6rem; font-weight: 600; border-radius: .25rem; }
        
        .modal-content { border-radius: 1rem; }
    </style>
</head>
<body>
<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <div>
            <h1 class="fw-bolder text-dark mb-0">🌊 Todo List <span class="text-primary">App</span></h1>
            <small class="text-secondary">Kelola tugas harianmu dengan baik</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary rounded-pill" onclick="window.location.href='index.php'">🔄 Refresh</button>
            <button type="button" class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAdd">➕ Tambah Todo</button>
        </div>
    </div>
    
    <hr class="mb-4">

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger rounded-0 border-0 shadow-sm"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success rounded-0 border-0 shadow-sm"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php
        $currentFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    ?>
    <form class="row g-3 align-items-center mb-4 p-3 bg-white rounded-3 shadow-sm" method="GET" action="index.php">
        <input type="hidden" name="action" value="index">
        
        <div class="col-auto">
            <label class="form-label mb-0 fw-bold text-secondary">Filter:</label>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group" aria-label="filter">
                <a class="btn <?= $currentFilter==='all' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-start" href="index.php?filter=all<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">Semua</a>
                <a class="btn <?= $currentFilter==='finished' ? 'btn-success' : 'btn-outline-success' ?>" href="index.php?filter=finished<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">Selesai</a>
                <a class="btn <?= $currentFilter==='unfinished' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-end" href="index.php?filter=unfinished<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">Belum Selesai</a>
            </div>
        </div>

        <div class="col-md">
            <input type="search" name="q" class="form-control rounded-pill" placeholder="🔍 Cari judul atau deskripsi..." value="<?= htmlspecialchars($q) ?>">
        </div>

        <div class="col-auto">
            <button class="btn btn-primary rounded-pill" type="submit">Cari</button>
            <a class="btn btn-outline-secondary rounded-pill" href="index.php">Reset</a>
        </div>
    </form>

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
                    <div class="card todo-card shadow-lg h-100 <?= $finished ? 'border-success' : 'border-warning' ?> <?= $cardBg ?>">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold text-dark"><?= $safeTitle ?></h5>
                                <span class="badge <?= $finished ? 'bg-success' : 'bg-warning text-dark' ?> card-badge">
                                    <?= $finished ? '✅ Selesai' : '⏳ Belum' ?>
                                </span>
                            </div>

                            <p class="card-text text-secondary truncate-2 mb-3 flex-grow-1">
                                <?= nl2br($safeDesc ?: '— Tidak ada deskripsi —') ?>
                            </p>

                            <div class="small text-muted border-top pt-2 mt-auto">
                                🗓️ Dibuat: <?= date('d M Y H:i', strtotime($todo['created_at'])) ?><br>
                                ✏️ Diubah: <?= date('d M Y H:i', strtotime($todo['updated_at'])) ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-id="<?= (int)$todo['id'] ?>" data-action="detail">Detail</button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-action="edit"
                                        data-id="<?= (int)$todo['id'] ?>"
                                        data-title="<?= htmlspecialchars($todo['title'], ENT_QUOTES) ?>"
                                        data-description="<?= htmlspecialchars($todo['description'], ENT_QUOTES) ?>"
                                        data-finished="<?= $finished ? '1' : '0' ?>">
                                        Ubah
                                    </button>
                                </div>
                                <a href="index.php?action=delete&id=<?= (int)$todo['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus todo ini?')">🗑️ Hapus</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center shadow-sm py-4">🎉 Belum ada todo sesuai kriteria. Tambahkan yang baru!</div>
    <?php endif; ?>

</div>

<div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" action="index.php?action=create" method="POST" id="formAdd">
      <div class="modal-header bg-primary text-white border-0 rounded-top-4">
        <h5 class="modal-title">Tambah Todo Baru</h5>
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
        <h5 class="modal-title">Ubah Detail Todo</h5>
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