/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
  let isAdd, isEdit, isDelete;
  const addRoleModal = document.getElementById('addRoleModal');
  const updRoleModal = document.getElementById('updateRoleModal');

   // ajax setup
   $.ajaxSetup({
     headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
   });

   fetchDashboardTutupBuku();
 
    const dt_barang_table = document.querySelector('.datatables-barang');
    if (dt_barang_table) {
      isAdd = dt_barang_table.dataset.add;
      isEdit = dt_barang_table.dataset.edit;
      isDelete = dt_barang_table.dataset.delete;
      
      const dt_barang = new DataTable(dt_barang_table, {
        searching: true,  // Opsi ini akan menghilangkan input cari
        ordering: true,    // Opsi ini akan menghilangkan ordering
        paging: true,    // Opsi ini akan menghilangkan paging
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'stock-opname-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.tipe     = $('#filter-tipe-barang').val();
            d.bulan    = $('#filter-bulan').val();
            d.tahun    = $('#filter-tahun').val();
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
          { data: 'id', width: '20px' },
          { data: 'id', width: '30px' },
          { data: 'nama_bahan', width: '300px', className: 'text-nowrap' },
          { data: 'satuan' },
          { data: 'unit_awal', className: 'text-end' },
          { data: 'harga_awal', className: 'text-end' },
          { data: 'jumlah_awal', className: 'text-end' },
          { data: 'unit_tambah', className: 'text-end' },
          { data: 'harga_tambah', className: 'text-end' },
          { data: 'jumlah_tambah', className: 'text-end' },
          { data: 'unit_kurang', className: 'text-end' },
          { data: 'harga_kurang', className: 'text-end' },
          { data: 'jumlah_kurang', className: 'text-end' },
          { data: 'unit_retur', className: 'text-end' },
          { data: 'harga_retur', className: 'text-end' },
          { data: 'jumlah_retur', className: 'text-end' },
          { data: 'unit_adjust', className: 'text-end' },
          { data: 'harga_adjust', className: 'text-end' },
          { data: 'jumlah_adjust', className: 'text-end' },
          { data: 'unit_akhir', className: 'text-end' },
          { data: 'harga_akhir', className: 'text-end' },
          { data: 'jumlah_akhir', className: 'text-end' },
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
        ],
        order: [[1, 'asc']],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [
              {
                pageLength: {
                  menu: [10, 20, 50, 70, 100],
                  // menu: [[10, 20, 50, 70, 100, -1], [10, 20, 50, 70, 100, 'All']],
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
        }
      });
    }

    const dt_sparepart_table = document.querySelector('.datatables-sparepart');
    if (dt_sparepart_table) {
      isAdd = dt_sparepart_table.dataset.add;
      isEdit = dt_sparepart_table.dataset.edit;
      isDelete = dt_sparepart_table.dataset.delete;

      const dt_sparepart = new DataTable(dt_sparepart_table, {
        searching: true,  // Opsi ini akan menghilangkan input cari
        ordering: true,    // Opsi ini akan menghilangkan ordering
        paging: true,    // Opsi ini akan menghilangkan paging
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          url: baseUrl + 'stock-opname-list',
          data: function (d) {
            // Ambil data dari input form modal dan masukkan ke parameter request
            d.tipe     = $('#filter-tipe-barang').val();
            d.bulan    = $('#filter-bulan').val();
            d.tahun    = $('#filter-tahun').val();
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
          { data: 'id', width: '20px' },
          { data: 'id', width: '30px' },
          // { data: 'bulan' },
          // { data: 'tahun' },
          { data: 'nama_merek' },
          { data: 'nama_tipe' },
          { data: 'kode_input' },
          { data: 'nama_sparepart', width: '300px', className: 'text-nowrap' },
          { data: 'satuan' },
          { data: 'unit_awal', className: 'text-end' },
          { data: 'harga_awal', className: 'text-end' },
          { data: 'jumlah_awal', className: 'text-end' },
          { data: 'unit_tambah', className: 'text-end' },
          { data: 'harga_tambah', className: 'text-end' },
          { data: 'jumlah_tambah', className: 'text-end' },
          { data: 'unit_kurang', className: 'text-end' },
          { data: 'harga_kurang', className: 'text-end' },
          { data: 'jumlah_kurang', className: 'text-end' },
          { data: 'unit_retur', className: 'text-end' },
          { data: 'harga_retur', className: 'text-end' },
          { data: 'jumlah_retur', className: 'text-end' },
          { data: 'unit_adjust', className: 'text-end' },
          { data: 'harga_adjust', className: 'text-end' },
          { data: 'jumlah_adjust', className: 'text-end' },
          { data: 'unit_akhir', className: 'text-end' },
          { data: 'harga_akhir', className: 'text-end' },
          { data: 'jumlah_akhir', className: 'text-end' },
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
          }
        ],
        order: [[6, 'asc']],
        layout: {
          topStart: {
            rowClass: 'row m-3 my-0 justify-content-between',
            features: [
              {
                pageLength: {
                  menu: [10, 20, 50, 70, 100],
                  // menu: [[10, 20, 50, 70, 100, -1], [10, 20, 50, 70, 100, 'All']],
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
        }
      });
    }

    // Batasi hanya 1 checkbox yang boleh dipilih
    document.addEventListener('click', function (e) {
      const chk = e.target.closest('.datatables-barang .dt-checkboxes');
      if (chk) {
        if (chk.checked) {
            $('.dt-checkboxes').not(chk).prop('checked', false);
        }
      }

      const chk2 = e.target.closest('.datatables-sparepart .dt-checkboxes');
      if (chk2) {
        if (chk2.checked) {
            $('.dt-checkboxes').not(chk2).prop('checked', false);
        }
      }
    });

   // export excel
   const exportBtn = document.querySelector('.btn-export-excel');
   if (exportBtn) {
      exportBtn.addEventListener('click', function () {
        if(isAdd) {
          // Ambil data dari form filter
          // Kita ambil value manual karena form berada di dalam modal
          let params = {
            tipe: $('#filter-tipe-barang').val(),
            bulan: $('#filter-bulan').val(),
            tahun: $('#filter-tahun').val()
          };

          // Bersihkan parameter kosong agar URL tidak terlalu panjang
          let queryString = $.param(params);
          
          // Redirect window untuk download file
          // Pastikan route URL sesuai dengan konfigurasi route Anda
          window.location.href = `${baseUrl}gudang/stock-opname-export?` + queryString;
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

   // Konsolidasi
   const btnKonsolidasi = document.querySelector('.btn-konsolidasi');
   if (btnKonsolidasi) {
    btnKonsolidasi.addEventListener('click', function () {
      // Cek Izin Edit
      if (!isEdit) {
        Swal.fire({
          icon: 'error',
          title: 'Akses Ditolak',
          text: 'Anda tidak memiliki izin untuk konsolidasi data.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const modalInstance = new bootstrap.Modal(updRoleModal);
      modalInstance.show();

      document.getElementById('kons-tipe-barang').value = $('#filter-tipe-barang option:selected').val();
      document.getElementById('kons-bulan').value = $('#filter-bulan option:selected').val();
      document.getElementById('kons-tahun').value = $('#filter-tahun option:selected').val();
      document.getElementById('kons-nama-tipe-barang').value = $('#filter-tipe-barang option:selected').text();
      document.getElementById('kons-periode').value = $('#filter-bulan option:selected').text() + ' ' + $('#filter-tahun option:selected').text();
    });
   }

  // Adjustment
  const btnAdjustSelected = document.querySelector('.btn-adjust');
  if (btnAdjustSelected) {
    btnAdjustSelected.addEventListener('click', function () {
      // Cek Izin Edit
      if (!isEdit) {
        Swal.fire({
          icon: 'error',
          title: 'Akses Ditolak',
          text: 'Anda tidak memiliki izin untuk adjust data.',
          customClass: { confirmButton: 'btn btn-primary' }
        });
        return;
      }

      const tipe = $('#filter-tipe-barang').val();

      // Tentukan class tabel berdasarkan tipe
      const tableClass = (tipe == "S" || tipe == "T") ? '.datatables-sparepart' : '.datatables-barang';

      // Cari checkbox yang tercentang
      const selectedCheckbox = document.querySelector(`${tableClass} .dt-checkboxes:checked`);

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

        const modalInstance = new bootstrap.Modal(addRoleModal);
        modalInstance.show();

        let params = {
          tipe: tipe,
          kode: kode
        };

        // Bersihkan parameter kosong agar URL tidak terlalu panjang
        let queryString = $.param(params);

        // Pastikan route URL sesuai dengan konfigurasi route Anda
        const url = `${baseUrl}gudang/stock-opname-cek-saldo?` + queryString;

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
            document.getElementById('user_id').value = data.id;
            document.getElementById('add-tipe-barang').value = tipe;
            document.getElementById('add-nama-barang').value = data.nama_bahan;
            document.getElementById('add-satuan').value = data.satuan;
            document.getElementById('add-unit-adjust').value = data.unit_adjust;
            document.getElementById('add-harga-adjust').value = data.harga_adjust;
            document.getElementById('add-jumlah-adjust').value = data.jumlah_adjust;
            document.getElementById('add-unit-akhir').value = data.unit_akhir;
            document.getElementById('add-harga-akhir').value = data.harga_akhir;
            document.getElementById('add-jumlah-akhir').value = data.jumlah_akhir;

            $('#view-part').hide();
            if(tipe == "S" || tipe == "T") {
              $('#view-part').show();
              document.getElementById('add-nomor-input').value = data.kode_input;
              document.getElementById('add-merek-kendaraan').value = data.nama_merek;
              document.getElementById('add-tipe-kendaraan').value = data.nama_tipe;
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
            text: 'Gagal cek data Saldo',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        });
      }
    });
  }

  // validating form and updating user's data
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const fv = FormValidation.formValidation(filterForm, {
      fields: {
        tipe: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tipe Barang'
            }
          }
        },
        tgl_awal: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Periode'
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

  const addNewDataForm = document.getElementById('addNewDataForm');
  if (addNewDataForm) {
    const fv = FormValidation.formValidation(addNewDataForm, {
      fields: {
        nama_barang: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Nama Barang'
            }
          }
        },
        unit_adjust: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Qty Adjust'
            },
            callback: {
              callback: function (input) {
                // 1. Ambil nilai Qty yang diketik user
                // Hapus koma jika inputan menggunakan format ribuan (masking angka)
                const qtyValue = parseFloat(input.value.replace(/,/g, '')) || 0;
                
                // 2. Ambil batas maksimal dari input hidden unit_akhir
                const maxUnit = parseFloat(document.getElementById('unit_akhir').value) || 0;
    
                // 3. Logika Validasi Range
                if (qtyValue <= 0) {
                  return {
                    valid: false,
                    message: 'Qty tidak boleh kurang dari 0'
                  };
                }
                
                // Jika lulus semua kondisi, maka valid
                return true;
              }
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

      fetch(`${baseUrl}stock-opname-list`, {
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
          fetchDashboardTutupBuku();

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          // Refresh DataTable
          dt_barang_table && new DataTable(dt_barang_table).draw();
          dt_sparepart_table && new DataTable(dt_sparepart_table).draw();

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

  const konsolidasiDataForm = document.getElementById('konsolidasiDataForm');
  if (konsolidasiDataForm) {
    const fv = FormValidation.formValidation(konsolidasiDataForm, {
      fields: {
        nama_tipe_barang: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Tipe Barang'
            }
          }
        },
        periode: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Periode'
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
      // konsolidasiDataForm.submit();

      // adding or updating user when form successfully validate
      const formData = new FormData(konsolidasiDataForm);
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

      fetch(`${baseUrl}gudang/konsolidasi-saldo`, {
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
          fetchDashboardTutupBuku();

          // Hide offcanvas
          const offcanvasInstance = bootstrap.Modal.getInstance(updateRoleModal);
          offcanvasInstance && offcanvasInstance.hide();

          // Refresh DataTable
          dt_barang_table && new DataTable(dt_barang_table).draw();
          dt_sparepart_table && new DataTable(dt_sparepart_table).draw();

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

    const invoiceItemPriceList = document.querySelectorAll('.invoice-price');
    if (invoiceItemPriceList) {
      invoiceItemPriceList.forEach(function (invoiceItemPrice) {
        if (invoiceItemPrice) {
          invoiceItemPrice.addEventListener('input', event => {
            invoiceItemPrice.value = formatNumeral(event.target.value, {
              delimiter: ',',
              numeral: true
            });

            if (invoiceItemPrice.name != 'jumlah') {
              hitungTotalBarang();
            }
          });
        }
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
    // clearSelect('#add-group-bahan');

    $('#add-unit-adjust').addClass('is-invalid');
  }

  function fetchDashboardTutupBuku() {
    
    // Fetch data
    fetch(`${baseUrl}tutup-buku-list?tipe=total-data`)
    .then(response => {
      if (!response.ok) throw new Error('Gagal mengambil data');
      return response.json();
    })
    .then(data => {
      $("#total-bahan").html(data.saldo_bahan);
      $("#total-cat").html(data.saldo_cat);
      $("#total-sparepart").html(data.saldo_sparepart);
      $("#periode-bahan").html(data.periode_bahan);
      $("#periode-cat").html(data.periode_cat);
      $("#periode-sparepart").html(data.periode_sparepart);
    })
    .catch(error => {
      console.error('Error:', error);
    });
  }

  function hitungTotalBarang() {
    // Ambil elemen input di baris tersebut
    let elQty = $('#add-unit-adjust');
    let elHarga = $('#add-harga-adjust');
    let elJumlah = $('#add-jumlah-adjust');

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