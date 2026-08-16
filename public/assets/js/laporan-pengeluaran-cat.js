'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dt_pengeluaran_cat;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_pengeluaran_cat_table = document.querySelector('.datatables-pengeluaran-cat');
  if (dt_pengeluaran_cat_table) {
    isAdd = dt_pengeluaran_cat_table.dataset.add;

    dt_pengeluaran_cat = new DataTable(dt_pengeluaran_cat_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-pengeluaran-cat-list',
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
        { data: 'kode_pengeluaran' },
        { data: 'tgl_pengeluaran', className: 'text-center' },
        { data: 'no_bon' },
        { data: 'kode_spk' },
        { data: 'no_polisi' },
        { data: 'posisi_pekerjaan' },
        { data: 'point_panel' },
        { data: 'kode_barang' },
        { data: 'nama_cat' },
        { data: 'qty', className: 'text-end' },
        { data: 'satuan' },
        { data: 'harga_lama', className: 'text-end' },
        { data: 'harga', className: 'text-end' },
        { data: 'jumlah', className: 'text-end' }
      ],
      language: {
        emptyTable: 'No data available in table',
        zeroRecords: 'No data available in table'
      },
      createdRow: function (row, data, dataIndex) {
        if (data.is_subtotal) {
          $(row).css({
            'font-weight': 'bold'
          });

          // Merge kolom harga_lama dan harga (kolom ke-13 dan ke-14)
          $(row).find('td:eq(12)').attr('colspan', 2);
          $(row).find('td:eq(13)').remove();
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
        window.location.href = `${baseUrl}laporan-pengeluaran-cat/export?` + queryString;
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk akses export data',
          customClass: {
            confirmButton: 'btn btn-success'
          }
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
        const printUrl = `${baseUrl}laporan-pengeluaran-cat/print?` + queryString;
        window.open(printUrl, '_blank');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk akses print data',
          customClass: {
            confirmButton: 'btn btn-success'
          }
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

  // Select2 initialization
  const select2Elements = document.querySelectorAll('.select2');
  if (select2Elements.length) {
    select2Elements.forEach(function (element) {
      const $element = $(element);
      const placeholder =
        $element.data('placeholder') ||
        $element.attr('placeholder') ||
        $element.find('option[value=""]').first().text() ||
        'Pilih';

      $element.wrap('<div class="position-relative"></div>').select2({
        placeholder: placeholder,
        allowClear: true,
        width: '100%',
        dropdownParent: $element.parent()
      });
    });
  }

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
