'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const table = document.querySelector('.datatables-laporan-unit-rawat-jalan');
  if (!table) return;

  const dt = new DataTable(table, {
    searching: false,
    ordering: false,
    paging: false,
    processing: true,
    serverSide: true,
    autoWidth: false, // tambah ini

    ajax: {
      url: baseUrl + 'laporan-unit-rawat-jalan-list',
      data: function (d) {
        d.tanggal = $('#filter-tanggal').val();
      },
      dataSrc: function (json) {
        json.data = Array.isArray(json.data) ? json.data : [];
        return json.data;
      }
    },
    columns: [
      { data: 'no', className: 'text-nowrap' },
      { data: 'kode_spk', className: 'text-nowrap' },
      { data: 'tgl_masuk', className: 'text-nowrap' },
      { data: 'tgl_rawat_jalan1', className: 'text-nowrap' },
      { data: 'tgl_rawat_jalan2', className: 'text-nowrap' },
      { data: 'no_polisi', className: 'text-nowrap' },
      { data: 'merek_tipe', className: 'text-nowrap' },
      { data: 'pemilik', className: 'text-nowrap' },
      { data: 'nama_pelanggan', className: 'text-nowrap' },
      { data: 'no_polis', className: 'text-nowrap' },
      { data: 'kode_claim', className: 'text-nowrap' }
    ],
    scrollX: true,
    displayLength: 25,
    language: {
      paginate: {
        next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
        previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
      }
    }
  });

  // ── Export Excel ──────────────────────────────────────────────
  document.querySelector('.btn-export-excel')?.addEventListener('click', function () {
    const params = new URLSearchParams({
      tanggal: $('#filter-tanggal').val()
    });
    window.open(`${baseUrl}laporan-unit-rawat-jalan/export?${params}`, '_blank');
  });

  // ── Print ─────────────────────────────────────────────────────
  document.querySelector('.btn-print')?.addEventListener('click', function () {
    const params = new URLSearchParams({
      tanggal: $('#filter-tanggal').val()
    });
    window.open(`${baseUrl}laporan-unit-rawat-jalan/print?${params}`, '_blank');
  });

  // ── Flatpickr ─────────────────────────────────────────────────
  document.querySelectorAll('.dt-date').forEach(el => {
    el._flatpickr || flatpickr(el, { dateFormat: 'd/m/Y', static: true });
  });
});
