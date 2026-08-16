// 'use strict';

// document.addEventListener('DOMContentLoaded', function (e) {
//   let isAdd;
//   let dt_analisa_bahan;

//   $.ajaxSetup({
//     headers: {
//       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//     }
//   });

//   const dt_analisa_bahan_table = document.querySelector('.datatables-analisa-bahan');
//   if (dt_analisa_bahan_table) {
//     isAdd = dt_analisa_bahan_table.dataset.add;

//     dt_analisa_bahan = new DataTable(dt_analisa_bahan_table, {
//       searching: false,
//       ordering: false,
//       paging: false,
//       processing: true,
//       serverSide: true,
//       scrollX: true,
//       ajax: {
//         url: baseUrl + 'laporan-analisa-bahan-list',
//         data: function (d) {
//           d.jenis_report = $('input[name="jenis_report"]:checked').val();
//           d.tahun = $('#filter-tahun').val();
//           d.bulan = $('#filter-bulan').val();
//         },
//         dataSrc: function (json) {
//           if (typeof json.recordsTotal !== 'number') {
//             json.recordsTotal = 0;
//           }
//           if (typeof json.recordsFiltered !== 'number') {
//             json.recordsFiltered = 0;
//           }
//           json.data = Array.isArray(json.data) ? json.data : [];
//           return json.data;
//         }
//       },
//       columns: [
//         { data: 'no', className: 'text-center' },
//         { data: 'nama_bahan' },
//         { data: 'qty', className: 'text-end' },
//         { data: 'harga', className: 'text-end' },
//         { data: 'jumlah', className: 'text-end' },
//         { data: 'satuan' },
//         { data: 'qty_per_point', className: 'text-end' },
//         { data: 'rupiah_per_point', className: 'text-end' }
//       ],
//       language: {
//         emptyTable: 'No data available in table',
//         zeroRecords: 'No data available in table'
//       }
//     });
//   }

//   const exportBtn = document.querySelector('.btn-export-excel');
//   if (exportBtn) {
//     exportBtn.addEventListener('click', function () {
//       if (isAdd) {
//         let params = {
//           jenis_report: $('input[name="jenis_report"]:checked').val(),
//           tahun: $('#filter-tahun').val(),
//           bulan: $('#filter-bulan').val()
//         };

//         let queryString = $.param(params);
//         window.location.href = `${baseUrl}laporan-analisa-bahan/export?` + queryString;
//       } else {
//         Swal.fire({
//           icon: 'error',
//           title: 'Error!',
//           text: 'Anda tidak memiliki izin untuk akses export data',
//           customClass: {
//             confirmButton: 'btn btn-success'
//           }
//         });
//       }
//     });
//   }

//   const printBtn = document.querySelector('.btn-print');
//   if (printBtn) {
//     printBtn.addEventListener('click', function () {
//       if (isAdd) {
//         let params = {
//           jenis_report: $('input[name="jenis_report"]:checked').val(),
//           tahun: $('#filter-tahun').val(),
//           bulan: $('#filter-bulan').val()
//         };

//         let queryString = $.param(params);
//         const printUrl = `${baseUrl}laporan-analisa-bahan/print?` + queryString;
//         window.open(printUrl, '_blank');
//       } else {
//         Swal.fire({
//           icon: 'error',
//           title: 'Error!',
//           text: 'Anda tidak memiliki izin untuk akses print data',
//           customClass: {
//             confirmButton: 'btn btn-success'
//           }
//         });
//       }
//     });
//   }

//   // Select2 initialization
//   const select2Elements = document.querySelectorAll('.select2');
//   if (select2Elements.length) {
//     select2Elements.forEach(function (element) {
//       const $element = $(element);
//       const placeholder =
//         $element.data('placeholder') ||
//         $element.attr('placeholder') ||
//         $element.find('option[value=""]').first().text() ||
//         'Pilih';

//       $element.wrap('<div class="position-relative"></div>').select2({
//         placeholder: placeholder,
//         allowClear: true,
//         width: '100%',
//         dropdownParent: $element.parent()
//       });
//     });
//   }

