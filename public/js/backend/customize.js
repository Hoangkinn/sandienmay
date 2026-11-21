// Preview button handler (supports left-click and mouse wheel middle-click auxclick)
$(document).on('click auxclick', '.btn-preview', function (e) {
    // Only handle left-click (button 0) or middle-click (button 1)
    if (e.type === 'auxclick' && e.button !== 1) return;
    if (e.type === 'click' && e.button !== 0) return;

    e.preventDefault();
    e.stopPropagation();

    var $btn = $(this);
    var $form = $btn.closest('form');
    if (!$form.length) {
        $form = $('form').first();
    }
    if (!$form.length) return;

    var previewUrl = $btn.attr('data-preview-action') || $btn.attr('formaction') || $btn.attr('href');
    if (!previewUrl || previewUrl === '#' || previewUrl.startsWith('javascript:')) return;

    var originalAction = $form.attr('action');
    var originalTarget = $form.attr('target');

    // Sync TinyMCE or CKEditor instances if present
    if (typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
    }

    $form.attr('action', previewUrl);
    $form.attr('target', '_blank');

    var $tempInput = $form.find('input[name="_preview_flag"][data-preview-flag="1"]');
    if (!$tempInput.length) {
        $tempInput = $('<input type="hidden" name="_preview_flag" value="preview" data-preview-flag="1">').appendTo($form);
    }

    // Dùng trigger thay vì gọi trực tiếp [0].submit() để tránh xung đột với nút submit có name="submit"
    $form.off('submit.preview_fix').on('submit.preview_fix', function (e) {
        // Cho phép form submit bình thường khi đổi action sang trang preview
    });

    // Gọi phương thức submit an toàn qua jQuery hoặc HTMLFormElement gốc thông qua call
    HTMLFormElement.prototype.submit.call($form[0]);

    setTimeout(function () {
        if (originalAction) {
            $form.attr('action', originalAction);
        } else {
            $form.removeAttr('action');
        }

        if (originalTarget) {
            $form.attr('target', originalTarget);
        } else {
            $form.removeAttr('target');
        }

        $tempInput.remove();
    }, 200);
});

