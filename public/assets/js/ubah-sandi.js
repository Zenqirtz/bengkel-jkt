/**
 * Page Data management
 */

 'use strict';

 // Datatable (js)
 document.addEventListener('DOMContentLoaded', function (e) {
   
   // ajax setup
   $.ajaxSetup({
     headers: {
       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
     }
   });
 
   // validating form and updating user's data
   const formChangePassword = document.getElementById('formChangePassword');
 
   // user form validation
   if (formChangePassword) {
     const fv = FormValidation.formValidation(formChangePassword, {
       fields: {
        newPassword: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Sandi Baru'
            },
            stringLength: {
              min: 8,
              message: 'Sandi harus lebih dari 8 Karakter'
            }
          }
        },
        confirmPassword: {
          validators: {
            notEmpty: {
              message: 'Silahkan Input Ulangi Sandi Baru'
            },
            identical: {
              compare: function () {
                return formChangePassword.querySelector('[name="newPassword"]').value;
              },
              message: 'Kata sandi dan konfirmasinya tidak sama'
            },
            stringLength: {
              min: 8,
              message: 'Sandi harus lebih dari 8 Karakter'
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
       // adding or updating user when form successfully validate
       const formData = new FormData(formChangePassword);
       const formDataObj = {};
 
       // Convert FormData to URL-encoded string
       formData.forEach((value, key) => {
         formDataObj[key] = value;
       });
 
       const searchParams = new URLSearchParams();
       for (const [key, value] of Object.entries(formDataObj)) {
         searchParams.append(key, value);
       }
 
       fetch(`${baseUrl}ubah-sandi`, {
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
            const { status, message } = JSON.parse(result);

            // reset input/textarea standar
            formChangePassword?.reset?.();

            if(status) {
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
              // sweetalert
              Swal.fire({
                icon: 'error',
                title: `Error!`,
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
 