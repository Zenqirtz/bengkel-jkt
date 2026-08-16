/**
 * Page Data management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-hl');
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
        url: baseUrl + 'harian-lepas-list',
        data: function (d) {
          d.no_transaksi = $('#filter-no-transaksi').val();
          d.nama_pekerja = $('#filter-nama').val();
          d.jenis_pekerjaan = $('#filter-jenis').val();
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
        { data: 'id' },
        { data: 'tanggal_transaksi' },
        { data: 'no_transaksi' },
        { data: 'nama_jenis_pekerjaan' },
        { data: 'nama_pekerja' },
        { data: 'nama_bank' },
        { data: 'no_rekening' },
        { data: 'total_nilai', className: 'text-end' }
      ],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          searchable: false,
          // render: function (data) {
          //   return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          // }
          render: function (data, type, full) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input"value="${data}"
            data-jenis-pekerjaan="${full.jenis_pekerjaan || ''}">`;
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
      },
      initComplete: function () {
        const sa = document.getElementById('selectAllHL');
        if (sa) {
          sa.addEventListener('change', function () {
            document.querySelectorAll('.datatables-hl .dt-checkboxes').forEach(chk => {
              chk.checked = this.checked;
            });
          });
        }
        dt_basic.on('draw', function () {
          const sa = document.getElementById('selectAllHL');
          if (sa) sa.checked = false;
        });
      }
    });

    // Batasi 1 checkbox aktif
    // document.addEventListener('click', function (e) {
    //   const chk = e.target.closest('.datatables-hl .dt-checkboxes');
    //   if (chk && chk.checked) {
    //     $('.datatables-hl .dt-checkboxes').not(chk).prop('checked', false);
    //   }
    // });

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
        document.getElementById('hl_id').value = '';
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

        const chk = document.querySelector('.datatables-hl .dt-checkboxes:checked');
        if (!chk) {
          alertPilihData();
          return;
        }

        clearFormData();

        fetch(`${baseUrl}harian-lepas-list/${chk.value}/edit`)
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
            document.getElementById('hl_id').value = d.id;
            document.getElementById('add-no-transaksi').value = d.no_transaksi;
            document.getElementById('add-kode-karyawan').value = d.kode_karyawan || '';
            document.getElementById('add-nama-pekerja').value = d.nama_pekerja || '';

            const tglEl = document.getElementById('add-tanggal');
            if (tglEl && tglEl._flatpickr) tglEl._flatpickr.setDate(d.tanggal_transaksi, false, 'd/m/Y');
            else if (tglEl) tglEl.value = d.tanggal_transaksi;

            setSelectValue('#add-jenis', d.jenis_pekerjaan, '');
            setSelectValue('#add-kode-bank', d.kode_bank, '');

            // Load pekerja dropdown
            if (d.jenis_pekerjaan) {
              const $selPek = $('#add-select-pekerja');
              if ($selPek.hasClass('select2-hidden-accessible')) $selPek.select2('destroy');
              $selPek.empty().append(new Option('Memuat data...', '', true, false));
              initSelect2Pekerja($selPek, 'Memuat data...');

              fetch(
                `${baseUrl}harian-lepas-list?get_pekerja=1&jenis_pekerjaan=${encodeURIComponent(d.jenis_pekerjaan)}`
              )
                .then(r => r.json())
                .then(data => {
                  if ($selPek.hasClass('select2-hidden-accessible')) $selPek.select2('destroy');
                  $selPek.empty().append(new Option('Pilih Pekerja', '', false, false));
                  data.forEach(function (item) {
                    const isSel = d.kode_karyawan && item.kode_karyawan === d.kode_karyawan;
                    $selPek.append(new Option(item.nama, item.kode_karyawan, false, isSel));
                  });
                  if (d.kode_karyawan && !$selPek.find(`option[value="${d.kode_karyawan}"]`).length) {
                    $selPek.append(new Option(d.nama_pekerja || d.kode_karyawan, d.kode_karyawan, false, true));
                  }
                  $selPek.val(d.kode_karyawan || null);
                  initSelect2Pekerja($selPek, 'Pilih Pekerja');
                  $selPek.trigger('change');
                })
                .catch(() => {
                  if ($selPek.hasClass('select2-hidden-accessible')) $selPek.select2('destroy');
                  $selPek
                    .empty()
                    .append(
                      new Option(d.nama_pekerja || 'Pilih Pekerja', d.kode_karyawan || '', false, !!d.kode_karyawan)
                    );
                  initSelect2Pekerja($selPek, 'Pilih Pekerja');
                  $selPek.trigger('change');
                });
            }

            // Populate detail SPK
            if (Array.isArray(d.details)) {
              d.details.forEach(function (row) {
                // addDetailRow(row.kode_spk, row.no_polisi, row.nama_pemilik, row.upah, row.persen, row.nilai);
                addDetailRow(
                  row.kode_spk,
                  row.no_polisi,
                  row.nama_tipe,
                  row.upah,
                  row.persen,
                  row.nilai,
                  row.persen_awal,
                  row.total_awal
                );
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

        const checked = document.querySelectorAll('.datatables-hl .dt-checkboxes:checked');
        if (!checked.length) {
          alertPilihData();
          return;
        }

        const ids = Array.from(checked).map(c => c.value);
        const jumlah = ids.length;
        const teksKonfirmasi =
          jumlah > 1 ? `Anda yakin akan menghapus ${jumlah} data terpilih?` : 'Anda yakin akan menghapus data ini?';

        Swal.fire({
          title: 'Konfirmasi?',
          text: teksKonfirmasi,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal',
          customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
          buttonsStyling: false
        }).then(result => {
          if (!result.value) return;

          let sukses = 0;
          let gagal = 0;

          const hapusSatuPersatu = index => {
            if (index >= ids.length) {
              dt_basic.draw();
              Swal.fire({
                icon: gagal === 0 ? 'success' : 'warning',
                title: gagal === 0 ? 'Hapus!' : 'Selesai dengan catatan',
                text: `Berhasil menghapus ${sukses} data.` + (gagal > 0 ? ` ${gagal} data gagal dihapus.` : ''),
                customClass: { confirmButton: 'btn btn-success' }
              });
              return;
            }

            fetch(`${baseUrl}harian-lepas-list/${ids[index]}`, {
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
                  json = { status: res.ok };
                }
                if (json.status) sukses++;
                else gagal++;
                hapusSatuPersatu(index + 1);
              })
              .catch(() => {
                gagal++;
                hapusSatuPersatu(index + 1);
              });
          };

          hapusSatuPersatu(0);
        });
      });
    }

    // ===== TOMBOL CETAK =====
    const btnCetak = document.querySelector('.cetak-record');
    if (btnCetak) {
      btnCetak.addEventListener('click', function () {
        const checked = document.querySelectorAll('.datatables-hl .dt-checkboxes:checked');

        if (!checked.length) {
          alertPilihData();
          return;
        }
        // const jenisList = [...new Set(Array.from(checked).map(c => c.dataset.jenisPekerjaan))];
        // if (jenisList.length > 1) {
        //   const namaJenisList = [
        //     ...new Set(
        //       Array.from(checked).map(c => {
        //         const tr = c.closest('tr');
        //         return tr ? tr.querySelectorAll('td')[3]?.textContent?.trim() || '' : '';
        //       })
        //     )
        //   ].filter(Boolean);

        //   Swal.fire({
        //     icon: 'warning',
        //     title: 'Peringatan',
        //     html: `Data yang dipilih memiliki jenis pekerjaan berbeda:<br><br>
        //    <b>${namaJenisList.join('<br>')}</b><br><br>
        //    Silahkan pilih data dengan jenis pekerjaan yang sama!`,
        //     customClass: { confirmButton: 'btn btn-primary' }
        //   });
        //   return;
        // }
        const jenisList = [...new Set(Array.from(checked).map(c => c.dataset.jenisPekerjaan))];
        if (jenisList.length > 1) {
          const namaJenisList = [
            ...new Set(
              Array.from(checked).map(c => {
                const tr = c.closest('tr');
                return tr ? tr.querySelectorAll('td')[3]?.textContent?.trim() || '' : '';
              })
            )
          ].filter(Boolean);

          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            html: `Data yang dipilih memiliki jenis pekerjaan berbeda:<br><br>
       <b>${namaJenisList.join('<br>')}</b><br><br>
       Silahkan pilih data dengan jenis pekerjaan yang sama!`,
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // ← TAMBAHKAN INI
        const namaPekerjaList = [
          ...new Set(
            Array.from(checked).map(c => {
              const tr = c.closest('tr');
              return tr ? tr.querySelectorAll('td')[4]?.textContent?.trim() || '' : '';
            })
          )
        ].filter(Boolean);

        if (namaPekerjaList.length > 1) {
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            html: `Data yang dipilih memiliki nama pekerja berbeda:<br><br>
       <b>${namaPekerjaList.join('<br>')}</b><br><br>
       Silahkan pilih data dengan nama pekerja yang sama!`,
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        const ids = Array.from(checked).map(c => c.value);
        const queryString = ids.map(id => `id[]=${id}`).join('&');
        window.open(`${baseUrl}keuangan/cetak-harian-lepas?` + queryString, '_blank');
      });
    }

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
        jenis_pekerjaan: { validators: { notEmpty: { message: 'Jenis Pekerjaan wajib dipilih' } } },
        nama_pekerja: { validators: { notEmpty: { message: 'Nama Pekerja wajib dipilih' } } },
        kode_bank: { validators: { notEmpty: { message: 'Keluar Kas/Bank wajib dipilih' } } },
        no_rekening: {
          validators: {
            callback: {
              message: 'No. Rekening wajib dipilih',
              callback: function () {
                const bank = ($('#add-kode-bank').val() || '').toUpperCase();
                const rek = ($('#add-no-rekening').val() || '').trim();
                if (bank === 'KAS') return true;
                return rek !== '';
              }
            }
          }
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

      // Validasi persen tiap baris
      for (const d of details) {
        if (!d.persen || d.persen <= 0 || d.persen > 100) {
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan!',
            text: `Persentase pada SPK ${d.kode_spk} harus antara 1–100%.`,
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }
      }

      document.getElementById('hl_details').value = JSON.stringify(details);

      // addNewDataForm.submit();

      const formData = new FormData(addNewDataForm);
      const params = new URLSearchParams();
      formData.forEach((value, key) => params.append(key, value));

      PleaseWaitPage();

      fetch(`${baseUrl}harian-lepas-list`, {
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

  // ===================== SYNC SELECT-ALL CHECKBOX SPK =====================
  document.addEventListener('change', function (e) {
    const chk = e.target.closest('.datatables-spk-hl .spk-hl-chk');
    if (chk && !chk.disabled) {
      const allChk = document.querySelector('.datatables-spk-hl thead .spk-chk-all');
      const enabled = document.querySelectorAll('.datatables-spk-hl .spk-hl-chk:not(:disabled)');
      if (allChk && enabled.length) {
        allChk.checked = Array.from(enabled).every(c => c.checked);
      }
    }
  });

  // ===================== AUTO-FILTER REKENING (event trigger) =====================
  $('#add-kode-bank').on('change', function () {
    filterRekeningByBank($(this).val() || '', '', '');
  });

  // ===================== LOAD PEKERJA BY JENIS PEKERJAAN =====================
  $('#add-jenis').on('change', function () {
    const jenis = $(this).val() || '';
    document.getElementById('add-kode-karyawan').value = '';
    document.getElementById('add-nama-pekerja').value = '';

    const $sel = $('#add-select-pekerja');
    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $sel.empty();

    if (!jenis) {
      $sel.append(new Option('Pilih Jenis Pekerjaan terlebih dahulu', '', true, false));
      initSelect2Pekerja($sel, 'Pilih Jenis Pekerjaan terlebih dahulu');
      return;
    }

    $sel.append(new Option('Memuat data...', '', true, false));
    initSelect2Pekerja($sel, 'Memuat data...');

    fetch(`${baseUrl}harian-lepas-list?get_pekerja=1&jenis_pekerjaan=${encodeURIComponent(jenis)}`)
      .then(r => r.json())
      .then(data => {
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append(new Option('Pilih Nama Pekerja', '', true, false));
        data.forEach(item => $sel.append(new Option(item.nama, item.kode_karyawan, false, false)));
        initSelect2Pekerja($sel, 'Pilih Nama Pekerja');
      })
      .catch(() => {
        if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
        $sel.empty().append(new Option('Gagal memuat data', '', true, false));
        initSelect2Pekerja($sel, 'Gagal memuat data');
      });
  });

  // Saat pekerja dipilih → isi hidden fields
  $('#add-select-pekerja').on('change', function () {
    const kode = $(this).val() || '';
    const nama = $(this).find('option:selected').text() || '';
    document.getElementById('add-kode-karyawan').value = kode;
    document.getElementById('add-nama-pekerja').value = kode && nama !== 'Pilih Pekerja' ? nama : '';
  });

  // Cegah buka dropdown jika jenis belum dipilih
  $('#add-select-pekerja').on('select2:opening', function (e) {
    if (!($('#add-jenis').val() || '')) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan!',
        text: 'Pilih Jenis Pekerjaan terlebih dahulu.',
        customClass: { confirmButton: 'btn btn-primary' }
      });
    }
  });

  // ===================== DATATABLE SPK POPUP =====================
  function _bukaModalSpk() {
    const spkTable = document.querySelector('.datatables-spk-hl');

    if (spkTable && !window.dt_spk_hl_global) {
      window.dt_spk_hl_global = new DataTable(spkTable, {
        searching: true,
        ordering: true, // aktifkan ordering
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: true,
        ajax: {
          url: baseUrl + 'harian-lepas-list',
          data: function (d) {
            d.get_spk = 1;
            d.kode_karyawan = document.getElementById('add-kode-karyawan').value || '';
            d.current_id = document.getElementById('hl_id').value || '';
            d.jenis_pekerjaan = $('#add-jenis').val() || '';
          },
          dataSrc: 'data'
        },
        columns: [
          { data: 'kode_spk', width: '30px' },
          { data: 'kode_spk' },
          { data: 'nama_tipe' },
          { data: 'no_polisi' },
          // { data: 'pemilik' },
          { data: 'upah', className: 'text-end' },
          { data: 'persen_sudah', className: 'text-center' },
          { data: 'total', className: 'text-end' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            searchable: false,
            render: function (data, type, full) {
              return `<input type="checkbox" class="spk-hl-chk form-check-input"
                value="${data}"
                data-no-polisi="${full.no_polisi || ''}"
                data-nama-tipe="${full.nama_tipe || ''}"
                data-upah="${full.upah || 0}"
                data-sisa="${full.sisa || 0}"
                data-persen="${full.persen_sudah || 0}"
                data-total="${full.total || 0}"
                style="width:16px;height:16px;cursor:pointer;">`;
            }
          },
          {
            targets: 5,
            render: function (data) {
              return data + '%';
            }
          }
        ],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
          },
          topEnd: { features: [{ search: { placeholder: 'Cari No. SPK / No. Polisi', text: '_INPUT_' } }] },
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
            previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>'
          },
          zeroRecords: 'Tidak ada SPK dengan sisa upah',
          emptyTable: 'Belum ada data',
          loadingRecords: 'Memuat...'
        },
        drawCallback: function () {
          // Buat header checkbox select-all (hanya sekali)
          const thCheck = document.querySelector('.datatables-spk-hl thead tr th:first-child');
          if (thCheck && !thCheck.querySelector('.spk-chk-all')) {
            thCheck.innerHTML = `<input type="checkbox" class="spk-chk-all form-check-input"
              style="width:16px;height:16px;cursor:pointer;">`;

            thCheck.querySelector('.spk-chk-all').addEventListener('change', function () {
              // Hanya centang/hapus centang checkbox yang TIDAK disabled
              document.querySelectorAll('.datatables-spk-hl .spk-hl-chk:not(:disabled)').forEach(c => {
                c.checked = this.checked;
              });
            });
          }

          const existingSPK = Array.from(document.querySelectorAll('#tbody-detail-spk tr[data-spk]')).map(
            tr => tr.dataset.spk
          );

          document.querySelectorAll('.datatables-spk-hl .spk-hl-chk').forEach(chk => {
            if (existingSPK.includes(chk.value)) {
              chk.checked = true;
              chk.disabled = true;
              const tr = chk.closest('tr');
              if (tr) {
                tr.style.opacity = '0.45';
                tr.style.background = '#f0f0f0';
                tr.title = 'Sudah ditambahkan';
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

          // Update state select-all (hitung yang enabled saja)
          const allChk = document.querySelector('.datatables-spk-hl thead .spk-chk-all');
          const enabled = document.querySelectorAll('.datatables-spk-hl .spk-hl-chk:not(:disabled)');
          if (allChk) {
            allChk.checked = enabled.length > 0 && Array.from(enabled).every(c => c.checked);
          }
        }
      });

      setTimeout(() => {
        [
          { selector: '.datatables-spk-hl .dt-length .form-select', classToAdd: 'ms-0' },
          { selector: '.datatables-spk-hl .dt-length', classToAdd: 'mb-md-5 mb-0' },
          { selector: '.datatables-spk-hl .dt-layout-table', classToRemove: 'row mt-2' },
          { selector: '.datatables-spk-hl .dt-layout-full', classToRemove: 'col-md col-12' }
        ].forEach(({ selector, classToRemove, classToAdd }) => {
          document.querySelectorAll(selector).forEach(el => {
            if (classToRemove) classToRemove.split(' ').forEach(c => el.classList.remove(c));
            if (classToAdd) classToAdd.split(' ').forEach(c => el.classList.add(c));
          });
        });
      }, 300);
    }

    if (window.dt_spk_hl_global) window.dt_spk_hl_global.ajax.reload();
    new bootstrap.Modal(modalPilihSpk).show();
  }

  // ===================== TOMBOL PILIH SPK =====================
  const btnPilihSpk = document.getElementById('btn-pilih-spk');
  if (btnPilihSpk) {
    btnPilihSpk.addEventListener('click', function () {
      if (!($('#add-jenis').val() || '')) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Pilih Jenis Pekerjaan terlebih dahulu.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }
      if (!(document.getElementById('add-kode-karyawan').value || '')) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Pilih Nama Pekerja terlebih dahulu.',
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
  const btnTambahSpk = document.getElementById('btn-tambah-spk-terpilih');
  if (btnTambahSpk) {
    btnTambahSpk.addEventListener('click', function () {
      const checked = document.querySelectorAll('.datatables-spk-hl .spk-hl-chk:checked:not(:disabled)');
      if (!checked.length) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Pilih minimal satu SPK.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const existingSPK = Array.from(document.querySelectorAll('#tbody-detail-spk tr[data-spk]')).map(
        tr => tr.dataset.spk
      );

      // checked.forEach(chk => {
      //   if (!existingSPK.includes(chk.value)) {
      //     const upah = chk.dataset.sisa; //parseFloat(chk.dataset.upah || 0);
      //     const total = 0; //parseFloat(chk.dataset.total || 0);
      //     const persenSudah = 0; //parseFloat(chk.dataset.persenSisa || 0);
      //     addDetailRow(chk.value, chk.dataset.noPolisi || '', chk.dataset.namaTipe || '', upah, persenSudah, total);
      //   }
      // });
      checked.forEach(chk => {
        if (!existingSPK.includes(chk.value)) {
          const upahAwal = chk.dataset.upah; // basis hitung sekarang: Upah Kerja Awal
          const persenAwal = chk.dataset.persen; // info: progress yang sudah ada sebelum transaksi ini
          const totalAwal = chk.dataset.total; // info: total yang sudah dibayar sebelum transaksi ini
          addDetailRow(
            chk.value,
            chk.dataset.noPolisi || '',
            chk.dataset.namaTipe || '',
            upahAwal,
            0,
            0,
            persenAwal,
            totalAwal
          );
        }
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
      document.querySelectorAll('.datatables-spk-hl .spk-hl-chk:not(:disabled)').forEach(c => (c.checked = false));
      const allChk = document.querySelector('.datatables-spk-hl thead .spk-chk-all');
      if (allChk) allChk.checked = false;
      if (_spkShouldReturn) {
        _spkShouldReturn = false;
        new bootstrap.Modal(addRoleModal).show();
      }
    });
  }

  // ===================== EVENT: PERSENTASE BERUBAH =====================
  document.getElementById('tbody-detail-spk')?.addEventListener('input', function (e) {
    const inp = e.target.closest('.persen-detail');
    if (inp) {
      const tr = inp.closest('tr');
      const upah = parseFloat((tr.dataset.upah || '0').replace(/,/g, '')) || 0;
      let persen = parseFloat(inp.value) || 0;
      if (persen < 0) {
        persen = 0;
        inp.value = 0;
      }
      if (persen > 100) {
        persen = 100;
        inp.value = 100;
      }

      const nilai = Math.round((persen / 100) * upah);
      const nilaiInp = tr.querySelector('.nilai-detail');
      if (nilaiInp) nilaiInp.value = nilai ? nilai.toLocaleString('en-US') : '';
      recalcTotal();
    }
  });

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

// function addDetailRow(kodeSpk, noPolisi, pemilik, upah, persen, nilai) {
//   const tbody = document.getElementById('tbody-detail-spk');
//   const noDataRow = document.getElementById('row-no-data');
//   if (noDataRow) noDataRow.remove();

//   const rowCount = tbody.querySelectorAll('tr[data-spk]').length + 1;
//   const upahNum = parseFloat(String(upah).replace(/[^0-9.]/g, '')) || 0;
//   const persenNum = parseFloat(persen) || 0;
//   const nilaiNum =
//     nilai !== undefined && nilai !== null && nilai !== ''
//       ? parseFloat(String(nilai).replace(/[^0-9.]/g, ''))
//       : Math.round((persenNum / 100) * upahNum);

//   const tr = document.createElement('tr');
//   tr.dataset.spk = kodeSpk;
//   tr.dataset.upah = upahNum;
//   tr.innerHTML = `
//     <td class="text-center row-num">${rowCount}</td>
//     <td>${kodeSpk}</td>
//     <td>${noPolisi || ''}</td>
//     <td>${pemilik || ''}</td>
//     <td class="text-end">${upahNum ? upahNum.toLocaleString('en-US') : ''}</td>
//     <td class="text-center">
//       <div class="input-group input-group-sm" style="width:80px;margin:0 auto;">
//         <input type="number" class="form-control form-control-sm text-center persen-detail"
//           value="${persenNum}" min="0" max="100" step="0.01" placeholder="0"
//           style="padding:2px 4px;font-size:12px;" />
//         <span class="input-group-text" style="padding:2px 5px;font-size:12px;">%</span>
//       </div>
//     </td>
//     <td>
//       <input type="text" class="form-control form-control-sm text-end nilai-detail input-readonly"
//         value="${nilaiNum ? nilaiNum.toLocaleString('en-US') : ''}" placeholder="0" readonly />
//     </td>
//     <td class="text-center">
//       <button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill btn-hapus-detail" title="Hapus">
//         <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
//       </button>
//     </td>
//   `;
//   tbody.appendChild(tr);
// }
function addDetailRow(kodeSpk, noPolisi, pemilik, upah, persen, nilai, persenAwal, totalAwal) {
  const tbody = document.getElementById('tbody-detail-spk');
  const noDataRow = document.getElementById('row-no-data');
  if (noDataRow) noDataRow.remove();

  const rowCount = tbody.querySelectorAll('tr[data-spk]').length + 1;
  const upahNum = parseFloat(String(upah).replace(/[^0-9.]/g, '')) || 0; // Upah Kerja Awal (basis hitung)
  const persenNum = parseFloat(persen) || 0;
  const persenAwalNum = parseFloat(persenAwal) || 0;
  const totalAwalNum = parseFloat(String(totalAwal || 0).replace(/[^0-9.]/g, '')) || 0;
  const nilaiNum =
    nilai !== undefined && nilai !== null && nilai !== ''
      ? parseFloat(String(nilai).replace(/[^0-9.]/g, ''))
      : Math.round((persenNum / 100) * upahNum);

  const sisa = (totalAwalNum > 0) ? (upahNum - totalAwalNum) : upahNum;

  const tr = document.createElement('tr');
  tr.dataset.spk = kodeSpk;
  tr.dataset.upah = upahNum; // dipakai event persen-detail & collectDetails -> sekarang basisnya Upah Awal
  tr.dataset.sisa = sisa;
  tr.innerHTML = `
    <td class="text-center row-num">${rowCount}</td>
    <td>${kodeSpk}</td>
    <td>${noPolisi || ''}</td>
    <td>${pemilik || ''}</td>
    <td class="text-end">${upahNum ? upahNum.toLocaleString('en-US') : ''}</td>
    <td class="text-center">${persenAwalNum}%</td>
    <td class="text-end">${totalAwalNum ? totalAwalNum.toLocaleString('en-US') : '0'}</td>
    <td class="text-center">
      <div class="input-group input-group-sm" style="width:80px;margin:0 auto;">
        <input type="number" class="form-control form-control-sm text-center persen-detail"
          value="${persenNum}" min="0" max="100" step="0.01" placeholder="0"
          style="padding:2px 4px;font-size:12px;" />
        <span class="input-group-text" style="padding:2px 5px;font-size:12px;">%</span>
      </div>
    </td>
    <td>
      <input type="text" class="form-control form-control-sm text-end nilai-detail input-readonly"
        value="${nilaiNum ? nilaiNum.toLocaleString('en-US') : ''}" placeholder="0" readonly />
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
  const rows = document.querySelectorAll('#tbody-detail-spk tr[data-spk]');
  rows.forEach((tr, i) => {
    const numCell = tr.querySelector('.row-num');
    if (numCell) numCell.textContent = i + 1;
  });
  if (!rows.length) {
    const tbody = document.getElementById('tbody-detail-spk');
    tbody.innerHTML =
      // '<tr id="row-no-data"><td colspan="8" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
      '<tr id="row-no-data"><td colspan="10" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
  }
}

function recalcTotal() {
  let total = 0;
  document.querySelectorAll('#tbody-detail-spk .nilai-detail').forEach(inp => {
    const raw = inp.value.replace(/[^0-9]/g, '');
    total += raw ? parseInt(raw, 10) : 0;
  });
  const fmtTotal = total.toLocaleString('en-US');
  document.getElementById('tfoot-total').textContent = fmtTotal;
  document.getElementById('add-total-nilai').value = fmtTotal;
}

function collectDetails() {
  const rows = document.querySelectorAll('#tbody-detail-spk tr[data-spk]');
  const result = [];
  rows.forEach(tr => {
    const kodeSpk = tr.dataset.spk;
    const noPolisi = tr.querySelectorAll('td')[2]?.textContent?.trim() || '';
    const namaTipe = tr.querySelectorAll('td')[3]?.textContent?.trim() || '';
    const upah = tr.dataset.upah || '0';
    const sisa = tr.dataset.sisa || '0';
    const persen = tr.querySelector('.persen-detail')?.value || '0';
    const nilaiRaw = tr.querySelector('.nilai-detail')?.value.replace(/[^0-9]/g, '') || '0';
    result.push({ kode_spk: kodeSpk, no_polisi: noPolisi, nama_tipe: namaTipe, upah, persen, sisa, nilai: nilaiRaw });
  });
  return result;
}

// ===================== AUTO-FILTER REKENING (global function) =====================
// DIUBAH: saat KAS dipilih, select2 diganti input readonly agar tampilan sama dengan field Nomor & Total Nilai
function filterRekeningByBank(kodeBank, namaBank, preselect) {
  const $sel = $('#add-no-rekening');
  if (!$sel.length) return;

  // Jika KAS → sembunyikan select2, tampilkan input readonly pengganti
  if ((kodeBank || '').toUpperCase() === 'KAS') {
    if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
    $sel.val('-');

    // Sembunyikan seluruh baris No. Rekening
    $('#wrap-no-rekening').closest('.row').hide();

    // Sembunyikan fake input jika ada
    $('#fake-no-rekening').hide();
    return;
  }

  // Selain KAS → sembunyikan input fake, tampilkan select2 kembali
  $('#wrap-no-rekening').closest('.row').show();
  $('#fake-no-rekening').hide();
  $sel.show();
  $sel.prop('disabled', false);
  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');
  $sel.empty().append(new Option('Pilih No. Rekening', '', true, false));

  if (!kodeBank) {
    initSelect2Rekening($sel);
    return;
  }

  fetch(`${baseUrl}harian-lepas-list?rekening=1&kode_bank=${kodeBank}`)
    .then(r => r.json())
    .then(data => {
      const valid = data.filter(item => item.no_rekening && item.no_rekening.trim() !== '');
      valid.forEach(item => {
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
    language: { noResults: () => 'Data belum diinput' }
  });
}

function initSelect2Pekerja($sel, placeholder) {
  if (!$sel.parent().hasClass('position-relative')) $sel.wrap('<div class="position-relative"></div>');
  $sel.select2({
    placeholder: placeholder || 'Pilih Pekerja',
    allowClear: true,
    width: '100%',
    dropdownParent: $sel.parent(),
    language: { noResults: () => 'Data belum diinput' }
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

// DIUBAH: tambah reset fake-no-rekening saat form di-clear
function clearFormData() {
  document.getElementById('addNewDataForm')?.reset();
  document.getElementById('add-no-transaksi').value = '';
  document.getElementById('add-nama-pekerja').value = '';
  document.getElementById('add-kode-karyawan').value = '';
  document.getElementById('add-total-nilai').value = '';

  const elTgl = document.getElementById('add-tanggal');
  if (elTgl) {
    if (elTgl._flatpickr) elTgl._flatpickr.setDate(new Date(), true);
    else elTgl.value = getTodayDMY();
  }

  clearSelect('#add-jenis');
  clearSelect('#add-kode-bank');

  // Reset fake input rekening jika ada, lalu tampilkan select2 kembali
  $('#fake-no-rekening').hide();
  $('#add-no-rekening').show();
  $('#wrap-no-rekening').closest('.row').show();
  filterRekeningByBank('', '', '');

  const $selPek = $('#add-select-pekerja');
  if ($selPek.length) {
    if ($selPek.hasClass('select2-hidden-accessible')) $selPek.select2('destroy');
    $selPek.empty().append(new Option('Pilih Pekerja', '', true, false));
    initSelect2Pekerja($selPek, 'Pilih Pekerja');
  }

  const tbody = document.getElementById('tbody-detail-spk');
  if (tbody)
    tbody.innerHTML =
      // '<tr id="row-no-data"><td colspan="8" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';
      '<tr id="row-no-data"><td colspan="10" class="text-center text-muted py-2">Belum ada SPK ditambahkan</td></tr>';

  document.getElementById('tfoot-total').textContent = '0';
}
