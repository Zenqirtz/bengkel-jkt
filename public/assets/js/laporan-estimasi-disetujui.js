/**
 * Laporan Estimasi Disetujui
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

  const dt_estimasi_disetujui_table = document.querySelector('.datatables-estimasi-disetujui');
  if (dt_estimasi_disetujui_table) {
    isAdd = dt_estimasi_disetujui_table.dataset.add;
    const dt_estimasi_disetujui = new DataTable(dt_estimasi_disetujui_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-estimasi-disetujui-list',
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

          // Update footer totals - disesuaikan dengan kolom yang ada
          $('#footer-total-perbaikan').text(json.grand_total_perbaikan || '0');
          $('#footer-total-sparepart').text(json.grand_total_sparepart || '0');
          $('#footer-total-lain').text(json.grand_total_lain || '0');
          $('#footer-total').text(json.grand_total || '0');
          $('#footer-total-perbaikan-s').text(json.grand_total_perbaikan_s || '0');
          $('#footer-total-sparepart-s').text(json.grand_total_sparepart_s || '0');
          $('#footer-total-lain-s').text(json.grand_total_lain_s || '0');
          $('#footer-total-s').text(json.grand_total_s || '0');
          $('#footer-total-or').text(json.grand_total_or_ass || '0');

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'kode_spk' },
        { data: 'no_polisi' },
        { data: 'kode_estimasi' },
        { data: 'nama_pelanggan' },
        { data: 'tgl_konsep' },
        { data: 'tgl_estimasi' },
        { data: 'tgl_persetujuan' },
        { data: 'total_perbaikan' },
        { data: 'total_sparepart' },
        { data: 'total_lain' },
        { data: 'total' },
        { data: 'total_perbaikan_s' },
        { data: 'total_sparepart_s' },
        { data: 'total_lain_s' },
        { data: 'total_s' },
        { data: 'total_or_ass' }
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
        window.location.href = `${baseUrl}laporan-estimasi-disetujui/export?` + queryString;
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
        const printUrl = `${baseUrl}laporan-estimasi-disetujui/print?` + queryString;
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
