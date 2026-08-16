/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isList, isAdd, isEdit, isDelete, fvPerbaikan, fvSparepart, fvLain;
  let selected_kode_permintaan = '';
  let selected_kode_cabang = '';
  let selected_kode_spk = '';
  let selected_tipe = 'detail';
  let rowToEdit = null;
  let dt_detail, dt_spk;

   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-permintaan'),
    addRoleModal = document.getElementById('addRoleModal'),
    viewSpkModal = document.getElementById('viewSpkModal'),
    offCanvasFormDet1 = document.getElementById('offcanvasAddDet1'),
    statusObj = {
    '09': { class: 'text-bg-danger' },
    '10': { class: 'text-bg-danger' },
    '11': { class: 'text-bg-danger' },
  };

  $("#addDetBarang").hide();
  fetchDashboardPermintaan();

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

   // COA datatable
   if (dt_basic_table) {
    isList = dt_basic_table.dataset.view;
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    tableTitle.innerHTML = dt_basic_table.dataset.title;
    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: true,    // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'permintaan-barang-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.kode_permintaan = $('#filter-nomor-permintaan').val();
          d.kode_spk = $('#filter-nomor-spk').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
          d.kode_bagian = $('#filter-kode-bagian').val();
          d.tipe_barang = $('#filter-tipe-barang').val();
          d.nama_pemilik = $('#filter-nama-pemilik').val();
          d.nama_pelanggan = $('#filter-nama-pelanggan').val();
          d.no_polisi = $('#filter-nomor-polisi').val();
          d.tipe_kendaraan = $('#filter-tipe-kendaraan').val();
          d.tipe = 'permintaan';
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
        { data: 'id' },
        { data: 'tanggal_permintaan' },
        { data: 'kode_permintaan' },
        { data: 'tipe_barang' },
        { data: 'posisi_pekerjaan' },
        { data: 'kode_spk' },
        { data: 'no_polisi' },
        { data: 'merek_tipe' },
        { data: 'pemilik' },
        { data: 'nama_pelanggan' }
      ],
      columnDefs: [
      //  {
      //    // For Responsive
      //    className: 'control',
      //    searchable: false,
      //    orderable: false,
      //    responsivePriority: 2,
      //    targets: 0,
      //    render: function (data, type, full, meta) {
      //      return '';
      //    }
      //  },
        {
          // For Checkboxes
          targets: 0,
          orderable: false,
          searchable: false,
          // responsivePriority: 2,
          checkboxes: true,
          render: function (data, type, full, meta) {
            return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          searchable: false,
          orderable: false,
          visible: false,
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },
      //  {
      //    // Actions
      //    //  title: 'Actions',
      //    targets: -1,
      //    searchable: false,
      //    orderable: false,
      //    visible: false,
      //    render: function (data, type, full, meta) {
      //      return (
      //        '<div class="d-flex align-items-center gap-4">' +
      //        `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="modal" data-bs-target="#addRoleModal" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
      //        `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
      //        '</div>'
      //      );
      //    }
      //  }
      ],
      // scrollY: '300px',
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
      displayLength: 10,
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
        // Remove btn-secondary from export buttons
        document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
          btn.classList.remove('btn-secondary');
        });
      }
    });

    const dt_detail_table = document.querySelector('.datatables-detail');
    if (dt_detail_table) {
      dt_detail = new DataTable(dt_detail_table, {
        searching: false,  // Opsi ini akan menghilangkan input cari
        ordering: false,    // Opsi lain tetap bisa jalan
        paging: false,
        info: false,
        processing: true,
        serverSide: false,
        destroy: true, // Fix Error reinitialise
        ajax: {
          url: baseUrl + 'permintaan-barang-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.kode_permintaan = selected_kode_permintaan;
            d.kode_cabang = selected_kode_cabang;
            d.kode_spk = selected_kode_spk;
            d.tipe = selected_tipe;
          }
        },
        columns: [
          // columns according to JSON
          { data: 'id', width: '20px' },
          { data: 'nama_bahan' },
          { data: 'no_sparepart' },
          { data: 'nama_satuan' },
          { data: 'qty' },
          { data: 'tipe' },
          { data: 'action', width: '20px' }
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
            targets: 1,
            render: function (data, type, full, meta) {
              return `
                ${data}
                <input type="hidden" name="detail[${full.id}][bahan]" value="${full.kode_bahan}" />
              `;
            }
          },
          {
            targets: 2,
            visible: false,
            render: function (data, type, full, meta) {
              return `
                ${data}
                <input type="hidden" name="detail[${full.id}][no_sparepart]" value="${full.no_sparepart}" />
              `;
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              return `
                ${data}
                <input type="hidden" name="detail[${full.id}][satuan]" value="${full.kode_satuan}" />
              `;
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              let val = data || 0;
              return `
                ${data}
                <input type="hidden" name="detail[${full.id}][qty]" class="add-qty" value="${data}" />
                <input type="hidden" name="detail[${full.id}][harga]" class="add-harga" value="${full.harga}" />
                <input type="hidden" name="detail[${full.id}][tipe]" value="${full.tipe}" />
              `;
            }
          },
          {
            // Actions
            //  title: 'Actions',
            targets: -1,
            searchable: false,
            orderable: false,
            visible: true,
            render: function (data, type, full, meta) {
              if(full.tipe_barang == 'S')  {
                if(full.cek != '1') {
                  let chked = 'checked'; //(data == '1') ? 'checked' : '';
                  return `<input type="checkbox" name="detail[${full.id}][cek]" class="dt-checkboxes-det form-check-input" value="1" ${chked}>`;
                } else {
                  return ``;
                }
              } else {
                return (
                  '<div class="d-flex align-items-center gap-4">' +
                  `<button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-detail" data-tipe="detail" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
                  `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-detail" data-tipe="detail" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
                  '</div>'
                );
              }
            }
          }
        ],
        // drawCallback: function() {
        //   hitungTotalPerbaikan();
        // },
      });
    }

    const dt_spk_table = document.querySelector('.datatables-spk');
    if (dt_spk_table) {
      dt_spk = new DataTable(dt_spk_table, {
        searching: true,  // Opsi ini akan menghilangkan input cari
        ordering: false,    // Opsi lain tetap bisa jalan
        processing: true,
        serverSide: true,
        // scrollY: '300px',
        scrollX: true,
        ajax: {
          url: baseUrl + 'permintaan-barang-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.tipe = selected_tipe; //'spk-baru';
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
          { data: 'id' },
          { data: 'tgl_masuk' },
          { data: 'kode_spk' },
          { data: 'no_polisi' },
          { data: 'nama_tipe' },
          { data: 'pemilik' },
          { data: 'nama_pelanggan' },
          { data: 'no_polis' },
          { data: 'kode_claim' },
          { data: 'keterangan' },
        ],
        columnDefs: [
          {
            // For Checkboxes
            targets: 0,
            orderable: false,
            searchable: false,
            // responsivePriority: 2,
            checkboxes: true,
            render: function (data, type, full, meta) {
              return `<input type="checkbox" class="dt-checkboxes-spk form-check-input" value="${data}">`;
            },
            checkboxes: {
              selectAllRender: '<input type="checkbox" class="form-check-input">'
            }
          },
          {
            searchable: false,
            orderable: false,
            visible: false,
            targets: 1,
            render: function (data, type, full, meta) {
              return `<span>${full.fake_id}</span>`;
            }
          },
          {
            targets: -1,
            render: function (data, type, full, meta) {
              const status = full['kode_status_spk'];
              const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';

              return '<span class="badge rounded-pill ' + badgeClass + '" text-capitalized>' + data + '</span>';
            }
          },
        ],
        // order: [[1, 'desc']],
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
        },
        // initComplete: function () {
        //   $('.card-header').after('<hr class="my-0">');
        //   // Remove btn-secondary from export buttons
        //   document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
        //     btn.classList.remove('btn-secondary');
        //   });
        // }
      });
    }


     // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.dt-checkboxes');
      if (chk) {
        if (chk.checked) {
            $('.dt-checkboxes').not(chk).prop('checked', false);
        }
      }

      const chk_spk = e.target.closest('.dt-checkboxes-spk');
      if (chk_spk) {
        if (chk_spk.checked) {
            $('.dt-checkboxes-spk').not(chk_spk).prop('checked', false);
        }
      }
    });

    const formFilter = document.getElementById('formCariData');
    if (formFilter) {
      formFilter.addEventListener('submit', function (e) {
        e.preventDefault(); // Mencegah reload halaman

        // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
        dt_basic.draw();

        // (Opsional) Tutup modal setelah klik cari
        const modalEl = document.getElementById('filterRoleModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if(modalInstance) {
            modalInstance.hide();
        }
      });
    }

    // changing the title
    const addNewBtn = document.querySelector('.add-record');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isAdd) {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk tambah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // reset seluruh form (termasuk select2)
        clearFormData();

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-permintaan .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          $(".btn-cek-spk").show();
          // $('#add-nomor-spk').prop('disabled', false);
          // $('#add-tipe-barang').prop('disabled', false).trigger('change');


          selected_kode_permintaan = $('#add-nomor-permintaan').val();
          selected_kode_cabang = $('#kode_cabang').val();
          selected_tipe = 'detail-sparepart';

          dt_detail.ajax.reload();
        } else {
          // Jika ada yang dipilih
          const kdPermintaan = selectedCheckbox.value;

          let params = {
            kode_permintaan: kdPermintaan
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);

          // Pastikan route URL sesuai dengan konfigurasi route Anda
          const url = `${baseUrl}gudang/permintaan-barang-cek-spk?` + queryString;

          // get data
          fetch(url)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if(status) {

              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-nomor-polisi').value = data.no_polisi;
              document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
              document.getElementById('add-nama-pemilik').value = data.pemilik;
              document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
              document.getElementById('add-nomor-polis').value = data.no_polis;
              document.getElementById('add-nomor-klaim').value = data.kode_claim;

              // setSelectValue('#add-tipe-barang', 'S', '');

              // fetchNamaBagian('#add-tipe-barang', '#add-kode-bagian', '00006');
              // setSelectValue('#add-kode-bagian', '00006', '');

              // selected_kode_spk = data.kode_spk; // Sesuaikan dengan nama field di database/json
              // selected_kode_cabang = data.kode_cabang;
              // selected_tipe = 'estimasi-sparepart';

              selected_kode_permintaan = $('#add-nomor-permintaan').val();
              selected_kode_cabang = $('#kode_cabang').val();
              selected_tipe = 'detail-sparepart';

              dt_detail.column(2).visible(true);

              // // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
              dt_detail.ajax.reload();


            } else {
              // sweetalert
              Swal.fire({
                icon: 'warning',
                title: `Peringatan!`,
                text: `${message}`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          })
          .catch(err => {
            Swal.fire({
              title: 'Error!',
              text: 'Gagal cek data SPK',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });

        }
      });
    }

    // Edit Record
    const btnEditSelected = document.querySelector('.edit-record');
    if (btnEditSelected) {
      btnEditSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isEdit) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk ubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-permintaan .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          toggleReadOnlyForm('#addRoleModal', false);

          PleaseWaitPage();

          // get data
          fetch(`${baseUrl}permintaan-barang-list/${user_id}/edit`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }

            if(status) {

              // Buka Modal secara manual
              const modalInstance = new bootstrap.Modal(addRoleModal);
              modalInstance.show();

              document.getElementById('user_id').value = data.id;
              document.getElementById('kode_bagian2').value = data.kode_bagian;

              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-nomor-polisi').value = data.no_polisi;
              document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
              document.getElementById('add-nama-pemilik').value = data.pemilik;
              document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
              document.getElementById('add-nomor-permintaan').value = data.kode_permintaan;
              document.getElementById('add-tanggal-permintaan').value = data.tanggal_permintaan;

              setSelectValue('#add-tipe-barang', data.kode_tipe_barang, '');
              setSelectValue('#add-kode-bagian', data.kode_bagian, '');

              fetchNamaBagian('#add-tipe-barang', '#add-kode-bagian', data.kode_bagian);

              selected_kode_permintaan = data.kode_permintaan; // Sesuaikan dengan nama field di database/json
              selected_kode_cabang = data.kode_cabang;

              if(data.kode_tipe_barang == 'S') {
                selected_tipe = 'detail-sparepart';
                dt_detail.column(2).visible(true);
                $("#addDetBarang").hide();
              } else {
                selected_tipe = 'detail';
                dt_detail.column(2).visible(false);
                $("#addDetBarang").show();
              }

              // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
              dt_detail.ajax.reload();

              $(".btn-cek-spk").hide();
              // $('#add-nomor-spk').prop('disabled', true);
              // $('#add-tipe-barang').prop('disabled', true).trigger('change');

            } else {
              // sweetalert
              Swal.fire({
                icon: 'warning',
                title: `Peringatan!`,
                text: `${message}`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          })
          .catch(err => {
            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }

            Swal.fire({
              title: 'Error!',
              text: 'Gagal Cetak Estimasi',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
        }
      });
    }

    // Delete Record
    const btnDeletedSelected = document.querySelector('.delete-record');
    if (btnDeletedSelected) {
      btnDeletedSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isDelete) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk hapus data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-permintaan .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // sweetalert for confirmation of delete
          Swal.fire({
            title: 'Konfirmasi?',
            text: "Anda yakin akan menghapus data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            customClass: {
              confirmButton: 'btn btn-primary me-3',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          }).then(function (result) {
            if (result.value) {
              // delete the data
              fetch(`${baseUrl}permintaan-barang-list/${user_id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  'Content-Type': 'application/json'
                }
              })
                .then(response => {
                  if (response.ok) {
                    fetchDashboardPermintaan();

                    dt_basic.draw();

                    // success sweetalert
                    Swal.fire({
                      icon: 'success',
                      title: 'Hapus!',
                      text: 'Data berhasil dihapus!',
                      customClass: {
                        confirmButton: 'btn btn-success'
                      }
                    });
                  } else {
                    throw new Error('Gagal Hapus Data');
                  }
                })
                .catch(error => {
                    //  console.log(error);
                    Swal.fire({
                      icon: 'error',
                      title: 'Error!',
                      text: `${error}`,
                      customClass: {
                        confirmButton: 'btn btn-success'
                      }
                    });
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              Swal.fire({
                title: 'Batal',
                text: 'Data batal dihapus!',
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          });
        }
      });
    }

    const btnCetakSelected = document.querySelector('.cetak-record');
    if (btnCetakSelected) {
      btnCetakSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isEdit) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk mengubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-permintaan .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          PleaseWaitPage();

          // get data
          fetch(`${baseUrl}permintaan-barang-list/${user_id}/edit`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }

            if(status) {

              let params = {
                id: data.id
              };

              // Bersihkan parameter kosong agar URL tidak terlalu panjang
              let queryString = $.param(params);

              // Redirect window untuk download file
              // Pastikan route URL sesuai dengan konfigurasi route Anda
              const printUrl = `${baseUrl}gudang/cetak-permintaan-barang?` + queryString;
              window.open(printUrl, '_blank');

            } else {
              // sweetalert
              Swal.fire({
                icon: 'warning',
                title: `Peringatan!`,
                text: `${message}`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          })
          .catch(err => {
            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }

            Swal.fire({
              title: 'Error!',
              text: 'Gagal cek data SPK',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
        }
      });
    }

    // View Record
    const btnViewSelected = document.querySelector('.view-record');
    if (btnViewSelected) {
      btnViewSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isList) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk view data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-permintaan .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          toggleReadOnlyForm('#addRoleModal', true);

          PleaseWaitPage();

          // get data
          fetch(`${baseUrl}permintaan-barang-list/${user_id}/edit`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if (document.querySelector(`.notiflix-loading`)) {
              Loading.remove();
            }

            if(status) {

              // Buka Modal secara manual
              const modalInstance = new bootstrap.Modal(addRoleModal);
              modalInstance.show();

              document.getElementById('user_id').value = data.id;
              document.getElementById('kode_bagian2').value = data.kode_bagian;

              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-nomor-polisi').value = data.no_polisi;
              document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
              document.getElementById('add-nama-pemilik').value = data.pemilik;
              document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
              document.getElementById('add-nomor-permintaan').value = data.kode_permintaan;
              document.getElementById('add-tanggal-permintaan').value = data.tanggal_permintaan;

              setSelectValue('#add-tipe-barang', data.kode_tipe_barang, '');
              setSelectValue('#add-kode-bagian', data.kode_bagian, '');

              fetchNamaBagian('#add-tipe-barang', '#add-kode-bagian', data.kode_bagian);

              selected_kode_permintaan = data.kode_permintaan; // Sesuaikan dengan nama field di database/json
              selected_kode_cabang = data.kode_cabang;

              if(data.kode_tipe_barang == 'S') {
                selected_tipe = 'detail-sparepart';
                dt_detail.column(2).visible(true);
                $("#addDetBarang").hide();
              } else {
                selected_tipe = 'detail';
                dt_detail.column(2).visible(false);
                $("#addDetBarang").show();
              }

              dt_detail.column(-1).visible(false);

              // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
              dt_detail.ajax.reload();

              $(".btn-cek-spk").hide();
              // $('#add-nomor-spk').prop('disabled', true);
              // $('#add-tipe-barang').prop('disabled', true).trigger('change');

            } else {
              
              if (document.querySelector(`.notiflix-loading`)) {
                Loading.remove();
              }

              // sweetalert
              Swal.fire({
                icon: 'warning',
                title: `Peringatan!`,
                text: `${message}`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          })
          .catch(err => {
            Swal.fire({
              title: 'Error!',
              text: 'Gagal View Permintaan Barang',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
        }
      });
    }

    // user form validation
    const addNewDataForm = document.getElementById('addNewDataForm');
    if (addNewDataForm) {
      const fv = FormValidation.formValidation(addNewDataForm, {
        fields: {
          kode_spk: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor SPK'
              }
            }
          },
          tanggal_permintaan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tanggal Permintaan'
              }
            }
          },
          tipe_barang: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tipe Barang'
              }
            }
          },
          kode_bagian: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Bagian'
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
        // addNewDataForm.submit();

        // adding or updating user when form successfully validate
        const formData = new FormData(addNewDataForm);
        const formDataObj = {};

        // Convert FormData to URL-encoded string
        formData.forEach((value, key) => {
          formDataObj[key] = value;
        });

        const searchParams = new URLSearchParams();
        for (const [key, value] of Object.entries(formDataObj)) {
          searchParams.append(key, value);
        }

        PleaseWaitPage();

        fetch(`${baseUrl}permintaan-barang-list`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: searchParams.toString()
        })
        .then(response => {
          console.log(response);
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.text();
        })
        .then(result => {
          const { status, message, errors } = JSON.parse(result);

          if (document.querySelector(`.notiflix-loading`)) {
            Loading.remove();
          }

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          if(status) {
            fetchDashboardPermintaan();

            // Refresh DataTable
            dt_basic_table && new DataTable(dt_basic_table).draw();

            // sweetalert
            Swal.fire({
              icon: 'success',
              title: `Informasi!`,
              text: `${message}`,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });

          } else {
            let errorHtml = `<p>${message}</p>`; // Pesan utama (Gagal menyimpan data)

            if (errors) {
              errorHtml += '<ul style="text-align: left; margin-top: 10px;">';
              // Loop setiap key error (no_polisi, no_rangka, dll)
              Object.values(errors).forEach(errorArray => {
                // Loop setiap pesan error di dalam array
                errorArray.forEach(errMsg => {
                  errorHtml += `<li>${errMsg}</li>`;
                });
              });
              errorHtml += '</ul>';
            }

            // sweetalert
            Swal.fire({
              icon: 'error',
              title: `Error!`,
              html: errorHtml,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        })
        .catch(err => {
          // Hide offcanvas
          // const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          // offcanvasInstance && offcanvasInstance.hide();

          if (document.querySelector(`.notiflix-loading`)) {
            Loading.remove();
          }

          Swal.fire({
            title: 'Error!',
            text: 'Gagal simpan data.',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        });
      });
    }

    // Cek SPK
    // const cekSPKBtn = document.querySelector('.btn-cek-spk');
    // if (cekSPKBtn) {
    //   cekSPKBtn.addEventListener('click', function () {
    //     if(isAdd) {
    //     const kdSPK = $("#add-nomor-spk").val();
    //     if(kdSPK.length) {

    //       clearFormData();

    //       let params = {
    //         kode_spk: kdSPK
    //       };

    //       // Bersihkan parameter kosong agar URL tidak terlalu panjang
    //       let queryString = $.param(params);

    //       // Pastikan route URL sesuai dengan konfigurasi route Anda
    //       const url = `${baseUrl}gudang/permintaan-barang-cek-spk?` + queryString;

    //       // get data
    //       fetch(url)
    //       .then(response => {
    //         if (!response.ok) {
    //           throw new Error('Network response was not ok');
    //         }
    //         return response.text();
    //       })
    //       .then(result => {
    //         const { status, message, data } = JSON.parse(result);

    //         if(status) {

    //           document.getElementById('add-nomor-spk').value = data.kode_spk;
    //           document.getElementById('add-nomor-polisi').value = data.no_polisi;
    //           document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
    //           document.getElementById('add-nama-pemilik').value = data.pemilik;
    //           document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;

    //           setSelectValue('#add-tipe-barang', 'S', '');

    //           fetchNamaBagian('#add-tipe-barang', '#add-kode-bagian', '00006');
    //           // setSelectValue('#add-kode-bagian', '00006', '');

    //           selected_kode_spk = data.kode_spk; // Sesuaikan dengan nama field di database/json
    //           selected_kode_cabang = data.kode_cabang;
    //           selected_tipe = 'estimasi-sparepart';

    //           dt_detail.column(2).visible(true);

    //           // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
    //           dt_detail.ajax.reload();


    //         } else {
    //           // sweetalert
    //           Swal.fire({
    //             icon: 'warning',
    //             title: `Peringatan!`,
    //             text: `${message}`,
    //             customClass: {
    //               confirmButton: 'btn btn-success'
    //             }
    //           });
    //         }
    //       })
    //       .catch(err => {
    //         Swal.fire({
    //           title: 'Error!',
    //           text: 'Gagal cek data SPK',
    //           icon: 'error',
    //           customClass: {
    //             confirmButton: 'btn btn-success'
    //           }
    //         });
    //       });
    //     } else {
    //       document.getElementById('add-nomor-spk')?.focus();

    //       Swal.fire({
    //         icon: 'warning',
    //         title: 'Peringatan',
    //         text: 'Silahkan Input Nomor SPK',
    //         customClass: { confirmButton: 'btn btn-primary' }
    //       });
    //     }
    //     } else {
    //       // Hide offcanvas
    //       const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
    //       offcanvasInstance && offcanvasInstance.hide();

    //       Swal.fire({
    //         icon: 'error',
    //         title: 'Error!',
    //         text: 'Anda tidak memiliki izin untuk akses tambah data',
    //         customClass: {
    //           confirmButton: 'btn btn-success'
    //         }
    //       });
    //     }
    //   });
    // }

    const cekSPKBtn = document.querySelector('.btn-cek-spk');
    if (cekSPKBtn) {
      cekSPKBtn.addEventListener('click', function () {
        if (!isAdd) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk tambah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Hide modal
        const modalInstance2 = bootstrap.Modal.getInstance(addRoleModal);
        modalInstance2 && modalInstance2.hide();

        const modalInstance = new bootstrap.Modal(viewSpkModal);
        modalInstance.show();

        selected_tipe = 'spk-baru-all';
        dt_spk.ajax.reload();
      });
    }

    // changing the title
    const addDetBtn = document.querySelector('.add-detail');
    if (addDetBtn) {
      addDetBtn.addEventListener('click', function () {
        if(isAdd) {
          // reset seluruh form (termasuk select2)
          clearFormDataDet1();
          fetchNamaBahan('#add-tipe-barang', '#add-bahan');
        } else {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
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

    // Delete Record Detail
    document.addEventListener('click', function (e) {
      const editDetBtn = e.target.closest('.edit-detail');
      if (editDetBtn) {
        if(isEdit) {
          clearFormDataDet1();

          const tipe = editDetBtn.dataset.tipe;
          const user_id = editDetBtn.dataset.id;

          // 1. Ambil instance DataTable
          const tableEl = document.querySelector('.datatables-detail');
          const dt = $(tableEl).DataTable();

          // 2. Cari baris (TR) terdekat dari tombol yang diklik
          const row = $(editDetBtn).closest('tr');

          // 3. AMBIL DATA (Tambahkan .data() di akhir)
          const rowData = dt.row(row).data();

          rowToEdit = dt.row(row);

          document.getElementById('est_dtl1_id').value = rowData.id;
          // document.getElementById('add-nomor-sparepart').value = rowData.no_sparepart;
          document.getElementById('add-qty-bahan').value = rowData.qty;
          // document.getElementById('add-harga-bahan').value = rowData.harga;
          // document.getElementById('add-jumlah-bahan').value = rowData.jumlah;

          fetchNamaBahan('#add-tipe-barang', '#add-bahan', rowData.kode_bahan);

          setSelectValue('#add-bahan', rowData.kode_bahan, '');
          setSelectValue('#add-satuan', rowData.kode_satuan, '');

        } else {
          // Hide offcanvas
          if (tipe == "detail") {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }

          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Anda tidak memiliki izin untuk akses rubah data',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      }

      const deleteDetBtn = e.target.closest('.delete-detail');
      if (deleteDetBtn) {
         if (isDelete) {
          const tipe = deleteDetBtn.dataset.tipe;
          if (tipe == "detail") {
            // 1. Ambil instance DataTable
            const tableEl = document.querySelector('.datatables-detail');
            const dt = $(tableEl).DataTable();

            // 2. Cari baris (TR) terdekat dari tombol yang diklik
            const row = $(deleteDetBtn).closest('tr');

            // 3. Hapus baris tersebut dari DataTable
            dt.row(row).remove().draw(false);

          }

         } else {
          // Hide offcanvas
          if (tipe == "detail") {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }

          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Anda tidak memiliki izin untuk akses hapus data',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
         }
      }
    });

    const addNewDataFormDet1 = document.getElementById('addNewDataFormDet1');
    if (addNewDataFormDet1) {
      if (fvPerbaikan) {
        fvPerbaikan.destroy(); // Hancurkan instance lama agar tidak menumpuk
      }

      fvPerbaikan = FormValidation.formValidation(addNewDataFormDet1, {
        fields: {
          kode_bahan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nama Barang'
              }
            }
          },
          kode_satuan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Satuan'
              }
            }
          },
          qty: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Qty'
              }
            }
          },
          // harga: {
          //   validators: {
          //     notEmpty: {
          //       message: 'Silahkan Input Harga'
          //     }
          //   }
          // }
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
        const cekID = $("#est_dtl1_id").val();
        if(cekID) {

          const oldData = rowToEdit.data();

          rowToEdit.data({
            id: oldData.id,           // Pertahankan ID lama
            fake_id: oldData.fake_id, // Pertahankan Fake ID lama

            // Update field sesuai input form terbaru
            kode_bahan: $("#add-bahan option:selected").val(),
            nama_bahan: $("#add-bahan option:selected").text(),
            no_sparepart: $("#add-nomor-sparepart").val(),
            qty: $("#add-qty-bahan").val(),
            kode_satuan: $("#add-satuan option:selected").val(),
            nama_satuan: $("#add-satuan option:selected").text(),
            harga: $("#add-harga-bahan").val(),
            jumlah: $("#add-jumlah-bahan").val(),

            action: oldData.action    // Pertahankan kolom action
          }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

          rowToEdit = null;

          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
          offcanvasInstance && offcanvasInstance.hide();

        } else {
          if (dt_detail) {
            const newId = 'new_' + Date.now();
            dt_detail.row.add({
                id: newId,
                fake_id: '',
                kode_bahan: $("#add-bahan option:selected").val(),
                nama_bahan: $("#add-bahan option:selected").text(),
                no_sparepart: $("#add-nomor-sparepart").val(),
                qty: $("#add-qty-bahan").val(),
                kode_satuan: $("#add-satuan option:selected").val(),
                nama_satuan: $("#add-satuan option:selected").text(),
                // harga: $("#add-harga-bahan").val(),
                // jumlah: $("#add-jumlah-bahan").val(),
                action: ''
            }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

            // Pindah ke halaman terakhir agar baris baru terlihat (opsional)
            // dt_detail.page('last').draw(false);

            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }
        }
      });
    }

    $('#add-tipe-barang').on('select2:select', function () {
      selected_kode_permintaan = $("#add-nomor-permintaan").val();
      selected_kode_cabang = $("#kode_cabang").val();
      selected_kode_spk = $("#add-nomor-spk").val();

      let selectedValue = $(this).val();
      if(selectedValue.length) {
        fetchNamaBagian('#add-tipe-barang', '#add-kode-bagian');
        if(selectedValue == 'S' || selectedValue == 'T') {
          selected_tipe = (selected_kode_permintaan.length) ? 'detail-sparepart' : 'estimasi-sparepart';
          dt_detail.column(2).visible(true);
          $("#addDetBarang").hide();
        } else {
          selected_tipe = 'detail';
          dt_detail.column(2).visible(false);
          $("#addDetBarang").show();
          fetchNamaBahan('#add-tipe-barang', '#add-bahan');
        }

        // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
        dt_detail.ajax.reload();
      }
    });

    const viewSPK = document.querySelector('.view-spk');
    if (viewSPK) {
      viewSPK.addEventListener('click', function () {
        if (!isAdd) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk tambah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Hide modal
        const modalInstance2 = bootstrap.Modal.getInstance(addRoleModal);
        modalInstance2 && modalInstance2.hide();

        const modalInstance = new bootstrap.Modal(viewSpkModal);
        modalInstance.show();

        selected_tipe = 'spk-baru';

        dt_spk.ajax.reload();
      });
    }

    const btnSpkSelected = document.querySelector('.btn-pilih-spk');
    if (btnSpkSelected) {
      btnSpkSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isAdd) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk tambah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes-spk:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih data pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const kdSPK = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          // Hide modal
          const modalInstance = bootstrap.Modal.getInstance(viewSpkModal);
          modalInstance && modalInstance.hide();

          // Show modal
          const modalInstance2 = new bootstrap.Modal(addRoleModal);
          modalInstance2.show();

          let params = {
            kode_spk: kdSPK
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);

          // Pastikan route URL sesuai dengan konfigurasi route Anda
          const url = `${baseUrl}gudang/permintaan-barang-cek-spk?` + queryString;

          // get data
          fetch(url)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);

            if(status) {

              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-nomor-polisi').value = data.no_polisi;
              document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
              document.getElementById('add-nama-pemilik').value = data.pemilik;
              document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
              document.getElementById('add-nomor-polis').value = data.no_polis;
              document.getElementById('add-nomor-klaim').value = data.kode_claim;

              setSelectValue('#add-tipe-barang', 'S', '');

              fetchNamaBagian('#add-tipe-barang', '#add-kode-bagian', '00006');
              // setSelectValue('#add-kode-bagian', '00006', '');

              selected_kode_spk = data.kode_spk; // Sesuaikan dengan nama field di database/json
              selected_kode_cabang = data.kode_cabang;
              selected_tipe = 'estimasi-sparepart';

              dt_detail.column(2).visible(true);

              // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
              dt_detail.ajax.reload();


            } else {
              // sweetalert
              Swal.fire({
                icon: 'warning',
                title: `Peringatan!`,
                text: `${message}`,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            }
          })
          .catch(err => {
            Swal.fire({
              title: 'Error!',
              text: 'Gagal cek data SPK',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
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

      // Delete record
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

  const invoiceItemPriceList = document.querySelectorAll('.invoice-price');
  if (invoiceItemPriceList) {
    invoiceItemPriceList.forEach(function (invoiceItemPrice) {
      if (invoiceItemPrice) {
        invoiceItemPrice.addEventListener('input', event => {
          invoiceItemPrice.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });

          // if(invoiceItemPrice.name != "jumlah") {
          //   hitungTotalBarang();
          // }
        });
      }
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

    const inputUppercaseList = document.querySelectorAll('.text-uppercase');
    if (inputUppercaseList) {
      inputUppercaseList.forEach(function (inputUppercase) {
        inputUppercase.addEventListener('input', event => {
          inputUppercase.value = inputUppercase.value.toUpperCase();
        });
      });
    }

 });

  function fetchNamaBagian(selectorTipeBarang, selectorBagian, kdBagian='') {
    const tipeBarang = $(selectorTipeBarang).val();
    // const kdBagian = $("#kode_bagian2").val();
    const selectBagian = $(selectorBagian);

    // Hanya request jika Merek DAN Jenis sudah dipilih
    if (tipeBarang) {

      // Disable sementara agar user tahu sedang loading
      selectBagian.prop('disabled', true);

      // Gunakan helper clearSelect yg sudah ada di kode Anda untuk reset opsi lama
      clearSelect('#add-kode-bagian', { keepOptions: false });

      // Fetch data
      fetch(`${baseUrl}gudang/get-bagian?tipe_barang=${tipeBarang}`)
        .then(response => {
          if (!response.ok) throw new Error('Gagal mengambil data');
          return response.json();
        })
        .then(data => {
          // Tambahkan opsi default
          const defaultOption = new Option('Pilih Bagian', '', true, true);
          selectBagian.append(defaultOption).trigger('change');

          // Loop data dari server dan masukkan ke select
          data.forEach(item => {
            // Cek apakah kode_posisi saat ini sama dengan yang dicari
            var isSelected = (item.kode_posisi == kdBagian);

            // Pastikan key 'item.id' dan 'item.nama_tipe' sesuai return Controller
            const newOption = new Option(item.posisi_pekerjaan, item.kode_posisi, false, isSelected);
            selectBagian.append(newOption);
          });

          // Refresh Select2 agar opsi baru muncul
          selectBagian.trigger('change');
          selectBagian.prop('disabled', false); // Aktifkan kembali
        })
        .catch(error => {
          console.error('Error:', error);
          selectBagian.prop('disabled', false);
        });

    } else {
      // Jika salah satu kosong, reset Tipe menjadi kosong
      clearSelect('#add-kode-bagian');
    }
  }

  function fetchNamaBahan(selectorJenis, selectorBahan, kdBahan='') {
    const jenisId = $(selectorJenis).val();
    // const kdBahan = $("#kode_bahan2").val();
    const selectBahan = $(selectorBahan);

    // Hanya request jika Merek DAN Jenis sudah dipilih
    if (jenisId) {

      // Disable sementara agar user tahu sedang loading
      selectBahan.prop('disabled', true);

      // Gunakan helper clearSelect yg sudah ada di kode Anda untuk reset opsi lama
      clearSelect('#add-bahan', { keepOptions: false });

      // Fetch data
      fetch(`${baseUrl}gudang/get-nama-bahan?jenis_id=${jenisId}`)
        .then(response => {
          if (!response.ok) throw new Error('Gagal mengambil data');
          return response.json();
        })
        .then(data => {
          // Tambahkan opsi default
          const defaultOption = new Option('Pilih Nama Bahan', '', true, true);
          selectBahan.append(defaultOption).trigger('change');

          // Loop data dari server dan masukkan ke select
          data.forEach(item => {
            // Cek apakah kode_bahan saat ini sama dengan yang dicari
            var isSelected = (item.kode_bahan == kdBahan);

            // Pastikan key 'item.id' dan 'item.nama_tipe' sesuai return Controller
            const newOption = new Option(item.nama_bahan, item.kode_bahan, false, isSelected);
            selectBahan.append(newOption);
          });

          // Refresh Select2 agar opsi baru muncul
          selectBahan.trigger('change');
          selectBahan.prop('disabled', false); // Aktifkan kembali
        })
        .catch(error => {
          console.error('Error:', error);
          selectBahan.prop('disabled', false);
        });

    } else {
      // Jika salah satu kosong, reset Tipe menjadi kosong
      clearSelect('#add-bahan');
    }
  }

  function fetchDashboardPermintaan() {

    // Fetch data
    fetch(`${baseUrl}permintaan-barang-list?tipe=total-data`)
    .then(response => {
      if (!response.ok) throw new Error('Gagal mengambil data');
      return response.json();
    })
    .then(data => {
      $("#total-spk").html(data.spk);
      $("#total-permintaan-bulan").html(data.permintaan_bulan);
      $("#total-permintaan-tahun").html(data.permintaan_tahun);
    })
    .catch(error => {
      console.error('Error:', error);
    });

  }

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

  function setRadioValue(name, value) {
    // 1. Cari semua radio button dengan name tersebut
    const $radios = $(`input[name="${name}"]`);

    // Guard clause: Jika elemen tidak ditemukan, stop.
    if (!$radios.length) return;

    // 2. Normalisasi value:
    // Jika null/undefined maka null, selain itu ubah ke String agar pencarian atribut akurat
    const val = (value !== null && value !== undefined) ? String(value) : null;

    // 3. Reset state: Uncheck semua radio button di grup ini terlebih dahulu
    $radios.prop('checked', false);

    // 4. Jika value ada, cari radio yang cocok dan set checked
    if (val !== null) {
        // Filter elemen yang value-nya sama persis
        $radios.filter(`[value="${val}"]`).prop('checked', true);
    }

    // 5. Trigger event 'change' (Penting jika ada logic lain yang memantau perubahan radio)
    $radios.trigger('change');
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

 function clearFormData() {
   const form = document.getElementById('addNewDataForm');
   // reset input/textarea standar
   form?.reset?.();

   // kosongkan select (Select2)
   clearSelect('#add-tipe-barang');
   clearSelect('#add-kode-bagian');

   $('#add-nomor-spk').addClass('is-invalid');
   $('#add-tanggal-permintaan').addClass('is-invalid');
   $('#add-tipe-barang').addClass('is-invalid');
   $('#add-kode-bagian').addClass('is-invalid');

   // bersihkan error jQuery/FormValidation (kalau ada)
   // try {
   //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
   // } catch (e) {}
 }

  function clearFormDataDet1() {
    const form = document.getElementById('addNewDataFormDet1');
    // reset input/textarea standar
    form?.reset?.();

    $("#est_dtl1_id").val('');
    $("#kode_bahan2").val('');

    // kosongkan select (Select2)
    clearSelect('#add-bahan');
    clearSelect('#add-satuan');

    $('#add-bahan').addClass('is-invalid');
    $('#add-satuan').addClass('is-invalid');
    $('#add-qty-bahan').addClass('is-invalid');
  }

  function toggleReadOnlyForm(containerSelector, isReadOnly) {
    const container = $(containerSelector);
    
    // 1. Kunci atau buka semua tag input, select, dan textarea
    container.find('input, select, textarea').prop('disabled', isReadOnly);
    
    // 2. Khusus untuk plugin Select2 (termasuk select2-ajax), wajib di-trigger
    if (container.find('.select2, .select2-ajax').length) {
        container.find('.select2, .select2-ajax').prop('disabled', isReadOnly).trigger('change.select2');
    }
    
    // 3. Sembunyikan atau tampilkan tombol Simpan (tipe submit)
    if (isReadOnly) {
        container.find('button[type="submit"]').hide();
        // Jika ada tombol 'Tambah Detail' di dalam modal, Anda juga bisa sembunyikan
        container.find('.add-detail').hide(); 
    } else {
        container.find('button[type="submit"]').show();
        container.find('.add-detail').show();
    }
  }

  // function hitungTotalBarang() {
  //   // Ambil elemen input di baris tersebut
  //   let elQty = $('#add-qty-bahan');
  //   let elHarga = $('#add-harga-bahan');
  //   let elJumlah = $('#add-jumlah-bahan');

  //   // Ambil Nilai & Bersihkan format (hapus koma)
  //   let qty = parseFloat(elQty.val().replace(/,/g, '')) || 0;
  //   let harga = parseFloat(elHarga.val().replace(/,/g, '')) || 0;

  //   // Hitung Subtotal Baris
  //   let subtotal = qty * harga;

  //   // Format hasil ke string angka (misal: 2,000.00)
  //   let formattedSubtotal = new Intl.NumberFormat('en-US', {
  //       minimumFractionDigits: 0,
  //       maximumFractionDigits: 0
  //   }).format(subtotal);

  //   // Update Kolom JUMLAH di baris tersebut
  //   elJumlah.val(formattedSubtotal);
  // }

  // function hitungTotalPerbaikan() {
  //   let total = 0;
  //   let total_seluruh = 0;
  //   let ppn = 0;

  //   let ppn_persen = $('#add-ppn-persen').val();

  //   // Loop semua elemen dengan class 'add-harga'
  //   $('.add-jumlah').each(function() {
  //       // Ambil value
  //       let val = $(this).val();

  //       // Bersihkan format (hapus koma) agar bisa dijumlahkan
  //       // Contoh: "405,000.00" menjadi 405000.00
  //       // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
  //       let cleanVal = val.replace(/,/g, '');

  //       // Konversi ke float, jika NaN (kosong) anggap 0
  //       let numberVal = parseFloat(cleanVal) || 0;

  //       total += numberVal;
  //   });

  //   $('.add-ppn:checked').each(function() {
  //     // Ambil value
  //     let nilai = $(this).val();

  //     if (nilai == "1") {
  //       ppn = (ppn_persen > 0) ? total / ppn_persen : 0;
  //     }

  //   });

  //   total_seluruh = (total + ppn);

  //   // Format kembali ke tampilan angka (misal: 1,000,000.00)
  //   let formattedTotal = new Intl.NumberFormat('en-US', {
  //       minimumFractionDigits: 2,
  //       maximumFractionDigits: 2
  //   }).format(total);

  //   // Format kembali ke tampilan angka (misal: 1,000,000.00)
  //   let formattedPPN = new Intl.NumberFormat('en-US', {
  //     minimumFractionDigits: 2,
  //     maximumFractionDigits: 2
  //   }).format(ppn);

  //   // Format kembali ke tampilan angka (misal: 1,000,000.00)
  //   let formattedTotalTagihan = new Intl.NumberFormat('en-US', {
  //     minimumFractionDigits: 2,
  //     maximumFractionDigits: 2
  //   }).format(total_seluruh);

  //   // Update teks di elemen footer
  //   // $('#total-detail').text(formattedTotal);
  //   $('#add-subtotal').val(formattedTotal);
  //   $('#add-ppn').val(formattedPPN);
  //   $('#add-total-detail').val(formattedTotalTagihan);
  // }
