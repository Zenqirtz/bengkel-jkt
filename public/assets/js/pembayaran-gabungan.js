/**
 * Page Data management
 */


'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-pg');
  const addRoleModal = document.getElementById('addRoleModal');
  const filterRoleModal = document.getElementById('filterRoleModal');
  const modalPilihIg = document.getElementById('modalPilihIg');

  // Flag: sedang navigasi ke modal IG (jangan reset form)
  let _navigatingToIg = false;

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // ===================== FLATPICKR =====================
  document.querySelectorAll('.dt-date').forEach(function (el) {
    flatpickr(el, { monthSelectorType: 'static', static: true, dateFormat: 'd/m/Y' });
  });

  // ===================== DATATABLE UTAMA =====================
  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,
      ordering: true,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'pembayaran-gabungan-list',
        data: function (d) {
          d.no_transaksi = $('#filter-no-transaksi').val();
          d.nama_supplier = $('#filter-nama').val();
          d.jenis_pembayaran = $('#filter-jenis').val();
          d.kode_bank = $('#filter-kode-bank').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'id', width: '20px' },
        { data: 'tanggal_transaksi' },
        { data: 'no_transaksi' },
        { data: 'jenis_pembayaran' },
        { data: 'nama_supplier' },
        { data: 'nama_bank' },
        { data: 'no_rekening' },
        { data: 'total_nilai', className: 'text-end' }
      ],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          searchable: false,
          render: function (data) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          }
        }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
        },
        topEnd: { features: [{ search: { placeholder: 'Cari', text: '_INPUT_' } }] },
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
      }
    });

    // Batasi 1 checkbox aktif di tabel utama
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-pg .dt-checkboxes');
      if (chk && chk.checked) {
        $('.datatables-pg .dt-checkboxes').not(chk).prop('checked', false);
      }
    });

    // ===== FORM CARI =====
    const formFilter = document.getElementById('formCariData');
    if (formFilter) {
      formFilter.addEventListener('submit', function (e) {
        e.preventDefault();
        dt_basic.draw();
        bootstrap.Modal.getInstance(filterRoleModal)?.hide();
      });
    }

    // ===== TOMBOL TAMBAH =====
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
        if (!isAdd) {
          alertAksesDitolak('tambah');
          return;
        }
        document.getElementById('pg_id').value = '';
        clearFormData();
      });
    }

    // ===== TOMBOL UBAH =====
    const btnEdit = document.querySelector('.edit-record');
    if (btnEdit) {
      btnEdit.addEventListener('click', function () {
        if (!isEdit) {
          alertAksesDitolak('ubah');
          return;
        }

        const chk = document.querySelector('.datatables-pg .dt-checkboxes:checked');
        if (!chk) {
          alertPilihData();
          return;
        }

        clearFormData();

        fetch(`${baseUrl}pembayaran-gabungan-list/${chk.value}/edit`)
          .then(r => r.json())
          .then(res => {
            if (!res.status) {
              Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: res.message,
                customClass: { confirmButton: 'btn btn-primary' }
              });
              return;
            }

            const d = res.data;

            document.getElementById('pg_id').value = d.id;
            document.getElementById('add-no-transaksi').value = d.no_transaksi;
            document.getElementById('add-kode-pemasok').value = d.kode_pemasok || '';
            document.getElementById('add-nama-supplier').value = d.nama_supplier || '';

            // Set tanggal
            const tglEl = document.getElementById('add-tanggal');
            if (tglEl && tglEl._flatpickr) tglEl._flatpickr.setDate(d.tanggal_transaksi, false, 'd/m/Y');
            else if (tglEl) tglEl.value = d.tanggal_transaksi;

            setSelectValue('#add-jenis', d.jenis_pembayaran, '');
            setSelectValue('#add-kode-bank', d.kode_bank, '');

            // Load supplier dropdown berdasarkan jenis, lalu set kode_pemasok
            if (d.jenis_pembayaran) {
              const $selSup = $('#add-select-supplier');
              if ($selSup.hasClass('select2-hidden-accessible')) $selSup.select2('destroy');
              $selSup.empty().append(new Option('Memuat data...', '', true, false));
              initSelect2Supplier($selSup, 'Memuat data...');

              fetch(
                `${baseUrl}pembayaran-gabungan-list?get_supplier=1&jenis_pembayaran=${encodeURIComponent(d.jenis_pembayaran)}`
              )
                .then(r => r.json())
                .then(data => {
                  if ($selSup.hasClass('select2-hidden-accessible')) $selSup.select2('destroy');
                  $selSup.empty().append(new Option('Pilih Supplier', '', false, false));

                  data.forEach(function (item) {
                    const isSelected = d.kode_pemasok && item.kode_pemasok === d.kode_pemasok;
                    $selSup.append(new Option(item.nama_pemasok, item.kode_pemasok, false, isSelected));
                  });

                  // Fallback: jika kode_pemasok tidak ada di list
                  if (d.kode_pemasok && !$selSup.find(`option[value="${d.kode_pemasok}"]`).length) {
                    $selSup.append(new Option(d.nama_supplier || d.kode_pemasok, d.kode_pemasok, false, true));
                  }

                  $selSup.val(d.kode_pemasok || null);
                  initSelect2Supplier($selSup, 'Pilih Supplier');
                  $selSup.trigger('change');
                })
                .catch(() => {
                  if ($selSup.hasClass('select2-hidden-accessible')) $selSup.select2('destroy');
                  $selSup
                    .empty()
                    .append(
                      new Option(d.nama_supplier || 'Pilih Supplier', d.kode_pemasok || '', false, !!d.kode_pemasok)
                    );
                  initSelect2Supplier($selSup, 'Pilih Supplier');
                  $selSup.trigger('change');
                });
            }

            // Populate detail IG
            if (Array.isArray(d.details)) {
              d.details.forEach(function (row) {
                addDetailRow(row.kode_input, row.no_bon, row.nilai);
              });
              recalcTotal();
            }

            const modalInstance = new bootstrap.Modal(addRoleModal);
            modalInstance.show();
            addRoleModal.addEventListener('shown.bs.modal', function onShown() {
              filterRekeningByBank(d.kode_bank, d.nama_bank, d.no_rekening);
              addRoleModal.removeEventListener('shown.bs.modal', onShown);
            });
          })
          .catch(() => {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Gagal mengambil data.',
              customClass: { confirmButton: 'btn btn-primary' }
            });
          });
      });
    }

    // ===== TOMBOL HAPUS =====
    const btnDelete = document.querySelector('.delete-record');
    if (btnDelete) {
      btnDelete.addEventListener('click', function () {
        if (!isDelete) {
          alertAksesDitolak('hapus');
          return;
        }

        const chk = document.querySelector('.datatables-pg .dt-checkboxes:checked');
        if (!chk) {
          alertPilihData();
          return;
        }

        Swal.fire({
          title: 'Konfirmasi?',
          text: 'Anda yakin akan menghapus data ini?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal',
          customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
          buttonsStyling: false
        }).then(result => {
          if (result.value) {
            fetch(`${baseUrl}pembayaran-gabungan-list/${chk.value}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
              }
            })
              .then(async res => {
                const text = await res.text();
                let json = {};
                try {
                  json = JSON.parse(text);
                } catch {
                  json = { status: res.ok, message: res.ok ? 'Berhasil' : 'Gagal' };
                }

                if (json.status) {
                  dt_basic.draw();
                  Swal.fire({
                    icon: 'success',
                    title: 'Hapus!',
                    text: json.message || 'Data berhasil dihapus.',
                    customClass: { confirmButton: 'btn btn-success' }
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: json.message || 'Gagal menghapus data.',
                    customClass: { confirmButton: 'btn btn-primary' }
                  });
                }
              })
              .catch(err => {
                Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: String(err),
                  customClass: { confirmButton: 'btn btn-success' }
                });
              });
          } else {
            Swal.fire({
              title: 'Batal',
              text: 'Data batal dihapus.',
              icon: 'error',
              customClass: { confirmButton: 'btn btn-success' }
            });
          }
        });
      });
    }

    // Styling DataTable
    setTimeout(() => {
      [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
        { selector: '.dt-length', classToAdd: 'mb-md-5 mb-0' },
        { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
        { selector: '.dt-layout-full', classToRemove: 'col-md col-12' }
      ].forEach(({ selector, classToRemove, classToAdd }) => {
        document.querySelectorAll(selector).forEach(el => {
          if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
          if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
        });
      });
    }, 100);
  }

  // ===================== FORM VALIDATION =====================
  const addNewDataForm = document.getElementById('addNewDataForm');
  if (addNewDataForm) {
    const fv = FormValidation.formValidation(addNewDataForm, {
      fields: {
        tanggal_transaksi: { validators: { notEmpty: { message: 'Tanggal wajib diisi' } } },
        jenis_pembayaran: { validators: { notEmpty: { message: 'Jenis Pembayaran wajib dipilih' } } },
        nama_supplier: { validators: { notEmpty: { message: 'Nama Supplier wajib dipilih' } } },
        kode_bank: { validators: { notEmpty: { message: 'Keluar Kas/Bank wajib dipilih' } } },
        no_rekening: { validators: { notEmpty: { message: 'No. Rekening wajib dipilih' } } }
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
      const details = collectDetails();
      if (details.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Minimal satu Input Gudang harus ditambahkan.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      document.getElementById('pg_details').value = JSON.stringify(details);

      const formData = new FormData(addNewDataForm);
      const params = new URLSearchParams();
      formData.forEach((value, key) => params.append(key, value));

      PleaseWaitPage();

      fetch(`${baseUrl}pembayaran-gabungan-list`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params.toString()
      })
        .then(async res => {
          const text = await res.text();
          let json = {};
          try {
            json = JSON.parse(text);
          } catch {
            json = { status: false, message: 'Gagal memproses respons.' };
          }

          if (document.querySelector('.notiflix-loading')) Loading.remove();
          bootstrap.Modal.getInstance(addRoleModal)?.hide();
          if (dt_basic_table) new DataTable(dt_basic_table).draw();

          if (json.status) {
            Swal.fire({
              icon: 'success',
              title: 'Informasi!',
              text: json.message,
              customClass: { confirmButton: 'btn btn-success' },
              buttonsStyling: false
            });
          } else {
            let html = `<p>${json.message}</p>`;
            if (json.errors) {
              html += '<ul style="text-align:left;margin-top:10px">';
              Object.values(json.errors).forEach(arr => arr.forEach(m => (html += `<li>${m}</li>`)));
              html += '</ul>';
            }
            Swal.fire({ icon: 'error', title: 'Error!', html, customClass: { confirmButton: 'btn btn-success' } });
          }
        })
        .catch(err => {
          if (document.querySelector('.notiflix-loading')) Loading.remove();
          bootstrap.Modal.getInstance(addRoleModal)?.hide();
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: String(err.message || err),
            customClass: { confirmButton: 'btn btn-success' }
          });
        });
    });

    if (addRoleModal) {
      addRoleModal.addEventListener('hidden.bs.modal', function () {
        if (_navigatingToIg) return; // jangan reset form saat pindah ke modal IG
        fv.resetForm(true);
        clearFormData();
      });
    }
  }

  // ===================== SELECT2 =====================
  $('.select2').each(function () {
    const $this = $(this);
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: $this.data('placeholder') || $this.find('option[value=""]').first().text() || 'Pilih',
      allowClear: true,
      width: '100%',
      dropdownParent: $this.parent()
    });
  });

  // ===================== UPPERCASE =====================
  document.querySelectorAll('.text-uppercase').forEach(el => {
    el.addEventListener('input', function () {
      this.value = this.value.toUpperCase();
    });
  });

  // ===================== AUTO-FILTER REKENING BY BANK =====================
  $('#add-kode-bank').on('change', function () {
    filterRekeningByBank($(this).val() || '', '', '');
  });

  // ===================== LOAD SUPPLIER BY JENIS PEMBAYARAN =====================
  $('#add-jenis').on('change', function () {
    const jenis = $(this).val() || '';

    // Reset supplier & hidden fields
    document.getElementById('add-kode-pemasok').value = '';
    document.getElementById('add-nama-supplier').value = '';

    const $sel = $('#add-select-supplier');
    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $sel.empty();

    if (!jenis) {
      $sel.append(new Option('Pilih Jenis Pembayaran terlebih dahulu', '', true, false));
      initSelect2Supplier($sel, 'Pilih Jenis Pembayaran terlebih dahulu');
      return;
    }

    $sel.append(new Option('Memuat data...', '', true, false));
    initSelect2Supplier($sel, 'Memuat data...');

    fetch(`${baseUrl}pembayaran-gabungan-list?get_supplier=1&jenis_pembayaran=${encodeURIComponent(jenis)}`)
      .then(r => r.json())
      .then(data => {
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append(new Option('Pilih Supplier', '', true, false));
        data.forEach(function (item) {
          $sel.append(new Option(item.nama_pemasok, item.kode_pemasok, false, false));
        });
        initSelect2Supplier($sel, 'Pilih Supplier');
      })
      .catch(() => {
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append(new Option('Gagal memuat data', '', true, false));
        initSelect2Supplier($sel, 'Gagal memuat data');
      });
  });

  // Saat supplier dipilih → isi hidden fields
  $('#add-select-supplier').on('change', function () {
    const kode = $(this).val() || '';
    const nama = $(this).find('option:selected').text() || '';
    document.getElementById('add-kode-pemasok').value = kode;
    document.getElementById('add-nama-supplier').value = kode && nama !== 'Pilih Supplier' ? nama : '';
  });

  // Cegah buka dropdown supplier jika jenis belum dipilih
  $('#add-select-supplier').on('select2:opening', function (e) {
    const jenis = $('#add-jenis').val() || '';
    if (!jenis) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan!',
        text: 'Pilih Jenis Pembayaran terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
    }
  });

  // ===================== SYNC SELECT-ALL CHECKBOX IG =====================
  document.addEventListener('change', function (e) {
    const chk = e.target.closest('.datatables-ig-pg .ig-pg-chk');
    if (chk && !chk.disabled) {
      const allChk = document.querySelector('.datatables-ig-pg thead .ig-chk-all');
      const enabled = document.querySelectorAll('.datatables-ig-pg .ig-pg-chk:not(:disabled)');
      if (allChk && enabled.length) {
        allChk.checked = Array.from(enabled).every(c => c.checked);
      }
    }
  });

  // ===================== FUNGSI BUKA MODAL IG =====================
  function _bukaModalIg() {
    const igPgTable = document.querySelector('.datatables-ig-pg');

    if (igPgTable && !window.dt_ig_pg_global) {
      window.dt_ig_pg_global = new DataTable(igPgTable, {
        searching: true,
        ordering: true,    // aktifkan ordering
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: true,
        ajax: {
          url: baseUrl + 'pembayaran-gabungan-list',
          data: function (d) {
            d.get_ig = 1;
            d.kode_pemasok = document.getElementById('add-kode-pemasok').value || '';
            d.current_id = document.getElementById('pg_id').value || '';
            d.jenis_pembayaran = $('#add-jenis').val() || '';
          },
          dataSrc: 'data'
        },
        columns: [
          { data: 'no_ig', width: '30px' },
          { data: 'tgl_input' },
          { data: 'no_ig' },
          { data: 'no_bon' },
          { data: 'total', className: 'text-end' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            searchable: false,
            render: function (data, type, full) {
              return `<input type="checkbox" class="ig-pg-chk form-check-input" value="${data}"
                data-bon="${full.no_bon || ''}" data-total="${full.total || 0}"
                style="width:16px;height:16px;cursor:pointer;">`;
            }
          },
          {
            targets: 1,
            searchable: false,
            render: function (data) {
              if (!data) return '-';
              const parts = data.split(' ')[0].split('-');
              return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : data;
            }
          },
          {
            targets: 4,
            searchable: false,
            render: function (data) {
              const num = parseInt(String(data || 0).replace(/[^0-9]/g, ''), 10) || 0;
              return num.toLocaleString('en-US');
            }
          }
        ],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
          },
          topEnd: { features: [{ search: { placeholder: 'Cari No. IG / No. Bon Toko', text: '_INPUT_' } }] },
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
          },
          zeroRecords: 'Data tidak ditemukan',
          emptyTable: 'Belum ada data',
          loadingRecords: 'Memuat...'
        },

        // ─────────────────────────────────────────────────────────────
        // drawCallback: setiap render, tandai IG yang sudah ada di detail
        // ─────────────────────────────────────────────────────────────
        drawCallback: function () {
          // Buat header checkbox select-all (hanya sekali)
          const thCheck = document.querySelector('.datatables-ig-pg thead tr th:first-child');
          if (thCheck && !thCheck.querySelector('.ig-chk-all')) {
            thCheck.innerHTML = `<input type="checkbox" class="ig-chk-all form-check-input"
              style="width:16px;height:16px;cursor:pointer;">`;

            thCheck.querySelector('.ig-chk-all').addEventListener('change', function () {
              // Hanya centang/hapus centang checkbox yang TIDAK disabled
              document.querySelectorAll('.datatables-ig-pg .ig-pg-chk:not(:disabled)').forEach(c => {
                c.checked = this.checked;
              });
            });
          }

          // Ambil daftar no_ig yang sudah ada di tbody-detail-ig
          const existingIg = Array.from(document.querySelectorAll('#tbody-detail-ig tr[data-ig]')).map(
            tr => tr.dataset.ig
          );

          // Tandai tiap checkbox: disabled + abu-abu jika sudah ada di detail
          document.querySelectorAll('.datatables-ig-pg .ig-pg-chk').forEach(chk => {
            if (existingIg.includes(chk.value)) {
              chk.checked = true;
              chk.disabled = true;
              const tr = chk.closest('tr');
              if (tr) {
                tr.style.opacity = '0.45';
                tr.style.background = '#f0f0f0';
                tr.title = 'Sudah ditambahkan ke daftar pembayaran';
              }
            } else {
              chk.checked = false;
              chk.disabled = false;
              const tr = chk.closest('tr');
              if (tr) {
                tr.style.opacity = '';
                tr.style.background = '';
                tr.title = '';
              }
            }
          });

          // Update state select-all (hanya hitung yang enabled)
          const allChk = document.querySelector('.datatables-ig-pg thead .ig-chk-all');
          const enabled = document.querySelectorAll('.datatables-ig-pg .ig-pg-chk:not(:disabled)');
          if (allChk) {
            allChk.checked = enabled.length > 0 && Array.from(enabled).every(c => c.checked);
          }
        }
      });

      // Fix styling DataTable IG
      setTimeout(() => {
        [
          { selector: '.datatables-ig-pg .dt-length .form-select', classToAdd: 'ms-0' },
          { selector: '.datatables-ig-pg .dt-length', classToAdd: 'mb-md-5 mb-0' },
          { selector: '.datatables-ig-pg .dt-layout-table', classToRemove: 'row mt-2' },
          { selector: '.datatables-ig-pg .dt-layout-full', classToRemove: 'col-md col-12' }
        ].forEach(({ selector, classToRemove, classToAdd }) => {
          document.querySelectorAll(selector).forEach(el => {
            if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
            if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
          });
        });
      }, 300);
    }

    // Reload data (drawCallback akan jalan dan sync state checkbox)
    loadIgToDataTable();
    new bootstrap.Modal(modalPilihIg).show();
  }

  // ===================== TOMBOL PILIH IG =====================
  const btnPilihIg = document.getElementById('btn-pilih-ig');
  if (btnPilihIg) {
    btnPilihIg.addEventListener('click', function () {
      const jenisCek = $('#add-jenis').val() || '';
      if (!jenisCek) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Pilih Jenis Pembayaran terlebih dahulu.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const kodePemasokCek = document.getElementById('add-kode-pemasok').value || '';
      if (!kodePemasokCek) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Pilih Nama Supplier terlebih dahulu.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const instForm = bootstrap.Modal.getInstance(addRoleModal);
      if (instForm) {
        _navigatingToIg = true;
        addRoleModal.addEventListener('hidden.bs.modal', function onHidden() {
          addRoleModal.removeEventListener('hidden.bs.modal', onHidden);
          _navigatingToIg = false;
          _bukaModalIg();
        });
        instForm.hide();
      } else {
        _bukaModalIg();
      }
    });
  }

  // ===== TOMBOL PILIH DI POPUP IG =====
  const btnTambahIgTerpilih = document.getElementById('btn-tambah-ig-terpilih');
  if (btnTambahIgTerpilih) {
    btnTambahIgTerpilih.addEventListener('click', function () {
      // Hanya ambil checkbox yang checked DAN tidak disabled
      const checked = document.querySelectorAll('.datatables-ig-pg .ig-pg-chk:checked:not(:disabled)');
      if (!checked.length) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih minimal satu Input Gudang.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const existingIg = Array.from(document.querySelectorAll('#tbody-detail-ig tr[data-ig]')).map(tr => tr.dataset.ig);

      checked.forEach(chk => {
        const noIg = chk.value;
        const noBon = chk.dataset.bon || '';
        const total = chk.dataset.total || 0;
        if (!existingIg.includes(noIg)) addDetailRow(noIg, noBon, total);
      });
      recalcTotal();

      const instIg = bootstrap.Modal.getInstance(modalPilihIg);
      if (instIg) {
        modalPilihIg.addEventListener('hidden.bs.modal', function onHiddenIg() {
          modalPilihIg.removeEventListener('hidden.bs.modal', onHiddenIg);
          new bootstrap.Modal(addRoleModal).show();
        });
        instIg.hide();
      } else {
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ===================== BATAL / X DI MODAL IG =====================
  if (modalPilihIg) {
    let _igShouldReturn = false;

    modalPilihIg.querySelectorAll('.btn-close').forEach(btn => {
      btn.addEventListener('click', () => {
        _igShouldReturn = true;
      });
    });

    modalPilihIg.querySelectorAll('.btn-outline-danger').forEach(btn => {
      btn.removeAttribute('data-bs-dismiss');
      btn.addEventListener('click', () => {
        _igShouldReturn = true;
        bootstrap.Modal.getInstance(modalPilihIg)?.hide();
      });
    });

    modalPilihIg.addEventListener('hidden.bs.modal', function () {
      // Reset semua checkbox di tabel IG (yang tidak disabled)
      document.querySelectorAll('.datatables-ig-pg .ig-pg-chk:not(:disabled)').forEach(c => (c.checked = false));
      const allChk = document.querySelector('.datatables-ig-pg thead .ig-chk-all');
      if (allChk) allChk.checked = false;

      if (_igShouldReturn) {
        _igShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ===================== EVENT: HAPUS BARIS DETAIL =====================
  document.getElementById('tbody-detail-ig')?.addEventListener('click', function (e) {
    const btnHapus = e.target.closest('.btn-hapus-detail');
    if (btnHapus) {
      btnHapus.closest('tr').remove();
      renumberRows();
      recalcTotal();
    }
  });
}); // end DOMContentLoaded

// ============================================================
// HELPERS
// ============================================================

function getTodayDMY() {
  const today = new Date();
  return `${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}/${today.getFullYear()}`;
}

function loadIgToDataTable() {
  if (!window.dt_ig_pg_global) return;
  window.dt_ig_pg_global.ajax.reload();
}

function addDetailRow(noIg, noBon, nilai) {
  const tbody = document.getElementById('tbody-detail-ig');
  const noDataRow = document.getElementById('row-no-data');
  if (noDataRow) noDataRow.remove();

  const rowCount = tbody.querySelectorAll('tr[data-ig]').length + 1;
  const nilaiNum = nilai ? parseInt(String(nilai).replace(/[^0-9]/g, ''), 10) : 0;
  const nilaiStr = nilaiNum ? nilaiNum.toLocaleString('en-US') : '';

  const tr = document.createElement('tr');
  tr.dataset.ig = noIg;
  tr.innerHTML = `
    <td class="text-center row-num">${rowCount}</td>
    <td>${noIg}</td>
    <td>${noBon || ''}</td>
    <td>
      <input type="text" class="form-control form-control-sm text-end nilai-detail input-readonly"
        value="${nilaiStr}" placeholder="0" readonly />
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill btn-hapus-detail" title="Hapus">
        <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
}

function renumberRows() {
  const rows = document.querySelectorAll('#tbody-detail-ig tr[data-ig]');
  rows.forEach((tr, i) => {
    const numCell = tr.querySelector('.row-num');
    if (numCell) numCell.textContent = i + 1;
  });
  if (!rows.length) {
    const tbody = document.getElementById('tbody-detail-ig');
    tbody.innerHTML =
      '<tr id="row-no-data"><td colspan="5" class="text-center text-muted py-2">Belum ada IG ditambahkan</td></tr>';
  }
}

function recalcTotal() {
  let total = 0;
  document.querySelectorAll('#tbody-detail-ig .nilai-detail').forEach(inp => {
    const raw = inp.value.replace(/[^0-9]/g, '');
    total += raw ? parseInt(raw, 10) : 0;
  });
  const fmtTotal = total.toLocaleString('en-US');
  document.getElementById('tfoot-total').textContent = fmtTotal;
  document.getElementById('add-total-nilai').value = fmtTotal;
}

function collectDetails() {
  const rows = document.querySelectorAll('#tbody-detail-ig tr[data-ig]');
  const result = [];
  rows.forEach(tr => {
    const kodeInput = tr.dataset.ig;
    const noBon = tr.querySelectorAll('td')[2]?.textContent?.trim() || '';
    const nilaiRaw = tr.querySelector('.nilai-detail')?.value.replace(/[^0-9]/g, '') || '0';
    result.push({ kode_input: kodeInput, no_bon: noBon, nilai: nilaiRaw });
  });
  return result;
}

function filterRekeningByBank(kodeBank, namaBank, preselect) {
  const $sel = $('#add-no-rekening');
  if (!$sel.length) return;
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
  $sel.empty().append(new Option('Pilih No. Rekening', '', true, false));

  if (!kodeBank) {
    initSelect2Rekening($sel);
    return;
  }

  fetch(`${baseUrl}pembayaran-gabungan-list?rekening=1&kode_bank=${kodeBank}`)
    .then(r => r.json())
    .then(data => {
      const valid = data.filter(item => item.no_rekening && item.no_rekening.trim() !== '');
      valid.forEach(function (item) {
        const isSelected = preselect ? item.no_rekening === preselect : false;
        $sel.append(new Option(item.no_rekening, item.no_rekening, false, isSelected));
      });
      initSelect2Rekening($sel);
      const opts = $sel.find('option[value!=""]');
      if (opts.length === 1) $sel.val(opts.first().val()).trigger('change');
      else if (preselect) $sel.val(preselect).trigger('change');
    })
    .catch(() => initSelect2Rekening($sel));
}

function initSelect2Rekening($sel) {
  if (!$sel.parent().hasClass('position-relative')) $sel.wrap('<div class="position-relative"></div>');
  $sel.select2({
    placeholder: 'Pilih No. Rekening',
    allowClear: true,
    width: '100%',
    dropdownParent: $sel.parent(),
    language: { noResults: () => 'Data tidak ditemukan' }
  });
}

function initSelect2Supplier($sel, placeholder) {
  if (!$sel.parent().hasClass('position-relative')) $sel.wrap('<div class="position-relative"></div>');
  $sel.select2({
    placeholder: placeholder || 'Pilih Supplier',
    allowClear: true,
    width: '100%',
    dropdownParent: $sel.parent(),
    language: { noResults: () => 'Data tidak ditemukan' }
  });
}

function clearSelect(selector) {
  const $el = $(selector);
  if (!$el.length) return;
  $el.val(null);
  if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change');
}

function setSelectValue(selector, value, textIfMissing) {
  const $el = $(selector);
  if (!$el.length) return;
  const val = value ?? '';
  if (val !== '' && !$el.find(`option[value="${val}"]`).length)
    $el.append(new Option(textIfMissing ?? val, val, false, false));
  $el.val(val === '' ? null : String(val));
  if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change');
}

function alertAksesDitolak(aksi) {
  Swal.fire({
    icon: 'error',
    title: 'Akses Ditolak',
    text: `Anda tidak memiliki izin untuk ${aksi} data.`,
    customClass: { confirmButton: 'btn btn-primary' }
  });
}

function alertPilihData() {
  Swal.fire({
    icon: 'warning',
    title: 'Peringatan',
    text: 'Silahkan pilih data pada tabel terlebih dahulu!',
    customClass: { confirmButton: 'btn btn-primary' }
  });
}

function clearFormData() {
  document.getElementById('addNewDataForm')?.reset();
  document.getElementById('add-no-transaksi').value = '';
  document.getElementById('add-nama-supplier').value = '';
  document.getElementById('add-kode-pemasok').value = '';
  document.getElementById('add-total-nilai').value = '';

  const elTgl = document.getElementById('add-tanggal');
  if (elTgl) {
    if (elTgl._flatpickr) elTgl._flatpickr.setDate(new Date(), true);
    else elTgl.value = getTodayDMY();
  }

  clearSelect('#add-jenis');
  clearSelect('#add-kode-bank');
  filterRekeningByBank('', '', '');

  const $selSup = $('#add-select-supplier');
  if ($selSup.length) {
    if ($selSup.hasClass('select2-hidden-accessible')) $selSup.select2('destroy');
    $selSup.empty().append(new Option('Pilih Supplier', '', true, false));
    initSelect2Supplier($selSup, 'Pilih Supplier');
  }

  const tbody = document.getElementById('tbody-detail-ig');
  if (tbody)
    tbody.innerHTML =
      '<tr id="row-no-data"><td colspan="5" class="text-center text-muted py-2">Belum ada IG ditambahkan</td></tr>';
  document.getElementById('tfoot-total').textContent = '0';
}
