/**
 * Laporan Insentif Pembayaran
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_insentif_table = document.querySelector('.datatables-insentif-pembayaran');
  if (dt_insentif_table) {
    isAdd = dt_insentif_table.dataset.add;
    const dt_insentif = new DataTable(dt_insentif_table, {
      searching: false,
      ordering: false,
      paging: false,
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-insentif-pembayaran-list',
        data: function (d) {
          d.tanggal_dari = $('#filter-tanggal-dari').val();
          d.tanggal_sampai = $('#filter-tanggal-sampai').val();
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
        { data: 'kode_lunas' },
        { data: 'kode_kwitansi' },
        { data: 'kode_voucher' },
        { data: 'kode_estimasi' },
        { data: 'kode_spk' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'nama_pelanggan' },
        { data: 'jasa', className: 'text-end' },
        { data: 'sparepart', className: 'text-end' },
        { data: 'jumlah', className: 'text-end' },
        { data: 'tgl_lunas', className: 'text-center' },
        { data: 'tgl_kwitansi', className: 'text-center' },
        { data: 'hari', className: 'text-center' }
      ]
    });
  }

  // export excel
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          tanggal_dari: $('#filter-tanggal-dari').val(),
          tanggal_sampai: $('#filter-tanggal-sampai').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-insentif-pembayaran/export?` + queryString;
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

  // print data
  const printBtn = document.querySelector('.btn-print');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          tanggal_dari: $('#filter-tanggal-dari').val(),
          tanggal_sampai: $('#filter-tanggal-sampai').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-insentif-pembayaran/print?` + queryString;
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

  // Phone Number mask
  const phoneMaskList = document.querySelectorAll('.phone-mask');
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

  // Tahun mask
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

  // Picker Date initialization
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickrDate.flatpickr({
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  // validating form
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
        tanggal_dari: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal Dari'
            }
          }
        },
        tanggal_sampai: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal Sampai'
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

function setSelectValue(selector, value, textIfMissing) {
  const $el = $(selector);
  if (!$el.length) return;

  const val = Array.isArray(value) ? value.map(v => String(v)) : (value ?? '');
  const isSelect2 = $el.hasClass('select2-hidden-accessible');

  const ensureOption = (v, t) => {
    if (!$el.find(`option[value="${v}"]`).length) {
      $el.append(new Option(t ?? v, v, false, false));
    }
  };

  if (Array.isArray(val)) {
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
    $el.trigger('change');
  }
}
