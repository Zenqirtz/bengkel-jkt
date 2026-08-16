/**
 * Page Data management
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

   const dt_kend_masuk_table = document.querySelector('.datatables-kendaraan-masuk');
   if (dt_kend_masuk_table) {
     isAdd = dt_kend_masuk_table.dataset.add;
     const dt_kend_masuk = new DataTable(dt_kend_masuk_table, {
       searching: false,  // Opsi ini akan menghilangkan input cari
       ordering: false,    // Opsi ini akan menghilangkan ordering
       paging: false,    // Opsi ini akan menghilangkan paging
       scrollX: true,
     });
   }

    const dt_belum_turun_lapangan_table = document.querySelector('.datatables-belum-turun-lapangan');
    if (dt_belum_turun_lapangan_table) {
      isAdd = dt_belum_turun_lapangan_table.dataset.add;
      const dt_belum_turun_lapangan = new DataTable(dt_belum_turun_lapangan_table, {
        searching: false,  // Opsi ini akan menghilangkan input cari
        ordering: false,    // Opsi ini akan menghilangkan ordering
        paging: false,    // Opsi ini akan menghilangkan paging
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'laporan-kendaraan-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.tipe_laporan     = $('#filter-tipe-laporan').val();
            d.jenis_laporan    = $('#filter-jenis-laporan').val();
            d.tgl_awal         = $('#filter-tgl-awal').val();
            d.tgl_akhir        = $('#filter-tgl-akhir').val();
            d.bulan            = $('#filter-bulan').val();
            d.tahun            = $('#filter-tahun').val();
            d.tahun2           = $('#filter-tahun2').val();
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
          // columns according to JSON
          { data: 'no' },
          { data: 'tgl_masuk' },
          { data: 'kode_spk' },
          { data: 'no_polisi' },
          { data: 'merek_tipe' },
          { data: 'nama_pelanggan' },
          { data: 'pemilik' },
        ]
      });
    }

    const dt_turun_lapangan_table = document.querySelector('.datatables-turun-lapangan');
    if (dt_turun_lapangan_table) {
      isAdd = dt_turun_lapangan_table.dataset.add;
      const dt_turun_lapangan = new DataTable(dt_turun_lapangan_table, {
        searching: false,  // Opsi ini akan menghilangkan input cari
        ordering: false,    // Opsi ini akan menghilangkan ordering
        paging: false,    // Opsi ini akan menghilangkan paging
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'laporan-kendaraan-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.tipe_laporan     = $('#filter-tipe-laporan').val();
            d.jenis_laporan    = $('#filter-jenis-laporan').val();
            d.tgl_awal         = $('#filter-tgl-awal').val();
            d.tgl_akhir        = $('#filter-tgl-akhir').val();
            d.bulan            = $('#filter-bulan').val();
            d.tahun            = $('#filter-tahun').val();
            d.tahun2           = $('#filter-tahun2').val();
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
          // columns according to JSON
          { data: 'no' },
          { data: 'tgl_turun_lapangan' },
          { data: 'kode_turun_lapangan' },
          { data: 'kode_spk' },
          { data: 'no_polisi' },
          { data: 'merek_tipe' },
          { data: 'nama_pelanggan' },
          { data: 'pemilik' },
          { data: 'tgl_rencana_selesai' },
          { data: 'status' },
        ]
      });
    }

   // export excel
   const exportBtn = document.querySelector('.btn-export-excel');
   if (exportBtn) {
      exportBtn.addEventListener('click', function () {
        if(isAdd) {
          // Ambil data dari form filter
          // Kita ambil value manual karena form berada di dalam modal
          let params = {
            tipe_laporan: $('#filter-tipe-laporan').val(),
            jenis_laporan: $('#filter-jenis-laporan').val(),
            tgl_awal: $('#filter-tgl-awal').val(),
            tgl_akhir: $('#filter-tgl-akhir').val(),
            bulan: $('#filter-bulan').val(),
            tahun: $('#filter-tahun').val(),
            tahun2: $('#filter-tahun2').val()
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);

          // Redirect window untuk download file
          // Pastikan route URL sesuai dengan konfigurasi route Anda
          window.location.href = `${baseUrl}laporan-kendaraan/export?` + queryString;
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
        if(isAdd) {
          // Ambil data dari form filter
          // Kita ambil value manual karena form berada di dalam modal
          let params = {
            tipe_laporan: $('#filter-tipe-laporan').val(),
            jenis_laporan: $('#filter-jenis-laporan').val(),
            tgl_awal: $('#filter-tgl-awal').val(),
            tgl_akhir: $('#filter-tgl-akhir').val(),
            bulan: $('#filter-bulan').val(),
            tahun: $('#filter-tahun').val(),
            tahun2: $('#filter-tahun2').val()
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);

          // Redirect window untuk download file
          // Pastikan route URL sesuai dengan konfigurasi route Anda
          // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
          const printUrl = `${baseUrl}laporan-kendaraan/print?` + queryString;
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
         allowClear: true,         // agar placeholder tampil saat kosong
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
      const jnsLapPeriode = $('#jns-lap-periode');
      const jnsLapBulan = $('#jns-lap-bulan');
      const jnsLapTahun = $('#jns-lap-tahun');

      jnsLapPeriode.hide();
      jnsLapBulan.hide();
      jnsLapTahun.hide();

      if(selectJenisLaporan.val() == "periode") {
        jnsLapPeriode.show();
      } else if(selectJenisLaporan.val() == "bulan") {
        jnsLapBulan.show();
      } else if(selectJenisLaporan.val() == "tahun") {
        jnsLapTahun.show();
      }

      selectJenisLaporan.on('change', function () {
        const jnsLap = $(this).val();

        jnsLapPeriode.hide();
        jnsLapBulan.hide();
        jnsLapTahun.hide();

        if(jnsLap == "periode") {
          jnsLapPeriode.show();
        } else if(jnsLap == "bulan") {
          jnsLapBulan.show();
        } else if(jnsLap == "tahun") {
          jnsLapTahun.show();
        }
      });
    }


    // validating form and updating user's data
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
      const fv = FormValidation.formValidation(filterForm, {
        fields: {
          tipe_laporan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Laporan'
              }
            }
          },
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
      if (val === '' || val === null) { $el.val(null); }
      else { ensureOption(String(val), textIfMissing); $el.val(String(val)); }
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

