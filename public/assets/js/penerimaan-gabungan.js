/**
 * Page Data management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-pg');
  const addRoleModal = document.getElementById('addRoleModal');
  const filterRoleModal = document.getElementById('filterRoleModal');
  const modalPilihSpk = document.getElementById('modalPilihSpk');

  // Flag: sedang navigasi ke modal SPK (jangan reset form)
  let _navigatingToSpk = false;

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
        url: baseUrl + 'penerimaan-gabungan-list',
        data: function (d) {
          d.no_transaksi = $('#filter-no-transaksi').val();
          d.nama_customer = $('#filter-nama').val();
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
        { data: 'id', width: '20px' },
        { data: 'tanggal_transaksi' },
        { data: 'no_transaksi' },
        { data: 'jenis_pembayaran' },
        { data: 'nama_customer' },
        { data: 'nama_bank' },
        { data: 'no_rekening' },
        { data: 'total_nilai', className: 'text-end' }
      ],
      columnDefs: [
        {
          // For Checkboxes
          targets: 0,
          orderable: false,
          searchable: false,
          // responsivePriority: 2,
          checkboxes: true,
          render: function (data, type, full, meta) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        }
        // {
        //   targets: 0,
        //   orderable: false,
        //   searchable: false,
        //   render: function (data) {
        //     return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
        //   }
        // }
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

        fetch(`${baseUrl}penerimaan-gabungan-list/${chk.value}/edit`)
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
            document.getElementById('add-nama-customer').value = d.nama_customer;
            document.getElementById('add-kode-pelanggan').value = d.kode_pelanggan || '';

            // Set tanggal
            const tglEl = document.getElementById('add-tanggal');
            if (tglEl && tglEl._flatpickr) tglEl._flatpickr.setDate(d.tanggal_transaksi, false, 'd/m/Y');
            else if (tglEl) tglEl.value = d.tanggal_transaksi;

            setSelectValue('#add-jenis', d.jenis_pembayaran, '');
            setSelectValue('#add-kode-bank', d.kode_bank, '');

            // Load customer dropdown berdasarkan jenis, lalu set kode_pelanggan
            if (d.jenis_pembayaran) {
              const $selCust = $('#add-select-customer');
              if ($selCust.hasClass('select2-hidden-accessible')) $selCust.select2('destroy');
              $selCust.empty().append(new Option('Memuat data...', '', true, false));
              initSelect2Customer($selCust, 'Pilih Customer');

              fetch(
                `${baseUrl}penerimaan-gabungan-list?get_customer=1&jenis_pembayaran=${encodeURIComponent(d.jenis_pembayaran)}`
              )
                .then(r => r.json())
                .then(data => {
                  if ($selCust.hasClass('select2-hidden-accessible')) $selCust.select2('destroy');
                  $selCust.empty().append(new Option('Pilih Customer', '', false, false));

                  data.forEach(function (item) {
                    const isSelected = d.kode_pelanggan && item.kode_pelanggan === d.kode_pelanggan;
                    $selCust.append(new Option(item.nama_pelanggan, item.kode_pelanggan, false, isSelected));
                  });

                  // Fallback: jika kode_pelanggan tidak ada di list
                  if (d.kode_pelanggan && !$selCust.find(`option[value="${d.kode_pelanggan}"]`).length) {
                    $selCust.append(new Option(d.nama_customer, d.kode_pelanggan, false, true));
                  }

                  $selCust.val(d.kode_pelanggan || null);
                  initSelect2Customer($selCust, 'Pilih Customer');
                  $selCust.trigger('change');
                })
                .catch(() => {
                  if ($selCust.hasClass('select2-hidden-accessible')) $selCust.select2('destroy');
                  $selCust
                    .empty()
                    .append(
                      new Option(d.nama_customer || 'Pilih Customer', d.kode_pelanggan || '', false, !!d.kode_pelanggan)
                    );
                  initSelect2Customer($selCust, 'Pilih Customer');
                  $selCust.trigger('change');
                });
            }

            // // Populate detail SPK
            // if (Array.isArray(d.details)) {
            //   d.details.forEach(function (row) {
            //     addDetailRow(row.no_spk, row.nama_customer, row.nilai);
            //   });
            //   recalcTotal();
            // }
            if (Array.isArray(d.details)) {
              d.details.forEach(function (row) {
                addDetailRow(row.no_spk, row.nama_customer, row.nilai, row.pph, row.biaya_merimen);
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
            fetch(`${baseUrl}penerimaan-gabungan-list/${chk.value}`, {
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

    document.getElementById('tbody-detail-spk')?.addEventListener('input', function (e) {
      const inp = e.target.closest('.pph-detail');
      if (!inp) return;

      const raw = inp.value.replace(/[^0-9]/g, '');

      const tr = inp.closest('tr');
      const nilaiInp = tr?.querySelector('.nilai-detail');
      const nilaiNum = parseInt((nilaiInp?.value || '0').replace(/[^0-9]/g, ''), 10) || 0;
      let pphNum = parseInt(raw || '0', 10) || 0;

      if (pphNum > nilaiNum) {
        pphNum = nilaiNum;
      }

      inp.value = pphNum ? pphNum.toLocaleString('en-US') : '';

      // Biaya Merimen
      // const inp2 = e.target.closest('.biaya-merimen-detail');
      // if (!inp2) return;

      // const raw2 = inp2.value.replace(/[^0-9]/g, '');
      // let bMerimenNum = parseInt(raw2 || '0', 10) || 0;
      // inp2.value = bMerimenNum ? bMerimenNum.toLocaleString('en-US') : '';

      recalcTotal();
    });

    document.getElementById('tbody-detail-spk')?.addEventListener('input', function (e) {
      const inp = e.target.closest('.biaya-merimen-detail');
      if (!inp) return;

      const raw = inp.value.replace(/[^0-9]/g, '');
      let bMerimenNum = parseInt(raw || '0', 10) || 0;

      inp.value = bMerimenNum ? bMerimenNum.toLocaleString('en-US') : '';

      recalcTotal();
    });

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
        nama_customer: { validators: { notEmpty: { message: 'Nama Customer wajib diisi' } } },
        kode_bank: { validators: { notEmpty: { message: 'Masuk Kas/Bank wajib dipilih' } } },
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
          text: 'Minimal satu SPK harus ditambahkan.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      document.getElementById('pg_details').value = JSON.stringify(details);

      const formData = new FormData(addNewDataForm);
      const params = new URLSearchParams();
      formData.forEach((value, key) => params.append(key, value));

      PleaseWaitPage();

      fetch(`${baseUrl}penerimaan-gabungan-list`, {
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

    // Hanya reset/clear jika TIDAK sedang navigasi ke modal SPK
    if (addRoleModal) {
      addRoleModal.addEventListener('hidden.bs.modal', function () {
        if (_navigatingToSpk) return;
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

  // ===================== LOAD CUSTOMER BY JENIS PEMBAYARAN =====================
  $('#add-jenis').on('change', function () {
    const jenis = $(this).val() || '';

    // Reset customer & hidden fields
    document.getElementById('add-kode-pelanggan').value = '';
    document.getElementById('add-nama-customer').value = '';

    const $sel = $('#add-select-customer');
    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $sel.empty();

    if (!jenis) {
      $sel.append(new Option('Pilih Jenis Pembayaran terlebih dahulu', '', true, false));
      initSelect2Customer($sel, 'Pilih Jenis Pembayaran terlebih dahulu');
      return;
    }

    $sel.append(new Option('Memuat data...', '', true, false));
    initSelect2Customer($sel, 'Memuat data...');

    fetch(`${baseUrl}penerimaan-gabungan-list?get_customer=1&jenis_pembayaran=${encodeURIComponent(jenis)}`)
      .then(r => r.json())
      .then(data => {
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append(new Option('Pilih Customer', '', true, false));
        data.forEach(function (item) {
          $sel.append(new Option(item.nama_pelanggan, item.kode_pelanggan, false, false));
        });
        initSelect2Customer($sel, 'Pilih Customer');
      })
      .catch(() => {
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append(new Option('Gagal memuat data', '', true, false));
        initSelect2Customer($sel, 'Gagal memuat data');
      });
  });

  // Saat customer dipilih → isi hidden fields
  $('#add-select-customer').on('change', function () {
    const kode = $(this).val() || '';
    const nama = $(this).find('option:selected').text() || '';
    document.getElementById('add-kode-pelanggan').value = kode;
    document.getElementById('add-nama-customer').value = kode && nama !== 'Pilih Customer' ? nama : '';
  });

  // Cegah buka dropdown customer jika jenis belum dipilih
  $('#add-select-customer').on('select2:opening', function (e) {
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

  // ===================== SYNC SELECT-ALL CHECKBOX SPK =====================
  document.addEventListener('change', function (e) {
    const chk = e.target.closest('.datatables-spk-pg .spk-pg-chk');
    if (chk && !chk.disabled) {
      const allChk = document.querySelector('.datatables-spk-pg thead .spk-chk-all');
      const enabled = document.querySelectorAll('.datatables-spk-pg .spk-pg-chk:not(:disabled)');
      if (allChk && enabled.length) {
        allChk.checked = Array.from(enabled).every(c => c.checked);
      }
    }
  });

  // ===================== FUNGSI BUKA MODAL SPK =====================
  function _bukaModalSpk() {
    const spkPgTable = document.querySelector('.datatables-spk-pg');

    if (spkPgTable && !window.dt_spk_pg_global) {
      window.dt_spk_pg_global = new DataTable(spkPgTable, {
        searching: true,
        ordering: true, // aktifkan ordering
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: true,
        // ajax: {
        //   url: baseUrl + 'penerimaan-gabungan-list',
        //   data: function (d) {
        //     d.get_spk = 1;
        //     d.jenis_pembayaran = $('#add-jenis').val() || '';
        //     d.kode_pelanggan = document.getElementById('add-kode-pelanggan').value || '';
        //     d.current_id = document.getElementById('pg_id').value || '';
        //   },
        //   dataSrc: 'data'
        // },
        // columns: [
        //   { data: 'no_spk', width: '30px' },
        //   { data: 'tgl_masuk' },
        //   { data: 'no_spk' },
        //   { data: 'nama_customer' },
        //   { data: 'total_or', className: 'text-end' }
        // ],
        order: [[1, 'desc']], // ← default: tgl_masuk terbaru di atas
        ajax: {
          url: baseUrl + 'penerimaan-gabungan-list',
          data: function (d) {
            d.get_spk = 1;
            d.jenis_pembayaran = $('#add-jenis').val() || '';
            d.kode_pelanggan = document.getElementById('add-kode-pelanggan').value || '';
            d.current_id = document.getElementById('pg_id').value || '';
          },
          dataSrc: 'data'
        },
        columns: [
          { data: 'no_spk', width: '30px' },
          { data: 'tanggal' },
          { data: 'no_spk' },
          { data: 'nama_customer' },
          { data: 'nilai', className: 'text-end' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            searchable: false,
            render: function (data, type, full) {
              return `<input type="checkbox" class="spk-pg-chk form-check-input" value="${data}"
                data-nama="${full.nama_customer || ''}" data-total="${full.nilai || 0}"
                style="width:16px;height:16px;cursor:pointer;">`;
            }
          },
          // {
          //   targets: 1,
          //   searchable: false,
          //   render: function (data) {
          //     if (!data) return '-';
          //     const parts = data.split(' ')[0].split('-');
          //     return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : data;
          //   }
          // },
          // {
          //   targets: 4,
          //   searchable: false,
          //   render: function (data) {
          //     const num = parseInt(String(data || 0).replace(/[^0-9]/g, ''), 10) || 0;
          //     return num.toLocaleString('en-US');
          //   }
          // }
        ],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
          },
          topEnd: { features: [{ search: { placeholder: 'Cari No. SPK / Nama Customer', text: '_INPUT_' } }] },
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
        // drawCallback: setiap render, tandai SPK yang sudah ada di detail
        // ─────────────────────────────────────────────────────────────
        drawCallback: function () {
          // Buat header checkbox select-all (hanya sekali)
          const thCheck = document.querySelector('.datatables-spk-pg thead tr th:first-child');
          if (thCheck && !thCheck.querySelector('.spk-chk-all')) {
            thCheck.innerHTML = `<input type="checkbox" class="spk-chk-all form-check-input"
              style="width:16px;height:16px;cursor:pointer;">`;

            thCheck.querySelector('.spk-chk-all').addEventListener('change', function () {
              // Hanya centang/hapus centang checkbox yang TIDAK disabled
              document.querySelectorAll('.datatables-spk-pg .spk-pg-chk:not(:disabled)').forEach(c => {
                c.checked = this.checked;
              });
            });
          }

          // Ambil daftar no_spk yang sudah ada di tbody-detail-spk
          const existingSpk = Array.from(document.querySelectorAll('#tbody-detail-spk tr[data-spk]')).map(
            tr => tr.dataset.spk
          );

          // Tandai tiap checkbox: disabled + abu-abu jika sudah ada di detail
          document.querySelectorAll('.datatables-spk-pg .spk-pg-chk').forEach(chk => {
            if (existingSpk.includes(chk.value)) {
              chk.checked = true;
              chk.disabled = true;
              const tr = chk.closest('tr');
              if (tr) {
                tr.style.opacity = '0.45';
                tr.style.background = '#f0f0f0';
                tr.title = 'Sudah ditambahkan ke daftar penerimaan';
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
          const allChk = document.querySelector('.datatables-spk-pg thead .spk-chk-all');
          const enabled = document.querySelectorAll('.datatables-spk-pg .spk-pg-chk:not(:disabled)');
          if (allChk) {
            allChk.checked = enabled.length > 0 && Array.from(enabled).every(c => c.checked);
          }
        }
      });

      // Fix styling DataTable SPK
      setTimeout(() => {
        [
          { selector: '.datatables-spk-pg .dt-length .form-select', classToAdd: 'ms-0' },
          { selector: '.datatables-spk-pg .dt-length', classToAdd: 'mb-md-5 mb-0' },
          { selector: '.datatables-spk-pg .dt-layout-table', classToRemove: 'row mt-2' },
          { selector: '.datatables-spk-pg .dt-layout-full', classToRemove: 'col-md col-12' }
        ].forEach(({ selector, classToRemove, classToAdd }) => {
          document.querySelectorAll(selector).forEach(el => {
            if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
            if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
          });
        });
      }, 300);
    }

    // Reload data (drawCallback akan jalan dan sync state checkbox)
    loadSpkToDataTable();
    new bootstrap.Modal(modalPilihSpk).show();
  }

  // ===================== TOMBOL PILIH SPK =====================
  const btnPilihSpk = document.getElementById('btn-pilih-spk');
  if (btnPilihSpk) {
    btnPilihSpk.addEventListener('click', function () {
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

      const kodeCek = document.getElementById('add-kode-pelanggan').value || '';
      if (!kodeCek) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Pilih Nama Customer terlebih dahulu.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const instForm = bootstrap.Modal.getInstance(addRoleModal);
      if (instForm) {
        _navigatingToSpk = true;
        addRoleModal.addEventListener('hidden.bs.modal', function onHidden() {
          addRoleModal.removeEventListener('hidden.bs.modal', onHidden);
          _navigatingToSpk = false;
          _bukaModalSpk();
        });
        instForm.hide();
      } else {
        _bukaModalSpk();
      }
    });
  }

  // ===== TOMBOL PILIH DI POPUP SPK =====
  const btnTambahSpkTerpilih = document.getElementById('btn-tambah-spk-terpilih');
  if (btnTambahSpkTerpilih) {
    btnTambahSpkTerpilih.addEventListener('click', function () {
      // Hanya ambil checkbox yang checked DAN tidak disabled
      const checked = document.querySelectorAll('.datatables-spk-pg .spk-pg-chk:checked:not(:disabled)');
      if (!checked.length) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih minimal satu SPK.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const existingSpk = Array.from(document.querySelectorAll('#tbody-detail-spk tr[data-spk]')).map(
        tr => tr.dataset.spk
      );

      checked.forEach(chk => {
        const noSpk = chk.value;
        const namaCustomer = chk.dataset.nama || '';
        const totalOr = chk.dataset.total || 0;
        if (!existingSpk.includes(noSpk)) addDetailRow(noSpk, namaCustomer, totalOr, 0, 0);
      });
      recalcTotal();

      const instSpk = bootstrap.Modal.getInstance(modalPilihSpk);
      if (instSpk) {
        modalPilihSpk.addEventListener('hidden.bs.modal', function onHiddenSpk() {
          modalPilihSpk.removeEventListener('hidden.bs.modal', onHiddenSpk);
          new bootstrap.Modal(addRoleModal).show();
        });
        instSpk.hide();
      } else {
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ===================== BATAL / X DI MODAL SPK =====================
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
      // Reset hanya checkbox yang tidak disabled
      document.querySelectorAll('.datatables-spk-pg .spk-pg-chk:not(:disabled)').forEach(c => (c.checked = false));
      const allChk = document.querySelector('.datatables-spk-pg thead .spk-chk-all');
      if (allChk) allChk.checked = false;

      if (_spkShouldReturn) {
        _spkShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ===================== EVENT: HAPUS BARIS DETAIL =====================
  document.getElementById('tbody-detail-spk')?.addEventListener('click', function (e) {
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

function loadSpkToDataTable() {
  if (!window.dt_spk_pg_global) return;
  window.dt_spk_pg_global.ajax.reload();
}

// function addDetailRow(noSpk, namaCustomer, nilai) {
//   const tbody = document.getElementById('tbody-detail-spk');
//   const noDataRow = document.getElementById('row-no-data');
//   if (noDataRow) noDataRow.remove();

//   const rowCount = tbody.querySelectorAll('tr[data-spk]').length + 1;
//   const nilaiNum = nilai ? parseInt(String(nilai).replace(/[^0-9]/g, ''), 10) : 0;
//   const nilaiStr = nilaiNum ? nilaiNum.toLocaleString('en-US') : '';

//   const tr = document.createElement('tr');
//   tr.dataset.spk = noSpk;
//   tr.innerHTML = `
//     <td class="text-center row-num">${rowCount}</td>
//     <td>${noSpk}</td>
//     <td>${namaCustomer || ''}</td>
//     <td>
//       <input type="text" class="form-control form-control-sm text-end nilai-detail input-readonly"
//         value="${nilaiStr}" placeholder="0" readonly />
//     </td>
//     <td class="text-center">
//       <button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill btn-hapus-detail" title="Hapus">
//         <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
//       </button>
//     </td>
//   `;
//   tbody.appendChild(tr);
// }
function addDetailRow(noSpk, namaCustomer, nilai, pph, biaya_merimen) {
  const tbody = document.getElementById('tbody-detail-spk');
  const noDataRow = document.getElementById('row-no-data');
  if (noDataRow) noDataRow.remove();

  const rowCount = tbody.querySelectorAll('tr[data-spk]').length + 1;
  const nilaiNum = nilai ? parseInt(String(nilai).replace(/[^0-9]/g, ''), 10) : 0;
  const nilaiStr = nilaiNum ? nilaiNum.toLocaleString('en-US') : '';
  const pphNum = pph ? parseInt(String(pph).replace(/[^0-9]/g, ''), 10) : 0;
  const pphStr = pphNum ? pphNum.toLocaleString('en-US') : '';
  const bMerimenNum = biaya_merimen ? parseInt(String(biaya_merimen).replace(/[^0-9]/g, ''), 10) : 0;
  const bMerimenStr = bMerimenNum ? bMerimenNum.toLocaleString('en-US') : '';

  const tr = document.createElement('tr');
  tr.dataset.spk = noSpk;
  tr.innerHTML = `
    <td class="text-center row-num">${rowCount}</td>
    <td>${noSpk}</td>
    <td>${namaCustomer || ''}</td>
    <td>
      <input type="text" class="form-control form-control-sm text-end nilai-detail input-readonly"
        value="${nilaiStr}" placeholder="0" readonly />
    </td>
    <td>
      <input type="text" class="form-control form-control-sm text-end pph-detail"
        value="${pphStr}" placeholder="0" />
    </td>
    <td>
      <input type="text" class="form-control form-control-sm text-end biaya-merimen-detail"
        value="${bMerimenStr}" placeholder="0" />
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill btn-hapus-detail" title="Hapus">
        <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
      </button>
    </td>
  `;
  tbody.appendChild(tr);
}

// function renumberRows() {
//   const rows = document.querySelectorAll('#tbody-detail-spk tr[data-spk]');
//   rows.forEach((tr, i) => {
//     const numCell = tr.querySelector('.row-num');
//     if (numCell) numCell.textContent = i + 1;
//   });
//   if (!rows.length) {
//     const tbody = document.getElementById('tbody-detail-spk');
//     tbody.innerHTML =
//       '<tr id="row-no-data"><td colspan="5" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
//   }
// }
function renumberRows() {
  const rows = document.querySelectorAll('#tbody-detail-spk tr[data-spk]');
  rows.forEach((tr, i) => {
    const numCell = tr.querySelector('.row-num');
    if (numCell) numCell.textContent = i + 1;
  });
  if (!rows.length) {
    const tbody = document.getElementById('tbody-detail-spk');
    tbody.innerHTML =
      '<tr id="row-no-data"><td colspan="7" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
  }
}

// function recalcTotal() {
//   let total = 0;
//   document.querySelectorAll('#tbody-detail-spk .nilai-detail').forEach(inp => {
//     const raw = inp.value.replace(/[^0-9]/g, '');
//     total += raw ? parseInt(raw, 10) : 0;
//   });
//   const fmtTotal = total.toLocaleString('en-US');
//   document.getElementById('tfoot-total').textContent = fmtTotal;
//   document.getElementById('add-total-nilai').value = fmtTotal;
// }
function recalcTotal() {
  let totalNilai = 0;
  let totalPph = 0;
  let totalBiayaMerimen = 0;

  document.querySelectorAll('#tbody-detail-spk tr[data-spk]').forEach(tr => {
    const nilaiRaw = tr.querySelector('.nilai-detail')?.value.replace(/[^0-9]/g, '') || '0';
    const pphRaw = tr.querySelector('.pph-detail')?.value.replace(/[^0-9]/g, '') || '0';
    const bMerimenRaw = tr.querySelector('.biaya-merimen-detail')?.value.replace(/[^0-9]/g, '') || '0';
    totalNilai += parseInt(nilaiRaw, 10) || 0;
    totalPph += parseInt(pphRaw, 10) || 0;
    totalBiayaMerimen += parseInt(bMerimenRaw, 10) || 0;
  });

  const totalBersih = totalNilai + totalPph + totalBiayaMerimen;

  document.getElementById('tfoot-total').textContent = totalNilai.toLocaleString('en-US');
  document.getElementById('tfoot-total-pph').textContent = totalPph.toLocaleString('en-US');
  document.getElementById('tfoot-total-merimen').textContent = totalBiayaMerimen.toLocaleString('en-US');
  document.getElementById('add-total-nilai').value = totalBersih.toLocaleString('en-US');
}

// function collectDetails() {
//   const rows = document.querySelectorAll('#tbody-detail-spk tr[data-spk]');
//   const result = [];
//   rows.forEach(tr => {
//     const noSpk = tr.dataset.spk;
//     const namaCustomer = tr.querySelectorAll('td')[2]?.textContent?.trim() || '';
//     const nilaiRaw = tr.querySelector('.nilai-detail')?.value.replace(/[^0-9]/g, '') || '0';
//     result.push({ no_spk: noSpk, nama_customer: namaCustomer, nilai: nilaiRaw });
//   });
//   return result;
// }
function collectDetails() {
  const rows = document.querySelectorAll('#tbody-detail-spk tr[data-spk]');
  const result = [];
  rows.forEach(tr => {
    const noSpk = tr.dataset.spk;
    const namaCustomer = tr.querySelectorAll('td')[2]?.textContent?.trim() || '';
    const nilaiRaw = tr.querySelector('.nilai-detail')?.value.replace(/[^0-9]/g, '') || '0';
    const pphRaw = tr.querySelector('.pph-detail')?.value.replace(/[^0-9]/g, '') || '0';
    const bMerimenRaw = tr.querySelector('.biaya-merimen-detail')?.value.replace(/[^0-9]/g, '') || '0';
    result.push({ no_spk: noSpk, nama_customer: namaCustomer, nilai: nilaiRaw, pph: pphRaw, biaya_merimen: bMerimenRaw });
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

  fetch(`${baseUrl}penerimaan-gabungan-list?rekening=1&kode_bank=${kodeBank}`)
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

function initSelect2Customer($sel, placeholder) {
  if (!$sel.parent().hasClass('position-relative')) $sel.wrap('<div class="position-relative"></div>');
  $sel.select2({
    placeholder: placeholder || 'Pilih Customer',
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
  document.getElementById('add-nama-customer').value = '';
  document.getElementById('add-kode-pelanggan').value = '';
  document.getElementById('add-total-nilai').value = '';

  const elTgl = document.getElementById('add-tanggal');
  if (elTgl) {
    if (elTgl._flatpickr) elTgl._flatpickr.setDate(new Date(), true);
    else elTgl.value = getTodayDMY();
  }

  clearSelect('#add-jenis');
  clearSelect('#add-kode-bank');
  filterRekeningByBank('', '', '');

  const $selCust = $('#add-select-customer');
  if ($selCust.length) {
    if ($selCust.hasClass('select2-hidden-accessible')) $selCust.select2('destroy');
    $selCust.empty().append(new Option('Pilih Customer', '', true, false));
    initSelect2Customer($selCust, 'Pilih Customer');
  }

  // const tbody = document.getElementById('tbody-detail-spk');
  // if (tbody)
  //   tbody.innerHTML =
  //     '<tr id="row-no-data"><td colspan="5" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
  // document.getElementById('tfoot-total').textContent = '0';
  const tbody = document.getElementById('tbody-detail-spk');
  if (tbody)
    tbody.innerHTML =
      '<tr id="row-no-data"><td colspan="6" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
  document.getElementById('tfoot-total').textContent = '0';
  document.getElementById('tfoot-total-pph').textContent = '0';
  document.getElementById('tfoot-total-merimen').textContent = '0';
}
