function initManualBook() {
  if (typeof Swal === 'undefined') {
    setTimeout(initManualBook, 100);
    return;
  }

  var config = document.getElementById('manual-book-config');
  if (!config) return;

  var cekUrl = config.dataset.cekUrl;
  var storeUrl = config.dataset.storeUrl;
  var destroyUrl = config.dataset.destroyUrl;
  var flipbookUrl = config.dataset.flipbookUrl;
  var csrf = config.dataset.csrf;
  var canManage = false;

  var content = document.getElementById('manual-book-content');

  // Set default Swal agar selalu mount ke body langsung
  Swal.mixin({ backdrop: true });

  function formatSize(bytes) {
    if (!bytes) return '0 KB';
    var mb = bytes / 1048576;
    return mb >= 1 ? mb.toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB';
  }

  function parseJsonResponse(r) {
    if (!r.ok) {
      return r.text().then(function (text) {
        throw new Error(
          'Server merespons HTTP ' + r.status + '. Isi: ' + (text ? text.substring(0, 300) : '(tidak ada isi)')
        );
      });
    }
    return r.text().then(function (text) {
      try {
        return JSON.parse(text);
      } catch (e) {
        throw new Error('Respons server bukan JSON yang valid: ' + text.substring(0, 300));
      }
    });
  }

  function mbSwal(opts) {
    // Pastikan Swal container dipindah ke body dan z-index tinggi
    return Swal.fire(
      Object.assign(
        {
          target: document.body,
          backdrop: true,
          customClass: Object.assign({ container: 'mb-swal-container' }, opts.customClass || {})
        },
        opts
      )
    );
  }

  // ─────────────────────────────────────────────────────────────────────
  // RENDER
  // ─────────────────────────────────────────────────────────────────────

  function renderUpload() {
    content.innerHTML =
      '<div class="text-center">' +
      '<i class="icon-base ri ri-upload-2-line icon-32px text-secondary mb-2"></i>' +
      '<p class="small text-secondary mb-3">Belum ada manual book</p>' +
      '<input type="file" id="manual-book-file" accept="application/pdf" class="form-control form-control-sm mb-2">' +
      '<button type="button" id="manual-book-upload-btn" class="btn btn-primary btn-sm w-100">Upload PDF</button>' +
      '</div>';
    document.getElementById('manual-book-upload-btn').addEventListener('click', uploadFile);
  }

  function renderNoAccess() {
    content.innerHTML =
      '<div class="text-center">' +
      '<i class="icon-base ri ri-book-line icon-32px text-secondary mb-2"></i>' +
      '<p class="small text-secondary mb-0">Belum ada manual book</p>' +
      '</div>';
  }

  function renderFile(data) {
    content.innerHTML =
      '<p class="fw-semibold mb-2">Manual Book</p>' +
      '<div class="d-flex align-items-center gap-2 border rounded p-2">' +
      '<i class="icon-base ri ri-file-pdf-2-line icon-24px text-danger"></i>' +
      '<div class="flex-grow-1" style="min-width:0;">' +
      '<div class="small fw-medium text-truncate">' +
      data.nama_file +
      '</div>' +
      '<div class="small text-secondary">' +
      formatSize(data.ukuran) +
      '</div>' +
      '</div>' +
      '<a href="' +
      flipbookUrl +
      '" target="_blank" class="btn btn-icon btn-sm btn-text-primary" title="Lihat">' +
      '<i class="icon-base ri ri-book-open-line"></i>' +
      '</a>' +
      //   '<button type="button" id="manual-book-delete-btn" class="btn btn-icon btn-sm btn-text-danger" title="Hapus">' +
      //   '<i class="icon-base ri ri-delete-bin-6-line"></i>' +
      //   '</button>' +
      //   '</div>';
      // document.getElementById('manual-book-delete-btn').addEventListener('click', confirmDelete);
      (canManage
        ? '<button type="button" id="manual-book-delete-btn" class="btn btn-icon btn-sm btn-text-danger" title="Hapus">' +
          '<i class="icon-base ri ri-delete-bin-6-line"></i>' +
          '</button>'
        : '') +
      '</div>';
    if (canManage) {
      document.getElementById('manual-book-delete-btn').addEventListener('click', confirmDelete);
    }
  }

  function renderLoadError(message) {
    content.innerHTML =
      '<div class="text-center">' +
      '<i class="icon-base ri ri-error-warning-line icon-32px text-danger mb-2"></i>' +
      '<p class="small text-danger mb-2">Gagal memuat data</p>' +
      '<p class="small text-secondary mb-3">' +
      message +
      '</p>' +
      '<button type="button" id="manual-book-retry-btn" class="btn btn-outline-secondary btn-sm w-100">Coba Lagi</button>' +
      '</div>';
    document.getElementById('manual-book-retry-btn').addEventListener('click', loadState);
  }

  // ─────────────────────────────────────────────────────────────────────
  // LOAD STATE
  // ─────────────────────────────────────────────────────────────────────

  function loadState() {
    content.innerHTML = '<p class="small text-secondary text-center mb-0">Memuat...</p>';
    fetch(cekUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(parseJsonResponse)
      .then(function (json) {
        canManage = json.can_manage || false;
        // json.ada ? renderFile(json.data) : renderUpload();
        if (json.ada) {
          renderFile(json.data);
        } else {
          canManage ? renderUpload() : renderNoAccess();
        }
      })
      .catch(function (err) {
        console.error('Gagal memuat status manual book:', err);
        renderLoadError(err.message);
      });
  }

  // ─────────────────────────────────────────────────────────────────────
  // UPLOAD
  // ─────────────────────────────────────────────────────────────────────

  function uploadFile() {
    var input = document.getElementById('manual-book-file');
    if (!input.files.length) {
      mbSwal({
        icon: 'warning',
        title: 'Pilih file dulu',
        text: 'Silakan pilih file PDF terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' },
        buttonsStyling: false
      });
      return;
    }

    var btn = document.getElementById('manual-book-upload-btn');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Mengunggah...';
    }

    var formData = new FormData();
    formData.append('file', input.files[0]);

    fetch(storeUrl, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
      .then(parseJsonResponse)
      .then(function (json) {
        if (json.status) {
          renderFile(json.data);
        } else {
          mbSwal({
            icon: 'error',
            title: 'Error!',
            text: json.message || 'Gagal mengunggah manual book.',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
          });
          if (btn) {
            btn.disabled = false;
            btn.textContent = 'Upload PDF';
          }
        }
      })
      .catch(function (err) {
        console.error('Gagal upload manual book:', err);
        mbSwal({
          icon: 'error',
          title: 'Error!',
          text: String(err.message || err),
          customClass: { confirmButton: 'btn btn-primary' },
          buttonsStyling: false
        });
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Upload PDF';
        }
      });
  }

  // ─────────────────────────────────────────────────────────────────────
  // HAPUS
  // ─────────────────────────────────────────────────────────────────────

  function confirmDelete() {
    // Tutup dropdown dulu
    var toggleEl = document.querySelector('.dropdown-manual-book .dropdown-toggle');
    if (toggleEl && typeof bootstrap !== 'undefined') {
      var bsDropdown = bootstrap.Dropdown.getInstance(toggleEl);
      if (bsDropdown) bsDropdown.hide();
    }

    setTimeout(function () {
      mbSwal({
        title: 'Konfirmasi?',
        text: 'Anda yakin akan menghapus manual book ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
      }).then(function (result) {
        if (!result.value) return;

        var deleteBtn = document.getElementById('manual-book-delete-btn');
        if (deleteBtn) deleteBtn.disabled = true;

        fetch(destroyUrl, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrf,
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
          .then(parseJsonResponse)
          .then(function (json) {
            if (json.status) {
              mbSwal({
                icon: 'success',
                title: 'Hapus!',
                text: 'Manual book berhasil dihapus.',
                customClass: { confirmButton: 'btn btn-success' },
                buttonsStyling: false
              });
              // renderUpload();
              canManage ? renderUpload() : renderNoAccess();
            } else {
              mbSwal({
                icon: 'error',
                title: 'Error!',
                text: json.message || 'Gagal menghapus manual book.',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
              });
              if (deleteBtn) deleteBtn.disabled = false;
            }
          })
          .catch(function (err) {
            console.error('Gagal hapus manual book:', err);
            mbSwal({
              icon: 'error',
              title: 'Error!',
              text: String(err.message || err),
              customClass: { confirmButton: 'btn btn-primary' },
              buttonsStyling: false
            });
            if (deleteBtn) deleteBtn.disabled = false;
          });
      });
    }, 300);
  }

  // ─────────────────────────────────────────────────────────────────────
  // INIT
  // ─────────────────────────────────────────────────────────────────────

  document.addEventListener('click', function (e) {
    if (e.target.closest('.dropdown-manual-book .dropdown-toggle')) {
      loadState();
    }
  });
}

document.addEventListener('DOMContentLoaded', initManualBook);
