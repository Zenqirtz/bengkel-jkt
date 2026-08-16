/**
 * Laporan Rincian Estimasi
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

  const dt_rincian_estimasi_table = document.querySelector('.datatables-rincian-estimasi');
  if (dt_rincian_estimasi_table) {
    isAdd = dt_rincian_estimasi_table.dataset.add;
    const dt_rincian_estimasi = new DataTable(dt_rincian_estimasi_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      scrollY: '400px',
      scrollCollapse: true,
      ajax: {
        url: baseUrl + 'laporan-rincian-estimasi-list',
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
          $('#footer-perbaikan').text(json.grand_total_perbaikan || '0');
          $('#footer-sparepart').text(json.grand_total_sparepart || '0');
          $('#footer-lain').text(json.grand_total_lain || '0');
          $('#footer-total-r').text(json.grand_total_r || '0');
          $('#footer-total-s').text(json.grand_total_s || '0');
          $('#footer-total-t').text(json.grand_total_t || '0');
          $('#footer-ppn').text(json.grand_ppn || '0');
          $('#footer-total').text(json.grand_total || '0');

          return json.data;
        }
      },
      columns: [
        { data: 'no', className: 'text-center' },
        { data: 'nama_pelanggan' },
        { data: 'kode_estimasi' },
        { data: 'tanggal', className: 'text-center' },
        { data: 'kode_spk' },
        { data: 'no_polisi', className: 'text-center' },
        { data: 'tipe_kendaraan' },
        { data: 'total_perbaikan', className: 'text-end' },
        { data: 'total_sparepart', className: 'text-end' },
        { data: 'total_lain', className: 'text-end' },
        { data: 'total_r', className: 'text-end' },
        { data: 'total_s', className: 'text-end' },
        { data: 'total_t', className: 'text-end' },
        { data: 'ppn', className: 'text-end' },
        { data: 'total', className: 'text-end' }
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
        window.location.href = `${baseUrl}laporan-rincian-estimasi/export?` + queryString;
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
        const printUrl = `${baseUrl}laporan-rincian-estimasi/print?` + queryString;
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
    flatpickrDate.flatpickr({
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
