/**
 * Page Data management
 */

 'use strict';

 document.addEventListener('DOMContentLoaded', function (e) {
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

  const sel = document.getElementById('add-group');
  if (!sel) return;

  const isAdd = sel.dataset.add;

  // Handler umum (berlaku untuk select biasa maupun Select2)
  const go = (val) => {
    if (!USER_PRIV_INDEX_URL) return;

    if(isAdd) {
      // jika kosong (di-clear), kembali ke halaman index tanpa parameter
      const url = val ? `${USER_PRIV_INDEX_URL}?id=${encodeURIComponent(val)}` : USER_PRIV_INDEX_URL;
      window.location.assign(url);
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
  };

  // Event standar
  sel.addEventListener('change', function () { go(this.value); });

  // Jika pakai Select2, tangkap event spesifik juga (opsional)
  if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
    const $sel = jQuery(sel);
    $sel.on('select2:select',   (e) => go(e.params?.data?.id || sel.value));
    $sel.on('select2:clear',    ()   => go(''));
  }

  FormValidation.formValidation(document.getElementById('addRoleForm'), {
    fields: {
      groupid: {
        validators: {
          notEmpty: {
            message: 'Silahkan Pilih Nama Group'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        // eleInvalidClass: '',
        eleValidClass: '',
        rowSelector: '.form-control-validation'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  // Select All checkbox click
  const selectAll = document.querySelector('#selectAll'),
  checkboxList = document.querySelectorAll('[type="checkbox"]');
  selectAll.addEventListener('change', t => {
    checkboxList.forEach(e => {
      e.checked = t.target.checked;
    });
  });

 });
 