/**
 * Page Data management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  let isAdd, isEdit, isDelete;

  const dt_basic_table = document.querySelector('.datatables-umb');
  const addRoleModal = document.getElementById('addRoleModal');
  const filterRoleModal = document.getElementById('filterRoleModal');

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // ===================== FLATPICKR =====================
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickrDate.forEach(function (el) {
      flatpickr(el, {
        monthSelectorType: 'static',
        static: true,
        dateFormat: 'd/m/Y'
      });
    });
  }

  // ===================== MASKING NILAI (invoice-price) =====================
  const invoiceItemPriceList = document.querySelectorAll('.invoice-price');
  if (invoiceItemPriceList) {
    invoiceItemPriceList.forEach(function (invoiceItemPrice) {
      if (invoiceItemPrice) {
        invoiceItemPrice.addEventListener('input', function (event) {
          // Hapus semua non-digit, lalu format ulang dengan koma ribuan
          const raw = event.target.value.replace(/[^0-9]/g, '');
          invoiceItemPrice.value = raw ? parseInt(raw, 10).toLocaleString('en-US') : '';
        });
      }
    });
  }

  // ===================== DATATABLE =====================
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
        url: baseUrl + 'uang-muka-pembelian-list',
        data: function (d) {
          d.no_transaksi = $('#filter-no-transaksi').val();
          d.nama = $('#filter-nama').val();
          d.jenis_pengeluaran = $('#filter-jenis').val();
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
        { data: 'jenis_pengeluaran' },
        { data: 'nama' },
        { data: 'nama_bank' },
        { data: 'no_rekening' },
        { data: 'nilai', className: 'text-end' }
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

    // Batasi hanya 1 checkbox
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-umb .dt-checkboxes');
      if (chk && chk.checked) {
        $('.datatables-umb .dt-checkboxes').not(chk).prop('checked', false);
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
        document.getElementById('user_id').value = '';
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

        const chk = document.querySelector('.datatables-umb .dt-checkboxes:checked');
        if (!chk) {
          alertPilihData();
          return;
        }

        clearFormData();

        fetch(`${baseUrl}uang-muka-pembelian-list/${chk.value}/edit`)
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

            document.getElementById('user_id').value = d.id;
            document.getElementById('add-no-transaksi').value = d.no_transaksi;
            document.getElementById('add-nama').value = d.nama;

            // Set tanggal
            const tglEl = document.getElementById('add-tanggal');
            if (tglEl && tglEl._flatpickr) {
              tglEl._flatpickr.setDate(d.tanggal_transaksi, false, 'd/m/Y');
            } else if (tglEl) {
              tglEl.value = d.tanggal_transaksi;
            }

            // Format nilai
            const nilaiEl = document.getElementById('add-nilai');
            const nilaiRaw = parseFloat(d.nilai_raw ?? 0);
            nilaiEl.value = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(nilaiRaw);

            setSelectValue('#add-jenis', d.jenis_pengeluaran, '');
            setSelectValue('#add-kode-bank', d.kode_bank, '');

            // Buka modal dulu
            const modalInstance = new bootstrap.Modal(addRoleModal);
            modalInstance.show();

            // filterRekeningByBank SETELAH modal fully shown
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

        const chk = document.querySelector('.datatables-umb .dt-checkboxes:checked');
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
            fetch(`${baseUrl}uang-muka-pembelian-list/${chk.value}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
              }
            })
              .then(res => {
                if (res.ok) {
                  dt_basic.draw();
                  Swal.fire({
                    icon: 'success',
                    title: 'Hapus!',
                    text: 'Data berhasil dihapus.',
                    customClass: { confirmButton: 'btn btn-success' }
                  });
                } else throw new Error('Gagal hapus');
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
        jenis_pengeluaran: { validators: { notEmpty: { message: 'Jenis Pengeluaran wajib dipilih' } } },
        nama: { validators: { notEmpty: { message: 'Nama wajib diisi' } } },
        kode_bank: { validators: { notEmpty: { message: 'Keluar Kas/Bank wajib dipilih' } } },
        no_rekening: { validators: { notEmpty: { message: 'No. Rekening wajib dipilih' } } },
        nilai: { validators: { notEmpty: { message: 'Nilai wajib diisi' } } }
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
      formData.forEach((value, key) => params.append(key, value));

      PleaseWaitPage();

      fetch(`${baseUrl}uang-muka-pembelian-list`, {
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

  // ===================== AUTO-FILTER NO REKENING BY BANK =====================
  $('#add-kode-bank').on('change', function () {
    const kodeBank = $(this).val() || '';
    filterRekeningByBank(kodeBank, '', '');
  });
}); // end DOMContentLoaded

// ===================== HELPERS =====================

function getTodayDMY() {
  const today = new Date();
  const dd = String(today.getDate()).padStart(2, '0');
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const yyyy = today.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
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

  fetch(`${baseUrl}uang-muka-pembelian-list?rekening=1&kode_bank=${kodeBank}`)
    .then(r => r.json())
    .then(data => {
      const valid = data.filter(item => item.no_rekening && item.no_rekening.trim() !== '');

      if (valid.length > 0) {
        valid.forEach(function (item) {
          const isSelected = preselect ? item.no_rekening === preselect : false;
          $sel.append(new Option(item.no_rekening, item.no_rekening, false, isSelected));
        });
      }

      initSelect2Rekening($sel);

      const opts = $sel.find('option[value!=""]');
      if (opts.length === 1) {
        $sel.val(opts.first().val()).trigger('change');
      } else if (preselect) {
        $sel.val(preselect).trigger('change');
      }
    })
    .catch(() => {
      initSelect2Rekening($sel);
    });
}

function initSelect2Rekening($sel) {
  if (!$sel.parent().hasClass('position-relative')) {
    $sel.wrap('<div class="position-relative"></div>');
  }
  $sel.select2({
    placeholder: 'Pilih No. Rekening',
    allowClear: true,
    width: '100%',
    dropdownParent: $sel.parent(),
    language: {
      noResults: function () {
        return 'Data tidak ditemukan';
      }
    }
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
  if (val !== '' && !$el.find(`option[value="${val}"]`).length) {
    $el.append(new Option(textIfMissing ?? val, val, false, false));
  }
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

  const noTrxEl = document.getElementById('add-no-transaksi');
  if (noTrxEl) noTrxEl.value = '';

  const nilaiEl = document.getElementById('add-nilai');
  if (nilaiEl) nilaiEl.value = '';

  const elTgl = document.getElementById('add-tanggal');
  if (elTgl) {
    if (elTgl._flatpickr) {
      elTgl._flatpickr.setDate(new Date(), true);
    } else {
      elTgl.value = getTodayDMY();
    }
  }

  clearSelect('#add-jenis');
  clearSelect('#add-kode-bank');
  filterRekeningByBank('', '', '');
}
