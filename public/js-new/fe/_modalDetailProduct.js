class ModalDetailProduct {
    static url = '';
    static modalDetail = document.querySelector('#modalDetailProduct');

    static addEventClick() {
        for (const el of document.querySelectorAll('.jsShowModalDetailProduct:not([data-inited])')) {
            el.dataset.inited = 1;

            el.addEventListener('click', async e => {
                // Nam trong the <a> cua o anh nen chan dieu huong trang chi tiet
                e.preventDefault();
                let url = el.closest('.item-product').querySelector('a').href;
                await this.showModal(url);
            });
        }
    }
    // static async showModal(url){
    //     Common.loadJs('_Library.min.js').then(() => {
    //         postData('get', url, { type: 'getFirstPage' }, false)
    //             .then(rs => rs.json())
    //             .then(rs3 => {
    //                 this.modalDetail.querySelector('.showContent').innerHTML = rs3.html;
    //                 jsPrice();
    //                 this.modalDetail.showModal();
    //                 setTimeout(() => Library.init());
    //             });
    //     });
    // }
    static async showModal(url) {
    Common.loadJs('_Library.min.js').then(() => {
        postData('get', url, { type: 'getFirstPage' }, false)
            .then(async rs => {
                // Đọc dữ liệu dưới dạng text thô trước để kiểm soát
                const responseText = await rs.text();
                try {
                    // Cố gắng parse chuỗi nhận được thành JSON Object
                    const data = JSON.parse(responseText);
                    return data;
                } catch (err) {
                    // Nếu bản thân responseText đã là một chuỗi JSON đã bị parse hoặc object lỗi
                    // Ta kiểm tra xem nó có chứa thuộc tính html không
                    if (responseText.includes('"html":') || responseText.startsWith('{')) {
                         try {
                             // Fix lỗi chuỗi bọc kép nếu hệ thống tự convert sai
                             let cleanData = (new Function("return " + responseText))();
                             return cleanData;
                         } catch(e) { }
                    }
                    // Trả về text thô nếu không thể cứu vãn
                    return { html: responseText };
                }
            })
            .then(rs3 => {
                // Đổ dữ liệu HTML sạch vào modal
                if (rs3 && rs3.html) {
                    this.modalDetail.querySelector('.showContent').innerHTML = rs3.html;
                } else if (typeof rs3 === 'string') {
                    this.modalDetail.querySelector('.showContent').innerHTML = rs3;
                }
                
                // Kích hoạt hiển thị modal và lại sức cho thư viện giao diện
                jsPrice();
                this.modalDetail.showModal();
                setTimeout(() => {
                    Library.init();
                    
                    // KÍCH HOẠT LẠI ĐỒNG HỒ ĐẾM NGƯỢC RIÊNG CHO MODAL TẠI ĐÂY
                    if (typeof window.initProductCountdown === 'function') {
                        window.initProductCountdown(this.modalDetail.querySelector('.showContent'));
                    }

                    // Popup chèn HTML muộn nên slider chưa được mount; chỉ quét trong modal
                    // để không đụng các carousel của trang.
                    if (typeof CustomGlide !== 'undefined') {
                        CustomGlide.initAll('#modalDetailProduct .glide:not(.glide--slider)');
                    } else {
                        Common.loadJs('CustomGlide.min.js')
                            .then(() => CustomGlide.initAll('#modalDetailProduct .glide:not(.glide--slider)'));
                    }
                }, 50);
            })
            .catch(error => {
                console.error("Lỗi luồng xử lý popup:", error);
            });
    });
}
    static hideModal(){
        this.modalDetail.close();
        this.modalDetail.querySelector('.showContent').innerHTML = '';
    }
}

class ModalCompareProduct {
    static ids = [];
    static modalCompare = document.querySelector('#modalCompareProduct');
    static endPointViewCompare = '/ajax/showModalCompare';
    static publicUrlCompare = '/so-sanh-san-pham/';

    static showModal(){
        postData('get', this.endPointViewCompare, this.ids, false)
            .then(rs => rs.json())
            .then(rs => {
                this.modalCompare.querySelector('.showContent').innerHTML = rs.html;
                jsPrice();
                this.modalCompare.show();
            })
    }

    static hideModal(){
        this.modalCompare.close();
        this.modalCompare.querySelector('.showContent').innerHTML = '';
        this.ids = [];          
        this.resetSwitch();     
    }

    static addEventClick() {
        for (const el of document.querySelectorAll('.jsShowModalCompareProduct:not([data-inited])')) {
            el.dataset.inited = 1;
            const id = el.getAttribute('data-id');

            el.addEventListener('click', e => {
                ModalCompareSelectProduct.hideModal();
                this.appendId(id); 
                
                if (this.ids.length > 0){
                    this.showModal();
                } else {
                    this.hideModal();
                }
                
                this.resetSwitch();
            });
        }
    }

