/**
 * Page Data management
 */
'use strict';

window._isOpeningPopup = false;

document.addEventListener('DOMContentLoaded', function () {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-im');
  const addRoleModal = document.getElementById('addRoleModal');
  const filterModal = document.getElementById('filterRoleModal');
  const modalPilihSpk = document.getElementById('modalPilihSpk');
  const modalPilihIg = document.getElementById('modalPilihIg');

  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  // ── FLATPICKR ────────────────────────────────────────────────────
  document.querySelectorAll('.dt-date').forEach(el => {
    flatpickr(el, { monthSelectorType: 'static', static: true, dateFormat: 'd/m/Y' });
  });

  // ── DATATABLE UTAMA ──────────────────────────────────────────────
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
      autoWidth: true,
      ajax: {
        url: baseUrl + 'input-memorial-list',
        data: function (d) {
          d.no_voucher = $('#filter-no-voucher').val();
          d.jenis = $('#filter-jenis').val();
          d.transaksi = $('#filter-transaksi').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
        },
        dataSrc: function (json) {
          json.recordsTotal = json.recordsTotal || 0;
          json.recordsFiltered = json.recordsFiltered || 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'id', width: '20px' },
        { data: 'tanggal' },
        { data: 'no_voucher' },
        { data: 'jenis' },
        { data: 'transaksi' },
        { data: 'no_spk' },
        { data: 'account_coa' },
        { data: 'jml_dibayar', className: 'text-end' }
      ],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          searchable: false,
          render: data => `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`
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
          next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
        }
      }
    });

    // Satu checkbox aktif
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-im .dt-checkboxes');
      if (chk && chk.checked) $('.datatables-im .dt-checkboxes').not(chk).prop('checked', false);
    });

    // ── Filter ───────────────────────────────────────────────────
    document.getElementById('formCariData')?.addEventListener('submit', function (e) {
      e.preventDefault();
      dt_basic.draw();
      bootstrap.Modal.getInstance(filterModal)?.hide();
    });

    // ── Tambah ───────────────────────────────────────────────────
    document.querySelector('.add-new')?.addEventListener('click', function () {
      if (!isAdd) {
        alertAksesDitolak('tambah');
        return;
      }
      clearFormData();
      document.getElementById('im_id').value = '';
    });

    // ── Ubah ─────────────────────────────────────────────────────
    document.querySelector('.edit-record')?.addEventListener('click', function () {
      if (!isEdit) {
        alertAksesDitolak('ubah');
        return;
      }
      const chk = document.querySelector('.datatables-im .dt-checkboxes:checked');
      if (!chk) {
        alertPilihData();
        return;
      }
      clearFormData();
      fetch(`${baseUrl}input-memorial-list/${chk.value}/edit`)
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
          document.getElementById('im_id').value = d.id;
          document.getElementById('add-no-voucher').value = d.no_voucher;
          document.getElementById('add-jml-dibayar').value = d.jml_dibayar_fmt;
          document.getElementById('add-sisa').value = d.sisa_fmt;
          document.getElementById('add-nilai').value = d.nilai_fmt;
          document.getElementById('add-keterangan').value = d.keterangan || '';

          const tglEl = document.getElementById('add-tanggal');
          if (tglEl?._flatpickr) tglEl._flatpickr.setDate(d.tanggal, false, 'd/m/Y');
          else if (tglEl) tglEl.value = d.tanggal;

          setSelectValue('#add-jenis', d.jenis, '');
          setSelectValue('#add-transaksi', d.transaksi, d.transaksi);
          setSelectValue('#add-account-coa', d.account_coa, d.account_coa);
          setSelectValue('#add-tipe', d.tipe, d.tipe);
          toggleTipe(d.tipe);

          if (d.tipe === 'SPK') {
            document.getElementById('add-no-spk').value = d.no_spk || '';
            document.getElementById('add-nama-pemilik').value = d.nama_pemilik || '';
            document.getElementById('add-no-polisi').value = d.no_polisi || '';
            loadInvoiceBySpk(d.no_spk, d.no_invoice);
          } else if (d.tipe === 'UMUM') {
            document.getElementById('add-nama-supplier').value = d.nama_supplier || '';
            document.getElementById('add-no-ig').value = d.no_ig || '';
            // Jika ada no_ig → load bon toko (nilai dari DB), jika tidak → nilai manual
            if (d.no_ig) {
              loadBonTokoByIg(d.no_ig, d.no_bon_toko, d.nilai_fmt);
            } else {
              // Tidak ada IG → nilai bisa diisi manual, set nilai dari DB
              setNilaiEditable(true);
              document.getElementById('add-nilai').value = d.nilai_fmt || '';
              hitungSisa();
            }
          }

          resetSisaStyle();
          new bootstrap.Modal(addRoleModal).show();
        });
    });

    // ── Hapus ────────────────────────────────────────────────────
    document.querySelector('.delete-record')?.addEventListener('click', function () {
      if (!isDelete) {
        alertAksesDitolak('hapus');
        return;
      }
      const chk = document.querySelector('.datatables-im .dt-checkboxes:checked');
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
        if (!result.value) return;
        fetch(`${baseUrl}input-memorial-list/${chk.value}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
          }
        })
          .then(r => r.json())
          .then(json => {
            dt_basic.draw();
            Swal.fire({
              icon: json.status ? 'success' : 'error',
              title: json.status ? 'Hapus!' : 'Error!',
              text: json.message,
              customClass: { confirmButton: json.status ? 'btn btn-success' : 'btn btn-primary' }
            });
          });
      });
    });

    setTimeout(() => {
      [
        { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
        { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
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

  // ── TIPE CHANGE ──────────────────────────────────────────────────
  $('#add-tipe').on('change', function () {
    toggleTipe($(this).val());
  });

  // ── NILAI manual input (aktif saat UMUM tanpa IG) ────────────────
  document.getElementById('add-nilai')?.addEventListener('input', function () {
    // Hanya format & hitung jika field ini editable
    if (!this.readOnly) {
      this.value = formatAngka(this.value);
      hitungSisa();
    }
  });

  // =====================================================================
  // POPUP: SPK
  // =====================================================================
  function _bukaModalSpk() {
    const spkPgTable = document.querySelector('.datatables-spk-popup');
    if (spkPgTable && !window.dt_spk_global) {
      window.dt_spk_global = new DataTable(spkPgTable, {
        searching: true,
        ordering: true, // aktifkan ordering
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: true,
        ajax: {
          url: baseUrl + 'input-memorial-list',
          data: function (d) {
            d.get_spk = 1;
          },
          dataSrc: 'data'
        },
        columns: [
          { data: 'no_spk', width: '30px' },
          { data: 'tgl_masuk' },
          { data: 'no_spk' },
          { data: 'nama_pemilik', defaultContent: '-' },
          { data: 'no_polisi', defaultContent: '-' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            searchable: false,
            render: function (data, type, full) {
              return `<input type="checkbox" class="spk-pg-chk form-check-input"
                value="${data}"
                data-nama="${full.nama_pemilik || ''}"
                data-polisi="${full.no_polisi || ''}"
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
          }
        ],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
          },
          topEnd: { features: [{ search: { placeholder: 'Cari No. SPK / Nama Pemilik', text: '_INPUT_' } }] },
          bottomStart: {
            rowClass: 'row mx-3 justify-content-between',
            features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
          },
          bottomEnd: 'paging'
        },
        displayLength: 10,
        language: {
          paginate: {
            next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
            previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
          },
          zeroRecords: 'Data tidak ditemukan',
          emptyTable: 'Belum ada data',
          loadingRecords: 'Memuat...'
        },
        drawCallback: function () {
          document.querySelectorAll('.datatables-spk-popup .spk-pg-chk').forEach(chk => {
            chk.addEventListener('change', function () {
              if (this.checked)
                document.querySelectorAll('.datatables-spk-popup .spk-pg-chk').forEach(c => {
                  if (c !== this) c.checked = false;
                });
            });
          });
        }
      });

      setTimeout(() => _stylePopupTable('.datatables-spk-popup'), 300);
    }

    if (window.dt_spk_global) window.dt_spk_global.ajax.reload();
    new bootstrap.Modal(modalPilihSpk).show();
  }

  document.getElementById('btn-pilih-spk')?.addEventListener('click', function () {
    const instForm = bootstrap.Modal.getInstance(addRoleModal);
    if (instForm) {
      window._isOpeningPopup = true;
      addRoleModal.addEventListener('hidden.bs.modal', function onHiddenSpk() {
        addRoleModal.removeEventListener('hidden.bs.modal', onHiddenSpk);
        window._isOpeningPopup = false;
        _bukaModalSpk();
      });
      instForm.hide();
    } else {
      _bukaModalSpk();
    }
  });

  document.getElementById('btn-pilih-spk-terpilih')?.addEventListener('click', function () {
    const chk = document.querySelector('.datatables-spk-popup .spk-pg-chk:checked');
    if (!chk) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        text: 'Pilih satu SPK terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
      return;
    }
    document.getElementById('add-no-spk').value = chk.value;
    document.getElementById('add-nama-pemilik').value = chk.dataset.nama || '';
    document.getElementById('add-no-polisi').value = chk.dataset.polisi || '';
    loadInvoiceBySpk(chk.value, '');

    const instSpk = bootstrap.Modal.getInstance(modalPilihSpk);
    if (instSpk) {
      modalPilihSpk.addEventListener('hidden.bs.modal', function onBack() {
        modalPilihSpk.removeEventListener('hidden.bs.modal', onBack);
        new bootstrap.Modal(addRoleModal).show();
      });
      instSpk.hide();
    } else {
      new bootstrap.Modal(addRoleModal).show();
    }
  });

  // ── Batal & X kembali ke form (SPK) ────────────────────────────
  if (modalPilihSpk) {
    let _spkShouldReturn = false;

    modalPilihSpk.querySelectorAll('.btn-close').forEach(btn => {
      btn.addEventListener('click', () => {
        _spkShouldReturn = true;
      });
    });

    modalPilihSpk.querySelectorAll('.btn-outline-danger').forEach(btn => {
      btn.removeAttribute('data-bs-dismiss');
      btn.addEventListener('click', () => {
        _spkShouldReturn = true;
        bootstrap.Modal.getInstance(modalPilihSpk)?.hide();
      });
    });

    modalPilihSpk.addEventListener('hidden.bs.modal', function () {
      document.querySelectorAll('.datatables-spk-popup .spk-pg-chk').forEach(c => (c.checked = false));
      if (_spkShouldReturn) {
        _spkShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // =====================================================================
  // POPUP: IG
  // =====================================================================
  function _bukaModalIg() {
    const igTable = document.querySelector('.datatables-ig-popup');
    if (igTable && !window.dt_ig_global) {
      window.dt_ig_global = new DataTable(igTable, {
        searching: true,
        ordering: true, // aktifkan ordering
        processing: true,
        serverSide: true,
        scrollX: false,
        autoWidth: false,
        ajax: {
          url: baseUrl + 'input-memorial-list',
          data: function (d) {
            d.get_ig = 1;
          },
          dataSrc: 'data'
        },
        columns: [
          { data: 'no_ig', width: '30px' },
          { data: 'tgl_input' },
          { data: 'no_ig' },
          { data: 'no_bon', defaultContent: '-' },
          { data: 'nama_supplier', defaultContent: '-' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            searchable: false,
            render: function (data, type, full) {
              return `<input type="checkbox" class="ig-pg-chk form-check-input"
                value="${data}"
                data-bon="${full.no_bon || ''}"
                data-supplier="${full.nama_supplier || ''}"
                data-total="${full.total || 0}"
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
          }
        ],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
          },
          topEnd: { features: [{ search: { placeholder: 'Cari No. IG / Nama Supplier', text: '_INPUT_' } }] },
          bottomStart: {
            rowClass: 'row mx-3 justify-content-between',
            features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
          },
          bottomEnd: 'paging'
        },
        displayLength: 10,
        language: {
          paginate: {
            next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
            previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
          },
          zeroRecords: 'Data tidak ditemukan',
          emptyTable: 'Belum ada data',
          loadingRecords: 'Memuat...'
        },
        drawCallback: function () {
          document.querySelectorAll('.datatables-ig-popup .ig-pg-chk').forEach(chk => {
            chk.addEventListener('change', function () {
              if (this.checked)
                document.querySelectorAll('.datatables-ig-popup .ig-pg-chk').forEach(c => {
                  if (c !== this) c.checked = false;
                });
            });
          });
        }
      });

      setTimeout(() => _stylePopupTable('.datatables-ig-popup'), 300);
    }

    if (window.dt_ig_global) window.dt_ig_global.ajax.reload();
    new bootstrap.Modal(modalPilihIg).show();
  }

  document.getElementById('btn-pilih-ig')?.addEventListener('click', function () {
    const instForm = bootstrap.Modal.getInstance(addRoleModal);
    if (instForm) {
      window._isOpeningPopup = true;
      addRoleModal.addEventListener('hidden.bs.modal', function onHiddenIg() {
        addRoleModal.removeEventListener('hidden.bs.modal', onHiddenIg);
        window._isOpeningPopup = false;
        _bukaModalIg();
      });
      instForm.hide();
    } else {
      _bukaModalIg();
    }
  });

  document.getElementById('btn-pilih-ig-terpilih')?.addEventListener('click', function () {
    const chk = document.querySelector('.datatables-ig-popup .ig-pg-chk:checked');
    if (!chk) {
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan',
        text: 'Pilih satu IG terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
      return;
    }
    document.getElementById('add-nama-supplier').value = chk.dataset.supplier || '';
    document.getElementById('add-no-ig').value = chk.value;
    // Setelah IG dipilih → nilai auto-fill dari IG, readonly
    loadBonTokoByIg(chk.value, '', null);

    const instIg = bootstrap.Modal.getInstance(modalPilihIg);
    if (instIg) {
      modalPilihIg.addEventListener('hidden.bs.modal', function onBack() {
        modalPilihIg.removeEventListener('hidden.bs.modal', onBack);
        new bootstrap.Modal(addRoleModal).show();
      });
      instIg.hide();
    } else {
      new bootstrap.Modal(addRoleModal).show();
    }
  });

  // ── Batal & X kembali ke form (IG) ─────────────────────────────
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
      document.querySelectorAll('.datatables-ig-popup .ig-pg-chk').forEach(c => (c.checked = false));
      if (_igShouldReturn) {
        _igShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ── HITUNG SISA ──────────────────────────────────────────────────
  document.querySelectorAll('.hitung-sisa').forEach(el => {
    el.addEventListener('input', function () {
      this.value = formatAngka(this.value);
      hitungSisa();
    });
  });

  // AUTO-FILL NILAI saat invoice dipilih (tipe SPK)
  $(document).on('change', '#add-no-invoice', function () {
    const nilai = $(this).find('option:selected').data('nilai') ?? 0;
    $('#add-nilai').val(nilai ? Number(nilai).toLocaleString('en-US') : '');
    hitungSisa();
  });

  // ── FORM VALIDATION ──────────────────────────────────────────────
  const addNewDataForm = document.getElementById('addNewDataForm');
  if (addNewDataForm) {
    const fv = FormValidation.formValidation(addNewDataForm, {
      fields: {
        tanggal: { validators: { notEmpty: { message: 'Tanggal wajib diisi' } } },
        jenis: { validators: { notEmpty: { message: 'Jenis wajib dipilih' } } },
        transaksi: { validators: { notEmpty: { message: 'Transaksi wajib dipilih' } } },
        tipe: { validators: { notEmpty: { message: 'Tipe wajib dipilih' } } },
        jml_dibayar: { validators: { notEmpty: { message: 'Jumlah wajib diisi' } } },
        account_coa: { validators: { notEmpty: { message: 'Account/COA wajib dipilih' } } }
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
      const formData = new FormData(addNewDataForm);
      const params = new URLSearchParams();
      formData.forEach((v, k) => params.append(k, v));
      PleaseWaitPage();
      fetch(`${baseUrl}input-memorial-list`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params.toString()
      })
        .then(r => r.json())
        .then(json => {
          if (document.querySelector('.notiflix-loading')) Loading.remove();
          bootstrap.Modal.getInstance(addRoleModal)?.hide();
          if (dt_basic_table) new DataTable(dt_basic_table).draw();
          Swal.fire({
            icon: json.status ? 'success' : 'error',
            title: json.status ? 'Informasi!' : 'Error!',
            text: json.message,
            customClass: { confirmButton: json.status ? 'btn btn-success' : 'btn btn-primary' }
          });
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

    // ← FIX: reset form HANYA jika modal ditutup beneran (bukan sementara untuk buka popup)
    addRoleModal?.addEventListener('hidden.bs.modal', function () {
      if (window._isOpeningPopup) return;
      fv.resetForm(true);
      clearFormData();
    });
  }

  // ── SELECT2 ──────────────────────────────────────────────────────
  $('.select2').each(function () {
    const $this = $(this);
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: $this.find('option[value=""]').first().text() || 'Pilih',
      allowClear: true,
      width: '100%',
      dropdownParent: $this.parent()
    });
  });
}); // end DOMContentLoaded

// ============================================================
// HELPER: set Nilai editable / readonly
// ============================================================
function setNilaiEditable(editable) {
  const nilaiEl = document.getElementById('add-nilai');
  if (!nilaiEl) return;
  if (editable) {
    nilaiEl.readOnly = false;
    nilaiEl.classList.remove('input-readonly');
    nilaiEl.placeholder = '0';
  } else {
    nilaiEl.readOnly = true;
    nilaiEl.classList.add('input-readonly');
    nilaiEl.placeholder = '';
  }
}

// ============================================================
// TOGGLE TIPE SPK / UMUM
// ============================================================
function toggleTipe(tipe) {
  const sectionSpk = document.getElementById('section-spk');
  const sectionIg = document.getElementById('section-ig');

  if (tipe === 'SPK') {
    $(sectionSpk).show();
    $(sectionIg).hide();
    clearIgFields();
    // Nilai readonly → diisi otomatis dari invoice
    setNilaiEditable(false);
    $('#add-nilai').val('');
    hitungSisa();
  } else if (tipe === 'UMUM') {
    $(sectionSpk).hide();
    $(sectionIg).hide();
    clearSpkFields();
    clearIgFields();
    // Nilai editable manual (belum ada IG)
    setNilaiEditable(true);
    $('#add-nilai').val('');
    hitungSisa();
  } else {
    $(sectionSpk).hide();
    $(sectionIg).hide();
    setNilaiEditable(false);
    $('#add-nilai').val('');
    hitungSisa();
  }
}

// ============================================================
// CONTROLLER: Load Invoice by SPK
// ============================================================
function loadInvoiceBySpk(noSpk, preselect) {
  const $sel = $('#add-no-invoice');
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
  $sel.empty().append(new Option('Pilih SPK dahulu', '', true, false));
  if (!noSpk) {
    initSelect2Simple($sel, 'Pilih SPK dahulu');
    $('#add-nilai').val('');
    hitungSisa();
    return;
  }
  fetch(`${baseUrl}input-memorial-list?get_invoice_by_spk=1&no_spk=${encodeURIComponent(noSpk)}`)
    .then(r => r.json())
    .then(data => {
      $sel.empty().append(new Option('Pilih Invoice', '', true, false));
      if (!data.length) {
        initSelect2Simple($sel, 'Invoice tidak tersedia');
        $('#add-nilai').val('');
        hitungSisa();
        return;
      }
      data.forEach(item => {
        const isSelected = preselect ? item.no_invoice === preselect : false;
        const opt = new Option(item.no_invoice, item.no_invoice, false, isSelected);
        $(opt).data('nilai', item.nilai);
        $sel.append(opt);
      });
      initSelect2Simple($sel, 'Pilih Invoice');
      if (preselect) $sel.val(preselect).trigger('change');
    })
    .catch(() => initSelect2Simple($sel, 'Pilih Invoice'));
}

// ============================================================
// CONTROLLER: Load Bon Toko by IG
// ============================================================
function loadBonTokoByIg(noIg, preselect, nilaiOverride) {
  const $sel = $('#add-no-bon-toko');
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
  $sel.empty().append(new Option('Pilih Bon Toko', '', true, false));

  if (!noIg) {
    initSelect2Simple($sel, 'Pilih IG dahulu');
    // Tidak ada IG → nilai bisa diisi manual (tipe UMUM)
    setNilaiEditable(true);
    if (nilaiOverride === undefined || nilaiOverride === null) {
      $('#add-nilai').val('');
      hitungSisa();
    }
    return;
  }

  // Ada IG → nilai auto dari data IG, kembalikan readonly
  setNilaiEditable(false);

  fetch(`${baseUrl}input-memorial-list?get_ig=1&search=${encodeURIComponent(noIg)}`)
    .then(r => r.json())
    .then(json => {
      const data = Array.isArray(json) ? json : json.data || [];
      const igRow = data.find(item => item.no_ig === noIg);
      if (nilaiOverride !== undefined && nilaiOverride !== null && nilaiOverride !== '') {
        $('#add-nilai').val(nilaiOverride);
      } else {
        const total = igRow ? parseFloat(igRow.total) || 0 : 0;
        $('#add-nilai').val(total ? total.toLocaleString('en-US') : '');
      }
      hitungSisa();
      data.forEach(item => {
        if (item.no_bon) {
          const isSelected = preselect ? item.no_bon === preselect : false;
          $sel.append(new Option(item.no_bon, item.no_bon, false, isSelected));
        }
      });
      initSelect2Simple($sel, 'Pilih Bon Toko');
      if (preselect) $sel.val(preselect).trigger('change');
    })
    .catch(() => initSelect2Simple($sel, 'Pilih Bon Toko'));
}

// ============================================================
// STYLING HELPER POPUP DATATABLE
// ============================================================
function _stylePopupTable(prefix) {
  [
    { selector: `${prefix} .dt-length .form-select`, classToAdd: 'ms-0' },
    { selector: `${prefix} .dt-length`, classToAdd: 'mb-md-5 mb-0' },
    { selector: `${prefix} .dt-layout-table`, classToRemove: 'row mt-2' },
    { selector: `${prefix} .dt-layout-full`, classToRemove: 'col-md col-12' }
  ].forEach(({ selector, classToRemove, classToAdd }) => {
    document.querySelectorAll(selector).forEach(el => {
      if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
      if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
    });
  });
}

// ============================================================
// SELECT2 HELPERS
// ============================================================
function initSelect2Simple($sel, placeholder) {
  if (!$sel.parent().hasClass('position-relative')) $sel.wrap('<div class="position-relative"></div>');
  $sel.select2({ placeholder, allowClear: true, width: '100%', dropdownParent: $sel.parent() });
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

function clearSelect(selector) {
  const $el = $(selector);
  if (!$el.length) return;
  $el.val(null);
  if ($el.hasClass('select2-hidden-accessible')) $el.trigger('change');
}

// ============================================================
// CLEAR FIELDS
// ============================================================
function clearSpkFields() {
  ['add-no-spk', 'add-nama-pemilik', 'add-no-polisi'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const $inv = $('#add-no-invoice');
  if ($inv.hasClass('select2-hidden-accessible')) $inv.select2('destroy');
  $inv.empty().append(new Option('Pilih SPK dahulu', '', true, false));
  initSelect2Simple($inv, 'Pilih SPK dahulu');
  $('#add-nilai').val('');
  hitungSisa();
}

function clearIgFields() {
  ['add-nama-supplier', 'add-no-ig'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const $bon = $('#add-no-bon-toko');
  if ($bon.hasClass('select2-hidden-accessible')) $bon.select2('destroy');
  $bon.empty().append(new Option('Pilih IG dahulu', '', true, false));
  initSelect2Simple($bon, 'Pilih IG dahulu');
  $('#add-nilai').val('');

  // Jika tipe UMUM → Nilai kembali editable saat IG di-clear
  const tipe = $('#add-tipe').val();
  if (tipe === 'UMUM') {
    setNilaiEditable(true);
  }

  hitungSisa();
}

// ============================================================
// KALKULASI SISA
// ============================================================
function hitungSisa() {
  const nilai = parseAngka($('#add-nilai').val());
  const jml = parseAngka($('#add-jml-dibayar').val());
  const sisa = nilai - jml;
  $('#add-sisa').val(sisa.toLocaleString('en-US'));
  if (sisa < 0) $('#add-sisa').removeClass('text-primary').addClass('text-danger');
  else $('#add-sisa').removeClass('text-danger').addClass('text-primary');
}

function resetSisaStyle() {
  $('#add-sisa').removeClass('text-danger').addClass('text-primary');
}

// ============================================================
// NUMBER UTILS
// ============================================================
function parseAngka(val) {
  return parseFloat(String(val).replace(/[^0-9.-]/g, '')) || 0;
}
function formatAngka(val) {
  const num = parseFloat(String(val).replace(/[^0-9]/g, '')) || 0;
  return num ? num.toLocaleString('en-US') : '';
}

// ============================================================
// ALERT HELPERS
// ============================================================
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
  [
    'add-no-voucher',
    'add-nilai',
    'add-jml-dibayar',
    'add-sisa',
    'add-keterangan',
    'add-no-spk',
    'add-nama-pemilik',
    'add-no-polisi',
    'add-nama-supplier',
    'add-no-ig'
  ].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  clearSelect('#add-jenis');
  clearSelect('#add-transaksi');
  clearSelect('#add-tipe');
  clearSelect('#add-account-coa');
  clearSpkFields();
  clearIgFields();

  // Reset Nilai ke readonly (tipe belum dipilih)
  setNilaiEditable(false);

  const tgl = document.getElementById('add-tanggal');
  if (tgl?._flatpickr) tgl._flatpickr.setDate(new Date(), true);
  $('#section-spk').hide();
  $('#section-ig').hide();
  resetSisaStyle();
}
