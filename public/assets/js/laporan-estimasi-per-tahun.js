/**
 * Laporan Estimasi Per Tahun Management
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

  const dt_estimasi_tahun_table = document.querySelector('.datatables-estimasi-tahun');
  if (dt_estimasi_tahun_table) {
    isAdd = dt_estimasi_tahun_table.dataset.add;
    const dt_estimasi_tahun = new DataTable(dt_estimasi_tahun_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-estimasi-tahun-list',
        data: function (d) {
          d.jenis_laporan = $('#filter-jenis-laporan').val();
          d.tahun = $('#filter-tahun').val();
          d.bulan = $('#filter-bulan').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'nama_pelanggan' },
        { data: 'unit' },
        { data: 'perbaikan_r' },
        { data: 'perbaikan_s' },
        { data: 'perbaikan_t' },
        { data: 'total_perbaikan' },
        { data: 'sparepart_r' },
        { data: 'sparepart_s' },
        { data: 'sparepart_t' },
        { data: 'total_sparepart' },
        { data: 'lain_r' },
        { data: 'lain_s' },
        { data: 'lain_t' },
        { data: 'total_lain' },
        { data: 'total_r' },
        { data: 'total_s' },
        { data: 'total_t' },
        { data: 'ppn' },
        { data: 'total' }
      ],
      columnDefs: [
        { className: 'text-center', targets: [0, 2] },
        { className: 'text-end', targets: [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19] }
      ],
      footerCallback: function (row, data, start, end, display) {
        var api = this.api();

        // Ambil data dari response JSON
        var json = api.ajax.json();

        if (json) {
          // Update footer dengan grand total dari server
          $(api.column(2).footer()).html(json.grand_unit || '0');
          $(api.column(3).footer()).html(json.grand_perbaikan_r || '0');
          $(api.column(4).footer()).html(json.grand_perbaikan_s || '0');
          $(api.column(5).footer()).html(json.grand_perbaikan_t || '0');
          $(api.column(6).footer()).html(json.grand_total_perbaikan || '0');
          $(api.column(7).footer()).html(json.grand_sparepart_r || '0');
          $(api.column(8).footer()).html(json.grand_sparepart_s || '0');
          $(api.column(9).footer()).html(json.grand_sparepart_t || '0');
          $(api.column(10).footer()).html(json.grand_total_sparepart || '0');
          $(api.column(11).footer()).html(json.grand_lain_r || '0');
          $(api.column(12).footer()).html(json.grand_lain_s || '0');
          $(api.column(13).footer()).html(json.grand_lain_t || '0');
          $(api.column(14).footer()).html(json.grand_total_lain || '0');
          $(api.column(15).footer()).html(json.grand_total_r || '0');
          $(api.column(16).footer()).html(json.grand_total_s || '0');
          $(api.column(17).footer()).html(json.grand_total_t || '0');
          $(api.column(18).footer()).html(json.grand_ppn || '0');
          $(api.column(19).footer()).html(json.grand_total || '0');
        }
      }
    });
  }

  // Export excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          jenis_laporan: $('#filter-jenis-laporan').val(),
          tahun: $('#filter-tahun').val(),
          bulan: $('#filter-bulan').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-estimasi-tahun/export?` + queryString;
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk export data',
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
          jenis_laporan: $('#filter-jenis-laporan').val(),
          tahun: $('#filter-tahun').val(),
          bulan: $('#filter-bulan').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-estimasi-tahun/print?` + queryString;
        window.open(printUrl, '_blank');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk print data',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  }

  // Select2 initialization
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var ph =
        $this.data('placeholder') ||
        $this.attr('placeholder') ||
        $this.find('option[value=""]').first().text() ||
        'Please select';

      if (typeof select2Focus === 'function') select2Focus($this);

      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: ph,
        allowClear: true,
        width: '100%',
        dropdownParent: $this.parent()
      });
    });
  }

  // Form validation
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
        jenis_laporan: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Jenis Laporan'
            }
          }
        },
        tahun: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tahun'
            }
          }
        },
        bulan: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Bulan'
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
