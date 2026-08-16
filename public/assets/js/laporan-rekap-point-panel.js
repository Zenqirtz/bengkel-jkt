/**
 * Laporan Rekap Point Panel Management
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

  const dt_rekap_point_panel_table = document.querySelector('.datatables-rekap-point-panel');
  if (dt_rekap_point_panel_table) {
    isAdd = dt_rekap_point_panel_table.dataset.add;
    const dt_rekap_point_panel = new DataTable(dt_rekap_point_panel_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-rekap-point-panel-list',
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }
          json.data = Array.isArray(json.data) ? json.data : [];

          // Update footer totals
          $('#footer-jumlah-spk').text(json.grand_jumlah_spk || '0');
          $('#footer-total-panel').text(json.grand_total_panel || '0');

          return json.data;
        }
      },
      columns: [
        {
          data: 'no',
          className: 'text-center'
        },
        {
          data: 'bulan'
        },
        {
          data: 'jumlah_spk',
          className: 'text-end'
        },
        {
          data: 'total_panel',
          className: 'text-end'
        }
      ]
    });
  }

  // Export excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          tahun: $('#filter-tahun').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-rekap-point-panel/export?` + queryString;
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
          tahun: $('#filter-tahun').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-rekap-point-panel/print?` + queryString;
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
        tahun: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Tahun'
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
