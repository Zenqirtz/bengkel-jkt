'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dt_invoice_terbit;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_table = document.querySelector('.datatables-invoice-terbit');

  // function getColumnsRekap() {
  //   return [
  //     { data: 'no', className: 'text-center' },
  //     { data: 'jenis_pelanggan' },
  //     { data: 'nama_pelanggan' },
  //     { data: 'jumlah_invoice', className: 'text-end' },
  //     { data: 'total_or', className: 'text-end' },
  //     { data: 'free', className: 'text-end' }
  //   ];
  // }
  function getColumnsRekap() {
    return [
      { data: 'no', className: 'text-center' },
      { data: 'nama_pelanggan' },
      { data: 'unit', className: 'text-end' },
      { data: 'jasa', className: 'text-end' },
      { data: 'bahan', className: 'text-end' },
      { data: 'sparepart', className: 'text-end' },
      { data: 'ppn', className: 'text-end' },
      { data: 'total_lain', className: 'text-end' },
      { data: 'total_invoice', className: 'text-end' },
      { data: 'total_or', className: 'text-end' },
      { data: 'tagihan', className: 'text-end' }
    ];
  }

  // function getColumnsRinci() {
  //   return [
  //     { data: 'no', className: 'text-center' },
  //     { data: 'no_invoice' },
  //     { data: 'tgl_invoice', className: 'text-center' },
  //     { data: 'kode_spk' },
  //     { data: 'no_polisi' },
  //     { data: 'merek_tipe' },
  //     { data: 'jenis_pelanggan' },
  //     { data: 'nama_pelanggan' },
  //     { data: 'tertanggung' },
  //     { data: 'pemilik' },
  //     { data: 'jenis_identitas', className: 'text-center' },
  //     { data: 'no_identitas' },
  //     { data: 'total_or', className: 'text-end' },
  //     { data: 'free', className: 'text-end' }
  //   ];
  // }
  function getColumnsRinci() {
    return [
      { data: 'no', className: 'text-center' },
      { data: 'no_invoice' },
      { data: 'kode_spk' },
      { data: 'no_polisi' },
      { data: 'nama_pelanggan' },
      { data: 'npwp_ktp' },
      { data: 'jasa', className: 'text-end' },
      { data: 'bahan', className: 'text-end' },
      { data: 'sparepart', className: 'text-end' },
      { data: 'ppn', className: 'text-end' },
      { data: 'total_lain', className: 'text-end' },
      { data: 'total_invoice', className: 'text-end' },
      { data: 'total_or', className: 'text-end' },
      { data: 'tagihan', className: 'text-end' }
    ];
  }

  // function getTheadRekap() {
  //   return `
  //     <tr>
  //       <th>No</th>
  //       <th>Jenis Pelanggan</th>
  //       <th>Nama Pelanggan</th>
  //       <th>Jumlah Invoice</th>
  //       <th>Total OR</th>
  //       <th>Free</th>
  //     </tr>`;
  // }
  function getTheadRekap() {
    return `
    <tr>
      <th>No</th>
      <th>Nama Asuransi</th>
      <th>Unit</th>
      <th>Jasa</th>
      <th>Bahan</th>
      <th>Sparepart</th>
      <th>PPN</th>
      <th>Total Lain</th>
      <th>Total Invoice</th>
      <th>Total OR</th>
      <th>Tagihan</th>
    </tr>`;
  }

  // function getTheadRinci() {
  //   return `
  //     <tr>
  //       <th>No</th>
  //       <th>No Invoice</th>
  //       <th>Tgl Invoice</th>
  //       <th>No SPK</th>
  //       <th>No Polisi</th>
  //       <th>Kendaraan</th>
  //       <th>Jenis Pelanggan</th>
  //       <th>Nama Pelanggan</th>
  //       <th>Tertanggung</th>
  //       <th>Pemilik</th>
  //       <th>Jenis Identitas</th>
  //       <th>No Identitas</th>
  //       <th>Total OR</th>
  //       <th>Free</th>
  //     </tr>`;
  // }
  function getTheadRinci() {
    return `
    <tr>
      <th>No</th>
      <th>No Invoice</th>
      <th>No SPK</th>
      <th>No Polisi</th>
      <th>Nama Asuransi</th>
      <th>NPWP/KTP</th>
      <th>Jasa</th>
      <th>Bahan</th>
      <th>Sparepart</th>
      <th>PPN</th>
      <th>Total Lain</th>
      <th>Total Invoice</th>
      <th>Total OR</th>
      <th>Tagihan</th>
    </tr>`;
  }

  function initDataTable(jenisReport) {
    if (dt_invoice_terbit) {
      dt_invoice_terbit.destroy();
      $(dt_table).find('tbody').remove();
    }

    const theadHtml = jenisReport === 'Rekap' ? getTheadRekap() : getTheadRinci();
    $(dt_table).find('thead').html(theadHtml);

    dt_invoice_terbit = new DataTable(dt_table, {
      searching: false,
      ordering: false,
      paging: true,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-invoice-terbit-list',
        data: function (d) {
          d.jenis_report = jenisReport;
          // d.tahun = $('#filter-tahun').val();
          // d.bulan = $('#filter-bulan').val();
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
        },
        // dataSrc: function (json) {
        //   if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
        //   if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
        //   json.data = Array.isArray(json.data) ? json.data : [];
        //   $('#display-periode').text(json.periode ?? '-');
        //   return json.data;
        // }
        dataSrc: function (json) {
          if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
          if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
          json.data = Array.isArray(json.data) ? json.data : [];
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
      },
      // 2. TAMBAH blok layout DI SINI, tepat setelah columns: [...]
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [{ pageLength: { menu: [10, 20, 50, 70, 100], text: '_MENU_' } }]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
        },
        bottomEnd: 'paging'
      },

      // 3. TAMBAH displayLength dan language DI SINI, setelah layout
      displayLength: 10,
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      }
    });
  }

  if (dt_table) {
    isAdd = dt_table.dataset.add;
    const initialJenis = dt_table.dataset.jenisReport || 'Rekap';
    initDataTable(initialJenis);
  }

  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          jenis_report: $('input[name="jenis_report"]:checked').val(),
          // tahun: $('#filter-tahun').val(),
          // bulan: $('#filter-bulan').val()
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val()
        };
        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-invoice-terbit/export?` + queryString;
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
          // tahun: $('#filter-tahun').val(),
          // bulan: $('#filter-bulan').val()
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val()
        };
        let queryString = $.param(params);
        window.open(`${baseUrl}laporan-invoice-terbit/print?` + queryString, '_blank');
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
        jenis_report: { validators: { notEmpty: { message: 'Silahkan Pilih Jenis Report' } } },
        // tahun: { validators: { notEmpty: { message: 'Silahkan Pilih Tahun' } } },
        // bulan: { validators: { notEmpty: { message: 'Silahkan Pilih Bulan' } } }
        tgl_awal: { validators: { notEmpty: { message: 'Silahkan Input Tanggal Awal' } } },
        tgl_akhir: { validators: { notEmpty: { message: 'Silahkan Input Tanggal Akhir' } } }
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
