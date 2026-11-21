class LwmAdminDropzone {
    constructor() {
    }

    init(ele_id, tinymce_id) {
        this.ele_id = $(ele_id);
        this.tinymce_id = tinymce_id || false;
        this.content_type = this.ele_id.data('content_type') || '';
        this.content_id = this.ele_id.data('content_id') || 0;
        this.content_input = this.ele_id.data('content_input') || '';
        this.content_input_ele = this.content_input ? $('#' + this.content_input) : null;
        this.route = this.ele_id.data('route');
        this.token = this.ele_id.data('token');
        this.primary = this.ele_id.data('primary') || false;
        this.default_values = this.ele_id.data('default_values') || [];
        this.image_use = [];
        this.dropzone = null;

        let vm = this;
        Dropzone.autoDiscover = false;
        vm.ele_id.dropzone({
            url: vm.route,
            params: {
                'content_type': vm.content_type,
                'content_id': vm.content_id
            },
            addRemoveLinks: true,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.svg,.webp",
            dictDefaultMessage: '<span class="text-center"><span class="font-lg visible-xs-block visible-sm-block visible-lg-block"><span class="font-lg"><i class="fa fa-caret-right text-danger"></i> Drop files <span class="font-xs">to upload</span></span><span>&nbsp&nbsp<h4 class="display-inline"> (Or Click)</h4></span>',
            dictResponseError: 'Error uploading file!',
            dictRemoveFile: '<i class="trash fa fa-trash-alt"></i>',
            headers: {
                'X-CSRF-TOKEN': vm.token
            },
            previewsContainer: ele_id + ' .dropzone-previews',
            init: function () {
                let myDropzone = this;
                vm.dropzone = myDropzone;

                // Dropzone render thẳng object 422 của Laravel nên ô lỗi chỉ hiện "[object Object]".
                // setTimeout để ghi đè sau khi listener mặc định của Dropzone render xong.
                myDropzone.on('error', function (file, message) {
                    if (!message) {
                        return;
                    }
                    let msg = typeof message === 'object'
                        ? ((message.errors && message.errors.file && message.errors.file[0]) || message.message)
                        : message;
                    if (!msg) {
                        return;
                    }
                    if (file.previewElement) {
                        setTimeout(function () {
                            $(file.previewElement).find('[data-dz-errormessage]').text(msg);
                        }, 0);
                    }
                    toastr.error(msg);
                });

                if (vm.default_values.length) {
                    vm.addDefaultImages();
                }

                myDropzone.on('removedfile', function (file) {
                    vm.removeImage($(file.previewElement));

                    // console.log(vm.content_input);
                    if (vm.content_input) {
                        // console.log(file);
                        vm.deleteContentInput(file.name);
                    }

                    if ('item' in file) {
                        file.item['_token'] = vm.token;

                        $.post('/backend/dropzone/delete-image', file.item, function (response) {
                            //console.log(response);
                        });
                    }
                });

                myDropzone.on('success', function (file, items) {
                    if (vm.tinymce_id) {
                        vm.imageClick(file);
                    }
                    file.item = items[0];

                    if (file.previewElement && file.item) {
                        file.previewElement.setAttribute('data-url', file.item.url);
                        file.previewElement.setAttribute('data-name', file.item.name);
                        file.previewElement.setAttribute('data-thumb_url', file.item.thumb_url);
                    }

                    if (vm.content_input_ele) {
                        let json = vm.getInputValue();

                        items.map(function (item) {
                            let data = {'name': item.name, 'url': item.url, 'thumb_url': item.thumb_url};
                            if (vm.primary) {
                                data['primary'] = false;
                            }
                            json.push(data);
                            vm.image_use.push(item.name);
                            return item;
                        });
                        vm.content_input_ele.val('').val(JSON.stringify(json));//.trigger('change');
                    }
                });

                myDropzone.on('addedfile', function (file) {
                    if (vm.primary) {
                        vm.addPrimaryIcon(file);
                    }
                });
            }
        });

        if (vm.content_input) {
            $('#' + vm.content_input).on('change', function () {
                let json_str = $(this).val()
                    , json = json_str ? JSON.parse(json_str) : [];

                $.each(json, function (i, file) {
                    if (vm.image_use.indexOf(file.name) === -1) {
                        vm.dropzone.displayExistingFile(file, file.url, null, null, false);
                        if (file.previewElement) {
                            file.previewElement.setAttribute('data-url', file.url);
                            file.previewElement.setAttribute('data-name', file.name);
                            file.previewElement.setAttribute('data-thumb_url', file.thumb_url);
                        }
                        if (vm.tinymce_id) {
                            vm.imageClick(file);
                        }
                        vm.image_use.push(file.name);
                    }
                });
            });
        }
    }

    addDefaultImages() {
        let vm = this;
        $.each(vm.default_values, function (i, file) {
            vm.dropzone.displayExistingFile(file, file.url, null, null, false);
            if (vm.tinymce_id) {
                vm.imageClick(file);
            }
            if (file.previewElement) {
                file.previewElement.classList.add('default-item');
                file.previewElement.setAttribute('data-url', file.url);
                file.previewElement.setAttribute('data-name', file.name);
                file.previewElement.setAttribute('data-thumb_url', file.thumb_url);
            }
            if ('name' in file) {
                vm.image_use.push(file.name);
            }
            if (vm.primary) {
                vm.addPrimaryIcon(file);
            }
        });
    }

    addPrimaryIcon(file) {
        let vm = this
            , default_str = 'primary' in file && file.primary ? 'default' : ''
            , wrapper = $(file.previewElement).closest('.image_dropzone_wrapper');
        file._primaryLink = Dropzone.createElement(`<a class="dz-primary ${default_str}" href="javascript:undefined;"><i class="far fa-check-square"></i></a>`);
        file.previewElement.appendChild(file._primaryLink);
        file._primaryLink.addEventListener('click', function (e) {
            // file.name là tên gốc phía trình duyệt, còn JSON trong textarea lưu tên server
            // trả về (có prefix timestamp) → phải lấy data-name thì setPrimaryImage mới khớp.
            vm.setPrimaryImage(file.previewElement.getAttribute('data-name') || file.name);
            $('.dz-primary', wrapper).removeClass('default');
            $(file.previewElement).find('.dz-primary').addClass('default');
        });
    }

    imageClick(file) {
        let vm = this;
        if (typeof tinymce == 'object') {
            file.previewElement.addEventListener("click", function () {
                tinymce.get(vm.tinymce_id).insertContent(file.item.image_tag);
            });
        }
    }

    removeImage(file_item) {
        let target_input = file_item.closest('.dropzone-upload').find('textarea');
        if (!target_input.length) {
            return false;
        }
        let item_url = '/storage/images/' + file_item.find('img').attr('alt')
            , json_value = target_input.val() !== '' ? JSON.parse(target_input.val()) : [];


        json_value = json_value.filter(function (item) {
            return item.url !== item_url;
        });

        if (json_value.length === 0) {
            target_input.val('');
        } else {
            target_input.val(JSON.stringify(json_value));
        }

    }

    getInputValue() {
        let vm = this;
        if (!vm.content_input) {
            return [];
        }

        let input_val = vm.content_input_ele.val();
        if (input_val === '') {
            return [];
        }
        let json_value = JSON.parse(vm.content_input_ele.val());
        return json_value ? json_value : [];
    }

    setPrimaryImage(image_name) {
        let vm = this
            , json_value = vm.getInputValue();
        if (json_value) {
            json_value = json_value.map(function (item) {
                item.primary = item.name === image_name;
                return item;
            });
            if (json_value.length === 0) {
                vm.content_input_ele.val('');
            } else {
                vm.content_input_ele.val(JSON.stringify(json_value));
            }
        }
    }

    deleteContentInput(image_name) {
        let vm = this;
        if (!vm.content_input) {
            return false;
        }
        let json_value = vm.getInputValue();
        if (json_value) {
            json_value = json_value.filter(function (item) {
                return item.name !== image_name;
            });
            if (json_value.length === 0) {
                vm.content_input_ele.val('');
            } else {
                vm.content_input_ele.val(JSON.stringify(json_value));
            }
        }
        return true;
    }
}