    static submit() {
        if (!this.ids.length) return;
        window.location.href = baseUrl + this.publicUrlCompare + this.ids.join('-');
    }

    static appendId(id) {
        id = String(id);
        const index = this.ids.indexOf(id);

        if (index !== -1) {
            this.ids.splice(index, 1);
            return;
        }

        if (this.ids.length >= 3) {
            this.ids = [];
        }

        this.ids.push(id);
    }

    static remove(index){
        if(index === undefined || index === null || isNaN(index) || index < 0 || index >= this.ids.length){
            this.ids = []; 
        } else {
            this.ids.splice(index, 1); 
        }

        if (this.ids.length > 0) {
            this.showModal();
        } else {
            this.hideModal();
        }
        this.resetSwitch();
    }

    static resetSwitch(){
        document.querySelectorAll('.jsShowModalCompareProduct').forEach(el => {
            let id = String(el.getAttribute('data-id'));
            let checkbox = el.querySelector('input[type="checkbox"]');
            let textSpan = el.querySelector('.jsCompareText') || el.querySelector('span:not([class*="icon"]):not(.badge)');

            if (this.ids.includes(id)) {
                el.classList.add('compare-switch-on');
                if (checkbox) checkbox.checked = true;
                if (textSpan) textSpan.textContent = 'Bỏ so sánh';
                if (el.tagName === 'BUTTON') el.textContent = 'Bỏ chọn';
            } else {
                el.classList.remove('compare-switch-on');
                if (checkbox) checkbox.checked = false;
                if (textSpan) textSpan.textContent = 'So sánh';
                if (el.tagName === 'BUTTON') el.textContent = 'Chọn';
            }
        });
    }
}
class ModalCompareSelectProduct {
    static endPointShowModal = '/ajax/showModalCompareSelectProduct';
    static modal = document.querySelector('#modalCompareSelectProduct');
    static timer = null;

    static async showModal(keyword) {
        postData('get', this.endPointShowModal, { id: ModalCompareProduct.ids[0] ?? 0, keyword:keyword ?? '' }, false)
            .then(rs => rs.json())
            .then(rs => {
                this.modal.querySelector('.showContent').innerHTML = rs.html;
                this.modal.showModal();
                jsPrice();
                ModalCompareProduct.addEventClick();
                ModalCompareProduct.resetSwitch();
            });
    }

    static hideModal() {
        this.modal.close();
        this.modal.querySelector('.showContent').innerHTML = '';
    }

    static init() {
        // Dialog so sánh bị comment trên trang chủ/danh mục -> modal null, nếu không chặn thì
        // dòng querySelector dưới throw và toàn bộ listener bên dưới (nút X của popup xem nhanh,
        // stepper số lượng, initProductCountdown) không bao giờ được gắn.
        if (!this.modal) return;

        const input = this.modal.querySelector('input');
        if (!input) return;

        input.addEventListener('input', e => {
            clearTimeout(this.timer);
            const keyword = e.target.value.trim();
            this.timer = setTimeout(() => {
                if (keyword) this.showModal(keyword);
            }, 300);
        });
    }
}
ModalDetailProduct.addEventClick();
ModalCompareProduct.addEventClick();
ModalCompareProduct.resetSwitch();
ModalCompareSelectProduct.init();

function addClickOutsideToClose(dialog, hideCallback) {
    // Dialog so sánh đang bị comment trên nhiều trang -> truyền vào null, nếu không chặn thì
    // dòng này throw và toàn bộ listener bên dưới (kể cả nút X của popup xem nhanh) không được gắn.
    if (!dialog) return;
    dialog.addEventListener('click', e => {
        const rect = dialog.getBoundingClientRect();
        if (
            e.clientX < rect.left ||
            e.clientX > rect.right ||
            e.clientY < rect.top ||
            e.clientY > rect.bottom
        ) {
            hideCallback();
        }
    });
}

addClickOutsideToClose(ModalDetailProduct.modalDetail, () => ModalDetailProduct.hideModal());
addClickOutsideToClose(ModalCompareProduct.modalCompare, () => ModalCompareProduct.hideModal());
addClickOutsideToClose(ModalCompareSelectProduct.modal, () => ModalCompareSelectProduct.hideModal());

document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-close');

        if (target === 'modalDetailProduct') ModalDetailProduct.hideModal();
        if (target === 'modalCompareProduct') ModalCompareProduct.hideModal();
        if (target === 'modalCompareSelectProduct') ModalCompareSelectProduct.hideModal();
    });
});

