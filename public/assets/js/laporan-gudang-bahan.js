'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dt_gudang_bahan;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_gudang_bahan_table = document.querySelector('.datatables-gudang-bahan');
  if (dt_gudang_bahan_table) {
    isAdd = dt_gudang_bahan_table.dataset.add;

    // dt_gudang_bahan = new DataTable(dt_gudang_bahan_table, {
    //   searching: false,
    //   ordering: false,
    //   paging: false,
    //   processing: true,
    //   serverSide: true,
    //   scrollX: true,
    //   ajax: {
    //     url: baseUrl + 'laporan-gudang-bahan-list',
    //     data: function (d) {
    //       d.tgl_awal = $('#filter-tgl-awal').val();
    //       d.tgl_akhir = $('#filter-tgl-akhir').val();
    //     },
    //     dataSrc: function (json) {
    //       if (typeof json.recordsTotal !== 'number') {
    //         json.recordsTotal = 0;
    //       }
    //       if (typeof json.recordsFiltered !== 'number') {
    //         json.recordsFiltered = 0;
    //       }
    //       json.data = Array.isArray(json.data) ? json.data : [];
    //       return json.data;
    //     }
    //   },
    //   columns: [
    //     { data: 'no', className: 'text-center' },
    //     { data: 'tanggal', className: 'text-center' },
    //     { data: 'kode_input' },
    //     { data: 'nama_pemasok' },
    //     { data: 'nama_bahan' },
    //     { data: 'group_bahan' },
    //     { data: 'no_po' },
    //     { data: 'qty', className: 'text-end' },
    //     { data: 'kode_satuan' },
    //     { data: 'harga', className: 'text-end' },
    //     { data: 'jumlah_sebelum', className: 'text-end' },
    //     { data: 'ppn', className: 'text-end' },
    //     { data: 'jumlah', className: 'text-end' },
    //     { data: 'cash', className: 'text-end' },
    //     { data: 'credit', className: 'text-end' }
    //   ],
    //   language: {
    //     emptyTable: 'No data available in table',
    //     zeroRecords: 'No data available in table'
    //   },
    //   createdRow: function (row, data, dataIndex) {
    //     if (data.is_subtotal) {
    //       $(row).css({
    //         'font-weight': 'bold'
    //       });
    //       $('td', row).eq(9).css('white-space', 'nowrap');
    //     }
    //   }
    // });
    dt_gudang_bahan = new DataTable(dt_gudang_bahan_table, {
      searching: false,
      ordering: false,
      paging: true, // diubah dari false
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-gudang-bahan-list',
        data: function (d) {
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          return json.data;
        }
      },
      columns: [
        { data: 'no', className: 'text-center' },
        { data: 'tanggal', className: 'text-center' },
        { data: 'kode_input' },
        { data: 'nama_pemasok' },
        { data: 'nama_bahan' },
        { data: 'group_bahan' },
        { data: 'no_po' },
        { data: 'qty', className: 'text-end' },
        { data: 'kode_satuan' },
        { data: 'harga', className: 'text-end' },
        { data: 'jumlah_sebelum', className: 'text-end' },
        { data: 'ppn', className: 'text-end' },
        { data: 'jumlah', className: 'text-end' },
        { data: 'cash', className: 'text-end' },
        { data: 'credit', className: 'text-end' }
      ],
      // TAMBAHAN: layout untuk page-length dropdown dan info/pagination
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 20, 50, 70, 100],
                text: '_MENU_'
              }
            }
          ]
        },
        topEnd: null,
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: [
            {
              info: {
                text: 'Showing _START_ to _END_ of _TOTAL_ entries'
              }
            }
          ]
        },
        bottomEnd: 'paging'
      },
      displayLength: 10, // TAMBAHAN
      language: {
        emptyTable: 'No data available in table',
        zeroRecords: 'No data available in table',
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      },
      createdRow: function (row, data, dataIndex) {
        if (data.is_subtotal) {
          $(row).css({ 'font-weight': 'bold' });
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
        window.location.href = `${baseUrl}laporan-gudang-bahan/export?` + queryString;
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
        const printUrl = `${baseUrl}laporan-gudang-bahan/print?` + queryString;
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
