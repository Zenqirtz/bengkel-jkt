/**
 * Page Data management
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-ig');
  const uploadBonModal = document.getElementById('uploadBonModal');

  // CSRF untuk semua AJAX jQuery
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // ────────────────────────────────────────────────
  //  DataTable Utama
  // ────────────────────────────────────────────────
  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,
      ordering: true,
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'foto-bon-list',
        data: function (d) {
          d.kode_input = $('#filter-kode-input').val();
          d.kode_order = $('#filter-kode-order').val();
          d.kode_spk = $('#filter-kode-spk').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
          d.nama_pemasok = $('#filter-nama-pemasok').val();
          d.tipe_barang = $('#filter-tipe-barang').val();
          d.tipe = 'input-gudang';
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'id' }, // 0 — checkbox
        { data: 'id' }, // 1 — no (hidden)
        { data: 'tanggal' }, // 2
        { data: 'kode_input' }, // 3
        { data: 'tipe_barang' }, // 4
        { data: 'kode_order' }, // 5
        { data: 'kode_spk' }, // 6
        { data: 'nama_pemasok' }, // 7
        { data: 'total', className: 'text-end' }, // 8
        { data: 'photo', className: 'text-center' } // 9
      ],
      columnDefs: [
        // Checkbox
        {
          targets: 0,
          orderable: false,
          searchable: false,
          render: function (data) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          }
        },
        // No (hidden)
        {
          targets: 1,
          searchable: false,
          orderable: false,
          visible: false,
          render: function (data, type, full) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        // Kolom Foto (badge)
        {
          targets: -1,
          orderable: false,
          render: function (data) {
            if (data === '1') {
              return `<span class="badge badge-center text-bg-success"><i class="icon-base ri ri-check-line"></i></span>`;
            }
            return `<span class="badge badge-center text-bg-danger"><i class="icon-base ri ri-close-line"></i></span>`;
          }
        }
      ],
      scrollX: true,
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [{ pageLength: { menu: [10, 20, 50, 70, 100], text: '_MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Cari', text: '_INPUT_' } }]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
        },
        bottomEnd: 'paging'
      },
      displayLength: 10,
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      },
      initComplete: function () {
        $('.card-header').after('<hr class="my-0">');
        document.querySelectorAll('.dt-buttons .btn').forEach(btn => btn.classList.remove('btn-secondary'));
      }
    });

    // ── Batasi hanya 1 checkbox ──────────────────
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.dt-checkboxes');
      if (chk && chk.checked) {
        $('.dt-checkboxes').not(chk).prop('checked', false);
      }
    });

    // ── Tombol LIHAT ────────────────────────────
    const btnLihat = document.querySelector('.lihat-foto');
    if (btnLihat) {
      btnLihat.addEventListener('click', function () {
        if (!isEdit) {
          return _alertDenied('mengubah');
        }
        const chk = document.querySelector('.datatables-ig .dt-checkboxes:checked');
        if (!chk) return _alertPilih();

        clearFormBon();
        _openModal();
        $('.btn-submit').hide();
        $('.filter-file-photo').hide();
        $('.filter-photo-container').show();

        fetchHeaderIG(chk.value, function () {
          fetchFotoBon();
          _bindDeleteFoto();
          _bindDownloadAll();
        });
      });
    }

    // ── Tombol UPLOAD ────────────────────────────
    const btnUpload = document.querySelector('.upload-foto');
    if (btnUpload) {
      btnUpload.addEventListener('click', function () {
        if (!isAdd) return _alertDenied('tambah');
        const chk = document.querySelector('.datatables-ig .dt-checkboxes:checked');
        if (!chk) return _alertPilih();

        clearFormBon();
        _openModal();
        $('.btn-submit').show();
        $('.filter-file-photo').show();
        $('.filter-photo-container').hide();

        fetchHeaderIG(chk.value, function (data) {
          document.getElementById('ig_id').value = chk.value;
        });
      });
    }

    // ── Form Validasi & Submit Upload ────────────
    const uploadBonForm = document.getElementById('uploadBonForm');
    if (uploadBonForm) {
      FormValidation.formValidation(uploadBonForm, {
        fields: {
          photo: {
            validators: { notEmpty: { message: 'Silahkan pilih file foto' } }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: () => '.form-control-validation'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      }).on('core.form.valid', function () {
        const fd = new FormData(uploadBonForm);

        PleaseWaitPage();

        fetch(baseUrl + 'foto-bon-list', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
          body: fd
        })
          .then(r => r.json())
          .then(({ status, message, errors }) => {
            if (document.querySelector('.notiflix-loading')) Loading.remove();

            bootstrap.Modal.getInstance(uploadBonModal)?.hide();

            if (status) {
              dt_basic.draw();
              Swal.fire({
                icon: 'success',
                title: 'Informasi!',
                text: message,
                customClass: { confirmButton: 'btn btn-success' }
              });
            } else {
              let html = `<p>${message}</p>`;
              if (errors) {
                html += '<ul style="text-align:left;margin-top:10px;">';
                Object.values(errors).forEach(arr =>
                  arr.forEach(m => {
                    html += `<li>${m}</li>`;
                  })
                );
                html += '</ul>';
              }
              Swal.fire({ icon: 'error', title: 'Error!', html, customClass: { confirmButton: 'btn btn-success' } });
            }
          })
          .catch(() => {
            if (document.querySelector('.notiflix-loading')) Loading.remove();
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Gagal upload foto.',
              customClass: { confirmButton: 'btn btn-success' }
            });
          });
      });
    }

    // ── Form Filter ──────────────────────────────
    const formFilter = document.getElementById('formFilterIG');
    if (formFilter) {
      formFilter.addEventListener('submit', function (e) {
        e.preventDefault();
        dt_basic.draw();
        bootstrap.Modal.getInstance(document.getElementById('filterRoleModal'))?.hide();
      });
    }

    // ── Layout tweaks (sama persis dengan foto-pekerjaan) ──
    setTimeout(() => {
      [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
        { selector: '.dt-length', classToAdd: 'mb-md-5 mb-0' },
        {
          selector: '.dt-layout-end',
          classToRemove: 'justify-content-between',
          classToAdd: 'd-flex gap-md-4 justify-content-md-between justify-content-center gap-md-2 flex-wrap mt-0'
        },
        { selector: '.dt-layout-start', classToAdd: 'mt-md-0 mt-5' },
        { selector: '.dt-layout-start .dt-buttons', classToAdd: 'd-md-flex d-block gap-4 justify-content-center' },
        {
          selector: '.dt-layout-end .dt-buttons',
          classToAdd: 'd-md-flex d-block gap-4 mb-md-0 mb-5 justify-content-center'
        },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full', classToRemove: 'col-md col-12' },
        { selector: '.dt-layout-full .table', classToAdd: 'table-responsive' }
      ].forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(el => {
          if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
          if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
        });
      });
    }, 100);
  }

  // ── Select2 ─────────────────────────────────────
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var ph = $this.data('placeholder') || $this.find('option[value=""]').first().text() || 'Please select';
      if (typeof select2Focus === 'function') select2Focus($this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: ph,
        allowClear: true,
        width: '100%',
        dropdownParent: $this.parent()
      });
    });
  }

  // ── Flatpickr ───────────────────────────────────
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate.length) {
    flatpickrDate.forEach(function (el) {
      flatpickr(el, {
        monthSelectorType: 'static',
        static: true,
        dateFormat: 'd/m/Y'
      });
    });
  }

  // ================================================================
  //  FUNGSI HELPER
  // ================================================================

  function _openModal() {
    new bootstrap.Modal(uploadBonModal).show();
  }

  function _alertDenied(aksi) {
    Swal.fire({
      icon: 'error',
      title: 'Akses Ditolak',
      text: `Anda tidak memiliki izin untuk ${aksi} data.`,
      customClass: { confirmButton: 'btn btn-primary' }
    });
  }

  function _alertPilih() {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan',
      text: 'Silahkan pilih (checklist) data Input Gudang pada tabel terlebih dahulu!',
      customClass: { confirmButton: 'btn btn-primary' }
    });
  }

  /** Ambil header IG dan isi form */
  function fetchHeaderIG(id, callback) {
    fetch(`${baseUrl}foto-bon-list/${id}/edit`)
      .then(r => r.json())
      .then(data => {
        document.getElementById('add-kode-input').value = data.kode_input ?? '';
        document.getElementById('add-tanggal').value = data.tanggal ?? '';
        document.getElementById('add-tipe-barang').value = data.tipe_barang ?? '';
        document.getElementById('add-no-bon').value = data.no_bon ?? '';
        document.getElementById('add-kode-order').value = data.kode_order ?? '';
        document.getElementById('add-kode-spk').value = data.kode_spk ?? '';
        document.getElementById('add-nama-pemasok').value = data.nama_pemasok ?? '';
        document.getElementById('add-total').value = data.total ?? '';

        if (typeof callback === 'function') callback(data);
      });
  }

  /** Bind event hapus foto (dipanggil ulang setiap buka modal lihat) */
  function _bindDeleteFoto() {
    // Gunakan delegasi agar tidak tumpuk setelah re-render
    document.removeEventListener('click', _handleDeleteFoto);
    document.addEventListener('click', _handleDeleteFoto);
  }

  function _handleDeleteFoto(e) {
    const btn = e.target.closest('.delete-foto');
    if (!btn) return;

    if (!isDelete) {
      return Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Anda tidak memiliki izin hapus data.',
        customClass: { confirmButton: 'btn btn-success' }
      });
    }

    const id = btn.dataset.id;
    Swal.fire({
      title: 'Konfirmasi?',
      text: 'Anda yakin akan menghapus foto ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal',
      customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.value) {
        fetch(`${baseUrl}foto-bon-list/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
          }
        })
          .then(r => {
            if (r.ok) {
              fetchFotoBon();
              Swal.fire({
                icon: 'success',
                title: 'Hapus!',
                text: 'Foto berhasil dihapus.',
                customClass: { confirmButton: 'btn btn-success' }
              });
            } else throw new Error('Gagal hapus');
          })
          .catch(() =>
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Gagal hapus foto.',
              customClass: { confirmButton: 'btn btn-success' }
            })
          );
      } else {
        Swal.fire({
          title: 'Batal',
          text: 'Foto batal dihapus.',
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  }

  function _bindDownloadAll() {
    $(document)
      .off('click', '#btn-download-all')
      .on('click', '#btn-download-all', function () {
        const links = document.querySelectorAll('#photo-container a[download]');
        if (!links.length) {
          return Swal.fire('Info', 'Tidak ada gambar yang bisa didownload.', 'info');
        }
        links.forEach((link, i) => setTimeout(() => link.click(), i * 500));
      });
  }
});

// ================================================================
//  fetchFotoBon  — render galeri ke dalam #photo-container
// ================================================================
function fetchFotoBon() {
  const kode_cabang = $('#kode_cabang').val();
  const kode_input = $('#add-kode-input').val();

  fetch(
    `${baseUrl}keuangan/get-foto-bon?kode_cabang=${encodeURIComponent(kode_cabang)}&kode_input=${encodeURIComponent(kode_input)}`
  )
    .then(r => {
      if (!r.ok) throw new Error('Gagal mengambil data');
      return r.json();
    })
    .then(data => {
      const container = document.getElementById('photo-container');
      const btnDownloadAll = document.getElementById('btn-download-all');
      if (!container) return;

      container.innerHTML = '';

      if (data && data.length > 0) {
        btnDownloadAll?.classList.remove('d-none');

        data.forEach(photo => {
          let imgEl = '';
          let dlBtn = '';

          if (photo.photo_bon_base64) {
            imgEl = `<img src="data:image/jpeg;base64,${photo.photo_bon_base64}"
                          class="card-img-top"
                          alt="${photo.nama_file}"
                          style="height:200px;object-fit:cover;">`;

            dlBtn = `
              <a href="data:image/jpeg;base64,${photo.photo_bon_base64}"
                 download="${photo.nama_file}"
                 class="btn btn-success btn-sm position-absolute rounded-circle"
                 style="top:-10px;right:25px;width:30px;height:30px;padding:0;
                        display:flex;align-items:center;justify-content:center;
                        text-decoration:none;z-index:10;">
                <i class="icon-base ri ri-download-2-line"></i>
              </a>`;
          } else {
            imgEl = `<div class="bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                       <span class="text-muted"><i class="icon-base ri ri-image-line icon-26px"></i></span>
                     </div>`;
          }

          container.insertAdjacentHTML(
            'beforeend',
            `
            <div class="col-md-4 col-sm-6">
              <div class="card position-relative border">
                <button type="button"
                        class="btn btn-danger btn-sm position-absolute rounded-circle delete-foto"
                        data-id="${photo.id}"
                        style="top:-10px;right:-10px;width:30px;height:30px;padding:0;line-height:1;">
                  <i class="icon-base ri ri-delete-bin-7-line"></i>
                </button>
                ${dlBtn}
                ${imgEl}
                <div class="card-body text-center p-2">
                  <h6 class="card-title mb-0">${photo.nama_file}</h6>
                </div>
              </div>
            </div>
          `
          );
        });
      } else {
        btnDownloadAll?.classList.add('d-none');
        container.innerHTML = `
          <div class="col-12 text-center py-4">
            <span class="text-muted">Tidak ada foto bon untuk Input Gudang ini.</span>
          </div>`;
      }
    })
    .catch(err => console.error('fetchFotoBon error:', err));
}

function clearFormBon() {
  const form = document.getElementById('uploadBonForm');
  form?.reset?.();
}
