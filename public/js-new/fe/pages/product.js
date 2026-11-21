// Product page: quantity stepper, review statistics, coupon copy
const input = document.querySelector('.input-number input[type=number]');
document.querySelectorAll('.input-number .btn-change').forEach(btn => {
    btn.addEventListener('click', () => {
        let step = parseInt(btn.dataset.step, 10) || 1;
        let min = input.min ? parseInt(input.min, 10) : -Infinity;
        let max = input.max ? parseInt(input.max, 500000) : Infinity;
        let val = parseInt(input.value, 10) || 0;

        val += step;
        if (val < min) val = min;
        if (val > max) val = max;

        input.value = val;

        // phát sự kiện input & change để form/JS khác có thể bắt được
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});


function loadStatisticReview() {
    setTimeout(() => {
        let boxStatistic = document.querySelector('#statisticReview');
        if (!boxStatistic || !window.endPointGetStatisticReview) return;
        let rows = boxStatistic.querySelectorAll(':scope > div'); // 5 dòng
        let result = postData('get', endPointGetStatisticReview, {}, false);
        result.then(e => {
            if (!e.ok) return;
            e.json().then(rs => {
                let sum = Object.values(rs).reduce((a, b) => a + b, 0);
                Object.entries(rs).forEach(([star, count]) => {
                    let index = 6 - parseInt(star);
                    let row = rows[index - 1]; 
                    if (!row) return;
                    let progressBar = row.querySelector('.progressProduct .progress-bar');
                    let countVote = row.querySelector('.countVote');
                    let percent = sum > 0 ? (count / sum * 100) : 0;
                    if (progressBar) progressBar.style.width = percent + '%';
                    if (countVote) countVote.innerText = Math.round(percent) + '%';
                });
            });
        });
    }, 3500);
}
loadStatisticReview();

// Nút "Yêu cầu báo giá": điện thoại/tablet gọi thẳng hotline, máy tính mở dialog form
// (PC không có app quay số nên tel: chỉ ra hộp thoại "chọn ứng dụng" của Windows).
(function () {
    const dialog = document.getElementById('quoteDialog');
    if (!dialog) return;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-quote[data-quote-phone]');
        if (!btn) return;

        if (window.matchMedia('(pointer: coarse)').matches) {
            window.location.href = 'tel:' + btn.dataset.quotePhone;
            return;
        }

        const qty = document.querySelector('.input-number input[type=number]');
        const hiddenQty = dialog.querySelector('input[name=quantity]');
        if (qty && hiddenQty) hiddenQty.value = parseInt(qty.value, 10) || 1;

        dialog.showModal();
        dialog.querySelector('input[name=name]')?.focus();
    });

    dialog.querySelector('.quote-dialog-close')?.addEventListener('click', () => dialog.close());

    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) dialog.close();
    });

    window.onQuoteSent = (rs, form) => {
        form.reset();
        dialog.close();
    };

    window.onQuoteError = (rs, form) => {
        const errors = rs && rs.errors ? rs.errors : {};
        // input ẩn không bong bóng được, mà setCustomValidity còn chặn lần submit sau
        form.querySelectorAll('input[type=hidden]').forEach((el) => el.setCustomValidity(''));
        const messages = Object.values(errors).flat();
        // 429 (throttle) và lỗi khác không có errors -> phải ra toast, không được im lặng
        Toastr.error(messages.length ? messages.join('\n')
            : ((rs && rs.message) || 'Gửi yêu cầu thất bại, vui lòng thử lại.'));
    };
})();

