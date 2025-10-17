"use strict";

// Giỏ hàng
let cart = [];
let cartModal;

// Thêm CSS animations
const style = document.createElement('style');
style.id = 'cart-animations';
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(450px); opacity: 0; }
        40% { transform: translateX(-10px); }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    
    @keyframes progressBar {
        to { width: 0; }
    }
    
    .custom-notification {
        position: fixed;
        top: 30px;
        right: 20px;
        z-index: 10000;
        padding: 15px 20px;
        color: #fff;
        width: 400px;
        max-width: 90vw;
        display: grid;
        grid-template-columns: 60px 1fr 40px;
        border-radius: 8px;
        background-image: linear-gradient(to right, #0abf3055, #22242f 30%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        animation: slideInRight 0.4s ease;
    }
    
    .custom-notification .fa-circle-check, .custom-notification .fa-info-circle, .custom-notification .fa-exclamation-triangle {
        color: #0abf30;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2rem;
    }
    
    .custom-notification .fa-exclamation-triangle {
        color: #ff6b35;
    }
    
    .custom-notification .content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .custom-notification .title {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .custom-notification span {
        color: #fff;
        opacity: 0.9;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    
    .custom-notification .close-btn {
        color: #fff;
        opacity: 0.6;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.3s;
        font-size: 1.2rem;
        z-index: 10001;
    }
    
    .custom-notification .close-btn:hover {
        opacity: 1;
    }
    
    .custom-notification .progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        background-color: #0abf30;
        width: 100%;
        height: 3px;
        box-shadow: 0 0 10px #0abf30;
        animation: progressBar 5s linear forwards;
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo cartModal
    const modalElement = document.getElementById('cartModal');
    if (modalElement) {
        cartModal = new bootstrap.Modal(modalElement, { backdrop: 'static' });
        console.log('cartModal initialized:', cartModal);
    } else {
        console.error('Modal element #cartModal not found!');
    }

    // Gán sự kiện cho nút giỏ hàng
    const cartButton = document.getElementById('cartButton');
    if (cartButton) {
        cartButton.addEventListener('click', openCart);
    } else {
        console.error('Cart button #cartButton not found!');
    }

    // Load giỏ hàng từ localStorage
    const savedCart = localStorage.getItem('cartData');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartIcon();
    }

    // Gán sự kiện cho các nút Đặt Ngay
    document.querySelectorAll('.btn-danger, .order-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.card, .menu-card, .combo-card');
            const name = card.querySelector('.card-title, h3, h5').textContent.trim();
            const priceText = card.querySelector('.card-text, .price').textContent.trim();
            let img = card.querySelector('.cover-image, .card-img-top, img');
            img = img ? img.src : 'image/logo.png';
            const price = parseInt(priceText.replace(/[^\d]/g, ''));
            addToCart(name, price, img);
        });
    });

    // Xử lý nút đóng cho toast tĩnh (nếu có trong HTML)
    document.querySelectorAll('.toast .fa-xmark').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const toast = this.closest('.toast');
            toast.style.animation = 'fadeOut 0.4s ease';
            setTimeout(() => {
                toast.remove();
            }, 400);
        });
    });
});

// Hàm hiển thị thông báo
function showNotification(message, type = 'success') {
    // Xóa thông báo cũ nếu có
    const oldNotification = document.querySelector('.custom-notification');
    if (oldNotification) oldNotification.remove();

    const notification = document.createElement('div');
    notification.className = 'custom-notification';
    
    let icon, bgColor, iconColor;
    
    if (type === 'success') {
        icon = '<i class="fa-solid fa-circle-check"></i>';
        bgColor = 'linear-gradient(to right, #0abf3055, #22242f 30%)';
        iconColor = '#0abf30';
    } else if (type === 'warning') {
        icon = '<i class="fa-solid fa-exclamation-triangle"></i>';
        bgColor = 'linear-gradient(to right, #ff6b3555, #22242f 30%)';
        iconColor = '#ff6b35';
    } else {
        icon = '<i class="fa-solid fa-info-circle"></i>';
        bgColor = 'linear-gradient(to right, #3498db55, #22242f 30%)';
        iconColor = '#3498db';
    }
    
    const title = type === 'success' ? 'Thành công' : 
                  type === 'warning' ? 'Cảnh báo' : 'Thông báo';
    
    notification.innerHTML = `
        ${icon}
        <div class="content">
            <div class="title">${title}</div>
            <span>${message}</span>
        </div>
        <i class="fa-solid fa-xmark close-btn"></i>
    `;
    
    notification.style.backgroundImage = bgColor;
    
    const iconEl = notification.querySelector('i:first-child');
    iconEl.style.color = iconColor;
    
    const closeBtn = notification.querySelector('.close-btn');
    
    const progressBar = document.createElement('div');
    progressBar.className = 'progress-bar';
    progressBar.style.backgroundColor = iconColor;
    progressBar.style.boxShadow = `0 0 10px ${iconColor}`;
    notification.appendChild(progressBar);
    
    document.body.appendChild(notification);
    
    // Xử lý sự kiện đóng
    closeBtn.addEventListener('click', () => {
        notification.style.animation = 'fadeOut 0.4s ease';
        setTimeout(() => notification.remove(), 400);
    });
    
    // Tự động xóa sau 5 giây nếu không đóng thủ công
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'fadeOut 0.4s ease';
            setTimeout(() => notification.remove(), 400);
        }
    }, 5000);
}

function addToCart(name, price, img) {
    const existing = cart.find(item => item.name === name);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ name, price, img, qty: 1 });
    }
    saveCart();
    updateCartIcon();
    showNotification(`Đã thêm <strong>${name}</strong> vào giỏ hàng!`, 'success');
}

