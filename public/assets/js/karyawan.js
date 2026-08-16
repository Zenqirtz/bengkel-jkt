/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
    let isAdd, isEdit, isDelete;
 
   // Variable declaration for table
   const dt_basic_table = document.querySelector('.datatables-karyawan'),
     addRoleModal = document.getElementById('addRoleModal'),
     viewFotoModal = document.getElementById('viewFotoModal'),
     statusObj = {
      'Y': { title: 'Aktif', class: 'text-bg-success' },
      'N': { title: 'Tidak Aktif', class: 'text-bg-danger' },
      'T': { title: 'Tidak Aktif', class: 'text-bg-danger' },
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

    const dt_basic = new DataTable(dt_basic_table, {
      searching: false,  // Opsi ini akan menghilangkan input cari
      ordering: true,    // Opsi lain tetap bisa jalan
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: baseUrl + 'karyawan-list',
        data: function (d) {
          // Ambil data dari input form modal dan masukkan ke parameter request
          d.nik = $('#filter-nik').val();
          d.nama = $('#filter-nama').val();
          d.posisi = $('#filter-posisi').val();
          d.jabatan = $('#filter-jabatan').val();
          d.pajak = $('#filter-pajak').val();
          d.telepon = $('#filter-telepon').val();
          d.alamat = $('#filter-alamat').val();
          d.status_karyawan = $('#filter-status-karyawan').val();
          d.status_aktif = $('#filter-status-aktif').val();
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
        { data: 'kode_karyawan' },
        { data: 'nama' },
        { data: 'nm_jabatan' },
        { data: 'posisi_pekerjaan' },
        { data: 'nik' },
        { data: 'no_hp' },
        { data: 'alamat' },
        { data: 'nm_status_pajak' },
        { data: 'nm_status_karyawan' },
        { data: 'tgl_masuk' },
        { data: 'tgl_keluar' },
        { data: 'file_photo', className: 'text-center' },
        { data: 'file_ktp', className: 'text-center' },
        { data: 'file_ttd', className: 'text-center' },
        { data: 'nm_status_aktif' },
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
           return `<input type="checkbox" class="dt-checkboxes form-check-input" value="${data}">`;
         },
         checkboxes: {
           selectAllRender: '<input type="checkbox" class="form-check-input">'
         }
        },
        {
          searchable: false,
          orderable: false,
          visible: true,
          targets: 1,
          render: function (data, type, full, meta) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        // {
        //   targets: 2,
        //   responsivePriority: 4,
        //   render: function (data, type, full, meta) {
        //     const userImg = full['logo_cabang'];
        //     const name = full['nama_cabang'];
        //     const post = full['nama_singkat'];
        //     let output;

        //     if (userImg) {
        //       // For Avatar image
        //       output = `<img src="${assetsPath}img/cabang/${userImg}" alt="Avatar" class="rounded-circle">`;
        //     } else {
        //       // For Avatar badge
        //       const stateNum = Math.floor(Math.random() * 6);
        //       const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
        //       const state = states[stateNum];
        //       let initials = name.match(/\b\w/g) || [];
        //       initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
        //       output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
        //     }

        //     // Creates full output for row
        //     const rowOutput = `
        //       <div class="d-flex justify-content-start align-items-center user-name">
        //         <div class="avatar-wrapper">
        //           <div class="avatar me-2">
        //             ${output}
        //           </div>
        //         </div>
        //         <div class="d-flex flex-column">
        //           <span class="emp_name text-truncate h6 mb-0">${name}</span>
        //           <small class="emp_post text-truncate">${post}</small>
        //         </div>
        //       </div>
        //     `;

        //     return rowOutput;
        //   }
        // },
        {
          targets: -4,
          orderable: false,
          // responsivePriority: 2
          render: function (data, type, full, meta) {
            if(data == '1') {
              // return `<button class="btn btn-xs btn-icon btn-success download-file" data-id="${full['id']}" data-tipe="photo" title="Download"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
              return `<button class="btn btn-xs btn-icon btn-success lihat-file" data-id="${full['id']}" data-tipe="photo" title="Lihat File"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
            } else {
              return `<span class="badge badge-center text-bg-danger"><i class="icon-base ri ri-close-line"></i></span>`;
            }
          }
        },
        {
          targets: -3,
          orderable: false,
          // responsivePriority: 2
          render: function (data, type, full, meta) {
            if(data == '1') {
              // return `<button class="btn btn-xs btn-icon btn-success download-file" data-id="${full['id']}" data-tipe="ktp" title="Download"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
              return `<button class="btn btn-xs btn-icon btn-success lihat-file" data-id="${full['id']}" data-tipe="ktp" title="Lihat File"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
            } else {
              return `<span class="badge badge-center text-bg-danger"><i class="icon-base ri ri-close-line"></i></span>`;
            }
          }
        },
        {
          targets: -2,
          orderable: false,
          // responsivePriority: 2
          render: function (data, type, full, meta) {
            if(data == '1') {
              return `<button class="btn btn-xs btn-icon btn-success lihat-file" data-id="${full['id']}" data-tipe="ttd" title="Lihat File"><i class="icon-base ri ri-check-line icon-22px"></i></button>`;
            } else {
              return `<span class="badge badge-center text-bg-danger"><i class="icon-base ri ri-close-line"></i></span>`;
            }
          }
        },
        {
          targets: -1,
          render: function (data, type, full, meta) {
            const status = full['status_aktif'];

            if(status == 'Y') {
              return `<span class="badge rounded-pill text-bg-success text-capitalized">${data}</span>`;
            } else {
              return `<span class="badge rounded-pill text-bg-danger text-capitalized">${data}</span>`;
            }
          }
        },
      ],
      // scrollY: '300px',
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

    // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-karyawan .dt-checkboxes');
      if (chk) {
        if (chk.checked) {
            $('.dt-checkboxes').not(chk).prop('checked', false);
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
        const selectedCheckbox = document.querySelector('.datatables-karyawan .dt-checkboxes:checked');

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

          // get data
          fetch(`${baseUrl}karyawan-list/${user_id}/edit`)
          .then(response => response.json())
          .then(data => {
            // Buka Modal secara manual
            const modalInstance = new bootstrap.Modal(addRoleModal);
            modalInstance.show();

            document.getElementById('user_id').value = data.id;
            document.getElementById('old_photo').value = data.file_photo;
            document.getElementById('old_photo_ktp').value = data.file_ktp;
            document.getElementById('add-kode-karyawan').value = data.kode_karyawan;
            document.getElementById('add-nik').value = data.nik;
            document.getElementById('add-nama').value = data.nama;
            document.getElementById('add-alamat').value = data.alamat;
            document.getElementById('add-phone').value = data.no_hp;
            document.getElementById('add-tgl-masuk').value = data.tgl_masuk;
            document.getElementById('add-tgl-keluar').value = data.tgl_keluar;
            document.getElementById('add-absen').value = data.no_absen;

            setSelectValue('#add-posisi',   data.kode_posisi, '');
            setSelectValue('#add-jabatan',   data.kode_jabatan, '');
            setSelectValue('#add-pajak',   data.status_pajak, '');
            setSelectValue('#add-status-karyawan',   data.status_karyawan, '');
            setSelectValue('#add-status-aktif',   data.status_aktif, '');
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
        const selectedCheckbox = document.querySelector('.datatables-karyawan .dt-checkboxes:checked');

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
              fetch(`${baseUrl}karyawan-list/${user_id}`, {
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
        }
      });
    }
 
    document.addEventListener('click', function (e) {
      if (e.target.closest('.download-file')) {
        const dwlBtn = e.target.closest('.download-file');
        const user_id = dwlBtn.dataset.id;
        const tipe = dwlBtn.dataset.tipe;

        if(user_id.length) {
          let params = {
            id: user_id,
            tipe: tipe
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);
          
          // Redirect window untuk download file
          // Pastikan route URL sesuai dengan konfigurasi route Anda
          // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
          const printUrl = `${baseUrl}setting/download-file-karyawan?` + queryString;
          window.open(printUrl, '_blank');
        }
      }

      if (e.target.closest('.lihat-file')) {
        const dwlBtn = e.target.closest('.lihat-file');
        const user_id = dwlBtn.dataset.id;
        const tipe = dwlBtn.dataset.tipe;

        if(user_id.length) {

          let params = {
            id: user_id,
            tipe: tipe
          };

          // // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);

          fetch(`${baseUrl}setting/karyawan-lihat-foto?` + queryString)
          .then(response => {
            if (!response.ok) throw new Error('Gagal mengambil data');
            return response.json();
          })
          .then(photo => {

            const modalInstance = new bootstrap.Modal(viewFotoModal);
            modalInstance.show();

            document.getElementById('view-kode-karyawan').value = photo.kode_karyawan;
            document.getElementById('view-nama-karyawan').value = photo.nama;

            // Loop Gambar
            const photoContainer = document.getElementById('photo-container');

            if (photoContainer) {
              // 1. Kosongkan isi container sebelum mengisinya dengan data baru
              photoContainer.innerHTML = ''; 

              // 2. Cek apakah ada data foto dari respon AJAX
              if (photo.photo_panel_base64.length > 0) {
                
                // Tentukan apakah menampilkan gambar Base64 atau Placeholder abu-abu
                let imageElement = '';
                let downloadButton = '';

                if (photo.photo_panel_base64) {
                  imageElement = `<img src="data:image/jpeg;base64,${photo.photo_panel_base64}" class="card-img-top" alt="${photo.nama_panel}" style="height: auto; object-fit: cover;">`;

                  downloadButton = `
                    <a href="data:image/jpeg;base64,${photo.photo_panel_base64}" 
                      download="${photo.nama_panel}" 
                      class="btn btn-success btn-sm position-absolute rounded-circle" 
                      style="top: -10px; right: 25px; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; text-decoration: none; z-index: 10;">
                      <i class="icon-base ri ri-download-2-line"></i>
                    </a>
                  `;
                } else {
                  imageElement = `
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                      <span class="text-muted"><i class="icon-base ri ri-delete-bin-7-line"></i></span>
                    </div>
                  `;
                }

                // Susun struktur HTML Card persis seperti View Blade sebelumnya
                const cardHTML = `
                  <div class="col-md-12 col-sm-12 text-center">
                    <div class="card position-relative border">
                      
                      <button type="button" class="btn btn-danger btn-sm position-absolute rounded-circle delete-foto" 
                          data-id="${photo.id}" data-tipe="${tipe}"
                          style="top: -10px; right: -10px; width: 30px; height: 30px; padding: 0; line-height: 1;">
                        <i class="icon-base ri ri-delete-bin-7-line"></i>
                      </button>

                      ${downloadButton}

                      ${imageElement}

                      <div class="card-body text-center p-2">
                        <h6 class="card-title mb-0">${photo.nama_panel}</h6>
                      </div>

                    </div>
                  </div>
                `;

                // Masukkan HTML Card ke dalam Container
                photoContainer.insertAdjacentHTML('beforeend', cardHTML);

              } else {
                // Tampilan jika tidak ada foto sama sekali di database
                photoContainer.innerHTML = `
                  <div class="col-12 text-center py-4">
                    <span class="text-muted">Tidak ada foto.</span>
                  </div>
                `;
              }
            }
          })
          .catch(error => {
            console.error('Error:', error);
          });
          
          // // Redirect window untuk download file
          // // Pastikan route URL sesuai dengan konfigurasi route Anda
          // // window.location.href = `${baseUrl}laporan-kendaraan/print?` + queryString;
          // const printUrl = `${baseUrl}setting/download-file-karyawan?` + queryString;
          // window.open(printUrl, '_blank');
        }
      }

      if (e.target.closest('.delete-foto')) {
        if (isDelete) {
          const deleteBtn = e.target.closest('.delete-foto');
          const user_id = deleteBtn.dataset.id;
          const tipe = deleteBtn.dataset.tipe;
          // const dtrModal = document.querySelector('.dtr-bs-modal.show');

          // hide responsive modal in small screen
          // if (dtrModal) {
          //   const bsModal = bootstrap.Modal.getInstance(dtrModal);
          //   bsModal.hide();
          // }
  
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

              let params = {
                id: user_id,
                tipe: tipe
              };
    
              // // Bersihkan parameter kosong agar URL tidak terlalu panjang
              let queryString = $.param(params);

              fetch(`${baseUrl}setting/karyawan-hapus-foto?` + queryString)
              .then(async (response) => {
                const text = await response.text();
                let json = {};
                try { json = JSON.parse(text); } catch { json = { status:false, message:text || 'Gagal' }; }
                if (!response.ok) throw new Error(json.message || 'Gagal simpan');
      
                // Hide offcanvas
                const offcanvasInstance = bootstrap.Modal.getInstance(viewFotoModal);
                offcanvasInstance && offcanvasInstance.hide();
      
                // Refresh DataTable
                dt_basic_table && new DataTable(dt_basic_table).draw();
      
                if(json.status) {
                  Swal.fire({
                    icon: 'success',
                    title: 'Informasi!',
                    text: json.message || 'Berhasil.',
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false
                  });
                } else {
                  let errorHtml = `<p>${json.message}</p>`; // Pesan utama (Gagal menyimpan data)
      
                  if (json.errors) {
                    errorHtml += '<ul style="text-align: left; margin-top: 10px;">';
                    // Loop setiap key error (no_polisi, no_rangka, dll)
                    Object.values(json.errors).forEach(errorArray => {
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
              .catch((err) => {
                Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: String(err.message || err),
                  customClass: { confirmButton: 'btn btn-success' }
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
 
    // changing the title
    const addNewBtn = document.querySelector('.add-new');
    if (addNewBtn) {
      addNewBtn.addEventListener('click', function () {
      if(isAdd) {
        document.getElementById('user_id').value = ''; //resetting input field
        document.getElementById('old_photo').value = '';
        // document.getElementById('offcanvasAddLabel').innerHTML = 'Tambah Data';

        // reset seluruh form (termasuk select2)
        clearFormData();

        // opsional: fokus ke username
        document.getElementById('add-nama-cabang')?.focus();
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
 
  // validating form and updating user's data
  const addNewDataForm = document.getElementById('addNewDataForm');
  // user form validation
  if (addNewDataForm) {
     const fv = FormValidation.formValidation(addNewDataForm, {
       fields: {
        nik: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input NIK'
            },
            stringLength: {
              max: 20,
              message: 'Maksimal 20 karakter'
            },
            regexp: {
              regexp: /^[a-zA-Z0-9 ]+$/,
              message: 'The name can only consist of alphabetical, number and space'
            }
          }
        },
        nama: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nama Karyawan'
            },
            stringLength: {
              max: 50,
              message: 'Maksimal 50 karakter'
            }
          }
        },
        alamat: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Alamat'
            }
          }
        },
        no_hp: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nomor HP'
            }
          }
        },
        status_pajak: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Status Pajak'
            }
          }
        },
        status_karyawan: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Status Karyawan'
            }
          }
        },
        status_aktif: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Status'
            }
          }
        },
        tgl_masuk: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tanggal Masuk'
            },
            date: {
              format: 'DD/MM/YYYY',
              message: 'Format tanggal tidak sesuai'
            }
          }
        },
        tgl_keluar: {
          validators: {
            date: {
              format: 'DD/MM/YYYY',
              message: 'Format tanggal tidak sesuai'
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

        // gunakan FormData apa adanya (include file)
        const form = addNewDataForm;
        const fd = new FormData(form);

        // endpoint: POST (create) atau PUT/PATCH (update)
        const id = document.getElementById('user_id').value; //(fd.get('user_id') || '').trim();
        const method = id ? 'POST' : 'POST';
        const url = id ? `${baseUrl}karyawan-list/${id}` : `${baseUrl}karyawan-list`;

        // jika update dengan method spoofing (Laravel)
        if (id) fd.append('_method', 'PUT');

        PleaseWaitPage();

        fetch(url, {
          method,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            // JANGAN set 'Content-Type' → biar browser set boundary otomatis
          },
          body: fd
        })
        .then(async (response) => {
          const text = await response.text();
          let json = {};
          try { json = JSON.parse(text); } catch { json = { status:false, message:text || 'Gagal' }; }
          if (!response.ok) throw new Error(json.message || 'Gagal simpan');

          if (document.querySelector(`.notiflix-loading`)) {
            Loading.remove();
          }

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          // Refresh DataTable
          dt_basic_table && new DataTable(dt_basic_table).draw();

          if(json.status) {
            Swal.fire({
              icon: 'success',
              title: 'Informasi!',
              text: json.message || 'Berhasil.',
              customClass: { confirmButton: 'btn btn-success' },
              buttonsStyling: false
            });
          } else {
            let errorHtml = `<p>${json.message}</p>`; // Pesan utama (Gagal menyimpan data)

            if (json.errors) {
              errorHtml += '<ul style="text-align: left; margin-top: 10px;">';
              // Loop setiap key error (no_polisi, no_rangka, dll)
              Object.values(json.errors).forEach(errorArray => {
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
        .catch((err) => {
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          if (document.querySelector(`.notiflix-loading`)) {
            Loading.remove();
          }

          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: String(err.message || err),
            customClass: { confirmButton: 'btn btn-success' }
          });
        });
     });
 
      // clearing form data when offcanvas hidden
      addRoleModal.addEventListener('hidden.bs.modal', function () {
        fv.resetForm(true);
        clearFormData();
      });
  }
 
  // Phone mask initialization
  const phoneMaskList = document.querySelectorAll('.phone-mask');
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      phoneMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        phoneMask.value = formatGeneral(cleanValue, {
          blocks: [4, 4, 4],
          delimiters: ['-', '-']
        });
      });
      registerCursorTracker({
        input: phoneMask,
        delimiter: '-'
      });
    });
  }

  const telpMaskList = document.querySelectorAll('.telp-mask');
  if (telpMaskList) {
    telpMaskList.forEach(function (telpMask) {
      telpMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        telpMask.value = formatGeneral(cleanValue, {
          blocks: [3, 10],
          delimiters: ['-']
        });
      });
      registerCursorTracker({
        input: telpMask,
        delimiter: '-'
      });
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
 
    // Phone mask initialization
    const flatpickrDate = document.querySelectorAll('.dt-date');
    if (flatpickrDate) {
     flatpickrDate.flatpickr({
       monthSelectorType: 'static',
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
    clearSelect('#add-posisi');
    clearSelect('#add-jabatan');
    clearSelect('#add-pajak');
    clearSelect('#add-status-karyawan');
    clearSelect('#add-status-aktif');

    $('#add-nik').addClass('is-invalid');
    $('#add-nama').addClass('is-invalid');
    $('#add-alamat').addClass('is-invalid');
    $('#add-phone').addClass('is-invalid');
    $('#add-tgl-masuk').addClass('is-invalid');
    // $('#add-posisi').addClass('is-invalid');
    $('#add-pajak').addClass('is-invalid');
    $('#add-status-karyawan').addClass('is-invalid');
    $('#add-status-aktif').addClass('is-invalid');
 
    // bersihkan error jQuery/FormValidation (kalau ada)
    // try {
    //   const fv = FormValidation && FormValidation.utils && FormValidation.utils closest ? null : null;
    // } catch (e) {}
 }
 