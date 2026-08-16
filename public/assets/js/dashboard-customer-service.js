/**
 * Page Data management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  // Color Variables
  // const purpleColor = '#8c57ff',
  //   yellowColor = '#ffe800',
  //   cyanColor = '#28dac6',
  //   orangeColor = '#FF8132',
  //   orangeLightColor = '#ffcf5c',
  //   oceanBlueColor = '#299AFF',
  //   greyColor = '#4F5D70',
  //   greyLightColor = '#EDF1F4',
  //   blueColor = '#2B9AFF',
  //   blueLightColor = '#84D0FF';

  // overriding color variables for chartjs
  let cardColor, headingColor, labelColor, borderColor, legendColor, info, danger, primary;

  if (isDarkStyle) {
    cardColor = window.Helpers.getCssVar('paper-bg', true);
    headingColor = window.Helpers.getCssVar('heading-color', true);
    labelColor = window.Helpers.getCssVar('secondary-color', true);
    legendColor = window.Helpers.getCssVar('body-color', true);
    borderColor = window.Helpers.getCssVar('border-color', true);
    primary = window.Helpers.getCssVar('primary', true);
    info = window.Helpers.getCssVar('info', true);
    danger = window.Helpers.getCssVar('danger', true);
  } else {
    cardColor = window.Helpers.getCssVar('paper-bg', true);
    headingColor = window.Helpers.getCssVar('heading-color', true);
    labelColor = window.Helpers.getCssVar('secondary-color', true);
    legendColor = window.Helpers.getCssVar('body-color', true);
    borderColor = window.Helpers.getCssVar('border-color', true);
    primary = window.Helpers.getCssVar('primary', true);
    info = window.Helpers.getCssVar('info', true);
    danger = window.Helpers.getCssVar('danger', true);
  }

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  const statusObj = {
    '09': { class: 'text-bg-danger' },
    10: { class: 'text-bg-danger' },
    11: { class: 'text-bg-danger' }
  };

  const dt_basic_table = document.querySelector('.datatables-spk');
  if (dt_basic_table) {
    const dt_basic = new DataTable(dt_basic_table, {
      searching: true, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'home-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.tipe = 'spk';
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
        { data: 'id' },
        { data: 'tgl_masuk' },
        { data: 'kode_spk' },
        { data: 'keterangan' },
        { data: 'no_polisi' },
        { data: 'nama_tipe' },
        { data: 'pemilik' },
        { data: 'nama_pelanggan' },
        { data: 'status_spk' },
        { data: 'no_polis' },
        { data: 'kode_claim' }
      ],
      columnDefs: [
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 0,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        {
          targets: 3,
          // responsivePriority: 6,
          render: function (data, type, full, meta) {
            const status = full['kode_status_spk'];
            const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';

            return '<span class="badge rounded-pill ' + badgeClass + '" text-capitalized>' + data + '</span>';
          }
        }
      ],
      // scrollY: '300px',
      scrollX: true,
      order: [[2, 'desc']],
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

  const dt_detail1_table = document.querySelector('.datatables-detail1');
  if (dt_detail1_table) {
    const dt_detail1 = new DataTable(dt_detail1_table, {
      searching: true, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'home-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.tipe = 'detail1';
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
        { data: 'id' },
        { data: 'kode_spk' },
        { data: 'tgl_masuk' },
        { data: 'kode_estimasi' },
        { data: 'tgl_estimasi' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'pemilik' }
      ],
      columnDefs: [
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 0,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        }
      ],
      // scrollY: '300px',
      scrollX: true,
      order: [[2, 'desc']],
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

  const dt_detail2_table = document.querySelector('.datatables-detail2');
  if (dt_detail2_table) {
    const dt_detail2 = new DataTable(dt_detail2_table, {
      searching: true, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'home-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.tipe = 'detail2';
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
        { data: 'id' },
        { data: 'kode_spk' },
        { data: 'tgl_masuk' },
        { data: 'no_polisi' },
        { data: 'tipe_kendaraan' },
        { data: 'pemilik' },
        { data: 'tgl_turun_lapangan' },
        { data: 'tgl_rencana_selesai' },
        { data: 'sisa_waktu' }
      ],
      columnDefs: [
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 0,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        }
      ],
      // scrollY: '300px',
      scrollX: true,
      order: [[2, 'desc']],
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

  const dt_detail3_table = document.querySelector('.datatables-detail3');
  if (dt_detail3_table) {
    const dt_detail3 = new DataTable(dt_detail3_table, {
      searching: true, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'home-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.tipe = 'detail3';
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
        { data: 'id' },
        { data: 'kode_spk' },
        { data: 'tgl_masuk' },
        { data: 'no_polisi' },
        { data: 'tipe_kendaraan' },
        { data: 'pemilik' }
      ],
      columnDefs: [
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 0,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        }
      ],
      // scrollY: '300px',
      scrollX: true,
      order: [[2, 'desc']],
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

  const dt_detail4_table = document.querySelector('.datatables-detail4');
  if (dt_detail4_table) {
    const dt_detail4 = new DataTable(dt_detail4_table, {
      searching: true, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'home-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.tipe = 'detail4';
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
        { data: 'id' },
        { data: 'kode_estimasi' },
        { data: 'tanggal' },
        { data: 'kode_spk' },
        { data: 'tgl_masuk' },
        { data: 'no_polisi' },
        { data: 'tipe_kendaraan' },
        { data: 'pemilik' },
        { data: 'nama_pelanggan' }
      ],
      columnDefs: [
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 0,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        }
      ],
      // scrollY: '300px',
      scrollX: true,
      order: [[2, 'desc']],
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

  document.addEventListener('click', function (e) {
    // Cari elemen terdekat dari titik klik yang memiliki class .view-detail
    // Ini mencegah error jika user tidak sengaja mengklik icon <i> di dalam button
    const btnViewDetail = e.target.closest('.view-detail');

    if (btnViewDetail) {
      // Ambil nilai data-tipe
      let tipe = btnViewDetail.dataset.tipe;

      const printUrl = `${baseUrl}home-list/` + tipe;
      window.open(printUrl, '_blank');
    }
  });

  const spkChart = document.getElementById('spkChart');
  if (spkChart) {
    const spkChartVar = new Chart(spkChart, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [
          {
            label: 'SPK Masuk',
            data: dataSpkMasuk,
            backgroundColor: '#ffcf5c',
            borderColor: 'transparent',
            maxBarThickness: 15,
            borderRadius: {
              topRight: 15,
              topLeft: 15
            }
          },
          {
            label: 'SPK Keluar',
            data: dataSpkKeluar,
            backgroundColor: '#FF0000',
            borderColor: 'transparent',
            maxBarThickness: 15,
            borderRadius: {
              topRight: 15,
              topLeft: 15
            }
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 500
        },
        plugins: {
          tooltip: {
            rtl: isRtl,
            backgroundColor: cardColor,
            titleColor: headingColor,
            bodyColor: legendColor,
            borderWidth: 1,
            borderColor: borderColor
          },
          legend: {
            display: true
          }
        },
        scales: {
          x: {
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              color: labelColor,
              font: {
                size: '13px'
              }
            }
          },
          y: {
            min: 0,
            max: maxScaleY,
            grid: {
              color: borderColor,
              drawBorder: false,
              borderColor: borderColor
            },
            ticks: {
              stepSize: 100,
              color: labelColor,
              font: {
                size: '13px'
              }
            }
          }
        }
      }
    });
  }
});

// ALERT BANNER
// function scrollToPending() {
//   const target = document.getElementById('pendingPekerjaan');
//   if (target) {
//     target.scrollIntoView({ behavior: 'smooth', block: 'center' });
//     // Highlight effect
//     target.querySelector('.card').style.boxShadow = '0 0 0 3px #dc354580';
//     setTimeout(() => {
//       target.querySelector('.card').style.boxShadow = '';
//     }, 2000);
//   }
// }
window.scrollToPending = function() {
  const target = document.getElementById('pendingPekerjaan');
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    target.querySelector('.card').style.boxShadow = '0 0 0 3px #dc354580';
    setTimeout(() => {
      target.querySelector('.card').style.boxShadow = '';
    }, 2000);
  }
}
