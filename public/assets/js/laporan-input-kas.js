'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dt_input_kas;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_input_kas_table = document.querySelector('.datatables-input-kas');
  if (dt_input_kas_table) {
    isAdd = dt_input_kas_table.dataset.add;

    dt_input_kas = new DataTable(dt_input_kas_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-input-kas-list',
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
          return json.data;
        }
      },
      columns: [
        { data: 'no', className: 'text-center' },
        { data: 'tanggal', className: 'text-center' },
        { data: 'no_bukti' },
        { data: 'memo' },
        { data: 'no_spk' },
        { data: 'no_input_gudang' },
        { data: 'debet', className: 'text-end' },
        { data: 'kredit', className: 'text-end' },
        { data: 'saldo', className: 'text-end' },
        { data: 'or_free' }
      ],
      language: {
        emptyTable: 'No data available in table',
        zeroRecords: 'No data available in table'
      },
      createdRow: function (row, data, dataIndex) {
        if (data.is_grand_total) {
          $(row).css({
            'font-weight': 'bold'
          });
          $('td', row).eq(9).css('white-space', 'nowrap');
        }
      }
    });
  }

  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-input-kas/export?` + queryString;
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk akses export data',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  }

  const printBtn = document.querySelector('.btn-print');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-input-kas/print?` + queryString;
        window.open(printUrl, '_blank');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk akses print data',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  }

  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickr(flatpickrDate, {
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
        tgl_awal: {
          validators: { notEmpty: { message: 'Silahkan Input Tanggal Awal' } }
        },
        tgl_akhir: {
          validators: { notEmpty: { message: 'Silahkan Input Tanggal Akhir' } }
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