//   const filterForm = document.getElementById('filterForm');
//   if (filterForm) {
//     const fv = FormValidation.formValidation(filterForm, {
//       fields: {
//         jenis_report: {
//           validators: {
//             notEmpty: {
//               message: 'Silahkan Pilih Jenis Report'
//             }
//           }
//         },
//         tahun: {
//           validators: {
//             notEmpty: {
//               message: 'Silahkan Pilih Tahun'
//             }
//           }
//         },
//         bulan: {
//           validators: {
//             notEmpty: {
//               message: 'Silahkan Pilih Bulan'
//             }
//           }
//         }
//       },
//       plugins: {
//         trigger: new FormValidation.plugins.Trigger(),
//         bootstrap5: new FormValidation.plugins.Bootstrap5({
//           eleValidClass: '',
//           rowSelector: function (field, ele) {
//             return '.form-control-validation';
//           }
//         }),
//         submitButton: new FormValidation.plugins.SubmitButton(),
//         autoFocus: new FormValidation.plugins.AutoFocus()
//       }
//     }).on('core.form.valid', function () {
//       filterForm.submit();
//     });
//   }
// });

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dt_analisa_bahan;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_analisa_bahan_table = document.querySelector('.datatables-analisa-bahan');

  function getColumnsRekap() {
    return [
      { data: 'no', className: 'text-center' },
      { data: 'nama_bahan' },
      { data: 'qty', className: 'text-end' },
      { data: 'harga', className: 'text-end' },
      { data: 'jumlah', className: 'text-end' },
      { data: 'satuan' },
      { data: 'qty_per_point', className: 'text-end' },
      { data: 'rupiah_per_point', className: 'text-end' }
    ];
  }

  function getColumnsRinci() {
    return [
      { data: 'no', className: 'text-center' },
      { data: 'tanggal', className: 'text-center' },
      { data: 'nama_bahan' },
      { data: 'qty', className: 'text-end' },
      { data: 'harga', className: 'text-end' },
      { data: 'jumlah', className: 'text-end' },
      { data: 'satuan' },
      { data: 'qty_per_point', className: 'text-end' },
      { data: 'rupiah_per_point', className: 'text-end' }
    ];
  }

  function getTheadRekap() {
    return `
      <tr>
        <th>No</th>
        <th>Nama Bahan</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Satuan</th>
        <th>Qty/ Point Panel</th>
        <th>Rupiah/ Point Panel</th>
      </tr>`;
  }

  function getTheadRinci() {
    return `
      <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Nama Bahan</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Jumlah</th>
        <th>Satuan</th>
        <th>Qty/ Point Panel</th>
        <th>Rupiah/ Point Panel</th>
      </tr>`;
  }

  function initDataTable(jenisReport) {
    if (dt_analisa_bahan) {
      dt_analisa_bahan.destroy();
      $(dt_analisa_bahan_table).find('tbody').remove();
    }

    const theadHtml = jenisReport === 'Rekap' ? getTheadRekap() : getTheadRinci();
    $(dt_analisa_bahan_table).find('thead').html(theadHtml);

    dt_analisa_bahan = new DataTable(dt_analisa_bahan_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-analisa-bahan-list',
        data: function (d) {
          d.jenis_report = jenisReport;
          d.tahun = $('#filter-tahun').val();
          d.bulan = $('#filter-bulan').val();
        },
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
          $('#display-periode').text(json.periode ?? '-');
          $('#display-jumlah-panel').text(json.jumlah_panel ?? '-');
          return json.data;
        }
      },
      columns: jenisReport === 'Rekap' ? getColumnsRekap() : getColumnsRinci(),
      language: {
        emptyTable: 'No data available in table',
        zeroRecords: 'No data available in table'
      },
      createdRow: function (row, data, dataIndex) {
        if (data.is_total) {
          $(row).addClass('fw-bold');
        }
      }
    });
  }

  if (dt_analisa_bahan_table) {
    isAdd = dt_analisa_bahan_table.dataset.add;
    const initialJenis = $('input[name="jenis_report"]:checked').val() || 'Rekap';
    initDataTable(initialJenis);
  }

  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          jenis_report: $('input[name="jenis_report"]:checked').val(),
          tahun: $('#filter-tahun').val(),
          bulan: $('#filter-bulan').val()
        };
        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-analisa-bahan/export?` + queryString;
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
          jenis_report: $('input[name="jenis_report"]:checked').val(),
          tahun: $('#filter-tahun').val(),
          bulan: $('#filter-bulan').val()
        };
        let queryString = $.param(params);
        window.open(`${baseUrl}laporan-analisa-bahan/print?` + queryString, '_blank');
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
        jenis_report: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Jenis Report'
            }
          }
        },
        tahun: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Tahun'
            }
          }
        },
        bulan: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Bulan'
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
      initDataTable($('input[name="jenis_report"]:checked').val());
    });
  }
});
