/**
 * Page Data management
 */

  'use strict';

  // Datatable (js)
  document.addEventListener('DOMContentLoaded', function (e) {
    let isAdd, isEdit, isDelete;

    // ajax setup
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
 
    fetchDashboardTutupBuku();
 
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
    

    // validating form and updating user's data
    const addNewDataForm = document.getElementById('addNewDataForm');
    if (addNewDataForm) {
      const fv = FormValidation.formValidation(addNewDataForm, {
        fields: {
          tipe: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tipe Barang'
              }
            }
          },
          bulan: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Bulan'
              }
            }
          },
          tahun: {
            validators: {
              notEmpty: {
                message: 'Silahkan Input Tahun'
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

        fetch(`${baseUrl}tutup-buku-list`, {
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
            fetchDashboardTutupBuku();

            clearFormData();

            // Hide offcanvas
            // const offcanvasInstance = bootstrap.Modal.getInstance(addRoleModal);
            // offcanvasInstance && offcanvasInstance.hide();
            
            // Refresh DataTable
            // dt_basic_table && new DataTable(dt_basic_table).draw();

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
  });

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

    clearSelect('#add-tipe-barang');
    clearSelect('#add-bulan');
    clearSelect('#add-tahun');
  }
 
 