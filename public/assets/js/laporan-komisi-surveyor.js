/**
 * Laporan Komisi Surveyor
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

  const dt_komisi_surveyor_table = document.querySelector('.datatables-komisi-surveyor');
  if (dt_komisi_surveyor_table) {
    isAdd = dt_komisi_surveyor_table.dataset.add;
    const dt_komisi_surveyor = new DataTable(dt_komisi_surveyor_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-komisi-surveyor-list',
        data: function (d) {
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
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
          $('#footer-jasa').text(json.grand_jasa || '0');
          $('#footer-sparepart').text(json.grand_sparepart || '0');
          $('#footer-lain').text(json.grand_lain || '0');
          $('#footer-jumlah').text(json.grand_jumlah || '0');

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'kode_spk' },
        { data: 'tgl_masuk' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'nama_pelanggan' },
        { data: 'no_polis' },
        { data: 'kode_estimasi' },
        { data: 'total_jasa' },
        { data: 'total_sparepart' },
        { data: 'total_lain' },
        { data: 'total_perbaikan' }
      ]
    });
  }

  // Export excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-komisi-surveyor/export?` + queryString;
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
      if (isAdd) {
        let params = {
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-komisi-surveyor/print?` + queryString;
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

  // Picker Date initialization
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickr(flatpickrDate, {
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  // Form validation
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
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
      filterForm.submit();
    });
  }
});
