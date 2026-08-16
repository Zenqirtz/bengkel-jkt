/**
 * Laporan Insentif S & T
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

  const dt_insentif_st_table = document.querySelector('.datatables-insentif-st');
  if (dt_insentif_st_table) {
    isAdd = dt_insentif_st_table.dataset.add;
    const dt_insentif_st = new DataTable(dt_insentif_st_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-insentif-st-list',
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
          $('#footer-insentif-s').text(json.grand_insentif_s || '0');
          $('#footer-insentif-t').text(json.grand_insentif_t || '0');
          $('#footer-wm-s').text(json.grand_wm_s || '0');
          $('#footer-marketing-s').text(json.grand_marketing_s || '0');
          $('#footer-kabeng-s').text(json.grand_kabeng_s || '0');
          $('#footer-sa-s').text(json.grand_sa_s || '0');
          $('#footer-wm-t').text(json.grand_wm_t || '0');
          $('#footer-marketing-t').text(json.grand_marketing_t || '0');
          $('#footer-kabeng-t').text(json.grand_kabeng_t || '0');
          $('#footer-sa-t').text(json.grand_sa_t || '0');

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'kode_spk' },
        { data: 'no_polisi' },
        { data: 'kode_estimasi' },
        { data: 'nama_asuransi' },
        { data: 'tgl_estimasi' },
        { data: 'insentif_s' },
        { data: 'wm_s' },
        { data: 'marketing_s' },
        { data: 'kabeng_s' },
        { data: 'sa_s' },
        { data: 'insentif_t' },
        { data: 'wm_t' },
        { data: 'marketing_t' },
        { data: 'kabeng_t' },
        { data: 'sa_t' }
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
        window.location.href = `${baseUrl}laporan-insentif-st/export?` + queryString;
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
        const printUrl = `${baseUrl}laporan-insentif-st/print?` + queryString;
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
