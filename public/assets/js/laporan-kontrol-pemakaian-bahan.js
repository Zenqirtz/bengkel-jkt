'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dtTable;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Parse angka dari format PHP number_format($x, 2, '.', '.') => "1.234.56"
  // atau number_format($x, 0, '.', '.') => "1.234.000"
  function parseNum(str) {
    if (!str || str === '-') return 0;
    var s = String(str).trim();
    var lastDot = s.lastIndexOf('.');
    if (lastDot !== -1 && s.length - lastDot - 1 <= 2) {
      var intPart = s.substring(0, lastDot).replace(/\./g, '');
      var decPart = s.substring(lastDot + 1);
      return parseFloat(intPart + '.' + decPart) || 0;
    }
    return parseFloat(s.replace(/\./g, '')) || 0;
  }

  // Format Qty: 2 desimal, titik sebagai pemisah ribuan dan desimal => 1.234.56
  function fmtDec(n) {
    var parts = Number(n).toFixed(2).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join('.');
  }

  // Format Harga/Total: integer, titik sebagai pemisah ribuan => 1.234.000
  function fmtInt(n) {
    return Math.round(n)
      .toString()
      .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  const dtEl = document.querySelector('.datatables-kontrol-pemakaian-bahan');
  if (dtEl) {
    isAdd = dtEl.dataset.add;

    dtTable = new DataTable(dtEl, {
      searching: false,
      ordering: false,
      paging: false,
      info: true,
      processing: true,
      serverSide: true,
      scrollX: true,
      language: {
        emptyTable: '',
        zeroRecords: '',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: 'Showing 0 to 0 of 0 entries',
        infoFiltered: '(filtered from _MAX_ total entries)'
      },
      ajax: {
        url: baseUrl + 'lap-kontrol-pemakaian-bahan-list',
        data: function (d) {
          d.no_spk = $('#filter-no-spk').val();
        },
        dataSrc: function (json) {
          // Update meta info
          if (json.meta) {
            $('#meta-no-spk').text(json.meta.no_spk || '-');
            $('#meta-point-panel').text(json.meta.point_panel || '-');
            $('#meta-pemilik').text(json.meta.nama_pemilik || '-');
            $('#meta-merek-tipe').text(json.meta.merek_tipe || '-');
            $('#meta-info').show();
          } else {
            $('#meta-info').hide();
          }

          json.recordsTotal = typeof json.recordsTotal === 'number' ? json.recordsTotal : 0;
          json.recordsFiltered = typeof json.recordsFiltered === 'number' ? json.recordsFiltered : 0;
          json.data = Array.isArray(json.data) ? json.data : [];

          // Hitung grand total footer
          let totStdQty = 0,
            totStdHarga = 0;
          let totAktualQty = 0,
            totAktualHarga = 0,
            totAktualTotal = 0;
          let totVarQty = 0,
            totVarHarga = 0;

          json.data.forEach(function (row) {
            totStdQty += parseNum(row.std_qty);
            totStdHarga += parseNum(row.std_harga);
            totAktualQty += parseNum(row.aktual_qty);
            totAktualHarga += parseNum(row.aktual_harga);
            totAktualTotal += parseNum(row.aktual_total);
            totVarQty += parseNum(row.variance_qty);
            totVarHarga += parseNum(row.variance_harga);
          });

          $('#foot-std-qty').text(fmtDec(totStdQty));
          $('#foot-std-harga').text(fmtInt(totStdHarga));
          $('#foot-aktual-qty').text(fmtDec(totAktualQty));
          $('#foot-aktual-harga').text(fmtInt(totAktualHarga));
          $('#foot-aktual-total').text(fmtInt(totAktualTotal));
          $('#foot-variance-qty').text(fmtDec(totVarQty));
          $('#foot-variance-harga').text(fmtInt(totVarHarga));

          return json.data;
        }
      },
      columns: [
        {
          data: null,
          render: function (data, type, row, meta) {
            return meta.row + 1;
          },
          className: 'text-center'
        },
        { data: 'posisi_pekerjaan' },
        { data: 'nama_bahan' },
        { data: 'std_qty', className: 'text-end' },
        { data: 'std_harga', className: 'text-end' },
        { data: 'aktual_qty', className: 'text-end' },
        { data: 'aktual_harga', className: 'text-end' },
        { data: 'aktual_total', className: 'text-end' },
        { data: 'variance_qty', className: 'text-end' },
        { data: 'variance_harga', className: 'text-end' }
      ]
    });
  }

  // Export Excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        const noSpk = $('#filter-no-spk').val();
        if (!noSpk) {
          Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: 'Nomor SPK wajib diisi.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }
        window.location.href = `${baseUrl}lap-kontrol-pemakaian-bahan/export?` + $.param({ no_spk: noSpk });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk export data.',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  }

  // Print
  const printBtn = document.querySelector('.btn-print');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      if (isAdd) {
        const noSpk = $('#filter-no-spk').val();
        if (!noSpk) {
          Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: 'Nomor SPK wajib diisi.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }
        window.open(`${baseUrl}lap-kontrol-pemakaian-bahan/print?` + $.param({ no_spk: noSpk }), '_blank');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Anda tidak memiliki izin untuk print data.',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  }

  // Form Validation
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    FormValidation.formValidation(filterForm, {
      fields: {
        no_spk: {
          validators: {
            notEmpty: { message: 'Silahkan Input Nomor SPK' }
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
