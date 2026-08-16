/**
 * Laporan Rekap Estimasi
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;

  // Ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_bank_urut_table = document.querySelector('.datatables-bank-urut-voucher');
  if (dt_bank_urut_table) {
    isAdd = dt_bank_urut_table.dataset.add;
    const dt_bank_urut = new DataTable(dt_bank_urut_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-bank-urut-voucher-list',
        data: function (d) {
          d.kategori = $('#filter-kategori').val();
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
          d.kode_bank = $('#filter-bank').val();
          d.urut1 = $('select[name="urut1"]').val();
          d.urut2 = $('select[name="urut2"]').val();
          d.urut3 = $('select[name="urut3"]').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }
          json.data = Array.isArray(json.data) ? json.data : [];

          // Update footer totals
          $('#footer-debit').text(json.grand_debit || '0');
          $('#footer-kredit').text(json.grand_kredit || '0');
          $('#footer-saldo').text(json.grand_saldo || '0');

          return json.data;
        }
      },
      columns: [
        { data: 'no', className: 'text-center' },
        { data: 'tanggal', className: 'text-center' },
        { data: 'tanggal_ch_bg', className: 'text-center' },
        { data: 'memo' },
        { data: 'no_ch_bg' },
        { data: 'no_voucher_in' },
        { data: 'no_voucher_out' },
        { data: 'debit', className: 'text-end' },
        { data: 'kredit', className: 'text-end' },
        { data: 'saldo', className: 'text-end' },
        { data: 'tanggal_kliring', className: 'text-center' }
      ]
    });
  }

  // Export excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if(isAdd) {
        let params = {
          kategori: $('#filter-kategori').val(),
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val(),
          kode_bank: $('#filter-bank').val(),
          urut1: $('select[name="urut1"]').val(),
          urut2: $('select[name="urut2"]').val(),
          urut3: $('select[name="urut3"]').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-bank-urut-voucher/export?` + queryString;
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk akses tambah data',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  }

  // Print data
  const printBtn = document.querySelector('.btn-print');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      if(isAdd) {
        let params = {
          kategori: $('#filter-kategori').val(),
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val(),
          kode_bank: $('#filter-bank').val(),
          urut1: $('select[name="urut1"]').val(),
          urut2: $('select[name="urut2"]').val(),
          urut3: $('select[name="urut3"]').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-bank-urut-voucher/print?` + queryString;
        window.open(printUrl, '_blank');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk akses tambah data',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  }

  // Form validation
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
        kategori: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Kategori'
            }
          }
        },
        kode_bank: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Bank'
            }
          }
        },
        tgl_awal: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal Awal'
            }
          }
        },
        tgl_akhir: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal Akhir'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: function (field, ele) {
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      // filterForm.submit();

      let params = {
        tipe: 'generate',
        kategori: $('#filter-kategori').val(),
        tgl_awal: $('#filter-tgl-awal').val(),
        tgl_akhir: $('#filter-tgl-akhir').val(),
        kode_bank: $('#filter-bank').val(),
        urut1: $('select[name="urut1"]').val(),
        urut2: $('select[name="urut2"]').val(),
        urut3: $('select[name="urut3"]').val()
      };

      // Bersihkan parameter kosong agar URL tidak terlalu panjang
      let queryString = $.param(params);

      // Pastikan route URL sesuai dengan konfigurasi route Anda
      const url = `${baseUrl}laporan-bank-urut-voucher-list?` + queryString;

      PleaseWaitPage();

      // get data
      fetch(url)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.text();
      })
      .then(result => {

        if (document.querySelector(`.notiflix-loading`)) {
          Loading.remove();
        }

        const { status, message } = JSON.parse(result);

        if (status) {
          filterForm.submit();
        } else {
          // sweetalert
          Swal.fire({
            icon: 'warning',
            title: `Peringatan!`,
            text: `${message}`,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      })
      .catch(err => {
        if (document.querySelector(`.notiflix-loading`)) {
          Loading.remove();
        }

        Swal.fire({
          title: 'Error!',
          text: 'Gagal Generate Data',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      });

    });
  }

  // Picker Date initialization
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickrDate.flatpickr({
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  // Select2 initialization
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      // Ambil placeholder dari data/attr/opsi pertama kosong
      var ph =
        $this.data('placeholder') ||
        $this.attr('placeholder') ||
        $this.find('option[value=""]').first().text() ||
        'Please select';
  
      // Optional: fokus handler Anda
      if (typeof select2Focus === 'function') select2Focus($this);
  
      // Bungkus & init per elemen
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: ph,
        allowClear: true,         // agar placeholder tampil saat kosong
        width: '100%',
        dropdownParent: $this.parent()
      });
    });
  }
});
