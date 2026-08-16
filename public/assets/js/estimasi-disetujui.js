/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete, fvPerbaikan, fvSparepart, fvLain;
  let selected_kode_spk = '';
  let selected_kode_cabang = '';
  let rowToEdit = null;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-spk'),
     addRoleModal = document.getElementById('addRoleModal'),
     offCanvasFormDet1 = document.getElementById('offcanvasAddDet1'),
     offCanvasFormDet2 = document.getElementById('offcanvasAddDet2'),
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
         url: baseUrl + 'estimasi-disetujui-list',
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
                fetch(`${baseUrl}estimasi-disetujui-list/${user_id}`, {
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
            fetch(`${baseUrl}estimasi-disetujui-list/${user_id}/edit`)
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

            // get data
            fetch(`${baseUrl}estimasi-disetujui-list/${user_id}/edit`)
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.text();
            })
            .then(result => {
              const { status, message, data } = JSON.parse(result);
    
              if(status) {
                // Buka Modal secara manual
                const modalInstance = new bootstrap.Modal(addRoleModal);
                modalInstance.show();
    
                document.getElementById('user_id').value = data.estimasi_id;
                document.getElementById('kode_estimasi').value = data.kode_estimasi;
                document.getElementById('kode_spk').value = data.kode_spk;
                document.getElementById('kode_persetujuan').value = data.kode_persetujuan;

                document.getElementById('add-nomor-estimasi').value = data.kode_estimasi;
                document.getElementById('add-tanggal-estimasi').value = data.tgl_estimasi;
                document.getElementById('add-nomor-spk2').value = data.kode_spk;
                document.getElementById('add-nomor-polisi').value = data.no_polisi;
                document.getElementById('add-tipe-kendaraan').value = data.merek_tipe;
                document.getElementById('add-nama-pemilik').value = data.pemilik;
                document.getElementById('add-nama-pelanggan').value = data.nama_pelanggan;
                document.getElementById('add-nomor-claim').value = data.kode_claim;
                document.getElementById('add-memo').value = data.memo;

                document.getElementById('add-nomor-persetujuan2').value = data.kode_persetujuan;
                document.getElementById('add-tanggal-persetujuan').value = data.tgl_persetujuan;
                document.getElementById('add-disetujui-oleh').value = data.disetujui_oleh;
                
                setRadioValue('sifat_ppn', data.sifat_ppn);
                setRadioValue('sparepart_ppn', data.sparepart_ppn);
                setRadioValue('lain_ppn', data.lain_ppn);

                // document.getElementById('add-persen-jasa').value = data.persen_jasa;
                // document.getElementById('add-persen-bahan').value = data.persen_bahan;

                document.getElementById('add-total-perbaikan').value = data.total_perbaikan;
                document.getElementById('add-total-sparepart').value = data.total_sparepart;
                document.getElementById('add-total-lain').value = data.total_lain;
                document.getElementById('add-total-keseluruhan').value = data.total;

                document.getElementById('add-total-perbaikan-s').value = data.total_perbaikan_s;
                document.getElementById('add-total-sparepart-s').value = data.total_sparepart_s;
                document.getElementById('add-total-lain-s').value = data.total_lain_s;
                document.getElementById('add-total-kwitansi').value = data.total_kwitansi;
                document.getElementById('add-total-ppn').value = data.ppn_s;
                document.getElementById('add-total-or').value = data.total_or;
                document.getElementById('add-total-tagihan').value = data.total_s;

                // var btnDetail = document.querySelectorAll('.add-detail');
                // btnDetail.forEach(function(btn) {
                //     var cleanVal1 = data.total_perbaikan_s ? data.total_perbaikan_s.replace(/,/g, '') : '0';
                //     var total_perbaikan_s = parseFloat(cleanVal1) || 0;
                //     // Cek kondisi
                //     if (btn.getAttribute('data-tipe') == 'perbaikan' && total_perbaikan_s == 0) {
                //         // Hapus trigger offcanvas
                //         btn.removeAttribute('data-bs-toggle');
                //         btn.removeAttribute('data-bs-target');
                //     }

                //     var cleanVal2 = data.total_sparepart_s ? data.total_sparepart_s.replace(/,/g, '') : '0';
                //     var total_sparepart_s = parseFloat(cleanVal2) || 0;
                //     // Cek kondisi
                //     if (btn.getAttribute('data-tipe') == 'sparepart' && total_sparepart_s == 0) {
                //         // Hapus trigger offcanvas
                //         btn.removeAttribute('data-bs-toggle');
                //         btn.removeAttribute('data-bs-target');
                //     }

                //     var cleanVal3 = data.total_lain_s ? data.total_lain_s.replace(/,/g, '') : '0';
                //     var total_lain_s = parseFloat(cleanVal3) || 0;
                //     // Cek kondisi
                //     if (btn.getAttribute('data-tipe') == 'lain' && total_lain_s == 0) {
                //         // Hapus trigger offcanvas
                //         btn.removeAttribute('data-bs-toggle');
                //         btn.removeAttribute('data-bs-target');
                //     }

                //     var cleanVal4 = data.total_kwitansi ? data.total_kwitansi.replace(/,/g, '') : '0';
                //     var total_kwitansi = parseFloat(cleanVal4) || 0;
                //     // Cek kondisi
                //     if (btn.getAttribute('data-tipe') == 'salvage' && total_kwitansi == 0) {
                //         // Hapus trigger offcanvas
                //         btn.removeAttribute('data-bs-toggle');
                //         btn.removeAttribute('data-bs-target');
                //     }
                // });
    
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

            var tr = $(selectedCheckbox).closest('tr');
            var row = dt_basic.row(tr); 
            var data2 = row.data(); // Ini berisi objek data dari baris tersebut

            selected_kode_spk = data2.kode_spk; // Sesuaikan dengan nama field di database/json
            selected_kode_cabang = data2.kode_cabang;

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
                  url: baseUrl + 'estimasi-disetujui-list',
                  data: function (d) {
                    // Ambil data dari input form modal dan masukkan ke parameter request
                    d.kode_spk = selected_kode_spk;
                    d.kode_cabang = selected_kode_cabang;
                    d.tipe = 'estimasi-perbaikan';
                  }
                },
                columns: [
                  // columns according to JSON
                  { data: 'id' },
                  { data: 'jenis_pekerjaan' },
                  { data: 'panel_pekerjaan' },
                  { data: 'tipe' },
                  { data: 'harga' },
                  { data: 'harga_s' },
                  { data: 'cek' },
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
                  // {
                  //   targets: 1,
                  //   render: function (data, type, full, meta) {
                  //     return `
                  //       ${data} 
                  //       <input type="hidden" name="pekerjaan[${full.id}][jenis]" value="${full.kode_jenis_pekerjaan}" />
                  //     `; 
                  //   }
                  // },
                  // {
                  //   targets: 2,
                  //   render: function (data, type, full, meta) {
                  //     return `
                  //       ${data} 
                  //       <input type="hidden" name="pekerjaan[${full.id}][panel]" value="${full.kode_panel_pekerjaan}" />
                  //     `; 
                  //   }
                  // },
                  {
                      targets: 4,
                      className: 'text-end',
                      // render: function (data, type, full, meta) {
                      //   let val = data || 0; 
                      //   return `
                      //     ${data} 
                      //     <input type="hidden" name="pekerjaan[${full.id}][harga]" class="add-harga" value="${data}" />
                      //   `;  
                      // }
                  },
                  {
                      targets: 5,
                      className: 'text-end',
                      render: function (data, type, full, meta) {
                        // return `
                        //   ${data} 
                        //   <input type="hidden" name="pekerjaan[${full.id}][harga]" class="add-harga" value="${data}" />
                        // `; 
                        return `
                          <input type="text" name="pekerjaan[${full.id}][harga]" class="form-control form-control-sm text-end invoice-price add-harga" value="${data}" data-max="${full.harga}" />
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
                      let chked = (data == '1') ? 'checked' : '';
  
                      return `<input type="checkbox" name="pekerjaan[${full.id}][cek]" class="dt-checkboxes-det form-check-input" value="${data}" ${chked}>`;
                    },
                    // checkboxes: {
                    //   selectAllRender: '<input type="checkbox" class="form-check-input">'
                    // }
                  }
                ],
                drawCallback: function() {
                  // hitungTotalPerbaikan();

                  $('.datatables-perbaikan').off('change', '.dt-checkboxes-det').on('change', '.dt-checkboxes-det', function() {
                    // Panggil fungsi hitung ulang setiap kali checkbox diklik
                    hitungTotalPerbaikan();
                  });
                },
              });

              $('.datatables-perbaikan').on('input', '.invoice-price', function(event) {
                // Ambil value yang sedang diketik, hilangkan koma agar jadi angka murni
                let rawInput = event.target.value.replace(/,/g, ''); 
                
                // Ambil nilai maksimal dari atribut data-max
                let rawMax = $(this).data('max');
                rawMax = rawMax.replace(/,/g, '');

                // Ubah tipe datanya menjadi Float (angka desimal/bulat) untuk dibandingkan
                let numericInput = parseFloat(rawInput) || 0;
                let numericMax = parseFloat(rawMax) || 0;

                // LOGIKA PEMBATASAN
                // Jika angka yang diketik lebih besar dari existing data, paksa menjadi nilai max
                if (numericInput > numericMax) {
                  numericInput = numericMax;
                }

                // Format ulang dan kembalikan ke input form
                $(this).val(formatNumeral(String(numericInput), {
                  delimiter: ',',
                  numeral: true
                }));
            
                // (Opsional) Jika Anda ingin total langsung terhitung saat harga diketik
                hitungTotalPerbaikan(); 
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
                  url: baseUrl + 'estimasi-disetujui-list',
                  data: function (d) {
                    // Ambil data dari input form modal dan masukkan ke parameter request
                    d.kode_spk = selected_kode_spk;
                    d.kode_cabang = selected_kode_cabang;
                    d.tipe = 'estimasi-sparepart';
                  }
                },
                columns: [
                  // columns according to JSON
                  { data: 'id' },
                  { data: 'nama_sparepart' },
                  { data: 'tipe' },
                  { data: 'jumlah' },
                  { data: 'jumlah_s' },
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
                    targets: 3,
                    className: 'text-end',
                  },
                  {
                      targets: 4,
                      className: 'text-end',
                      render: function (data, type, full, meta) {
                        // return `
                        //   ${data} 
                        //   <input type="hidden" name="sparepart[${full.id}][harga]" class="add-jumlah-sparepart" value="${data}" />
                        // `; 
                        return `
                          <input type="text" name="sparepart[${full.id}][harga]" class="form-control form-control-sm text-end invoice-price add-jumlah-sparepart" value="${data}" data-max="${full.jumlah}" />
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
                      let chked = (data == '1') ? 'checked' : '';

                      return `<input type="checkbox" name="sparepart[${full.id}][cek]" class="dt-checkboxes-det form-check-input" value="${data}" ${chked}>`;
                    },
                    // checkboxes: {
                    //   selectAllRender: '<input type="checkbox" class="form-check-input">'
                    // }
                  }
                ],
                drawCallback: function() {
                  // hitungTotalPerbaikan(); 

                  $('.datatables-sparepart').off('change', '.dt-checkboxes-det').on('change', '.dt-checkboxes-det', function() {
                    // Panggil fungsi hitung ulang setiap kali checkbox diklik
                    hitungTotalPerbaikan();
                  });
                },
              });

              $('.datatables-sparepart').on('input', '.invoice-price', function(event) {
                // Ambil value yang sedang diketik, hilangkan koma agar jadi angka murni
                let rawInput = event.target.value.replace(/,/g, ''); 
                
                // Ambil nilai maksimal dari atribut data-max
                let rawMax = $(this).data('max');
                rawMax = rawMax.replace(/,/g, '');

                // Ubah tipe datanya menjadi Float (angka desimal/bulat) untuk dibandingkan
                let numericInput = parseFloat(rawInput) || 0;
                let numericMax = parseFloat(rawMax) || 0;

                // LOGIKA PEMBATASAN
                // Jika angka yang diketik lebih besar dari existing data, paksa menjadi nilai max
                if (numericInput > numericMax) {
                  numericInput = numericMax;
                }

                // Format ulang dan kembalikan ke input form
                $(this).val(formatNumeral(String(numericInput), {
                  delimiter: ',',
                  numeral: true
                }));
            
                // (Opsional) Jika Anda ingin total langsung terhitung saat harga diketik
                hitungTotalPerbaikan(); 
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
                  url: baseUrl + 'estimasi-disetujui-list',
                  data: function (d) {
                    // Ambil data dari input form modal dan masukkan ke parameter request
                    d.kode_spk = selected_kode_spk;
                    d.kode_cabang = selected_kode_cabang;
                    d.tipe = 'estimasi-lain';
                  }
                },
                columns: [
                  // columns according to JSON
                  { data: 'id' },
                  { data: 'memo' },
                  { data: 'tipe' },
                  { data: 'harga' },
                  { data: 'harga_s' },
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
                    targets: 3,
                    className: 'text-end'
                  },
                  {
                    targets: 4,
                    className: 'text-end',
                    render: function (data, type, full, meta) {
                      // return `
                      //   ${data} 
                      //   <input type="hidden" name="lain[${full.id}][harga]" class="add-harga-lain" value="${data}" />
                      // `; 
                      return `
                        <input type="text" name="lain[${full.id}][harga]" class="form-control form-control-sm text-end invoice-price add-harga-lain" value="${data}" data-max="${full.harga}" />
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
                      let chked = (data == '1') ? 'checked' : '';

                      return `<input type="checkbox" name="lain[${full.id}][cek]" class="dt-checkboxes-det form-check-input" value="${data}" ${chked}>`;
                    },
                    // checkboxes: {
                    //   selectAllRender: '<input type="checkbox" class="form-check-input">'
                    // }
                  }
                ],
                drawCallback: function() {
                  hitungTotalPerbaikan();

                  $('.datatables-lain').off('change', '.dt-checkboxes-det').on('change', '.dt-checkboxes-det', function() {
                    // Panggil fungsi hitung ulang setiap kali checkbox diklik
                    hitungTotalPerbaikan();
                  });
                },
              });

              $('.datatables-lain').on('input', '.invoice-price', function(event) {
                // Ambil value yang sedang diketik, hilangkan koma agar jadi angka murni
                let rawInput = event.target.value.replace(/,/g, ''); 
                
                // Ambil nilai maksimal dari atribut data-max
                let rawMax = $(this).data('max');
                rawMax = rawMax.replace(/,/g, '');

                // Ubah tipe datanya menjadi Float (angka desimal/bulat) untuk dibandingkan
                let numericInput = parseFloat(rawInput) || 0;
                let numericMax = parseFloat(rawMax) || 0;

                // LOGIKA PEMBATASAN
                // Jika angka yang diketik lebih besar dari existing data, paksa menjadi nilai max
                if (numericInput > numericMax) {
                  numericInput = numericMax;
                }

                // Format ulang dan kembalikan ke input form
                $(this).val(formatNumeral(String(numericInput), {
                  delimiter: ',',
                  numeral: true
                }));
            
                // (Opsional) Jika Anda ingin total langsung terhitung saat harga diketik
                hitungTotalPerbaikan(); 
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
                  } else if (tipe == "salvage") {
                    $("#salvage").val($("#add-salvage").val());
                  }
                } else {
                  // Hide offcanvas
                  if (tipe == "perbaikan") {
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
                    offcanvasInstance && offcanvasInstance.hide();
                  } else if (tipe == "sparepart") {
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
                    offcanvasInstance && offcanvasInstance.hide();
                  } else if (tipe == "lain") {
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasFormDet1);
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

            });

            const addNewDataFormDet1 = document.getElementById('addNewDataFormDet1');
            if (addNewDataFormDet1) {
              if (fvPerbaikan) {
                fvPerbaikan.destroy(); // Hancurkan instance lama agar tidak menumpuk
              }
              
              fvPerbaikan = FormValidation.formValidation(addNewDataFormDet1, {
                fields: {
                  tipe_diskon: {
                    validators: {
                      notEmpty: {
                        message: 'Silahkan Input Tipe Diskon'
                      }
                    }
                  },
                  diskon: {
                    validators: {
                      notEmpty: {
                        message: 'Silahkan Input Nilai Diskon'
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
                var tipe = $("#tipe").val();
                var diskon = $("#add-diskon").val();

                $('.add-tipe-diskon:checked').each(function() {
                  // Ambil value
                  // let tipe = $(this).data('tipe');
                  var nilai = $(this).val();

                  // if (tipe == "perbaikan") {
                  //   $("#add-disc-tipe-perbaikan").val(nilai);
                  //   $("#add-disc-perbaikan").val(diskon);
                  //   $('#add-total-perbaikan-s').val(diskon);
                  // } else if (tipe == "sparepart") {
                  //   $("#add-disc-tipe-sparepart").val(nilai);
                  //   $("#add-disc-sparepart").val(diskon);
                  // } else if (tipe == "lain") {
                  //   $("#add-disc-tipe-lain").val(nilai);
                  //   $("#add-disc-lain").val(diskon);
                  // }

                  if(nilai == "0") { // Diskon

                    if (tipe == "perbaikan") {
                      var total = $('#add-total-perbaikan').val();
                      var cleanVal = total ? total.replace(/,/g, '') : '0';
                      total = parseFloat(cleanVal) || 0;

                      total = total - (total * diskon / 100);

                      var formattedTotal = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      }).format(total);  

                      $("#add-disc-tipe-perbaikan").val(nilai);
                      $("#add-disc-perbaikan").val(diskon);
                      $('#add-total-perbaikan-s').val(formattedTotal);
                    } else if (tipe == "sparepart") {
                      var total = $('#add-total-sparepart').val();
                      var cleanVal = total ? total.replace(/,/g, '') : '0';
                      total = parseFloat(cleanVal) || 0;

                      total = total - (total * diskon / 100);

                      var formattedTotal = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      }).format(total);  

                      $("#add-disc-tipe-sparepart").val(nilai);
                      $("#add-disc-sparepart").val(diskon);
                      $('#add-total-sparepart-s').val(formattedTotal);
                    } else if (tipe == "lain") {
                      var total = $('#add-total-lain').val();
                      var cleanVal = total ? total.replace(/,/g, '') : '0';
                      total = parseFloat(cleanVal) || 0;

                      total = total - (total * diskon / 100);

                      var formattedTotal = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      }).format(total);  

                      $("#add-disc-tipe-lain").val(nilai);
                      $("#add-disc-lain").val(diskon);
                      $('#add-total-lain-s').val(formattedTotal);
                    }

                  } else if(nilai == "1") { // Penawaran
                    
                    if (tipe == "perbaikan") {
                      $("#add-disc-tipe-perbaikan").val(nilai);
                      $("#add-disc-perbaikan").val(diskon);
                      $('#add-total-perbaikan-s').val(diskon);
                    } else if (tipe == "sparepart") {
                      $("#add-disc-tipe-sparepart").val(nilai);
                      $("#add-disc-sparepart").val(diskon);
                      $('#add-total-sparepart-s').val(diskon);
                    } else if (tipe == "lain") {
                      $("#add-disc-tipe-lain").val(nilai);
                      $("#add-disc-lain").val(diskon);
                      $('#add-total-lain-s').val(diskon);
                    }

                  }

                  hitungTotalPerbaikan();
            
                });

                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet1);
                offcanvasInstance && offcanvasInstance.hide();
              });
            }
            
            const addNewDataFormDet2 = document.getElementById('addNewDataFormDet2');
            if (addNewDataFormDet2) {
              if (fvSparepart) {
                fvSparepart.destroy(); // Hancurkan instance lama agar tidak menumpuk
              }
              
              fvSparepart = FormValidation.formValidation(addNewDataFormDet2, {
                fields: {
                  salvage: {
                    validators: {
                      notEmpty: {
                        message: 'Silahkan Input Nilai Salvage'
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
                var totKwitansi = $("#add-total-kwitansi").val();
                var salvage = $("#add-salvage").val();
                
                var cleanVal = totKwitansi ? totKwitansi.replace(/,/g, '') : '0';
                totKwitansi = parseFloat(cleanVal) || 0;

                var cleanVal2 = salvage ? salvage.replace(/,/g, '') : '0';
                salvage = parseFloat(cleanVal2) || 0;
                
                var totKwitansiNew = totKwitansi - salvage;

                $("#add-total-kwitansi").val(totKwitansiNew);
                $("#salvage").val(salvage);

                hitungTotalPerbaikan();

                const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasAddDet2);
                offcanvasInstance && offcanvasInstance.hide();

              });
            }

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
          tgl_persetujuan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tanggal Persetujuan'
              }
            }
          },
          kode_claim: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Nomor Klaim'
              }
            }
          },
          disetujui_oleh: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Disetujui Oleh'
              }
            }
          },
          // persen_bahan: {
          //   validators: {
          //     callback: {
          //       message: 'Total Bahan dan Jasa maksimal 100%',
          //       callback: function (input) {
          //         // Ambil nilai, hapus koma (jika ada class ribuan), jadikan angka mutlak
          //         const bahan = parseFloat(input.value.replace(/,/g, '')) || 0;
          //         const jasa = parseFloat(document.getElementById('add-persen-jasa').value.replace(/,/g, '')) || 0;
                  
          //         // Validasi sukses jika total <= 100
          //         return (bahan + jasa) <= 100;
          //       }
          //     }
          //   }
          // },
          // persen_jasa: {
          //   validators: {
          //     callback: {
          //       message: 'Total Bahan dan Jasa maksimal 100%',
          //       callback: function (input) {
          //         const jasa = parseFloat(input.value.replace(/,/g, '')) || 0;
          //         const bahan = parseFloat(document.getElementById('add-persen-bahan').value.replace(/,/g, '')) || 0;
                  
          //         return (bahan + jasa) <= 100;
          //       }
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

        fetch(`${baseUrl}estimasi-disetujui-list`, {
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

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          if(status) {
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
            
            hitungTotalPerbaikan();
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
  //  clearSelect('#add-nopolisi');
 
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
    $("#add-diskon").val('');

    $('#offcanvasAddLabelDet1').text('Diskon Perbaikan');
    $('#tipe').val('perbaikan');

    var total = $("#add-total-perbaikan-s").val();
    var cleanVal = total ? total.replace(/,/g, '') : '0';
    total = parseFloat(cleanVal) || 0;

    // if(total > 0) {
    //   $('#btn-simpan').prop('disabled', false);
    // } else {
    //   $('#btn-simpan').prop('disabled', true);
    // }

    // kosongkan select (Select2)
    //  clearSelect('#add-jenis-pekerjaan');
  }

  function clearFormDataDet2() {
    const form = document.getElementById('addNewDataFormDet2');
    // reset input/textarea standar
    form?.reset?.();

    $("#est_dtl2_id").val('');
    $("#add-diskon").val('');

    $('#offcanvasAddLabelDet1').text('Diskon Sparepart');
    $('#tipe').val('sparepart');

    var total = $("#add-total-sparepart-s").val();
    var cleanVal = total ? total.replace(/,/g, '') : '0';
    total = parseFloat(cleanVal) || 0;

    // if(total > 0) {
    //   $('#btn-simpan').prop('disabled', false);
    // } else {
    //   $('#btn-simpan').prop('disabled', true);
    // }

  }

  function clearFormDataDet3() {
    const form = document.getElementById('addNewDataFormDet3');
    // reset input/textarea standar
    form?.reset?.();

    $("#est_dtl3_id").val('');
    $("#add-diskon").val('');

    $('#offcanvasAddLabelDet1').text('Diskon Lain-lain');
    $('#tipe').val('lain');

    var total = $("#add-total-lain-s").val();
    var cleanVal = total ? total.replace(/,/g, '') : '0';
    total = parseFloat(cleanVal) || 0;

    // if(total > 0) {
    //   $('#btn-simpan').prop('disabled', false);
    // } else {
    //   $('#btn-simpan').prop('disabled', true);
    // }

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
    let total_or = $('#add-total-or').val();
    let salvage = $("#salvage").val();

    let cleanValPPN = ppn_persen ? ppn_persen.replace(/,/g, '') : '0';
    ppn_persen = (parseFloat(cleanValPPN) / 100) || 0;

    let cleanValOR = total_or ? total_or.replace(/,/g, '') : '0';
    total_or = parseFloat(cleanValOR) || 0;

    let cleanValSv = salvage ? salvage.replace(/,/g, '') : '0';
    salvage = parseFloat(cleanValSv) || 0;

    let tipe_diskon_perbaikan = $("#add-disc-tipe-perbaikan").val();
    let tipe_diskon_part = $("#add-disc-tipe-sparepart").val();
    let tipe_diskon_lain = $("#add-disc-tipe-lain").val();

    if(tipe_diskon_perbaikan == "1" || tipe_diskon_perbaikan == "0") {
      total = $('#add-total-perbaikan-s').val();
      total = total ? total.replace(/,/g, '') : '0';
      total = parseFloat(total) || 0;
    } else {
      $('.datatables-perbaikan .dt-checkboxes-det:checked').each(function() {
        // 1. Ambil elemen baris (TR) dari checkbox yang dipilih
        let row = $(this).closest('tr');

        // 2. Cari input hidden class '.add-point' yang ada di baris tersebut
        let pointInput = row.find('.add-harga');
        let val = pointInput.val();

        // 3. Bersihkan format & Konversi ke Float
        let cleanVal = val ? val.replace(/,/g, '') : '0';
        let numberVal = parseFloat(cleanVal) || 0;

        // let tipe_diskon = $("#add-disc-tipe").val();
        // let diskon = $("#add-disc-perbaikan").val();
        
        // let cleanDisc = diskon ? diskon.replace(/,/g, '') : '0';
        // diskon = parseFloat(cleanDisc) || 0;

        // if(tipe_diskon == "1") { // Diskon Harga
        //   if(diskon > 0) {
        //     numberVal = diskon;
        //   }
        // } else { // Diskon Persen
        //   if(diskon > 0) {
        //     numberVal = numberVal - (numberVal * diskon / 100);
        //   }
        // }

        // 4. Tambahkan ke total
        total += numberVal;
      });
    }

    if(tipe_diskon_part == "1" || tipe_diskon_part == "0") {
      total_part = $('#add-total-sparepart-s').val();
      total_part = total_part ? total_part.replace(/,/g, '') : '0';
      total_part = parseFloat(total_part) || 0;
    } else {
      $('.datatables-sparepart .dt-checkboxes-det:checked').each(function() {
        // 1. Ambil elemen baris (TR) dari checkbox yang dipilih
        let row = $(this).closest('tr');

        // 2. Cari input hidden class '.add-point' yang ada di baris tersebut
        let pointInput = row.find('.add-jumlah-sparepart');
        let val = pointInput.val();

        // 3. Bersihkan format & Konversi ke Float
        let cleanVal = val ? val.replace(/,/g, '') : '0';
        let numberVal = parseFloat(cleanVal) || 0;

        let tipe_diskon = $("#add-disc-tipe").val();
        let diskon = $("#add-disc-sparepart").val();
        
        // let cleanDisc = diskon ? diskon.replace(/,/g, '') : '0';
        // diskon = parseFloat(cleanDisc) || 0;

        // if(tipe_diskon == "1") { // Diskon Harga
        //   if(diskon > 0) {
        //     numberVal = diskon;
        //   }
        // } else { // Diskon Persen
        //   if(diskon > 0) {
        //     numberVal = numberVal - (numberVal * diskon / 100);
        //   }
        // }

        // 4. Tambahkan ke total
        total_part += numberVal;
      });
    }
    
    if(tipe_diskon_lain == "1" || tipe_diskon_lain == "0") {
      total_lain = $('#add-total-lain-s').val();
      total_lain = total_lain ? total_lain.replace(/,/g, '') : '0';
      total_lain = parseFloat(total_lain) || 0;
    } else {
      $('.datatables-lain .dt-checkboxes-det:checked').each(function() {
        // 1. Ambil elemen baris (TR) dari checkbox yang dipilih
        let row = $(this).closest('tr');

        // 2. Cari input hidden class '.add-point' yang ada di baris tersebut
        let pointInput = row.find('.add-harga-lain');
        let val = pointInput.val();

        // 3. Bersihkan format & Konversi ke Float
        let cleanVal = val ? val.replace(/,/g, '') : '0';
        let numberVal = parseFloat(cleanVal) || 0;

        let tipe_diskon = $("#add-disc-tipe").val();
        let diskon = $("#add-disc-lain").val();
        
        // let cleanDisc = diskon ? diskon.replace(/,/g, '') : '0';
        // diskon = parseFloat(cleanDisc) || 0;

        // if(tipe_diskon == "1") { // Diskon Harga
        //   if(diskon > 0) {
        //     numberVal = diskon;
        //   }
        // } else { // Diskon Persen
        //   if(diskon > 0) {
        //     numberVal = numberVal - (numberVal * diskon / 100);
        //   }
        // }

        // 4. Tambahkan ke total
        total_lain += numberVal;
      });
    }

    $('.add-ppn:checked').each(function() {
      // Ambil value
      let tipe = $(this).data('tipe');
      let nilai = $(this).val();

      if (nilai == "1" && tipe == "perbaikan") {
        // let valDis = $("#add-disc-perbaikan").val();
        // let cleanValDis = valDis.replace(/,/g, '');
        // let numberValDis = parseFloat(cleanValDis) || 0;

        // ppn = (ppn_persen > 0) ? (total - numberValDis) / ppn_persen : 0;
        ppn = (ppn_persen > 0) ? total * ppn_persen : 0;
      } else if (nilai == "1" && tipe == "sparepart") {
        // let valDis = $("#add-disc-sparepart").val();
        // let cleanValDis = valDis.replace(/,/g, '');
        // let numberValDis = parseFloat(cleanValDis) || 0;

        // ppn_part = (ppn_persen > 0) ? (total_part - numberValDis) / ppn_persen : 0;
        ppn_part = (ppn_persen > 0) ? total_part * ppn_persen : 0;
      } else if (nilai == "1" && tipe == "lain") {
        // let valDis = $("#add-disc-lain").val();
        // let cleanValDis = valDis.replace(/,/g, '');
        // let numberValDis = parseFloat(cleanValDis) || 0;

        // ppn_lain = (ppn_persen > 0) ? (total_lain - numberValDis) / ppn_persen : 0;
        ppn_lain = (ppn_persen > 0) ? total_lain * ppn_persen : 0;
      }

    });

    subtotal = (total + total_part + total_lain) - salvage;
    total_seluruh = subtotal;

    total_ppn = ppn + ppn_part + ppn_lain;
    total_seluruh = (total_seluruh + total_ppn) - total_or;

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
    // $('#total-perbaikan').text(formattedTotal);
    // $('#total-sparepart').text(formattedTotalPart);
    // $('#total-lain').text(formattedTotalLain);
    // $('#disp-subtotal-keseluruhan').text(formattedSubtotal);
    // $('#disp-total-diskon').text(formattedTotalDiskon);
    // $('#disp-total-ppn').text(formattedPPN);
    // $('#disp-total-keseluruhan').text(formattedTotalSeluruh);

    $('#add-total-perbaikan-s').val(formattedTotal);
    $('#add-total-sparepart-s').val(formattedTotalPart);
    $('#add-total-lain-s').val(formattedTotalLain);
    $('#add-total-kwitansi').val(formattedSubtotal);
    // $('#add-total-diskon').val(formattedTotalDiskon);
    $('#add-total-ppn').val(formattedPPN);
    $('#add-total-tagihan').val(formattedTotalSeluruh);
  }

 