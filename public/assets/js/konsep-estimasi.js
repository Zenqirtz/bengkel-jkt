/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete, fvPerbaikan, fvSparepart, fvLain;
  let selected_kode_spk = '';
  let selected_kode_cabang = '';
  let selected_kode_status_spk = '';
  let rowToEdit = null;
  let allowModalClose = false;

  const today = new Date();
  const dd = String(today.getDate()).padStart(2, '0');
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const yyyy = today.getFullYear();
  const tanggalHariIni = dd + '/' + mm + '/' + yyyy;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-spk'),
     addRoleModal = document.getElementById('addRoleModal'),
     offCanvasFormDet1 = document.getElementById('offcanvasAddDet1'),
     offCanvasFormDet2 = document.getElementById('offcanvasAddDet2'),
     offCanvasFormDet3 = document.getElementById('offcanvasAddDet3'),
    //  statusObj = {
    //   '01': { class: 'text-bg-success' },
    //   '02': { class: 'text-bg-info' },
    // };
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
 
   // COA datatable
   if (dt_basic_table) {
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
         url: baseUrl + 'konsep-estimasi-list',
         data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.kode_spk = $('#filter-nomor-spk').val();
            d.no_polisi = $('#filter-no-polisi').val();
            d.tgl_masuk_awal = $('#filter-tgl-spk-awal').val();
            d.tgl_masuk_akhir = $('#filter-tgl-spk-akhir').val();
            d.nama_pelanggan = $('#filter-nama-pelanggan').val();
            d.nama_pemilik = $('#filter-nama-pemilik').val();
            d.no_polis = $('#filter-no-polis').val();
            d.kode_claim = $('#filter-no-klaim').val();
            d.status_spk = $('#filter-status-spk').val(); // Pastikan value select2 terambil
            d.status = $('#filter-status').val();
            d.tipe = 'estimasi';
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
          { data: 'action' }
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
        //  {
        //    targets: 2,
        //    responsivePriority: 2
        //  },
        //  {
        //    targets: 3,
        //    responsivePriority: 3
        //  },
         {
           // Actions
           //  title: 'Actions',
           targets: -1,
           searchable: false,
           orderable: false,
           visible: false,
           render: function (data, type, full, meta) {
             return (
               '<div class="d-flex align-items-center gap-4">' +
               `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-record" data-id="${full['id']}" data-bs-toggle="modal" data-bs-target="#addRoleModal" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
               `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-record" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
               '</div>'
             );
           }
         }
       ],
       // scrollY: '300px',
       scrollX: true,
       order: [[1, 'desc']],
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
      //          return 'Details of ' + data['kode_spk'];
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

     // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.dt-checkboxes');
      if (chk) {
        if (chk.checked) {
            $('.dt-checkboxes').not(chk).prop('checked', false);
        }
      }
    });
 
    // Delete Record
    document.addEventListener('click', function (e) {
      if (e.target.closest('.delete-record')) {
        if (isDelete) {
          const deleteBtn = e.target.closest('.delete-record');
          const user_id = deleteBtn.dataset.id;
          const dtrModal = document.querySelector('.dtr-bs-modal.show');
  
          // hide responsive modal in small screen
          if (dtrModal) {
            const bsModal = bootstrap.Modal.getInstance(dtrModal);
            bsModal.hide();
          }
  
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
              fetch(`${baseUrl}konsep-estimasi-list/${user_id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  'Content-Type': 'application/json'
                }
              })
                .then(response => {
                  if (response.ok) {
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
        } else {
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

    // edit record
    document.addEventListener('click', function (e) {
      if (e.target.closest('.edit-record')) {
        if(isEdit) {
          const editBtn = e.target.closest('.edit-record');
          const user_id = editBtn.dataset.id;
          const dtrModal = document.querySelector('.dtr-bs-modal.show');
  
          // hide responsive modal in small screen
          if (dtrModal) {
            const bsModal = bootstrap.Modal.getInstance(dtrModal);
            bsModal.hide();
          }

          // get data
          fetch(`${baseUrl}konsep-estimasi-list/${user_id}/edit`)
            .then(response => response.json())
            .then(data => {
              document.getElementById('user_id').value = data.id;
              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-no-polis').value = data.no_polis;
              document.getElementById('add-no-klaim').value = data.kode_claim;
              document.getElementById('add-tertanggung').value = data.tertanggung;
              document.getElementById('add-catatan-khusus').value = data.catatan_khusus;
              document.getElementById('add-jenis-perbaikan').value = data.jenis_perbaikan;

              setSelectValue('#add-status-spk', data.kode_status_spk,  '');
              setSelectValue('#add-jenis-asuransi', data.kode_jenis_pelanggan,  '');
              setSelectValue('#add-pelanggan', data.kode_pelanggan,  '');
              setSelectValue('#add-perantara', data.kode_perantara,  '');
              setSelectValue('#add-marketing', data.kode_marketing,  '');
              setSelectValue('#add-jenis-polis', data.kode_jenis_polis,  '');

              setSelectValue('#add-nopolisi', data.no_polisi,  '');
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

    const btnEditSelected = document.querySelector('.edit-selected-spk');
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
        const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data SPK pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          clearFormData();

          // 1. Buka Modal secara manual
          const modalInstance = new bootstrap.Modal(addRoleModal);
          modalInstance.show();

          PleaseWaitPage();

          // get data
          fetch(`${baseUrl}konsep-estimasi-list/${user_id}/edit`)
            .then(response => response.json())
            .then(data => {
              
              if (document.querySelector(`.notiflix-loading`)) {
                Loading.remove();
              }

              document.getElementById('user_id').value = data.id;
              document.getElementById('konsep_id').value = data.konsep_id;
              document.getElementById('add-nomor-konsep').value = data.kode_konsep_estimasi;
              document.getElementById('add-tanggal-konsep').value = data.tgl_konsep;
              document.getElementById('add-nomor-spk').value = data.kode_spk;
              document.getElementById('add-nomor-spk2').value = data.kode_spk;
              document.getElementById('add-nomor-polisi').value = data.no_polisi;
              document.getElementById('add-nama-pemilik').value = data.pemilik;
              document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
              document.getElementById('add-nomor-polis').value = data.no_polis;
              document.getElementById('add-nomor-klaim').value = data.kode_claim;
              document.getElementById('add-tahun').value = data.tahun;
              document.getElementById('add-tahun2').value = data.tahun;
              document.getElementById('add-lama-pekerjaan').value = data.lama_pekerjaan;
              document.getElementById('add-surveyor').value = data.nama_surveyor;
              document.getElementById('add-tanggal-survey').value = data.tgl_survey;
              document.getElementById('add-memo').value = data.memo;
              document.getElementById('add-pelanggan').value = data.kode_pelanggan;

              setSelectValue('#add-estimator', data.kode_estimator, '');
              setSelectValue('#add-pelanggan2', data.kode_pelanggan, '');
            });

          var tr = $(selectedCheckbox).closest('tr');
          var row = dt_basic.row(tr); 
          var data2 = row.data(); // Ini berisi objek data dari baris tersebut

          selected_kode_spk = data2.kode_spk; // Sesuaikan dengan nama field di database/json
          selected_kode_cabang = data2.kode_cabang;
          selected_kode_status_spk = data2.kode_status_spk;

          if(selected_kode_status_spk == '01' || selected_kode_status_spk == '02') {
            $('.btn-submit, .add-detail, .edit-detail, .delete-detail').prop('disabled', false);
          } else {
            $('.btn-submit, .add-detail, .edit-detail, .delete-detail').prop('disabled', true);
          }

          let dt_perbaikan;
          const dt_perbaikan_table = document.querySelector('.datatables-perbaikan');
          if (dt_perbaikan_table) {
            dt_perbaikan = new DataTable(dt_perbaikan_table, {
              searching: false,  // Opsi ini akan menghilangkan input cari
              ordering: false,    // Opsi lain tetap bisa jalan
              paging: false,
              info: false,
              processing: true,
              serverSide: false,
              destroy: true, // Fix Error reinitialise
              ajax: {
                url: baseUrl + 'konsep-estimasi-list',
                data: function (d) {
                  // Ambil data dari input form modal dan masukkan ke parameter request
                  d.kode_spk = selected_kode_spk;
                  d.kode_cabang = selected_kode_cabang;
                  d.tipe = 'konsep-estimasi-perbaikan';
                }
              },
              columns: [
                // columns according to JSON
                { data: 'id' },
                { data: 'jenis_pekerjaan' },
                { data: 'panel_pekerjaan' },
                { data: 'harga' },
                { data: 'tipe' },
                { data: 'action' }
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
                    const tgl = full['created_at'];
                    const usr = full['created_by'];

                    const rowOutput = `
                      <input type="hidden" name="pekerjaan[${full.id}][cek]" value="${full.cek}" />
                      <input type="hidden" name="pekerjaan[${full.id}][jenis]" value="${full.kode_jenis_pekerjaan}" />
                      <div class="d-flex justify-content-start align-items-center user-name">
                        <div class="d-flex flex-column">
                          <span class="emp_name text-truncate h6 mb-0">${data}</span>
                          <small class="emp_post text-truncate">Tgl Buat: ${tgl}</small>
                          <small class="emp_post text-truncate">Dibuat oleh: ${usr}</small>
                        </div>
                      </div>
                    `;

                    return rowOutput;
                    // return `
                    //   ${data} 
                    //   <input type="hidden" name="pekerjaan[${full.id}][jenis]" value="${full.kode_jenis_pekerjaan}" />
                    // `; 
                  }
                },
                {
                  targets: 2,
                  render: function (data, type, full, meta) {
                    return `
                      ${data} 
                      <input type="hidden" name="pekerjaan[${full.id}][panel]" value="${full.kode_panel_pekerjaan}" />
                    `; 
                  }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) {
                      let val = data || 0; 
                      return `
                        ${data} 
                        <input type="hidden" name="pekerjaan[${full.id}][harga]" class="add-harga" value="${data}" />
                      `;  
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                      return `
                        ${data} 
                        <input type="hidden" name="pekerjaan[${full.id}][tipe]" value="${data}" />
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
                    return (
                      '<div class="d-flex align-items-center gap-4">' +
                      `<button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-detail" data-tipe="perbaikan" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet1" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
                      `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-detail" data-tipe="perbaikan" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
                      '</div>'
                    );
                  }
                }
              ],
              drawCallback: function() {
                disabledButton(selected_kode_status_spk);
                hitungTotalPerbaikan();
              },
            });
          }

          let dt_sparepart;
          const dt_sparepart_table = document.querySelector('.datatables-sparepart');
          if (dt_sparepart_table) {
            dt_sparepart = new DataTable(dt_sparepart_table, {
              searching: false,  // Opsi ini akan menghilangkan input cari
              ordering: false,    // Opsi lain tetap bisa jalan
              paging: false,
              info: false,
              processing: true,
              serverSide: false,
              destroy: true, // Fix Error reinitialise
              ajax: {
                url: baseUrl + 'konsep-estimasi-list',
                data: function (d) {
                  // Ambil data dari input form modal dan masukkan ke parameter request
                  d.kode_spk = selected_kode_spk;
                  d.kode_cabang = selected_kode_cabang;
                  d.tipe = 'konsep-estimasi-sparepart';
                }
              },
              columns: [
                // columns according to JSON
                { data: 'id' },
                { data: 'nama_sparepart' },
                { data: 'no_sparepart' },
                { data: 'qty' },
                { data: 'harga' },
                { data: 'jumlah' },
                { data: 'tipe' },
                { data: 'action' }
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
                    const tgl = full['created_at'];
                    const usr = full['created_by'];

                    const rowOutput = `
                      <input type="hidden" name="sparepart[${full.id}][cek]" value="${full.cek}" />
                      <input type="hidden" name="sparepart[${full.id}][kode_sparepart]" value="${full.kode_sparepart}" />
                      <div class="d-flex justify-content-start align-items-center user-name">
                        <div class="d-flex flex-column">
                          <span class="emp_name text-truncate h6 mb-0">${data}</span>
                          <small class="emp_post text-truncate">Tgl Buat: ${tgl}</small>
                          <small class="emp_post text-truncate">Dibuat oleh: ${usr}</small>
                        </div>
                      </div>
                    `;

                    return rowOutput;
                    // return `
                    //   ${data} 
                    //   <input type="hidden" name="sparepart[${full.id}][kode_sparepart]" value="${full.kode_sparepart}" />
                    // `; 
                  }
                },
                {
                  targets: 2,
                  render: function (data, type, full, meta) {
                    return `
                      ${data} 
                      <input type="hidden" name="sparepart[${full.id}][no_sparepart]" value="${data}" />
                    `; 
                  }
                },
                {
                  targets: 3,
                  render: function (data, type, full, meta) {
                    let val = data || 0; 
                    return `
                      ${data} 
                      <input type="hidden" name="sparepart[${full.id}][qty]" class="add-qty-sparepart" value="${data}" />
                    `;  
                  }
                },
                {
                  targets: 4,
                  render: function (data, type, full, meta) {
                    let val = data || 0; 
                    return `
                      ${data} 
                      <input type="hidden" name="sparepart[${full.id}][harga]" class="add-harga-sparepart" value="${data}" />
                    `; 
                  }
                },
                {
                  targets: 5,
                  render: function (data, type, full, meta) {
                    let val = data || 0; 
                    return `
                      ${data} 
                      <input type="hidden" name="sparepart[${full.id}][jumlah]" class="add-jumlah-sparepart" value="${data}" />
                    `;
                  }
                },
                {
                  targets: 6,
                  render: function (data, type, full, meta) {
                    return `
                      ${data} 
                      <input type="hidden" name="sparepart[${full.id}][tipe]" value="${data}" />
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
                    return (
                      '<div class="d-flex align-items-center gap-4">' +
                      `<button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-detail" data-tipe="sparepart" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet2" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
                      `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-detail" data-tipe="sparepart" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
                      '</div>'
                    );
                  }
                }
              ],
              drawCallback: function() {
                disabledButton(selected_kode_status_spk);
                hitungTotalPerbaikan(); 
              },
            });
          }

          let dt_lain;
          const dt_lain_table = document.querySelector('.datatables-lain');
          if (dt_lain_table) {
            dt_lain = new DataTable(dt_lain_table, {
              searching: false,  // Opsi ini akan menghilangkan input cari
              ordering: false,    // Opsi lain tetap bisa jalan
              paging: false,
              info: false,
              processing: true,
              serverSide: false,
              destroy: true, // Fix Error reinitialise
              ajax: {
                url: baseUrl + 'konsep-estimasi-list',
                data: function (d) {
                  // Ambil data dari input form modal dan masukkan ke parameter request
                  d.kode_spk = selected_kode_spk;
                  d.kode_cabang = selected_kode_cabang;
                  d.tipe = 'konsep-estimasi-lain';
                }
              },
              columns: [
                // columns according to JSON
                { data: 'id' },
                { data: 'memo' },
                { data: 'harga' },
                { data: 'tipe' },
                { data: 'action' }
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
                    const tgl = full['created_at'];
                    const usr = full['created_by'];

                    const rowOutput = `
                      <input type="hidden" name="lain[${full.id}][cek]" value="${full.cek}" />
                      <input type="hidden" name="lain[${full.id}][memo]" value="${data}" />
                      <div class="d-flex justify-content-start align-items-center user-name">
                        <div class="d-flex flex-column">
                          <span class="emp_name text-truncate h6 mb-0">${data}</span>
                          <small class="emp_post text-truncate">Tgl Buat: ${tgl}</small>
                          <small class="emp_post text-truncate">Dibuat oleh: ${usr}</small>
                        </div>
                      </div>
                    `;

                    return rowOutput;
                    // return `
                    //   ${data} 
                    //   <input type="hidden" name="lain[${full.id}][memo]" value="${data}" />
                    // `;
                  }
                },
                {
                  targets: 2,
                  render: function (data, type, full, meta) {
                    let val = data || 0; 
                    return `
                      ${data} 
                      <input type="hidden" name="lain[${full.id}][harga]" class="add-harga-lain" value="${data}" />
                    `;
                  }
                },
                {
                  targets: 3,
                  render: function (data, type, full, meta) {
                    return `
                      ${data} 
                      <input type="hidden" name="lain[${full.id}][tipe]" value="${data}" />
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
                    return (
                      '<div class="d-flex align-items-center gap-4">' +
                      `<button type="button" class="btn btn-icon btn-text-secondary btn-sm rounded-pill edit-detail" data-tipe="lain" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDet3" title="Ubah"><i class="icon-base ri ri-edit-box-line icon-22px"></i></button>` +
                      `<button class="btn btn-icon btn-text-secondary btn-sm rounded-pill delete-detail" data-tipe="lain" data-id="${full['id']}" title="Hapus"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></button>` +
                      '</div>'
                    );
                  }
                }
              ],
              drawCallback: function() {
                disabledButton(selected_kode_status_spk);
                hitungTotalPerbaikan();
              },
            });
          }

          document.addEventListener('click', function (e) {
            // Cek apakah yang diklik adalah tombol delete atau icon di dalamnya
            const addDetBtn = e.target.closest('.add-detail');
            if (addDetBtn) {
              const tipe = addDetBtn.dataset.tipe;

              if(isAdd) {
                // reset seluruh form (termasuk select2)
                if (tipe == "perbaikan") {
                  clearFormDataDet1();
                } else if (tipe == "sparepart") {
                  clearFormDataDet2();
                } else if (tipe == "lain") {
                  clearFormDataDet3();
                }
              } else {
                // Hide offcanvas
                if (tipe == "perbaikan") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
                  offcanvasInstance && offcanvasInstance.hide();
                } else if (tipe == "sparepart") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet2);
                  offcanvasInstance && offcanvasInstance.hide();
                } else if (tipe == "lain") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet3);
                  offcanvasInstance && offcanvasInstance.hide();
                }
                
                Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: 'Anda tidak memiliki izin untuk akses tambah data',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              }
            }

            const editDetBtn = e.target.closest('.edit-detail');
            if (editDetBtn) {
              if(isEdit) {
                const tipe = editDetBtn.dataset.tipe;
                const user_id = editDetBtn.dataset.id;

                if (tipe == "perbaikan") {
                  // 1. Ambil instance DataTable
                  const tableEl = document.querySelector('.datatables-perbaikan');
                  const dt = $(tableEl).DataTable();
      
                  // 2. Cari baris (TR) terdekat dari tombol yang diklik
                  const row = $(editDetBtn).closest('tr');

                  // 3. AMBIL DATA (Tambahkan .data() di akhir)
                  const rowData = dt.row(row).data();

                  rowToEdit = dt.row(row);

                  document.getElementById('est_dtl1_id').value = rowData.id;
                  document.getElementById('add-harga').value = rowData.harga;
      
                  setSelectValue('#add-jenis-pekerjaan', rowData.kode_jenis_pekerjaan, '');
                  setSelectValue('#add-panel-pekerjaan', rowData.kode_panel_pekerjaan, '');
                  setSelectValue('#add-tipe-pekerjaan', rowData.tipe, '');
                } else if (tipe == "sparepart") {
                  // 1. Ambil instance DataTable
                  const tableEl = document.querySelector('.datatables-sparepart');
                  const dt = $(tableEl).DataTable();
      
                  // 2. Cari baris (TR) terdekat dari tombol yang diklik
                  const row = $(editDetBtn).closest('tr');

                  // 3. AMBIL DATA (Tambahkan .data() di akhir)
                  const rowData = dt.row(row).data();

                  rowToEdit = dt.row(row);

                  document.getElementById('est_dtl2_id').value = rowData.id;
                  document.getElementById('add-nomor-sparepart').value = rowData.no_sparepart;
                  document.getElementById('add-qty-sparepart').value = rowData.qty;
                  document.getElementById('add-harga-sparepart').value = rowData.harga;
                  document.getElementById('add-jumlah-sparepart').value = rowData.jumlah;
      
                  setSelectValue('#add-kode-sparepart', rowData.kode_sparepart, '');
                  setSelectValue('#add-tipe-sparepart', rowData.tipe, '');
                } else if (tipe == "lain") {
                  // 1. Ambil instance DataTable
                  const tableEl = document.querySelector('.datatables-lain');
                  const dt = $(tableEl).DataTable();
      
                  // 2. Cari baris (TR) terdekat dari tombol yang diklik
                  const row = $(editDetBtn).closest('tr');

                  // 3. AMBIL DATA (Tambahkan .data() di akhir)
                  const rowData = dt.row(row).data();

                  rowToEdit = dt.row(row);

                  document.getElementById('est_dtl3_id').value = rowData.id;
                  document.getElementById('add-memo-lain').value = rowData.memo;
                  document.getElementById('add-harga-lain').value = rowData.harga;
      
                  setSelectValue('#add-tipe-lain', rowData.tipe, '');
                }
    
              } else {
                // Hide offcanvas
                if (tipe == "perbaikan") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
                  offcanvasInstance && offcanvasInstance.hide();
                } else if (tipe == "sparepart") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet2);
                  offcanvasInstance && offcanvasInstance.hide();
                } else if (tipe == "lain") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet3);
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
              if(isDelete) {
                const tipe = deleteDetBtn.dataset.tipe;

                if (tipe == "perbaikan") {
                  // 1. Ambil instance DataTable
                  const tableEl = document.querySelector('.datatables-perbaikan');
                  const dt = $(tableEl).DataTable();

                  // 2. Cari baris (TR) terdekat dari tombol yang diklik
                  const row = $(deleteDetBtn).closest('tr');
      
                  // 3. Hapus baris tersebut dari DataTable
                  dt.row(row).remove().draw(false);

                } else if (tipe == "sparepart") {
                  // 1. Ambil instance DataTable
                  const tableEl = document.querySelector('.datatables-sparepart');
                  const dt = $(tableEl).DataTable();

                  // 2. Cari baris (TR) terdekat dari tombol yang diklik
                  const row = $(deleteDetBtn).closest('tr');
      
                  // 3. Hapus baris tersebut dari DataTable
                  dt.row(row).remove().draw(false);

                } else if (tipe == "lain") {
                  // 1. Ambil instance DataTable
                  const tableEl = document.querySelector('.datatables-lain');
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
                if (tipe == "perbaikan") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
                  offcanvasInstance && offcanvasInstance.hide();
                } else if (tipe == "sparepart") {
                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet2);
                  offcanvasInstance && offcanvasInstance.hide();
                } else if (tipe == "lain") {
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
                kode_jenis_pekerjaan: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Jenis Pekerjaan'
                    }
                  }
                },
                kode_panel_pekerjaan: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Panel Pekerjaan'
                    }
                  }
                },
                harga: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Harga'
                    }
                  }
                },
                tipe: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Tipe Pekerjaan'
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
              const cekID = $("#est_dtl1_id").val();
              if(cekID) {

                const oldData = rowToEdit.data();

                rowToEdit.data({
                  id: oldData.id,           // Pertahankan ID lama
                  fake_id: oldData.fake_id, // Pertahankan Fake ID lama
                  
                  // Update field sesuai input form terbaru
                  kode_jenis_pekerjaan: $("#add-jenis-pekerjaan option:selected").val(),
                  kode_panel_pekerjaan: $("#add-panel-pekerjaan option:selected").val(),
                  jenis_pekerjaan: $("#add-jenis-pekerjaan option:selected").text(),
                  panel_pekerjaan: $("#add-panel-pekerjaan option:selected").text(),
                  harga: $("#add-harga").val(),
                  tipe: $("#add-tipe-pekerjaan option:selected").text(),
                  cek: '1',
                  created_at: tanggalHariIni, 
                  created_by: authUsername,
                  
                  action: oldData.action    // Pertahankan kolom action
                }).draw(false); // Draw false agar tidak reset paging

                rowToEdit = null;

                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
                offcanvasInstance && offcanvasInstance.hide();
                
              } else {
                if (dt_perbaikan) {
                  const newId = 'new_' + Date.now();

                  dt_perbaikan.row.add({
                      id: newId, 
                      fake_id: '', 
                      kode_jenis_pekerjaan: $("#add-jenis-pekerjaan option:selected").val(),
                      kode_panel_pekerjaan: $("#add-panel-pekerjaan option:selected").val(),
                      jenis_pekerjaan: $("#add-jenis-pekerjaan option:selected").text(),
                      panel_pekerjaan: $("#add-panel-pekerjaan option:selected").text(),
                      harga: $("#add-harga").val(),
                      tipe: $("#add-tipe-pekerjaan option:selected").text(),
                      cek: '1',
                      created_at: tanggalHariIni, 
                      created_by: authUsername,
                      action: '' 
                  }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

                  // Pindah ke halaman terakhir agar baris baru terlihat (opsional)
                  // dt_perbaikan.page('last').draw(false);

                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
                  offcanvasInstance && offcanvasInstance.hide();
                }
              }
            });
          }
          
          const addNewDataFormDet2 = document.getElementById('addNewDataFormDet2');
          if (addNewDataFormDet2) {
            if (fvSparepart) {
              fvSparepart.destroy(); // Hancurkan instance lama agar tidak menumpuk
            }
            
            fvSparepart = FormValidation.formValidation(addNewDataFormDet2, {
              fields: {
                kode_sparepart: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Nama Sparepart'
                    }
                  }
                },
                no_sparepart: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Nomor Sparepart'
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
                },
                jumlah: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Jumlah'
                    }
                  }
                },
                tipe: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Tipe Pekerjaan'
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
              const cekID = $("#est_dtl2_id").val();
              if(cekID) {

                const oldData = rowToEdit.data();

                rowToEdit.data({
                  id: oldData.id,           // Pertahankan ID lama
                  fake_id: oldData.fake_id, // Pertahankan Fake ID lama
                  
                  // Update field sesuai input form terbaru
                  kode_sparepart: $("#add-kode-sparepart option:selected").val(),
                  nama_sparepart: $("#add-kode-sparepart option:selected").text(),
                  no_sparepart: $("#add-nomor-sparepart").val(),
                  qty: $("#add-qty-sparepart").val(),
                  harga: $("#add-harga-sparepart").val(),
                  jumlah: $("#add-jumlah-sparepart").val(),
                  tipe: $("#add-tipe-sparepart option:selected").text(),
                  cek: '1',
                  created_at: tanggalHariIni, 
                  created_by: authUsername,
                  
                  action: oldData.action    // Pertahankan kolom action
                }).draw(false); // Draw false agar tidak reset paging

                rowToEdit = null;

                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet2);
                offcanvasInstance && offcanvasInstance.hide();
                
              } else {
                if (dt_sparepart) {
                  const newId = 'new_' + Date.now();

                  dt_sparepart.row.add({
                      id: newId, 
                      fake_id: '', 
                      kode_sparepart: $("#add-kode-sparepart option:selected").val(),
                      nama_sparepart: $("#add-kode-sparepart option:selected").text(),
                      no_sparepart: $("#add-nomor-sparepart").val(),
                      qty: $("#add-qty-sparepart").val(),
                      harga: $("#add-harga-sparepart").val(),
                      jumlah: $("#add-jumlah-sparepart").val(),
                      tipe: $("#add-tipe-sparepart option:selected").text(),
                      cek: '1',
                      created_at: tanggalHariIni, 
                      created_by: authUsername,
                      action: '' 
                  }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

                  // Pindah ke halaman terakhir agar baris baru terlihat (opsional)
                  // dt_sparepart.page('last').draw(false);

                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet2);
                  offcanvasInstance && offcanvasInstance.hide();
                }
              }
            });
          }

          const addNewDataFormDet3 = document.getElementById('addNewDataFormDet3');
          if (addNewDataFormDet3) {
            if (fvLain) {
              fvLain.destroy(); // Hancurkan instance lama agar tidak menumpuk
            }
            
            fvLain = FormValidation.formValidation(addNewDataFormDet3, {
              fields: {
                memo: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Pekerjaan Lain-lain'
                    }
                  }
                },
                harga: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Harga'
                    }
                  }
                },
                tipe: {
                  validators: {
                    notEmpty: {
                      message: 'Silahkan Input Tipe Pekerjaan'
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
              const cekID = $("#est_dtl3_id").val();
              if(cekID) {

                const oldData = rowToEdit.data();

                rowToEdit.data({
                  id: oldData.id,           // Pertahankan ID lama
                  fake_id: oldData.fake_id, // Pertahankan Fake ID lama
                  
                  // Update field sesuai input form terbaru
                  memo: $("#add-memo-lain").val(),
                  harga: $("#add-harga-lain").val(),
                  tipe: $("#add-tipe-lain option:selected").text(),
                  cek: '1',
                  created_at: tanggalHariIni, 
                  created_by: authUsername,
                  
                  action: oldData.action    // Pertahankan kolom action
                }).draw(false); // Draw false agar tidak reset paging

                rowToEdit = null;

                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet3);
                offcanvasInstance && offcanvasInstance.hide();
                
              } else {
                if (dt_lain) {
                  const newId = 'new_' + Date.now();

                  dt_lain.row.add({
                      id: newId, 
                      fake_id: '', 
                      memo: $("#add-memo-lain").val(),
                      harga: $("#add-harga-lain").val(),
                      tipe: $("#add-tipe-lain option:selected").text(),
                      cek: '1',
                      created_at: tanggalHariIni, 
                      created_by: authUsername,
                      action: '' 
                  }).draw(false); // .draw(false) agar tidak mereset paging kembali ke halaman 1

                  // Pindah ke halaman terakhir agar baris baru terlihat (opsional)
                  // dt_lain.page('last').draw(false);

                  const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet3);
                  offcanvasInstance && offcanvasInstance.hide();
                }
              }
            });
          }
        }
      });
    }

    const btnEditSelected2 = document.querySelector('.cetak-konsep');
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
        const selectedCheckbox = document.querySelector('.datatables-spk .dt-checkboxes:checked');

        if (!selectedCheckbox) {
          // Jika tidak ada yang dipilih
          Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Silahkan pilih (checklist) data SPK pada tabel terlebih dahulu!',
            customClass: { confirmButton: 'btn btn-primary' }
          });
        } else {
          // Jika ada yang dipilih
          const user_id = selectedCheckbox.value;

          // get data
          fetch(`${baseUrl}konsep-estimasi-list/${user_id}`)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.text();
          })
          .then(result => {
            const { status, message, data } = JSON.parse(result);
  
            if(status) {
             
              let params = {
                id: data.id
              };
    
              // Bersihkan parameter kosong agar URL tidak terlalu panjang
              let queryString = $.param(params);
              
              // Redirect window untuk download file
              // Pastikan route URL sesuai dengan konfigurasi route Anda
              // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
              const printUrl = `${baseUrl}administrasi/cetak-konsep-estimasi?` + queryString;
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
            Swal.fire({
              title: 'Error!',
              text: 'Gagal Cetak Konsep',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          });
        }
      });
    }
 
    // changing the title
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
        if(isAdd) {
          // reset seluruh form (termasuk select2)
          clearFormData();
        } else {
          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
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

    // user form validation
    const addNewDataForm = document.getElementById('addNewDataForm');
    if (addNewDataForm) {
      const fv = FormValidation.formValidation(addNewDataForm, {
        fields: {
          kode_estimator: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nama Estimator'
              }
            }
          },
          // kode_pelanggan: {
          //   validators: {
          //     notEmpty: {
          //       message: 'Silahkan Input Nama Asuransi'
          //     }
          //   }
          // },
          nama_surveyor: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nama Surveyor'
              }
            }
          },
          tgl_survey: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tanggal Survey'
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
        allowModalClose = true;
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

        fetch(`${baseUrl}konsep-estimasi-list`, {
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

          
          if(status) {
            // Refresh DataTable
            dt_basic_table && new DataTable(dt_basic_table).draw();

            // Hide offcanvas
            const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
            offcanvasInstance && offcanvasInstance.hide();

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

    const formFilter = document.getElementById('formFilterSpk');
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

    const chkPPN = document.querySelectorAll('.add-ppn');
    if (chkPPN) {
      chkPPN.forEach(radio => {
        radio.addEventListener('change', function() {
          // Ambil value jika radio ini dicentang
          if (this.checked) {
              // let tipe = this.dataset.tipe;
              // let nilai = this.value;
              
              hitungTotalPerbaikan()
          }
        });
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
        });
      }
    });
  }

  const invoiceItemPriceSparepartList = document.querySelectorAll('.invoice-price-sparepart');
  if (invoiceItemPriceSparepartList) {
    invoiceItemPriceSparepartList.forEach(function (invoiceItemPriceSparepart) {
      if (invoiceItemPriceSparepart) {
        invoiceItemPriceSparepart.addEventListener('input', event => {
          invoiceItemPriceSparepart.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });

          hitungEstimasiSparepart();
        });
      }
    });
  }

  const invoiceDiskonList = document.querySelectorAll('.invoice-diskon');
  if (invoiceDiskonList) {
    invoiceDiskonList.forEach(function (invoiceDiskon) {
      if (invoiceDiskon) {
        invoiceDiskon.addEventListener('input', event => {
          invoiceDiskon.value = formatNumeral(event.target.value, {
            delimiter: ',',
            numeral: true
          });

          hitungTotalPerbaikan();
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

    // Number Only
  const numberMaskList = document.querySelectorAll('.text-number');
  if (numberMaskList) {
    numberMaskList.forEach(function (numberMask) {
      numberMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        numberMask.value = formatGeneral(cleanValue, {
          blocks: [99],
          delimiters: ['']
        });
      });
      registerCursorTracker({
        input: numberMask,
        delimiter: ''
      });
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
  
  $('#addRoleModal').on('hide.bs.modal', function (e) {
    // 1. Cek apakah form dalam mode "Lihat" (Read-Only)
    // Kita deteksi dari tombol submit yang disembunyikan di script sebelumnya
    let isReadOnly = $(this).find('button[type="submit"]').is(':hidden');

    // Jika mode Lihat ATAU sudah diizinkan lewat SweetAlert, biarkan modal tertutup
    if (isReadOnly || allowModalClose) {
        allowModalClose = false; // Reset nilai untuk penggunaan berikutnya
        return; 
    }

    // 2. Jika mode Tambah/Edit, cegah modal tertutup
    e.preventDefault();

    // 3. Tampilkan SweetAlert Konfirmasi
    Swal.fire({
        title: 'Tutup Form?',
        text: "Yakin akan tutup form ini? Data yang belum disimpan mungkin akan hilang.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tutup',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.isConfirmed) {
            allowModalClose = true; // Berikan izin tutup
            $('#addRoleModal').modal('hide'); // Perintahkan modal untuk tutup kembali
        }
    });
  });

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
   clearSelect('#add-estimator');
   clearSelect('#add-pelanggan');
  //  clearSelect('#add-surveyor');

  // $('#add-estimator').addClass('input-wajib');
  // $('#add-pelanggan').addClass('input-wajib');
  // $('#add-surveyor').addClass('input-wajib');
  // $('#add-tanggal-survey').addClass('input-wajib');
 
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
    $("#jenis_pekerjaan").val('');
    $("#panel_pekerjaan").val('');

    // kosongkan select (Select2)
    clearSelect('#add-jenis-pekerjaan');
    clearSelect('#add-panel-pekerjaan');
    clearSelect('#add-tipe-pekerjaan');
  }

  function clearFormDataDet2() {
    const form = document.getElementById('addNewDataFormDet2');
    // reset input/textarea standar
    form?.reset?.();

    $("#est_dtl2_id").val('');
    $("#nama_pekerjaan").val('');

    // kosongkan select (Select2)
    clearSelect('#add-kode-sparepart');
    clearSelect('#add-tipe-sparepart');
  }

  function clearFormDataDet3() {
    const form = document.getElementById('addNewDataFormDet3');
    // reset input/textarea standar
    form?.reset?.();

    $("#est_dtl3_id").val('');

    // kosongkan select (Select2)
    clearSelect('#add-tipe-lain');
  }

  function hitungTotalPerbaikan() {
    let total = 0;
    let total_lain = 0;
    let total_part = 0;
    let total_seluruh = 0;
    let total_disc = 0;
    let subtotal = 0;
    let ppn = 0;
    let ppn_lain = 0;
    let ppn_part = 0;
    let total_ppn = 0;

    let ppn_persen = $('#add-ppn-persen').val();

    // Loop semua elemen dengan class 'add-harga'
    $('.add-harga').each(function() {
        // Ambil value
        let val = $(this).val();

        // Bersihkan format (hapus koma) agar bisa dijumlahkan
        // Contoh: "405,000.00" menjadi 405000.00
        // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
        let cleanVal = val.replace(/,/g, '');

        // Konversi ke float, jika NaN (kosong) anggap 0
        let numberVal = parseFloat(cleanVal) || 0;

        total += numberVal;
    });

    $('.add-jumlah-sparepart').each(function() {
      // Ambil value
      let val2 = $(this).val();

      // Bersihkan format (hapus koma) agar bisa dijumlahkan
      // Contoh: "405,000.00" menjadi 405000.00
      // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
      let cleanVal2 = val2.replace(/,/g, '');

      // Konversi ke float, jika NaN (kosong) anggap 0
      let numberVal2 = parseFloat(cleanVal2) || 0;

      total_part += numberVal2;
    });

    // $('.add-harga-sparepart').each(function() {
    //   let row = $(this).closest('tr');
      
    //   // Ambil elemen input di baris tersebut
    //   let elQty = row.find('.add-qty-sparepart');
    //   let elHarga = row.find('.add-harga-sparepart');
    //   let elJumlah = row.find('.add-jumlah-sparepart');

    //   // Ambil Nilai & Bersihkan format (hapus koma)
    //   let qty = parseFloat(elQty.val().replace(/,/g, '')) || 0;
    //   let harga = parseFloat(elHarga.val().replace(/,/g, '')) || 0;

    //   // Hitung Subtotal Baris
    //   let subtotal = qty * harga;

    //   // Format hasil ke string angka (misal: 2,000.00)
    //   let formattedSubtotal = new Intl.NumberFormat('en-US', {
    //       minimumFractionDigits: 2,
    //       maximumFractionDigits: 2
    //   }).format(subtotal);

    //   // Update Kolom JUMLAH di baris tersebut
    //   elJumlah.val(formattedSubtotal);

    //   total_part += subtotal;
    // });

    $('.add-harga-lain').each(function() {
      // Ambil value
      let val3 = $(this).val();

      // Bersihkan format (hapus koma) agar bisa dijumlahkan
      // Contoh: "405,000.00" menjadi 405000.00
      // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
      let cleanVal3 = val3.replace(/,/g, '');

      // Konversi ke float, jika NaN (kosong) anggap 0
      let numberVal3 = parseFloat(cleanVal3) || 0;

      total_lain += numberVal3;
    });

    $('.invoice-diskon').each(function() {
      // Ambil value
      let val4 = $(this).val();

      // Bersihkan format (hapus koma) agar bisa dijumlahkan
      // Contoh: "405,000.00" menjadi 405000.00
      // Jika Anda menggunakan library masking lain, sesuaikan cara unmask-nya
      let cleanVal4 = val4.replace(/,/g, '');

      // Konversi ke float, jika NaN (kosong) anggap 0
      let numberVal4 = parseFloat(cleanVal4) || 0;

      total_disc += numberVal4;
    });

    // $('.add-ppn:checked').each(function() {
    //   // Ambil value
    //   let tipe = $(this).data('tipe');
    //   let nilai = $(this).val();

    //   if (nilai == "1" && tipe == "perbaikan") {
    //     let valDis = $("#add-disc-perbaikan").val();
    //     let cleanValDis = valDis.replace(/,/g, '');
    //     let numberValDis = parseFloat(cleanValDis) || 0;

    //     ppn = (ppn_persen > 0) ? (total - numberValDis) / ppn_persen : 0;
    //   } else if (nilai == "1" && tipe == "sparepart") {
    //     let valDis = $("#add-disc-sparepart").val();
    //     let cleanValDis = valDis.replace(/,/g, '');
    //     let numberValDis = parseFloat(cleanValDis) || 0;

    //     ppn_part = (ppn_persen > 0) ? (total_part - numberValDis) / ppn_persen : 0;
    //   } else if (nilai == "1" && tipe == "lain") {
    //     let valDis = $("#add-disc-lain").val();
    //     let cleanValDis = valDis.replace(/,/g, '');
    //     let numberValDis = parseFloat(cleanValDis) || 0;

    //     ppn_lain = (ppn_persen > 0) ? (total_lain - numberValDis) / ppn_persen : 0;
    //   }

    // });

    subtotal = total + total_part + total_lain;
    total_seluruh = subtotal - total_disc;

    total_ppn = ppn + ppn_part + ppn_lain;
    total_seluruh = total_seluruh + total_ppn;

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedTotal = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(total);

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedTotalPart = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(total_part);   

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedTotalLain = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(total_lain); 

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedSubtotal = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(subtotal);   

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedTotalDiskon = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(total_disc);

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedPPN = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(total_ppn);  

    // Format kembali ke tampilan angka (misal: 1,000,000.00)
    let formattedTotalSeluruh = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(total_seluruh);  

    // Update teks di elemen footer
    $('#total-perbaikan').text(formattedTotal);
    $('#total-sparepart').text(formattedTotalPart);
    $('#total-lain').text(formattedTotalLain);
    $('#disp-subtotal-keseluruhan').text(formattedSubtotal);
    $('#disp-total-diskon').text(formattedTotalDiskon);
    $('#disp-total-ppn').text(formattedPPN);
    $('#disp-total-keseluruhan').text(formattedTotalSeluruh);

    $('#add-total-perbaikan').val(formattedTotal);
    $('#add-total-sparepart').val(formattedTotalPart);
    $('#add-total-lain').val(formattedTotalLain);
    $('#add-subtotal-keseluruhan').val(formattedSubtotal);
    $('#add-total-diskon').val(formattedTotalDiskon);
    $('#add-total-ppn').val(formattedPPN);
    $('#add-total-keseluruhan').val(formattedTotalSeluruh);
  }

  function hitungEstimasiSparepart() {
    // Ambil elemen input di baris tersebut
    let elQty = $('#add-qty-sparepart');
    let elHarga = $('#add-harga-sparepart');
    let elJumlah = $('#add-jumlah-sparepart');

    // Ambil Nilai & Bersihkan format (hapus koma)
    let qty = parseFloat(elQty.val().replace(/,/g, '')) || 0;
    let harga = parseFloat(elHarga.val().replace(/,/g, '')) || 0;

    // Hitung Subtotal Baris
    let subtotal = qty * harga;

    // Format hasil ke string angka (misal: 2,000.00)
    let formattedSubtotal = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(subtotal);

    // Update Kolom JUMLAH di baris tersebut
    elJumlah.val(formattedSubtotal);
  }

  function disabledButton(status) {

    $('.edit-detail, .delete-detail').each(function() {
      if(status == '01' || status == '02') {
        $(this).prop('disabled', false);
      } else {
        $(this).prop('disabled', true);
      }
    });

  }