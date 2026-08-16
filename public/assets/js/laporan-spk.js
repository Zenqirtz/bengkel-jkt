/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
  let validationStepper;

   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-spk'),
     statusObj = {
      '09': { class: 'text-bg-danger' },
      '10': { class: 'text-bg-danger' },
      '11': { class: 'text-bg-danger' },
    };

   // ajax setup
   $.ajaxSetup({
     headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
   });

   // datatable
   if (dt_basic_table) {
     isAdd = dt_basic_table.dataset.add;
     isEdit = dt_basic_table.dataset.edit;
     isDelete = dt_basic_table.dataset.delete;

     let tableTitle = document.createElement('h5');
     tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
     tableTitle.innerHTML = dt_basic_table.dataset.title;
     const dt_basic = new DataTable(dt_basic_table, {
       searching: false,
       ordering: true,
       processing: true,
       serverSide: true,
       ajax: {
         url: baseUrl + 'laporan-spklaporan-spk-list',
         data: function (d) {
            d.kode_spk = $('#filter-nomor-spk').val();
            d.no_polisi = $('#filter-no-polisi').val();
            d.tgl_masuk_awal = $('#filter-tgl-spk-awal').val();
            d.tgl_masuk_akhir = $('#filter-tgl-spk-akhir').val();
            d.nama_pelanggan = $('#filter-nama-pelanggan').val();
            d.nama_pemilik = $('#filter-nama-pemilik').val();
            d.no_polis = $('#filter-no-polis').val();
            d.kode_claim = $('#filter-no-klaim').val();
            d.status_spk = $('#filter-status-spk').val();
            d.status = $('#filter-status').val();
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
          { data: 'tgl_masuk' },
          { data: 'kode_spk' },
          { data: 'keterangan' },
          { data: 'no_polisi' },
          { data: 'nama_tipe' },
          { data: 'pemilik' },
          { data: 'nama_pelanggan' },
          { data: 'tgl_batal' },
          { data: 'tgl_turun_lapangan' },
          { data: 'tgl_finishing1' },
          { data: 'tgl_keluar' },
          { data: 'status_spk' },
          { data: 'no_polis' },
          { data: 'kode_claim' },
       ],
       columnDefs: [
         {
          targets: 2,
          render: function (data, type, full, meta) {
            const status = full['kode_status_spk'];
            const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';
            return (
              '<span class="badge rounded-pill ' +
              badgeClass +
              '" text-capitalized>' +
              data +
              '</span>'
            );
          }
         },
       ],
       scrollX: true,
       order: [[1, 'desc']],
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
         topEnd: {
           features: [
             {
               search: {
                 placeholder: 'Cari',
                 text: '_INPUT_'
               }
             }
           ]
         },
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
       displayLength: 50,
       language: {
         paginate: {
           next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
           previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
           first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
           last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
         }
       },
       initComplete: function () {
         $('.card-header').after('<hr class="my-0">');
         document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
           btn.classList.remove('btn-secondary');
         });
       }
     });

      // Handle Filter Search
      const formFilter = document.getElementById('formFilterSpk');
      if (formFilter) {
          formFilter.addEventListener('submit', function (e) {
              e.preventDefault();
              dt_basic.draw();
              const modalEl = document.getElementById('filterRoleModal');
              const modalInstance = bootstrap.Modal.getInstance(modalEl);
              if(modalInstance) {
                  modalInstance.hide();
              }
          });
      }

     // Filter form control to default size
     setTimeout(() => {
       const elementsToModify = [
         { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
         { selector: '.dt-length .form-select', classToAdd: 'ms-0' },
         { selector: '.dt-length', classToAdd: 'mb-md-5 mb-0' },
         {
           selector: '.dt-layout-end',
           classToRemove: 'justify-content-between',
           classToAdd: 'd-flex gap-md-4 justify-content-md-between justify-content-center gap-md-2 flex-wrap mt-0'
         },
         { selector: '.dt-layout-start', classToAdd: 'mt-md-0 mt-5' },
         {
           selector: '.dt-layout-start .dt-buttons',
           classToAdd: 'd-md-flex d-block gap-4 justify-content-center'
         },
         {
           selector: '.dt-layout-end .dt-buttons',
           classToAdd: 'd-md-flex d-block gap-4 mb-md-0 mb-5 justify-content-center'
         },
         { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
         { selector: '.dt-layout-full', classToRemove: 'col-md col-12' },
         { selector: '.dt-layout-full .table', classToAdd: 'table-responsive' }
       ];

       elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
         document.querySelectorAll(selector).forEach(element => {
           if (classToRemove) {
             classToRemove.split(' ').forEach(className => element.classList.remove(className));
           }
           if (classToAdd) {
             classToAdd.split(' ').forEach(className => element.classList.add(className));
           }
         });
       });
     }, 100);
   }

   const dt_spk_master_table = document.querySelector('.datatables-spk-master');
   if (dt_spk_master_table) {
     isAdd = dt_spk_master_table.dataset.add;
     const dt_spk_master = new DataTable(dt_spk_master_table, {
       searching: false,
       ordering: false,
       paging: false,
       processing: true,
       serverSide: true,
       scrollX: true,
       ajax: {
         url: baseUrl + 'laporan-spk-list',
         data: function (d) {
           d.tipe_laporan     = $('#filter-tipe-laporan').val();
           d.jenis_laporan    = $('#filter-jenis-laporan').val();
           d.tgl_awal         = $('#filter-tgl-awal').val();
           d.tgl_akhir        = $('#filter-tgl-akhir').val();
           d.bulan            = $('#filter-bulan').val();
           d.tahun            = $('#filter-tahun').val();
           d.tahun2           = $('#filter-tahun2').val();
           d.nama_customer    = $('#filter-nama-customer').val();
         },
         dataSrc: function (json) {
           if (typeof json.recordsTotal !== 'number') { json.recordsTotal = 0; }
           if (typeof json.recordsFiltered !== 'number') { json.recordsFiltered = 0; }
           json.data = Array.isArray(json.data) ? json.data : [];
           return json.data;
         }
       },
       columns: [
         { data: 'no' },
         { data: 'kode_spk' },
         { data: 'tgl_masuk' },
         { data: 'no_polisi' },
         { data: 'status' },
         { data: 'status_spk' },
         { data: 'merek_tipe' },
         { data: 'pemilik' },
         { data: 'telepon' },
         { data: 'jenis_perbaikan' },
         { data: 'nama_pelanggan' },
         { data: 'tgl_estimasi' },
         { data: 'kode_estimasi' },
         { data: 'nilai_estimasi' },
         { data: 'tgl_pengiriman' },
         { data: 'tgl_turun_lapangan' },
         { data: 'tgl_rencana_selesai' },
         { data: 'tgl_keluar' },
         { data: 'tanggal_or' },
         { data: 'kode_or' },
         { data: 'total_or' },
         { data: 'tgl_invoice' },
         { data: 'no_invoice' },
         { data: 'nilai_tawar' },
         { data: 'tgl_kwitansi' },
         { data: 'kode_kwitansi' },
         { data: 'nilai_kwitansi' },
         { data: 'nama_surveyor' },
         { data: 'nama_marketing' },
         { data: 'nama_perantara' },
       ]
     });
   }

    const dt_spk_tutup_table = document.querySelector('.datatables-spk-tutup');
    if (dt_spk_tutup_table) {
      isAdd = dt_spk_tutup_table.dataset.add;
      const dt_spk_tutup = new DataTable(dt_spk_tutup_table, {
        searching: false,
        ordering: false,
        paging: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'laporan-spk-list',
          data: function (d) {
            d.tipe_laporan     = $('#filter-tipe-laporan').val();
            d.jenis_laporan    = $('#filter-jenis-laporan').val();
            d.tgl_awal         = $('#filter-tgl-awal').val();
            d.tgl_akhir        = $('#filter-tgl-akhir').val();
            d.bulan            = $('#filter-bulan').val();
            d.tahun            = $('#filter-tahun').val();
            d.tahun2           = $('#filter-tahun2').val();
            d.nama_customer    = $('#filter-nama-customer').val();
          },
          dataSrc: function (json) {
            if (typeof json.recordsTotal !== 'number') { json.recordsTotal = 0; }
            if (typeof json.recordsFiltered !== 'number') { json.recordsFiltered = 0; }
            json.data = Array.isArray(json.data) ? json.data : [];
            return json.data;
          }
        },
        columns: [
          { data: 'no' },
          { data: 'tanggal_tutup' },
          { data: 'kode_tutup' },
          { data: 'kode_spk' },
          { data: 'pemilik' },
          { data: 'no_polisi' },
          { data: 'merek_tipe' },
        ]
      });
    }

    const dt_spk_batal_table = document.querySelector('.datatables-spk-batal');
    if (dt_spk_batal_table) {
      isAdd = dt_spk_batal_table.dataset.add;
      const dt_spk_batal = new DataTable(dt_spk_batal_table, {
        searching: false,
        ordering: false,
        paging: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'laporan-spk-list',
          data: function (d) {
            d.tipe_laporan     = $('#filter-tipe-laporan').val();
            d.jenis_laporan    = $('#filter-jenis-laporan').val();
            d.tgl_awal         = $('#filter-tgl-awal').val();
            d.tgl_akhir        = $('#filter-tgl-akhir').val();
            d.bulan            = $('#filter-bulan').val();
            d.tahun            = $('#filter-tahun').val();
            d.tahun2           = $('#filter-tahun2').val();
            d.nama_customer    = $('#filter-nama-customer').val();
          },
          dataSrc: function (json) {
            if (typeof json.recordsTotal !== 'number') { json.recordsTotal = 0; }
            if (typeof json.recordsFiltered !== 'number') { json.recordsFiltered = 0; }
            json.data = Array.isArray(json.data) ? json.data : [];
            return json.data;
          }
        },
        columns: [
          { data: 'no' },
          { data: 'tgl_batal' },
          { data: 'kode_spk' },
          { data: 'merek_tipe' },
          { data: 'no_polisi' },
          { data: 'nama_pelanggan' },
          { data: 'pemilik' },
          { data: 'batal_by' },
          { data: 'memo_batal' },
        ]
      });
    }

    const dt_spk_keluar_table = document.querySelector('.datatables-spk-keluar');
    if (dt_spk_keluar_table) {
      isAdd = dt_spk_keluar_table.dataset.add;
      const dt_spk_keluar = new DataTable(dt_spk_keluar_table, {
        searching: false,
        ordering: false,
        paging: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'laporan-spk-list',
          data: function (d) {
            d.tipe_laporan     = $('#filter-tipe-laporan').val();
            d.jenis_laporan    = $('#filter-jenis-laporan').val();
            d.tgl_awal         = $('#filter-tgl-awal').val();
            d.tgl_akhir        = $('#filter-tgl-akhir').val();
            d.bulan            = $('#filter-bulan').val();
            d.tahun            = $('#filter-tahun').val();
            d.tahun2           = $('#filter-tahun2').val();
            d.nama_customer    = $('#filter-nama-customer').val();
          },
          dataSrc: function (json) {
            if (typeof json.recordsTotal !== 'number') { json.recordsTotal = 0; }
            if (typeof json.recordsFiltered !== 'number') { json.recordsFiltered = 0; }
            json.data = Array.isArray(json.data) ? json.data : [];
            return json.data;
          }
        },
        columns: [
          { data: 'no' },
          { data: 'tgl_keluar' },
          { data: 'kode_keluar' },
          { data: 'kode_spk' },
          { data: 'no_polisi' },
          { data: 'merek_tipe' },
          { data: 'pemilik' },
          { data: 'tgl_tanda_terima' },
          // { data: 'nama_penerima' },
          // { data: 'nama_pengantar' },
        ]
      });
    }

   // export excel
   const exportBtn = document.querySelector('.btn-export-excel');
   if (exportBtn) {
      exportBtn.addEventListener('click', function () {
        if(isAdd) {
          let params = {
            tipe_laporan:  $('#filter-tipe-laporan').val(),
            jenis_laporan: $('#filter-jenis-laporan').val(),
            tgl_awal:      $('#filter-tgl-awal').val(),
            tgl_akhir:     $('#filter-tgl-akhir').val(),
            bulan:         $('#filter-bulan').val(),
            tahun:         $('#filter-tahun').val(),
            tahun2:        $('#filter-tahun2').val(),
            nama_customer: $('#filter-nama-customer').val(),
          };
          let queryString = $.param(params);
          window.location.href = `${baseUrl}laporan-spk/export?` + queryString;
        } else {
          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
          offcanvasInstance && offcanvasInstance.hide();
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Anda tidak memiliki izin untuk akses tambah data',
            customClass: { confirmButton: 'btn btn-success' }
          });
        }
     });
   }

   // print data
   const printBtn = document.querySelector('.btn-print');
   if (printBtn) {
    printBtn.addEventListener('click', function () {
        if(isAdd) {
          let params = {
            tipe_laporan:  $('#filter-tipe-laporan').val(),
            jenis_laporan: $('#filter-jenis-laporan').val(),
            tgl_awal:      $('#filter-tgl-awal').val(),
            tgl_akhir:     $('#filter-tgl-akhir').val(),
            bulan:         $('#filter-bulan').val(),
            tahun:         $('#filter-tahun').val(),
            tahun2:        $('#filter-tahun2').val(),
            nama_customer: $('#filter-nama-customer').val(),
          };
          let queryString = $.param(params);
          const printUrl = `${baseUrl}laporan-spk/print?` + queryString;
          window.open(printUrl, '_blank');
        } else {
          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
          offcanvasInstance && offcanvasInstance.hide();
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Anda tidak memiliki izin untuk akses tambah data',
            customClass: { confirmButton: 'btn btn-success' }
          });
        }
     });
   }

   // Phone Number mask initialization
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

   // *** DIHAPUS: blok Select2 AJAX untuk filter-nama-customer ***
   // Dropdown customer sekarang menggunakan opsi statis dari blade (loop $customerList)
   // dan di-handle oleh inisialisasi select2 di atas secara otomatis.

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
      if (val === '' || val === null) { $el.val(null); }
      else { ensureOption(String(val), textIfMissing); $el.val(String(val)); }
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