function updateCartIcon() {
    const cartBadges = document.querySelectorAll('#cartCount');
    const totalQty = cart.reduce((sum, i) => sum + i.qty, 0);
    cartBadges.forEach(badge => {
        badge.textContent = totalQty;
        badge.style.display = totalQty > 0 ? 'flex' : 'none';
    });
}

function openCart(event) {
    if (event) event.preventDefault();
    if (!cartModal) {
        const modalElement = document.getElementById('cartModal');
        if (modalElement) {
            cartModal = new bootstrap.Modal(modalElement, { backdrop: 'static' });
        } else {
            console.error('Cart modal element not found!');
            showNotification('Không thể mở giỏ hàng: Modal không tìm thấy!', 'info');
            return;
        }
    }
    updateCartModal();
    cartModal.show();
}

function updateCartModal() {
    const cartContainer = document.getElementById('cartItems');
    cartContainer.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        cartContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: #999;">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; margin-bottom: 20px; opacity: 0.5;"></i>
                <p style="font-size: 1.1rem; margin: 0;">Giỏ hàng trống</p>
            </div>
        `;
    } else {
        cart.forEach((item, index) => {
            const itemTotal = item.qty * item.price;
            total += itemTotal;
            cartContainer.innerHTML += `
                <div class="cart-item">
                    <img src="${item.img}" class="cart-item-image" alt="${item.name}">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${item.price.toLocaleString('vi-VN')}đ</div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="cart-qty-btn" onclick="changeQty(${index}, -1)">-</button>
                        <span class="cart-qty-value">${item.qty}</span>
                        <button class="cart-qty-btn" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                    <div class="cart-item-total">
                        <div class="cart-item-total-price">${itemTotal.toLocaleString('vi-VN')}đ</div>
                        <button class="cart-item-remove" onclick="removeItem(${index})" title="Xóa">×</button>
                    </div>
                </div>
            `;
        });
    }

    document.getElementById('cartTotal').textContent = total.toLocaleString('vi-VN') + 'đ';
}

function changeQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }
    saveCart();
    updateCartIcon();
    updateCartModal();
}

function removeItem(index) {
    const itemName = cart[index].name;
    cart.splice(index, 1);
    saveCart();
    updateCartIcon();
    updateCartModal();
    showNotification(`Đã xóa <strong>${itemName}</strong> khỏi giỏ hàng!`, 'info');
}

function checkout() {
    if (cart.length === 0) {
        showNotification('Giỏ hàng của bạn đang trống!', 'info');
        return;
    }

    // Kiểm tra đăng nhập
    if (!window.isLoggedIn) {
        showNotification('Vui lòng <strong>đăng nhập</strong> để đặt hàng!', 'warning');
        setTimeout(() => {
            window.location.href = window.loginUrl;
        }, 2000);
        return;
    }

    // Disable nút đặt hàng để tránh click nhiều lần
    const checkoutBtn = document.querySelector('.modal-footer .btn-danger');
    if (checkoutBtn) {
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Đang xử lý...';
    }

    // Gửi dữ liệu giỏ hàng qua AJAX
    fetch('/food/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart: cart })
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug
        
        // Kiểm tra status code
        if (response.status === 401) {
            return response.json().then(data => {
                throw new Error('UNAUTHORIZED');
            });
        }
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug
        
        if (data.success) {
            // Đóng modal trước
            if (cartModal) {
                cartModal.hide();
            }
            
            // Tính tổng tiền
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.qty;
            });
            
            // Hiển thị thông báo đơn giản hơn
            const message = `
                <div style="line-height: 1.8;">
                    <strong style="font-size: 1.1rem;">✅ Đặt hàng thành công!</strong><br>
                    <div style="margin-top: 10px;">
                        📦 <strong>${cart.length}</strong> sản phẩm<br>
                        💰 Tổng: <strong style="color: #ff6b35;">${total.toLocaleString('vi-VN')}đ</strong>
                    </div>
                    <div style="margin-top: 10px; color: #666; font-size: 0.95rem;">
                        Chúng tôi sẽ liên hệ bạn sớm nhất! 🎉
                    </div>
                </div>
            `;
            
            showNotification(message, 'success');
            
            // Xóa giỏ hàng sau khi thông báo hiển thị
            setTimeout(() => {
                cart = [];
                saveCart();
                updateCartIcon();
            }, 1500);
            
        } else {
            // Hiển thị lỗi từ server
            showNotification(data.message || 'Có lỗi xảy ra khi đặt hàng!', 'warning');
        }
    })
    .catch(error => {
        console.error('Lỗi:', error); // Debug
        
        if (error.message === 'UNAUTHORIZED') {
            // Đóng modal giỏ hàng
            if (cartModal) {
                cartModal.hide();
            }
            
            showNotification('Vui lòng <strong>đăng nhập</strong> để đặt hàng!', 'warning');
            
            setTimeout(() => {
                window.location.href = '/food/login.php';
            }, 2000);
        } else {
            showNotification('Đã xảy ra lỗi khi đặt hàng. Vui lòng thử lại!', 'warning');
        }
    })
    .finally(() => {
        // Enable lại nút đặt hàng
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Đặt Hàng';
        }
    });
}

function saveCart() {
    localStorage.setItem('cartData', JSON.stringify(cart));
}

function closeMenu() {
    document.getElementById('menuCheckbox').checked = false;
}
document.querySelector('.menu-overlay')?.addEventListener('click', closeMenu);

// Export functions
window.addToCart = addToCart;
window.openCart = openCart;
window.checkout = checkout;
window.changeQty = changeQty;
window.removeItem = removeItem;