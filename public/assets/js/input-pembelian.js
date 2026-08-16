/**
 * Page Data management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete, fvPerbaikan, fvSparepart, fvLain;
  let selected_status = '';
  let selected_kode_input = '';
  let selected_kode_order = '';
  let selected_tipe = 'detail';
  let rowToEdit = null;
  let dt_basic, dt_detail, dt_spk;

  // Variable declaration for table
  const dt_basic_table = document.querySelector('.datatables-order'),
    addRoleModal = document.getElementById('addRoleModal'),
    viewSpkModal = document.getElementById('viewSpkModal'),
    offCanvasFormDet1 = document.getElementById('offcanvasAddDet1'),
    offCanvasFormDet2 = document.getElementById('offcanvasAddDet2'),
    offCanvasFormDet3 = document.getElementById('offcanvasAddDet3'),
    statusObj = {
      0: { class: 'text-bg-danger' },
      1: { class: 'text-bg-success' }
    };

  fetchDashboardOrder();

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // COA datatable
  if (dt_basic_table) {
    isAdd = dt_basic_table.dataset.add;
    isEdit = dt_basic_table.dataset.edit;
    isDelete = dt_basic_table.dataset.delete;

    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center');
    tableTitle.innerHTML = dt_basic_table.dataset.title;
    dt_basic = new DataTable(dt_basic_table, {
      searching: false, // Opsi ini akan menghilangkan input cari
      ordering: true, // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'input-pembelian-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.kode_order = $('#filter-nomor-order').val();
          d.kode_input = $('#filter-nomor-input').val();
          d.kode_spk = $('#filter-nomor-spk').val();
          d.tanggal_awal = $('#filter-tanggal-awal').val();
          d.tanggal_akhir = $('#filter-tanggal-akhir').val();
          d.nama_pemilik = $('#filter-nama-pemilik').val();
          d.nama_pemasok = $('#filter-nama-pemasok').val();
          d.tipe_barang = $('#filter-tipe-barang').val();
          d.status_approve = selected_status;
          d.tipe = 'input-pembelian';
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
        { data: 'tanggal' },
        { data: 'kode_input' },
        { data: 'keterangan' },
        { data: 'kode_order' },
        { data: 'kode_spk' },
        { data: 'tipe_barang' },
        { data: 'nama_pemasok' },
        { data: 'total', className: 'text-end' }
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
        {
          targets: 4,
          // responsivePriority: 6,
          render: function (data, type, full, meta) {
            const status = full['status_approve'];
            const badgeClass = statusObj[status] ? statusObj[status].class : 'text-bg-success';

            return '<span class="badge rounded-pill ' + badgeClass + '" text-capitalized>' + data + '</span>';
          }
        }
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
      order: [[2, 'desc']],
      layout: {
        //  top2Start: {
        //    rowClass: 'row card-header mx-0 px-2',
        //    features: [tableTitle]
        //  },
        //  top2End: {
        //    features: [
        //      {
        //        buttons: [
        //          {
        //            text: '<i class="icon-base ri ri-add-line icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Tambah</span>',
        //            className: 'add-new btn btn-primary',
        //            attr: {
        //              'data-bs-toggle': 'modal',
        //              'data-bs-target': '#addRoleModal'
        //            }
        //          }
        //        ]
        //      }
        //    ]
        //  },
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
      // For responsive popup
      //  responsive: {
      //    details: {
      //      display: DataTable.Responsive.display.modal({
      //        header: function (row) {
      //          const data = row.data();
      //          return 'Details of ' + data['kode_order'];
      //        }
      //      }),
      //      type: 'column',
      //      renderer: function (api, rowIdx, columns) {
      //        const data = columns
      //          .map(function (col) {
      //            return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
      //              ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
      //                  <td>${col.title}:</td>
      //                  <td>${col.data}</td>
      //                </tr>`
      //              : '';
      //          })
      //          .join('');

      //        if (data) {
      //          const div = document.createElement('div');
      //          div.classList.add('table-responsive');
      //          const table = document.createElement('table');
      //          div.appendChild(table);
      //          table.classList.add('table');
      //          const tbody = document.createElement('tbody');
      //          tbody.innerHTML = data;
      //          table.appendChild(tbody);
      //          return div;
      //        }
      //        return false;
      //      }
      //    }
      //  },
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
        searching: false, // Opsi ini akan menghilangkan input cari
        ordering: false, // Opsi lain tetap bisa jalan
        paging: false,
        info: false,
        processing: true,
        serverSide: false,
        destroy: true, // Fix Error reinitialise
        ajax: {
          url: baseUrl + 'input-pembelian-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.kode_order = selected_kode_order;
            d.kode_input = selected_kode_input;
            d.tipe = selected_tipe;
          }
        },
        columns: [
          // columns according to JSON
          { data: 'id' },
          { data: 'nama_bahan' },
          { data: 'no_sparepart' },
          { data: 'nama_satuan' },
          { data: 'qty' },
          { data: 'harga' },
          { data: 'jumlah' },
          { data: 'cek' }
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
              `;
            }
          },
          {
            targets: 5,
            className: 'text-end',
            render: function (data, type, full, meta) {
              let val = data || 0;
              return `
                ${data}
                <input type="hidden" name="detail[${full.id}][harga]" class="add-harga" value="${data}" />
              `;
            }
          },
          {
            targets: 6,
            className: 'text-end',
            render: function (data, type, full, meta) {
              let val = data || 0;
              return `
                  ${data}
                  <input type="hidden" name="detail[${full.id}][jumlah]" class="add-jumlah" value="${data}" />
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
            checkboxes: true,
            render: function (data, type, full, meta) {
              let chked = data == '1' ? 'checked' : '';

              return `<input type="checkbox" name="detail[${full.id}][cek]" class="dt-checkboxes-det form-check-input" value="1" ${chked}>`;
            }
          }
        ],
        drawCallback: function () {
          hitungTotalPerbaikan();
          $('.datatables-detail')
            .off('change', '.dt-checkboxes-det')
            .on('change', '.dt-checkboxes-det', function () {
              // Panggil fungsi hitung ulang setiap kali checkbox diklik
              hitungTotalPerbaikan();
            });
        }
      });
    }

    const dt_spk_table = document.querySelector('.datatables-spk');
    if (dt_spk_table) {
      dt_spk = new DataTable(dt_spk_table, {
        searching: true, // Opsi ini akan menghilangkan input cari
        ordering: false, // Opsi lain tetap bisa jalan
        processing: true,
        serverSide: true,
        // scrollY: '300px',
        scrollX: true,
        ajax: {
          url: baseUrl + 'input-pembelian-list',
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
          { data: 'tanggal' },
          { data: 'kode_order' },
          { data: 'kode_permintaan' },
          { data: 'kode_spk' },
          { data: 'tipe_barang' },
          { data: 'nama_pemasok' }
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
          }
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
        }
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

    // Delete Record
    //  document.addEventListener('click', function (e) {
    //    if (e.target.closest('.delete-record')) {
    //       if (isDelete) {
    //         const deleteBtn = e.target.closest('.delete-record');
    //         const user_id = deleteBtn.dataset.id;
    //         const dtrModal = document.querySelector('.dtr-bs-modal.show');

    //         // hide responsive modal in small screen
    //         if (dtrModal) {
    //           const bsModal = bootstrap.Modal.getInstance(dtrModal);
    //           bsModal.hide();
    //         }

    //         // sweetalert for confirmation of delete
    //         Swal.fire({
    //           title: 'Konfirmasi?',
    //           text: "Anda yakin akan menghapus data ini!",
    //           icon: 'warning',
    //           showCancelButton: true,
    //           confirmButtonText: 'Ya, hapus!',
    //           cancelButtonText: 'Batal',
    //           customClass: {
    //             confirmButton: 'btn btn-primary me-3',
    //             cancelButton: 'btn btn-label-secondary'
    //           },
    //           buttonsStyling: false
    //         }).then(function (result) {
    //           if (result.value) {
    //             // delete the data
    //             fetch(`${baseUrl}input-pembelian-list/${user_id}`, {
    //               method: 'DELETE',
    //               headers: {
    //                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    //                 'Content-Type': 'application/json'
    //               }
    //             })
    //               .then(response => {
    //                 if (response.ok) {
    //                   dt_basic.draw();

    //                   // success sweetalert
    //                   Swal.fire({
    //                     icon: 'success',
    //                     title: 'Hapus!',
    //                     text: 'Data berhasil dihapus!',
    //                     customClass: {
    //                       confirmButton: 'btn btn-success'
    //                     }
    //                   });
    //                 } else {
    //                   throw new Error('Gagal Hapus Data');
    //                 }
    //               })
    //               .catch(error => {
    //                   //  console.log(error);
    //                   Swal.fire({
    //                     icon: 'error',
    //                     title: 'Error!',
    //                     text: `${error}`,
    //                     customClass: {
    //                       confirmButton: 'btn btn-success'
    //                     }
    //                   });
    //               });
    //           } else if (result.dismiss === Swal.DismissReason.cancel) {
    //             Swal.fire({
    //               title: 'Batal',
    //               text: 'Data batal dihapus!',
    //               icon: 'error',
    //               customClass: {
    //                 confirmButton: 'btn btn-success'
    //               }
    //             });
    //           }
    //         });
    //       } else {
    //         Swal.fire({
    //           icon: 'error',
    //           title: 'Error!',
    //           text: 'Anda tidak memiliki izin untuk akses hapus data',
    //           customClass: {
    //             confirmButton: 'btn btn-success'
    //           }
    //         });
    //       }
    //    }
    //  });

    // edit record
    document.addEventListener('click', function (e) {
      if (e.target.closest('.edit-record')) {
        if (isEdit) {
          const editBtn = e.target.closest('.edit-record');
          const user_id = editBtn.dataset.id;
          const dtrModal = document.querySelector('.dtr-bs-modal.show');

          // hide responsive modal in small screen
          if (dtrModal) {
            const bsModal = bootstrap.Modal.getInstance(dtrModal);
            bsModal.hide();
          }

          // get data
          fetch(`${baseUrl}input-pembelian-list/${user_id}/edit`)
            .then(response => response.json())
            .then(data => {
              document.getElementById('user_id').value = data.id;
              document.getElementById('add-nomor-order').value = data.kode_order;
              document.getElementById('add-no-polis').value = data.no_polis;
              document.getElementById('add-no-klaim').value = data.kode_claim;
              document.getElementById('add-tertanggung').value = data.tertanggung;
              document.getElementById('add-catatan-khusus').value = data.catatan_khusus;
              document.getElementById('add-jenis-detail').value = data.jenis_detail;

              setSelectValue('#add-status-order', data.kode_status_order, '');
              setSelectValue('#add-jenis-asuransi', data.kode_jenis_pelanggan, '');
              setSelectValue('#add-pelanggan', data.kode_pelanggan, '');
              setSelectValue('#add-perantara', data.kode_perantara, '');
              setSelectValue('#add-marketing', data.kode_marketing, '');
              setSelectValue('#add-jenis-polis', data.kode_jenis_polis, '');

              setSelectValue('#add-nopolisi', data.no_polisi, '');
            });
        } else {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

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
    });

    const btnEditSelected = document.querySelector('.edit-selected-order');
    if (btnEditSelected) {
      btnEditSelected.addEventListener('click', function () {
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
        const selectedCheckbox = document.querySelector('.datatables-order .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data Input Gudang!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          // get data
          fetch(`${baseUrl}input-pembelian-list/${user_id}/edit`)
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.text();
            })
            .then(result => {
              const { status, message, data } = JSON.parse(result);

              if (status) {
                // Buka Modal secara manual
                const modalInstance = new bootstrap.Modal(addRoleModal);
                modalInstance.show();

                $('.btn-cek-spk').hide();

                $('.btn-submit').html('Simpan');
                if (data.status_approve == '1') {
                  $('.btn-submit').hide();
                } else {
                  $('.btn-submit').show();
                }

                document.getElementById('user_id').value = data.id;
                document.getElementById('status_approve').value = '0';

                document.getElementById('add-nomor-order').value = data.kode_order;
                document.getElementById('add-nomor-order2').value = data.kode_order;
                document.getElementById('add-tanggal-order').value = data.tanggal;
                document.getElementById('add-tipe-barang').value = data.tipe_barang;
                document.getElementById('add-tipe-bayar').value = data.tipe_bayar;
                document.getElementById('add-pemasok').value = data.nama_pemasok;

                document.getElementById('add-nomor-spk').value = data.kode_spk;
                document.getElementById('add-nomor-polisi').value = data.no_polisi;
                document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
                document.getElementById('add-nama-pemilik').value = data.pemilik;
                document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
                document.getElementById('add-nomor-polis').value = data.no_polis;
                document.getElementById('add-nomor-klaim').value = data.kode_claim;

                document.getElementById('add-nomor-input').value = data.kode_input;
                document.getElementById('add-tanggal-input').value = data.tanggal;
                document.getElementById('add-nomor-bon').value = data.no_bon;
                document.getElementById('add-memo').value = data.memo;
                document.getElementById('add-diskon').value = data.diskon;
                document.getElementById('add-ppn').value = data.ppn;

                setRadioValue('sifat_ppn', data.sifat_ppn);

                selected_kode_order = '';
                selected_kode_input = data.kode_input;
                if (data.kode_tipe_barang == 'S' || data.kode_tipe_barang == 'T') {
                  $('#view-spk').show();
                  selected_tipe = 'detail-spk';

                  dt_detail.column(2).visible(true);
                } else {
                  $('#view-spk').hide();
                  selected_tipe = 'detail';

                  dt_detail.column(2).visible(false);
                }

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

    const btnApproveSelected = document.querySelector('.approve-selected-order');
    if (btnApproveSelected) {
      btnApproveSelected.addEventListener('click', function () {
        // Cek Izin Edit
        if (!isDelete) {
          Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: 'Anda tidak memiliki izin untuk mengubah data.',
            customClass: { confirmButton: 'btn btn-primary' }
          });
          return;
        }

        // Cari checkbox yang tercentang di dalam tabel
        const selectedCheckbox = document.querySelector('.datatables-order .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data Input Gudang!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          // get data
          fetch(`${baseUrl}input-pembelian-list/${user_id}/edit`)
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.text();
            })
            .then(result => {
              const { status, message, data } = JSON.parse(result);

              if (status && data.status_approve == '0') {
                // Buka Modal secara manual
                const modalInstance = new bootstrap.Modal(addRoleModal);
                modalInstance.show();

                $('.btn-cek-spk').hide();

                $('.btn-submit').html('Approve');
                if (data.status_approve == '1') {
                  $('.btn-submit').hide();
                } else {
                  $('.btn-submit').show();
                }

                document.getElementById('user_id').value = data.id;
                document.getElementById('status_approve').value = '1';

                document.getElementById('add-nomor-order').value = data.kode_order;
                document.getElementById('add-nomor-order2').value = data.kode_order;
                document.getElementById('add-tanggal-order').value = data.tanggal;
                document.getElementById('add-tipe-barang').value = data.tipe_barang;
                document.getElementById('add-tipe-bayar').value = data.tipe_bayar;
                document.getElementById('add-pemasok').value = data.nama_pemasok;

                document.getElementById('add-nomor-spk').value = data.kode_spk;
                document.getElementById('add-nomor-polisi').value = data.no_polisi;
                document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
                document.getElementById('add-nama-pemilik').value = data.pemilik;
                document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
                document.getElementById('add-nomor-polis').value = data.no_polis;
                document.getElementById('add-nomor-klaim').value = data.kode_claim;

                document.getElementById('add-nomor-input').value = data.kode_input;
                document.getElementById('add-tanggal-input').value = data.tanggal;
                document.getElementById('add-nomor-bon').value = data.no_bon;
                document.getElementById('add-memo').value = data.memo;
                document.getElementById('add-diskon').value = data.diskon;
                document.getElementById('add-ppn').value = data.ppn;

                setRadioValue('sifat_ppn', data.sifat_ppn);

                selected_kode_order = '';
                selected_kode_input = data.kode_input;
                if (data.kode_tipe_barang == 'S' || data.kode_tipe_barang == 'T') {
                  $('#view-spk').show();
                  selected_tipe = 'detail-spk';

                  dt_detail.column(2).visible(true);
                } else {
                  $('#view-spk').hide();
                  selected_tipe = 'detail';

                  dt_detail.column(2).visible(false);
                }

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

          // var tr = $(selectedCheckbox).closest('tr');
          // var row = dt_basic.row(tr);
          // var data2 = row.data(); // Ini berisi objek data dari baris tersebut

          // selected_kode_order = $("#add-nomor-order").val(); //data2.kode_order; // Sesuaikan dengan nama field di database/json

          // // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
          // dt_detail.ajax.reload();
        }
      });
    }

    const btnEditSelected2 = document.querySelector('.cetak-selected-order');
    if (btnEditSelected2) {
      btnEditSelected2.addEventListener('click', function () {
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
        const selectedCheckbox = document.querySelector('.datatables-order .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data Input Gudang!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // get data
          fetch(`${baseUrl}input-pembelian-list/${user_id}/edit`)
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.text();
            })
            .then(result => {
              const { status, message, data } = JSON.parse(result);

              if (status) {
                if (data.status_approve == '1') {
                  let params = {
                    id: user_id
                  };

                  // Bersihkan parameter kosong agar URL tidak terlalu panjang
                  let queryString = $.param(params);

                  // Redirect window untuk download file
                  // Pastikan route URL sesuai dengan konfigurasi route Anda
                  // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
                  const printUrl = `${baseUrl}gudang/entry-pembelian-cetak?` + queryString;
                  window.open(printUrl, '_blank');
                } else {
                  // sweetalert
                  Swal.fire({
                    icon: 'warning',
                    title: `Peringatan!`,
                    text: `Data Input Gudang masih menunggu approval`,
                    customClass: {
                      confirmButton: 'btn btn-success'
                    }
                  });
                }
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
        const selectedCheckbox = document.querySelector('.datatables-order .dt-checkboxes:checked');

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
            text: 'Anda yakin akan menghapus data ini!',
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
              fetch(`${baseUrl}input-pembelian-list/${user_id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  'Content-Type': 'application/json'
                }
              })
                .then(response => {
                  if (response.ok) {
                    fetchDashboardOrder();

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

    // changing the title
    const addNewBtn = document.querySelector('.add-new');
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

        selected_tipe = '';

        $('.btn-submit').html('Simpan');
        $('.btn-cek-spk').show();
        $('#view-spk').hide();
        $('.btn-submit').show();

        // Contoh: Hide kolom Action (Index 6 atau -1)
        dt_detail.column(2).visible(false);

        // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
        dt_detail.ajax.reload();
      });
    }

    // user form validation
    const addNewDataForm = document.getElementById('addNewDataForm');
    if (addNewDataForm) {
      const fv = FormValidation.formValidation(addNewDataForm, {
        fields: {
          kode_order: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor Purchase Order'
              }
            }
          },
          tanggal: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tanggal'
              }
            }
          },
          no_bon: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor Bon'
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

        fetch(`${baseUrl}input-pembelian-list`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: searchParams.toString()
        })
          .then(response => {
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

            if (status) {
              fetchDashboardOrder();

              // Hide offcanvas
              const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
              offcanvasInstance && offcanvasInstance.hide();

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
              text: 'Gagal simpan data baru.',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
      });
    }

    const formFilter = document.getElementById('formCariData');
    if (formFilter) {
      formFilter.addEventListener('submit', function (e) {
        selected_status = $('#filter-status-approve').val();

        e.preventDefault(); // Mencegah reload halaman

        // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
        dt_basic.draw();

        // (Opsional) Tutup modal setelah klik cari
        const modalEl = document.getElementById('filterRoleModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
          modalInstance.hide();
        }
      });
    }

    // changing the title
    const addDetBtn = document.querySelector('.add-detail');
    if (addDetBtn) {
      addDetBtn.addEventListener('click', function () {
        if (isAdd) {
          // reset seluruh form (termasuk select2)
          clearFormDataDet1();
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
        if (isEdit) {
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
          document.getElementById('add-nomor-sparepart').value = rowData.no_sparepart;
          document.getElementById('add-qty-bahan').value = rowData.qty;
          document.getElementById('add-harga-bahan').value = rowData.harga;
          document.getElementById('add-jumlah-bahan').value = rowData.jumlah;

          setSelectValue('#add-bahan', rowData.kode_bahan, rowData.nama_bahan);
          setSelectValue('#add-satuan', rowData.kode_satuan, '');

          const currentTipeBarang = $('#add-tipe-barang').val();
          if (currentTipeBarang == 'S') {
            $('#add-bahan').prop('disabled', true);
            $('#add-satuan').prop('disabled', true);
            $('#add-nomor-sparepart').prop('disabled', true);
            $('#add-qty-bahan').prop('disabled', true);
            $('#add-jumlah-bahan').prop('disabled', true);
          } else {
            $('#add-bahan').prop('disabled', false);
            $('#add-satuan').prop('disabled', false);
            $('#add-nomor-sparepart').prop('disabled', false);
            $('#add-qty-bahan').prop('disabled', false);
            $('#add-jumlah-bahan').prop('disabled', false);
          }

          document.getElementById('add-harga-bahan')?.focus();
        } else {
          // Hide offcanvas
          if (tipe == 'detail') {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
            offcanvasInstance && offcanvasInstance.hide();
          } else if (tipe == 'sparepart') {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet2);
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
          if (tipe == 'detail') {
            // 1. Ambil instance DataTable
            const tableEl = document.querySelector('.datatables-detail');
            const dt = $(tableEl).DataTable();

            // 2. Cari baris (TR) terdekat dari tombol yang diklik
            const row = $(deleteDetBtn).closest('tr');

            // 3. Hapus baris tersebut dari DataTable
            dt.row(row).remove().draw(false);
          } else if (tipe == 'sparepart') {
            // 1. Ambil instance DataTable
            const tableEl = document.querySelector('.datatables-sparepart');
            const dt = $(tableEl).DataTable();

            // 2. Cari baris (TR) terdekat dari tombol yang diklik
            const row = $(deleteDetBtn).closest('tr');

            // 3. Hapus baris tersebut dari DataTable
            dt.row(row).remove().draw(false);
          }

          // 4. Hitung ulang Total Perbaikan setelah baris hilang
          hitungTotalPerbaikan();
        } else {
          // Hide offcanvas
          if (tipe == 'detail') {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
            offcanvasInstance && offcanvasInstance.hide();
          } else if (tipe == 'sparepart') {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet2);
            offcanvasInstance && offcanvasInstance.hide();
          } else if (tipe == 'lain') {
            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet3);
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
          harga: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Harga'
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
        const cekID = $('#est_dtl1_id').val();
        if (cekID) {
          const oldData = rowToEdit.data();

          rowToEdit
            .data({
              id: oldData.id, // Pertahankan ID lama
              fake_id: oldData.fake_id, // Pertahankan Fake ID lama

              // Update field sesuai input form terbaru
              kode_bahan: $('#add-bahan option:selected').val(),
              nama_bahan: $('#add-bahan option:selected').text(),
              no_sparepart: $('#add-nomor-sparepart').val(),
              qty: $('#add-qty-bahan').val(),
              kode_satuan: $('#add-satuan option:selected').val(),
              nama_satuan: $('#add-satuan option:selected').text(),
              harga: $('#add-harga-bahan').val(),
              jumlah: $('#add-jumlah-bahan').val(),

              action: oldData.action // Pertahankan kolom action
            })
            .draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

          rowToEdit = null;

          const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
          offcanvasInstance && offcanvasInstance.hide();
        } else {
          if (dt_detail) {
            const newId = 'new_' + Date.now();
            dt_detail.row
              .add({
                id: newId,
                fake_id: '',
                kode_bahan: $('#add-bahan option:selected').val(),
                nama_bahan: $('#add-bahan option:selected').text(),
                no_sparepart: $('#add-nomor-sparepart').val(),
                qty: $('#add-qty-bahan').val(),
                kode_satuan: $('#add-satuan option:selected').val(),
                nama_satuan: $('#add-satuan option:selected').text(),
                harga: $('#add-harga-bahan').val(),
                jumlah: $('#add-jumlah-bahan').val(),
                action: ''
              })
              .draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

            // Pindah ke halaman terakhir agar baris baru terlihat (opsional)
            // dt_detail.page('last').draw(false);

            const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
            offcanvasInstance && offcanvasInstance.hide();
          }
        }
      });
    }

    const chkPPN = document.querySelectorAll('.add-ppn');
    if (chkPPN) {
      chkPPN.forEach(radio => {
        radio.addEventListener('change', function () {
          // Ambil value jika radio ini dicentang
          if (this.checked) {
            // let tipe = this.dataset.tipe;
            // let nilai = this.value;

            hitungTotalPerbaikan();
          }
        });
      });
    }

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

        selected_tipe = 'purchase-baru-all';

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
          const kode = selectedCheckbox.value;

          // reset seluruh form (termasuk select2)
          clearFormData();

          // Hide modal
          const modalInstance = bootstrap.Modal.getInstance(viewSpkModal);
          modalInstance && modalInstance.hide();

          // Show modal
          const modalInstance2 = new bootstrap.Modal(addRoleModal);
          modalInstance2.show();

          let params = {
            kode_order: kode
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);

          // Pastikan route URL sesuai dengan konfigurasi route Anda
          const url = `${baseUrl}gudang/entry-pembelian-cek-spk?` + queryString;

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

              if (status) {
                $('.btn-cek-spk').show();

                document.getElementById('add-nomor-order').value = data.kode_order;
                document.getElementById('add-nomor-order2').value = data.kode_order;
                document.getElementById('add-tanggal-order').value = data.tanggal;
                document.getElementById('add-tipe-barang').value = data.tipe_barang;
                document.getElementById('add-tipe-bayar').value = data.tipe_bayar;
                document.getElementById('add-pemasok').value = data.nama_pemasok;

                document.getElementById('add-nomor-spk').value = data.kode_spk;
                document.getElementById('add-nomor-polisi').value = data.no_polisi;
                document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
                document.getElementById('add-nama-pemilik').value = data.pemilik;
                document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
                document.getElementById('add-nomor-polis').value = data.no_polis;
                document.getElementById('add-nomor-klaim').value = data.kode_claim;
                // document.getElementById('add-ppn').value = data.ppn;

                setRadioValue('sifat_ppn', data.sifat_ppn);

                selected_kode_order = data.kode_order;
                if (data.kode_tipe_barang == 'S' || data.kode_tipe_barang == 'T') {
                  $('#view-spk').show();
                  selected_tipe = 'detail-spk';

                  dt_detail.column(2).visible(true);
                } else {
                  $('#view-spk').hide();
                  selected_tipe = 'detail';

                  dt_detail.column(2).visible(false);
                }

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

    const viewSPK2 = document.querySelector('.view-approve-po');
    if (viewSPK2) {
      viewSPK2.addEventListener('click', function () {
        selected_status = '0';
        dt_basic.ajax.reload();
      });
    }

    const viewPermintaan = document.querySelector('.view-permintaan');
    if (viewPermintaan) {
      viewPermintaan.addEventListener('click', function () {
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

        selected_tipe = 'purchase-baru';

        dt_spk.ajax.reload();
      });
    }

    // const selectSPK = $('#add-kode-spk');
    // const selectSPK = $('#view-spk');
    // const selectPermintaan = $('#add-kode-permintaan');
    const selectTipeBarang = $('#add-tipe-barang');
    if (selectTipeBarang.length) {
      // selectSPK.closest('.row').hide();
      // selectPermintaan.closest('.row').hide();
      // selectSPK.hide();
      selectTipeBarang.on('change', function () {
        const kd = $(this).val();
        if (kd == 'S') {
          // selectSPK.closest('.row').show();
          // selectPermintaan.closest('.row').show();
          // selectSPK.show();
          $('#no_sparepart').show();
          $('.add-detail').hide();
          // fetchPermintaan('#add-tipe-barang', '#add-kode-permintaan');
        } else if (kd == 'T') {
          $('#no_sparepart').show();
          $('.add-detail').show();
          // selectSPK.hide();
        } else {
          // selectSPK.closest('.row').hide();
          // selectPermintaan.closest('.row').hide();
          $('#no_sparepart').hide();
          $('.add-detail').show();
          // selectSPK.hide();

          // selected_tipe = 'detail';
          // dt_detail.column(2).visible(false);
          // dt_detail.ajax.reload();
        }
        // fetchNamaBahan('#add-tipe-barang', '#add-bahan');
      });
    }

    // if (selectPermintaan.length) {
    //   selectPermintaan.on('change', function () {
    //     const kd = $(this).val();
    //     if(kd) {

    //       fetch(`${baseUrl}gudang/get-permintaan?jenis_id=S&kode=${kd}&tipe=header`)
    //       .then(response => {
    //         if (!response.ok) throw new Error('Gagal mengambil data');
    //         return response.json();
    //       })
    //       .then(data => {

    //         document.getElementById('add-kode-spk').value = data.kode_spk;

    //       })
    //       .catch(error => {
    //         console.error('Error:', error);
    //       });

    //       selected_kode_permintaan = kd; // Sesuaikan dengan nama field di database/json
    //       selected_tipe = 'detail-permintaan';

    //       // Contoh: Hide kolom Action (Index 6 atau -1)
    //       dt_detail.column(2).visible(true);

    //       // // Reload DataTables (ini akan otomatis memanggil fungsi ajax.data di atas)
    //       dt_detail.ajax.reload();
    //     }
    //   });
    // }

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

          if (invoiceItemPrice.name == 'diskon') {
            hitungTotalPerbaikan();
          }
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
        allowClear: true, // agar placeholder tampil saat kosong
        width: '100%',
        dropdownParent: $this.parent()
      });
    });
  }

  // const selectBahan = $('#add-bahan');
  // if (selectBahan.length) {
  //   const phBahan = selectBahan.data('placeholder') || 'Pilih Nama Barang';

  //   // Tetap gunakan wrap untuk mencegah isu z-index/focus di modal Bootstrap
  //   selectBahan.wrap('<div class="position-relative"></div>').select2({
  //     placeholder: phBahan,
  //     allowClear: true,
  //     width: '100%',
  //     dropdownParent: selectBahan.parent(),
  //     minimumInputLength: 3, // Mulai cari setelah ketik 3 huruf
  //     ajax: {
  //       url: baseUrl + 'gudang/entry-pembelian-cari-bahan', // Sesuaikan dengan route API Anda
  //       dataType: 'json',
  //       delay: 250,
  //       data: function (params) {
  //         const currentTipeBarang = $('#add-tipe-barang').val();

  //         return {
  //           q: params.term, // Parameter pencarian yang dikirim ke controller
  //           tipe_barang: currentTipeBarang // Parameter pencarian yang dikirim ke controller
  //         };
  //       },
  //       processResults: function (data) {
  //         return {
  //           results: $.map(data, function (item) {
  //             return {
  //               id: item.kode_bahan,   // Nilai yang masuk ke value=""
  //               text: item.nama_bahan  // Teks yang tampil di layar
  //             }
  //           })
  //         };
  //       },
  //       cache: true
  //     }
  //   });
  // }

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
});

function fetchNamaBahan(selectorJenis, selectorBahan) {
  const jenisId = $(selectorJenis).val();
  const kdBahan = $('#kode_bahan2').val();
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
          var isSelected = item.kode_bahan == kdBahan;

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

function fetchDashboardOrder() {
  // Fetch data
  fetch(`${baseUrl}input-pembelian-list?tipe=total-data`)
    .then(response => {
      if (!response.ok) throw new Error('Gagal mengambil data');
      return response.json();
    })
    .then(data => {
      $('#total-permintaan').html(data.permintaan);
      $('#total-po-pending').html(data.po_pending);
      $('#total-po').html(data.po);
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

// function fetchPermintaan(selectorJenis, selectorBahan) {
//   const jenisId = $(selectorJenis).val();
//   const kdPermintaan = $("#kode_permintaan2").val();
//   const selectBahan = $(selectorBahan);

//   // Hanya request jika Merek DAN Jenis sudah dipilih
//   if (jenisId) {

//     // Disable sementara agar user tahu sedang loading
//     selectBahan.prop('disabled', true);

//     // Gunakan helper clearSelect yg sudah ada di kode Anda untuk reset opsi lama
//     clearSelect('#add-kode-permintaan', { keepOptions: false });

//     // Fetch data
//     fetch(`${baseUrl}gudang/get-permintaan?jenis_id=${jenisId}&kode=${kdPermintaan}&tipe=detail`)
//       .then(response => {
//         if (!response.ok) throw new Error('Gagal mengambil data');
//         return response.json();
//       })
//       .then(data => {
//         // Tambahkan opsi default
//         const defaultOption = new Option('Pilih Nomor Permintaan', '', true, true);
//         selectBahan.append(defaultOption).trigger('change');

//         // Loop data dari server dan masukkan ke select
//         data.forEach(item => {
//           // Cek apakah kode_bahan saat ini sama dengan yang dicari
//           var isSelected = (item.kode_permintaan == kdPermintaan);

//           // Pastikan key 'item.id' dan 'item.nama_tipe' sesuai return Controller
//           const newOption = new Option(item.kode_permintaan, item.kode_permintaan, false, isSelected);
//           selectBahan.append(newOption);
//         });

//         // Refresh Select2 agar opsi baru muncul
//         selectBahan.trigger('change');
//         selectBahan.prop('disabled', false); // Aktifkan kembali
//       })
//       .catch(error => {
//         console.error('Error:', error);
//         selectBahan.prop('disabled', false);
//       });

//   } else {
//     // Jika salah satu kosong, reset Tipe menjadi kosong
//     clearSelect('#add-kode-permintaan');
//   }
// }

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

function setRadioValue(name, value) {
  // 1. Cari semua radio button dengan name tersebut
  const $radios = $(`input[name="${name}"]`);

  // Guard clause: Jika elemen tidak ditemukan, stop.
  if (!$radios.length) return;

  // 2. Normalisasi value:
  // Jika null/undefined maka null, selain itu ubah ke String agar pencarian atribut akurat
  const val = value !== null && value !== undefined ? String(value) : null;

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
  //  clearSelect('#add-tipe-barang');
  //  clearSelect('#add-kode-permintaan');
  //  clearSelect('#add-tipe-bayar');
  //  clearSelect('#add-pemasok');

  //  fetchPermintaan('#add-tipe-barang', '#add-kode-permintaan');

  $('#add-nomor-order').addClass('is-invalid');
  $('#add-tanggal-input').addClass('is-invalid');
  $('#add-nomor-bon').addClass('is-invalid');

  // bersihkan error jQuery/FormValidation (kalau ada)
  // try {
  //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
  // } catch (e) {}
}

function clearFormDataDet1() {
  const form = document.getElementById('addNewDataFormDet1');
  // reset input/textarea standar
  form?.reset?.();

  $('#est_dtl1_id').val('');
  $('#kode_bahan2').val('');

  // kosongkan select (Select2)
  clearSelect('#add-bahan');
  clearSelect('#add-satuan');

  $('#add-bahan').addClass('is-invalid');
  $('#add-satuan').addClass('is-invalid');
  $('#add-qty-bahan').addClass('is-invalid');
  $('#add-harga-bahan').addClass('is-invalid');
}

function hitungTotalBarang() {
  // Ambil elemen input di baris tersebut
  let elQty = $('#add-qty-bahan');
  let elHarga = $('#add-harga-bahan');
  let elJumlah = $('#add-jumlah-bahan');

  // Ambil Nilai & Bersihkan format (hapus koma)
  let qty = parseFloat(elQty.val().replace(/,/g, '')) || 0;
  let harga = parseFloat(elHarga.val().replace(/,/g, '')) || 0;

  // Hitung Subtotal Baris
  let subtotal = qty * harga;

  // Format hasil ke string angka (misal: 2,000.00)
  let formattedSubtotal = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(subtotal);

  // Update Kolom JUMLAH di baris tersebut
  elJumlah.val(formattedSubtotal);
}

function hitungTotalPerbaikan() {
  let total = 0;
  let total_seluruh = 0;
  let grand_total = 0;
  let ppn = 0;

  let valPPN = $('#add-ppn-persen').val();
  let cleanValPPN = valPPN.replace(/,/g, '');
  let ppn_persen = (parseFloat(cleanValPPN) / 100) || 0;

  let diskon = $('#add-diskon').val();
  let cleanDisc = diskon ? diskon.replace(/,/g, '') : '0';
  diskon = parseFloat(cleanDisc) || 0;

  // Loop semua elemen dengan class 'add-jumlah'
  // $('.add-jumlah').each(function() {
  //     // Ambil value
  //     let val = $(this).val();

  //     // Bersihkan format (hapus koma) agar bisa dijumlahkan
  //     // Contoh: "405,000.00" menjadi 405000.00
  //     // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
  //     let cleanVal = val.replace(/,/g, '');

  //     // Konversi ke float, jika NaN (kosong) anggap 0
  //     let numberVal = parseFloat(cleanVal) || 0;

  //     total += numberVal;
  // });

  $('.datatables-detail .dt-checkboxes-det:checked').each(function () {
    // 1. Ambil elemen baris (TR) dari checkbox yang dipilih
    let row = $(this).closest('tr');

    // 2. Cari input hidden class '.add-point' yang ada di baris tersebut
    let pointInput = row.find('.add-jumlah');
    let val = pointInput.val();

    // 3. Bersihkan format & Konversi ke Float
    let cleanVal = val ? val.replace(/,/g, '') : '0';
    let numberVal = parseFloat(cleanVal) || 0;

    // 4. Tambahkan ke total
    total += numberVal;
  });

  $('.add-ppn:checked').each(function () {
    // Ambil value
    // let nilai_ppn = $("#add-ppn").val();
    let nilai = $(this).val();

    if (nilai == '1') {
      // ppn = (ppn_persen > 0) ? total / ppn_persen : 0;
      ppn = ppn_persen > 0 ? (total - diskon) * ppn_persen : 0;
    }

    // let cleanVal = nilai_ppn ? nilai_ppn.replace(/,/g, '') : '0';
    // nilai_ppn = parseFloat(cleanVal);

    // if(nilai_ppn > 0) {
    //   ppn = nilai_ppn;
    // } else {
    //   if (nilai == "1") {
    //     // ppn = (ppn_persen > 0) ? total / ppn_persen : 0;
    //     ppn = (ppn_persen > 0) ? (total - diskon) / ppn_persen : 0;
    //   }
    // }
  });

  if (total > 0) {
    total_seluruh = total - diskon;
    grand_total = total - diskon + ppn;
  } else {
    total = 0;
    diskon = 0;
    ppn = 0;
    total_seluruh = 0;
    grand_total = 0;
    $('#add-diskon').val(diskon);
  }

  // Format kembali ke tampilan angka (misal: 1,000,000.00)
  let formattedTotal = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total);

  // Format kembali ke tampilan angka (misal: 1,000,000.00)
  let formattedPPN = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(ppn);

  // Format kembali ke tampilan angka (misal: 1,000,000.00)
  let formattedTotalTagihan = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(total_seluruh);

  let formattedGrandTotal = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(grand_total);

  // Update teks di elemen footer
  // $('#total-detail').text(formattedTotal);
  $('#add-subtotal').val(formattedTotal);
  $('#add-subtotal2').val(formattedTotalTagihan);
  $('#add-ppn').val(formattedPPN);
  $('#add-total-detail').val(formattedGrandTotal);
}
