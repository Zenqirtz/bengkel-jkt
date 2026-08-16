'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd;
  let dt_aging_penawaran;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const dt_rekap_table = document.querySelector('.datatables-aging-penawaran-rekap');
  if (dt_rekap_table) {
    isAdd = dt_rekap_table.dataset.add;
    const dt_rekap = new DataTable(dt_rekap_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: false,    // Opsi ini akan menghilangkan ordering
      paging: false,    // Opsi ini akan menghilangkan paging
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'laporan-aging-penawaran-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.jenis_laporan    = $('input[name="jenis_laporan"]:checked').val();
          d.tgl_awal         = $('#filter-tgl-awal').val();
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

          // Update footer totals
          $('#footer-unit_1_2').text(json.grand_unit_1_2 || '0');
          $('#footer-unit_1_2_persen').text(json.grand_unit_1_2_persen || '0');
          $('#footer2-unit_1_2_persen').text(json.grand2_unit_1_2_persen || '0');
          $('#footer-nilai_1_2').text(json.grand_nilai_1_2 || '0');
          $('#footer-nilai_1_2_persen').text(json.grand_nilai_1_2_persen || '0');
          $('#footer2-nilai_1_2_persen').text(json.grand2_nilai_1_2_persen || '0');
          $('#footer-unit_3_5').text(json.grand_unit_3_5 || '0');
          $('#footer-unit_3_5_persen').text(json.grand_unit_3_5_persen || '0');
          $('#footer2-unit_3_5_persen').text(json.grand2_unit_3_5_persen || '0');
          $('#footer-nilai_3_5').text(json.grand_nilai_3_5 || '0');
          $('#footer-nilai_3_5_persen').text(json.grand_nilai_3_5_persen || '0');
          $('#footer2-nilai_3_5_persen').text(json.grand2_nilai_3_5_persen || '0');
          $('#footer-unit_5').text(json.grand_unit_5 || '0');
          $('#footer-unit_5_persen').text(json.grand_unit_5_persen || '0');
          $('#footer2-unit_5_persen').text(json.grand2_unit_5_persen || '0');
          $('#footer-nilai_5').text(json.grand_nilai_5 || '0');
          $('#footer-nilai_5_persen').text(json.grand_nilai_5_persen || '0');
          $('#footer2-nilai_5_persen').text(json.grand2_nilai_5_persen || '0');
          $('#footer-unit_blm_dikirim').text(json.grand_unit_blm_dikirim || '0');
          $('#footer-unit_blm_dikirim_persen').text(json.grand_unit_blm_dikirim_persen || '0');
          $('#footer2-unit_blm_dikirim_persen').text(json.grand2_unit_blm_dikirim_persen || '0');
          $('#footer-nilai_blm_dikirim').text(json.grand_nilai_blm_dikirim || '0');
          $('#footer-nilai_blm_dikirim_persen').text(json.grand_nilai_blm_dikirim_persen || '0');
          $('#footer2-nilai_blm_dikirim_persen').text(json.grand2_nilai_blm_dikirim_persen || '0');
          $('#footer-unit_total').text(json.grand_unit_total || '0');
          $('#footer-unit_total_persen').text(json.grand_unit_total_persen || '0');
          $('#footer2-unit_total_persen').text(json.grand2_unit_total_persen || '0');
          $('#footer-nilai_total').text(json.grand_nilai_total || '0');
          $('#footer-nilai_total_persen').text(json.grand_nilai_total_persen || '0');
          $('#footer2-nilai_total_persen').text(json.grand2_nilai_total_persen || '0');

          return json.data;
        }
      },
      columns: [
        { data: 'no' },
        { data: 'nama_pelanggan' },
        { data: 'unit_1_2' },
        { data: 'unit_1_2_persen' },
        { data: 'nilai_1_2', className: 'text-end' },
        { data: 'nilai_1_2_persen' },
        { data: 'unit_3_5' },
        { data: 'unit_3_5_persen' },
        { data: 'nilai_3_5', className: 'text-end' },
        { data: 'nilai_3_5_persen' },
        { data: 'unit_5' },
        { data: 'unit_5_persen' },
        { data: 'nilai_5', className: 'text-end' },
        { data: 'nilai_5_persen' },
        { data: 'unit_blm_dikirim' },
        { data: 'unit_blm_dikirim_persen' },
        { data: 'nilai_blm_dikirim', className: 'text-end' },
        { data: 'nilai_blm_dikirim_persen' },
        { data: 'unit_total' },
        { data: 'unit_total_persen' },
        { data: 'nilai_total', className: 'text-end' },
        { data: 'nilai_total_persen' },
      ],
      // footerCallback: function (row, data, start, end, display) {
      //   let api = this.api();

      //   // Helper function untuk mengubah string menjadi angka murni (float)
      //   // Jika format dari server menggunakan koma sebagai ribuan (misal: 1,000.50)
      //   let intVal = function (i) {
      //     if (typeof i === 'string') {
      //       // Hapus koma pemisah ribuan
      //       return i.replace(/[,]/g, '') * 1; 
      //     }
      //     return typeof i === 'number' ? i : 0;
      //   };

      //   // Loop untuk menghitung kolom index ke-2 sampai index ke-21
      //   for (let i = 2; i <= 21; i++) {
      //     // Jumlahkan semua data pada kolom ke-i
      //     let total = api
      //       .column(i)
      //       .data()
      //       .reduce(function (a, b) {
      //         return intVal(a) + intVal(b);
      //       }, 0);

      //     let formattedTotal = total;

      //     // Format angka berdasarkan jenis kolom (Rupiah vs Persentase)
      //     // Kolom Rupiah ada di index: 4, 8, 12, 16, 20
      //     if ([4, 8, 12, 16, 20].includes(i)) {
      //       formattedTotal = new Intl.NumberFormat('en-US', { 
      //         minimumFractionDigits: 0 
      //       }).format(total);
      //     } 
      //     // Kolom Persentase ada di index: 3, 5, 7, 9, 11, 13, 15, 17, 19, 21
      //     else if ([3, 5, 7, 9, 11, 13, 15, 17, 19, 21].includes(i)) {
      //       formattedTotal = total.toFixed(2);
      //     }

      //     // Tampilkan hasil penjumlahan ke dalam elemen <th> di tfoot
      //     $(api.column(i).footer()).html(formattedTotal);
      //   }
      // }
    });
  }

  const dt_rinci_table = document.querySelector('.datatables-aging-penawaran-rinci');
  if (dt_rinci_table) {
    isAdd = dt_rinci_table.dataset.add;
    const dt_rinci = new DataTable(dt_rinci_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: false,    // Opsi ini akan menghilangkan ordering
      paging: false,    // Opsi ini akan menghilangkan paging
      processing: true,
      serverSide: true,
      scrollX: true,
      scrollY: '400px',
      scrollCollapse: true,
      ajax: {
      url: baseUrl + 'laporan-aging-penawaran-list',
      data: function (d) {
        // Ambil data dari input form modal dan masukkan ke parameter request
        d.jenis_laporan    = $('input[name="jenis_laporan"]:checked').val();
        d.tgl_awal         = $('#filter-tgl-awal').val();
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
    // --- TAMBAHAN UNTUK ROW GROUPING ---
    drawCallback: function (settings) {
      var api = this.api();
      var rows = api.rows({ page: 'current' }).nodes();
      var lastAsuransi = null;
      var lastKeterangan = null;

      // Iterasi berdasarkan Index ke-14 (nama_pelanggan / Nama Asuransi)
      api.column(13, { page: 'current' }).data().each(function (asuransi, i) {
        // Ambil nilai Index ke-15 (minggu / Keterangan)
        var keterangan = api.column(14, { page: 'current' }).data()[i];

        // Grouping Level 1: Nama Asuransi
        if (lastAsuransi !== asuransi) {
          $(rows).eq(i).before(
            '<tr class="group"><td colspan="13" style="background-color: #d7e4ff; font-weight: bold; color: #000;">' + asuransi + '</td></tr>'
          );
          lastAsuransi = asuransi;
          lastKeterangan = null; // Reset sub-grup jika asuransi berubah
        }

        // Grouping Level 2: Keterangan
        if (lastKeterangan !== keterangan) {
          $(rows).eq(i).before(
            '<tr class="group"><td colspan="13" style="background-color: #f0f5ff; padding-left: 30px; font-weight: 600; color: #333;">' + keterangan + '</td></tr>'
          );
          lastKeterangan = keterangan;
        }
      });
    },
    // -----------------------------------
    columns: [
        { data: 'no' },
        { data: 'tanggal' },
        { data: 'kode_estimasi' },
        { data: 'kode_spk' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'kode_claim' },
        { data: 'no_polis' },
        { data: 'tertanggung' },
        { data: 'total' },
        { data: 'tgl_pengiriman' },
        { data: 'hari' },
        { data: 'kode_keluar' },
        { data: 'nama_pelanggan', visible: false },
        { data: 'minggu', visible: false },
      ]
    });
  }

  // Export Excel button
  const exportBtn = document.querySelector('.btn-export-excel');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          jenis_laporan: $('input[name="jenis_laporan"]:checked').val(),
          tgl_awal: $('#filter-tgl-awal').val()
        };

        let queryString = $.param(params);
        window.location.href = `${baseUrl}laporan-aging-penawaran/export?${queryString}`;
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

  // Print button
  const printBtn = document.querySelector('.btn-print');
  if (printBtn) {
    printBtn.addEventListener('click', function () {
      if (isAdd) {
        let params = {
          jenis_laporan: $('input[name="jenis_laporan"]:checked').val(),
          tgl_awal: $('#filter-tgl-awal').val()
        };

        let queryString = $.param(params);
        const printUrl = `${baseUrl}laporan-aging-penawaran/print?${queryString}`;
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

  // Flatpickr date picker
  const flatpickrDate = document.querySelectorAll('.dt-date');
  if (flatpickrDate) {
    flatpickr(flatpickrDate, {
      monthSelectorType: 'static',
      static: true,
      dateFormat: 'd/m/Y'
    });
  }

  // Form validation
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
        jenis_laporan: {
          validators: {
            notEmpty: {
              message: 'Silahkan Pilih Jenis Report'
            }
          }
        },
        tgl_awal: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal'
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
      // Form is valid, submit it
      filterForm.submit();
    });
  }
});
