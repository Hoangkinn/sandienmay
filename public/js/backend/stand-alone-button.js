(function ($) {
    let replace_domain = function (url, domain) {
        domain = domain || document.location.origin;
        return url.replace(domain, '');
    };

    $.fn.filemanager = function (type, options) {
        type = type || 'file';

        this.on('click', function (e) {
            let route_prefix = $(this).closest('.file_manager_button').data('route')
                , site_url = $(this).closest('.file_manager_button').data('site_url')
                , target_input = $('#' + $(this).data('input'))
                , target_preview = $('#' + $(this).data('preview'));

            window.open(route_prefix + '?type=' + type, 'FileManager', 'width=900,height=600');
            window.SetUrl = function (items) {
                //console.log(items);
                let json = [];
                if (target_input.val() !== '') {
                    json = JSON.parse(target_input.val());
                }

                let urls = json.map(function (item) {
                    return replace_domain(item.url, site_url);
                });

                items.map(function (item) {
                    item.url = replace_domain(item.url, site_url);
                    item.thumb_url = replace_domain(item.thumb_url, site_url);

                    if (urls.indexOf(item.url) === -1) {
                        json.push({'name': item.name, 'url': item.url, 'thumb_url': item.thumb_url});
                    } else {
                        console.log('anh da duoc chon: ' + item.url);
                    }
                    return item;
                });
                //
                $('.file_manager_lists').attr('data-count', json.length);

                // set the value of the desired input to image url
                target_input.val('').val(JSON.stringify(json)).trigger('change');

                // clear previous preview
                target_preview.html('');

                // set or change the preview image src
                if (type === 'image') {
                    let primary_image = target_input.data('primary_image');
                    json.forEach(function (item) {
                        let template = `<p class="file_manager_item"><a target="_blank" href="${item.url}">
                        <img src="${item.thumb_url}" height="80px" loading="lazy"/>
                        </a><i class="trash fa fa-trash-alt"></i>`;
                        if (primary_image) {
                            let primary_active = 'primary' in item && item.primary === true ? 'active' : '';
                            template += `<i class="primary-image ${primary_active} far fa-check-square"></i>`;
                        }
                        template += `</p>`;

                        target_preview.append(template);
                    });
                } else {
                    json.forEach(function (item) {
                        let template = `<p class="file_manager_item"><a target="_blank" href="${item.url}">
                        ${item.name}</a><i class="trash fa fa-trash-alt"></i></p>`;
                        target_preview.append(template);
                    });
                }

                // trigger change event
                target_preview.trigger('change');
            };
            return false;
        });
    }

})(jQuery);
