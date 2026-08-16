/**
 * Page Data management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_voucher_table = document.querySelector('.datatables-kwitansi-voucher');
  if (dt_voucher_table) {
    isAdd = dt_voucher_table.dataset.add;
    const dt_voucher = new DataTable(dt_voucher_table, {
      searching: false, // Opsi ini akan menghilangkan input cari
      ordering: false, // Opsi ini akan menghilangkan ordering
      paging: false, // Opsi ini akan menghilangkan paging
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-kwitansi-lunas-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.jenis_laporan = $('#filter-jenis-laporan').val();
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
          d.no_voucher = $('#filter-no-voucher').val();
        },
        dataSrc: function (json) {
          // Ensure recordsTotal and recordsFiltered are numeric and not undefined/null
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }

          // Fallback for empty data to avoid pagination NaN issue
          json.data = Array.isArray(json.data) ? json.data : [];

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'kode_voucher' },
        { data: 'tgl_lunas' },
        { data: 'kode_lunas_kwitansi' },
        { data: 'kode_spk' },
        { data: 'kode_kwitansi' },
        { data: 'kode_estimasi' },
        { data: 'no_polisi' },
        { data: 'jasa' },
        { data: 'bahan' },
        { data: 'total_sparepart_s' },
        { data: 'ppn' },
        { data: 'total_lain_s' },
        { data: 'total_or_ass' },
        { data: 'tagihan' },
        { data: 'pph' },
        { data: 'materai' },
        { data: 'uang_muka' },
        { data: 'diterima' },
        { data: 'tot_estimasi' },
        { data: 'biaya_real' }
      ],
      createdRow: function (row, data, dataIndex) {
        if (data.row_type === 'header') {
          $(row).addClass('table-light fw-bold');
          $(row).find('td').eq(0).attr('colspan', 21).text(data.header_label);
          $(row).find('td').slice(1).remove();
        } else if (data.row_type === 'columnheader') {
          $(row).addClass('table-secondary fw-bold');
        } else if (data.row_type === 'subtotal') {
          $(row).addClass('fw-bold').css({ 'background-color': '#DBEAFE', color: '#1E40AF' });
        } else if (data.row_type === 'info') {
          $(row).find('td').eq(0).attr('colspan', 21).html(data.info_label);
          $(row).find('td').slice(1).remove();
        } else if (data.row_type === 'grandtotal') {
          $(row).addClass('fw-bold').css({ 'background-color': '#DBEAFE', color: '#1E40AF' });
        }
      }
    });
  }

  const dt_rekap_table = document.querySelector('.datatables-kwitansi-rekap');
  if (dt_rekap_table) {
    isAdd = dt_rekap_table.dataset.add;
    const dt_rekap = new DataTable(dt_rekap_table, {
      searching: false, // Opsi ini akan menghilangkan input cari
      ordering: false, // Opsi ini akan menghilangkan ordering
      paging: false, // Opsi ini akan menghilangkan paging
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-kwitansi-lunas-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.jenis_laporan = $('#filter-jenis-laporan').val();
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
        },
        dataSrc: function (json) {
          // Ensure recordsTotal and recordsFiltered are numeric and not undefined/null
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }

          // Fallback for empty data to avoid pagination NaN issue
          json.data = Array.isArray(json.data) ? json.data : [];

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'nama_pelanggan' },
        { data: 'unit' },
        { data: 'tunai' },
        { data: 'bank' },
        { data: 'free' },
        { data: 'uang_muka' },
        { data: 'pph' },
        { data: 'materai' },
        { data: 'tagihan' },
        { data: 'diterima' },
        { data: 'tot_estimasi' },
        { data: 'perbaikan' },
        { data: 'sparepart' },
        { data: 'lain' }
      ],
      createdRow: function (row, data, dataIndex) {
        if (data.nama_pelanggan === 'Grand Total') {
          $(row).addClass('fw-bold');
        }
      }
    });
  }

  const dt_rinci_table = document.querySelector('.datatables-kwitansi-rinci');
  if (dt_rinci_table) {
    isAdd = dt_rinci_table.dataset.add;
    const dt_rinci = new DataTable(dt_rinci_table, {
      searching: false, // Opsi ini akan menghilangkan input cari
      ordering: false, // Opsi ini akan menghilangkan ordering
      paging: false, // Opsi ini akan menghilangkan paging
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-kwitansi-lunas-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.jenis_laporan = $('#filter-jenis-laporan').val();
          d.tgl_awal = $('#filter-tgl-awal').val();
          d.tgl_akhir = $('#filter-tgl-akhir').val();
        },
        dataSrc: function (json) {
          // Ensure recordsTotal and recordsFiltered are numeric and not undefined/null
          if (typeof json.recordsTotal !== 'number') {
            json.recordsTotal = 0;
          }
          if (typeof json.recordsFiltered !== 'number') {
            json.recordsFiltered = 0;
          }

          // Fallback for empty data to avoid pagination NaN issue
          json.data = Array.isArray(json.data) ? json.data : [];

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'kode_voucher' },
        { data: 'tgl_lunas' },
        { data: 'kode_lunas_kwitansi' },
        { data: 'kode_spk' },
        { data: 'kode_kwitansi' },
        { data: 'kode_estimasi' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'nama_pelanggan' },
        { data: 'tunai' },
        { data: 'bank' },
        { data: 'free' },
        { data: 'uang_muka' },
        { data: 'pph' },
        { data: 'materai' },
        { data: 'tagihan' },
        { data: 'diterima' },
        { data: 'tot_estimasi' },
        { data: 'biaya' }
      ],
      createdRow: function (row, data, dataIndex) {
        if (data.kode_voucher === 'Grand Total') {
          $(row).addClass('fw-bold');
        }
      }
    });
  }

  // export excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        // Ambil data dari form filter
        // Kita ambil value manual karena form berada di dalam modal
        let params = {
          jenis_laporan: $('#filter-jenis-laporan').val(),
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val(),
          no_voucher: $('#filter-no-voucher').val()
        };

        // Bersihkan parameter kosong agar URL tidak terlalu panjang
        let queryString = $.param(params);

        // Redirect window untuk download file
        // Pastikan route URL sesuai dengan konfigurasi route Anda
        window.location.href = `${baseUrl}laporan-kwitansi-lunas/export?` + queryString;
      } else {
        // Hide offcanvas
        const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
        offcanvasInstance && offcanvasInstance.hide();

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

  // print data
  const printBtn = document.querySelector('.btn-print');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      if (isAdd) {
        // Ambil data dari form filter
        // Kita ambil value manual karena form berada di dalam modal
        let params = {
          jenis_laporan: $('#filter-jenis-laporan').val(),
          tgl_awal: $('#filter-tgl-awal').val(),
          tgl_akhir: $('#filter-tgl-akhir').val(),
          no_voucher: $('#filter-no-voucher').val()
        };

        // Bersihkan parameter kosong agar URL tidak terlalu panjang
        let queryString = $.param(params);

        // Redirect window untuk download file
        // Pastikan route URL sesuai dengan konfigurasi route Anda
        // window.location.href = `${baseUrl}laporan-kwitansi/print?` + queryString;
        const printUrl = `${baseUrl}laporan-kwitansi-lunas/print?` + queryString;
        window.open(printUrl, '_blank');
      } else {
        // Hide offcanvas
        const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
        offcanvasInstance && offcanvasInstance.hide();

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

  // Phone Number mask initialization
  const phoneMaskList = document.querySelectorAll('.phone-mask');
  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      phoneMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        phoneMask.value = formatGeneral(cleanValue, {
          blocks: [3, 10],
          delimiters: ['-']
        });
      });
      registerCursorTracker({
        input: phoneMask,
        delimiter: '-'
      });
    });
  }

  // Tahun & CC mask initialization
  const tahunMaskList = document.querySelectorAll('.tahun-mask');
  if (tahunMaskList) {
    tahunMaskList.forEach(function (tahunMask) {
      tahunMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        tahunMask.value = formatGeneral(cleanValue, {
          blocks: [4],
          delimiters: ['']
        });
      });
      registerCursorTracker({
        input: tahunMask,
        delimiter: ''
      });
    });
  }

  // Select2 initialization
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      // Ambil placeholder dari data/attr/opsi pertama kosong
      var ph =
        $this.data('placeholder') ||
        $this.attr('placeholder') ||
        $this.find('option[value=""]').first().text() ||
        'Please select';

      // Optional: fokus handler Anda
      if (typeof select2Focus === 'function') select2Focus($this);

      // Bungkus & init per elemen
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: ph,
        allowClear: true, // agar placeholder tampil saat kosong
        width: '100%',
        dropdownParent: $this.parent()
      });
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

  // Picker Date Range initialization
  const flatpickrDateRange = document.querySelectorAll('.dt-date-range');
  if (flatpickrDateRange) {
    flatpickrDateRange.flatpickr({
      mode: 'range',
      // monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  // Jenis Laporan
  const selectJenisLaporan = $('#filter-jenis-laporan');
  if (selectJenisLaporan.length) {
    // const jnsLapPeriode = $('#jns-lap-periode');
    const noVoucher = $('#no-voucher');

    // jnsLapPeriode.hide();
    noVoucher.hide();

    if(selectJenisLaporan.val() == "periode") {
      // jnsLapPeriode.show();
    } else if(selectJenisLaporan.val() == "tahun") {
      // jnsLapTahun.show();
    } else if(selectJenisLaporan.val() == "voucher") {
      noVoucher.show();
    }

    selectJenisLaporan.on('change', function () {
      const jnsLap = $(this).val();

      // jnsLapPeriode.hide();
      noVoucher.hide();

      if(jnsLap == "periode") {
        // jnsLapPeriode.show();
      } else if(jnsLap == "tahun") {
        // jnsLapTahun.show();
      } else if(jnsLap == "voucher") {
        noVoucher.show();
      }
    });
  }

  // validating form and updating user's data
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
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: '',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
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

function setSelectValue(selector, value, textIfMissing) {
  const $el = $(selector);
  if (!$el.length) return;

  // Normalisasi tipe: option.value adalah string
  const val = Array.isArray(value) ? value.map(v => String(v)) : (value ?? '');
  const isSelect2 = $el.hasClass('select2-hidden-accessible');

  // Jika select2 pakai AJAX dan opsi belum ada, tambahkan sementara
  const ensureOption = (v, t) => {
    if (!$el.find(`option[value="${v}"]`).length) {
      $el.append(new Option(t ?? v, v, false, false)); // text fallback = value
    }
  };

  if (Array.isArray(val)) {
    // multiple select
    val.forEach(v => ensureOption(v, (textIfMissing && textIfMissing[v]) || v));
    $el.val(val);
  } else {
    if (val === '' || val === null) {
      $el.val(null);
    } else {
      ensureOption(String(val), textIfMissing);
      $el.val(String(val));
    }
  }

  // trigger agar UI Select2 ikut sync
  if (isSelect2) $el.trigger('change');
}

function clearSelect(selector, { keepOptions = true } = {}) {
  const $el = $(selector);
  if (!$el.length) return;

  if (!keepOptions) {
    $el.empty().append(new Option('', '', true, false));
  }
  $el.val(null);
  if ($el.hasClass('select2-hidden-accessible')) {
    $el.trigger('change'); // penting untuk sync UI Select2
  }
}