// Nội dung popup chèn bằng innerHTML nên không kịp dính listener một-lần của pages/product.js
document.addEventListener('click', e => {
    const btn = e.target.closest('#modalDetailProduct .input-number .btn-change');
    if (!btn) return;

    const input = btn.closest('.input-number').querySelector('input[type=number]');
    if (!input) return;

    const step = parseInt(btn.dataset.step, 10) || 1;
    const min = input.min ? parseInt(input.min, 10) : 1;
    const max = input.max ? parseInt(input.max, 10) : 500000;

    input.value = Math.min(max, Math.max(min, (parseInt(input.value, 10) || 0) + step));
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
});



window.initProductCountdown = function (context = document) {
            const THREE_DAYS_MS = 3 * 24 * 60 * 60 * 1000;
            let targetTime = localStorage.getItem("countdownTargetTime");
            const now = new Date().getTime();

            if (!targetTime || now >= parseInt(targetTime)) {
                targetTime = now + THREE_DAYS_MS;
                localStorage.setItem("countdownTargetTime", targetTime);
            } else {
                targetTime = parseInt(targetTime);
            }

            // Quét các class đếm ngược trong block context chỉ định
            const daysContainer = context.querySelector(".timerDaysContainer");
            const daysEl = context.querySelector(".timerDays");
            const hoursEl = context.querySelector(".timerHours");
            const minutesEl = context.querySelector(".timerMinutes");
            const secondsEl = context.querySelector(".timerSeconds");

            if (!hoursEl || !minutesEl || !secondsEl) return;

            // Xóa bộ đếm cũ nếu context này đã chạy trước đó để tránh nhân bản trùng lặp
            if (context.countdownInterval) {
                clearInterval(context.countdownInterval);
            }

            function updateCountdown() {
                const currentTime = new Date().getTime();
                let timeRemaining = targetTime - currentTime;

                if (timeRemaining <= 0) {
                    targetTime = currentTime + THREE_DAYS_MS;
                    localStorage.setItem("countdownTargetTime", targetTime);
                    timeRemaining = THREE_DAYS_MS;
                }

                const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

                if (days > 0) {
                    if (daysContainer) daysContainer.style.display = "inline";
                    if (daysEl) daysEl.textContent = String(days).padStart(2, "0");
                } else {
                    if (daysContainer) daysContainer.style.display = "none";
                }

                hoursEl.textContent = String(hours).padStart(2, "0");
                minutesEl.textContent = String(minutes).padStart(2, "0");
                secondsEl.textContent = String(seconds).padStart(2, "0");
            }

            updateCountdown();
            context.countdownInterval = setInterval(updateCountdown, 1000);
        };

        // Kích hoạt cho trang chi tiết chạy mặc định khi load trang xong
        document.addEventListener("DOMContentLoaded", function () {
            window.initProductCountdown(document);
        });

// Nút "Lưu mã" của khối Mã khuyến mãi đặt ở đây vì popup xem nhanh mở cả trên
// trang chủ / danh mục, những nơi không nạp pages/product.js.
document.addEventListener('click', function (event) {
    const button = event.target.closest('.btn-copy-coupon');
    if (!button) return;
    
    const code = button.getAttribute('data-code');
    console.log('Mã coupon được copy:', code);

    // GỌI TRỰC TIẾP LỆNH COPY NGAY TẠI SỰ KIỆN CLICK (KHÔNG QUA HÀM TRUNG GIAN)
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(() => {
            handleSuccess(button, code);
        }).catch(err => {
            console.error('Lỗi copy:', err);
            fallbackCopy(code, button);
        });
    } else {
        fallbackCopy(code, button);
    }
});

// Hàm fallback phụ trợ nếu trình duyệt cũ không hỗ trợ
function fallbackCopy(text, button) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            handleSuccess(button, text);
        } else {
            alert('Không thể copy mã!');
        }
    } catch (err) {
        console.error('Lỗi fallback copy:', err);
    }
    document.body.removeChild(textarea);
}

// Xử lý giao diện sau khi copy thành công
function handleSuccess(button, code) {
    showCustomToast("Đã lưu mã giảm giá: " + code);

    const originalText = button.innerText;
    button.innerText = 'Đã lưu!';
    button.style.backgroundColor = '#28a745';
    button.style.color = '#fff';

    setTimeout(() => {
        button.innerText = originalText;
        button.style.backgroundColor = '';
        button.style.color = '';
    }, 2000);
}

// Hàm hiển thị Toast góc trên bên phải
function showCustomToast(message) {
    const oldToast = document.getElementById('custom-coupon-toast');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'custom-coupon-toast';
    toast.innerText = message;
    
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        backgroundColor: '#00b074',
        color: '#fff',
        padding: '12px 20px',
        borderRadius: '4px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '999999',
        fontSize: '14px',
        fontWeight: '500',
        transition: 'all 0.3s ease',
        opacity: '0',
        transform: 'translateY(-10px)'
    });

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
