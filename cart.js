"use strict";

// Giỏ hàng
let cart = [];
let cartModal;

// Thêm CSS animations ngay khi load
const style = document.createElement('style');
style.id = 'cart-animations';
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(450px);
            opacity: 0;
        }
        40% {
            transform: translateX(-10px);
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @keyframes progressBar {
        to {
            width: 0;
        }
    }
    
    .custom-notification {
        position: relative;
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo cartModal
    const modalElement = document.getElementById('cartModal');
    if (modalElement) {
        cartModal = new bootstrap.Modal(modalElement);
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

    // Khi load trang, lấy dữ liệu giỏ hàng đã lưu
    const savedCart = localStorage.getItem('cartData');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartIcon();
    }

    // Nút Đặt Ngay → thêm vào giỏ
    document.querySelectorAll('.btn-danger, .order-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.card, .menu-card, .combo-card');
            const name = card.querySelector('.card-title, h3, h5').textContent.trim();
            const priceText = card.querySelector('.card-text, .price').textContent.trim();
            
            let img = card.querySelector('.cover-image') || card.querySelector('.card-img-top') || card.querySelector('img');
            img = img ? img.src : 'image/logo.png';
            
            const price = parseInt(priceText.replace(/[^\d]/g, ''));
            addToCart(name, price, img);
        });
    });
});

// Hàm hiển thị thông báo đẹp
function showNotification(message, type = 'success') {
    const oldNotification = document.querySelector('.custom-notification');
    if (oldNotification) {
        oldNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = 'custom-notification';
    
    const icon = type === 'success' ? 
        '<i class="fa-solid fa-circle-check"></i>' : 
        '<i class="fa-solid fa-info-circle"></i>';
    
    const bgColor = type === 'success' ? 
        'linear-gradient(to right, #0abf3055, #22242f 30%)' : 
        'linear-gradient(to right, #ff6b3555, #22242f 30%)';
    
    const iconColor = type === 'success' ? '#0abf30' : '#ff6b35';
    
    notification.innerHTML = `
        ${icon}
        <div class="content">
            <div class="title">${type === 'success' ? 'Thành công' : 'Thông báo'}</div>
            <span>${message}</span>
        </div>
        <i class="fa-solid fa-xmark close-btn"></i>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 30px;
        right: 20px;
        z-index: 9999;
        padding: 15px 20px;
        color: #fff;
        width: 400px;
        max-width: 90vw;
        display: grid;
        grid-template-columns: 60px 1fr 40px;
        border-radius: 8px;
        background-image: ${bgColor};
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        animation: slideInRight 0.4s ease, fadeOut 0.4s ease 4.6s;
    `;
    
    const iconEl = notification.querySelector('i:first-child');
    iconEl.style.cssText = `
        color: ${iconColor};
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2rem;
    `;
    
    const contentDiv = notification.querySelector('.content');
    contentDiv.style.cssText = `
        display: flex;
        flex-direction: column;
        justify-content: center;
    `;
    
    const title = notification.querySelector('.title');
    title.style.cssText = `
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 5px;
    `;
    
    const text = notification.querySelector('span');
    text.style.cssText = `
        color: #fff;
        opacity: 0.9;
        font-size: 0.95rem;
        line-height: 1.4;
    `;
    
    const closeBtn = notification.querySelector('.close-btn');
    closeBtn.style.cssText = `
        color: #fff;
        opacity: 0.6;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.3s;
        font-size: 1.2rem;
    `;
    
    closeBtn.addEventListener('mouseover', () => closeBtn.style.opacity = '1');
    closeBtn.addEventListener('mouseout', () => closeBtn.style.opacity = '0.6');
    closeBtn.addEventListener('click', () => notification.remove());
    
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: absolute;
        bottom: 0;
        left: 0;
        background-color: ${iconColor};
        width: 100%;
        height: 3px;
        box-shadow: 0 0 10px ${iconColor};
        animation: progressBar 5s linear 1 forwards;
    `;
    notification.appendChild(progressBar);
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
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
        if (totalQty > 0) {
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

function openCart(event) {
    if (event) event.preventDefault();
    
    // Khởi tạo cartModal nếu chưa tồn tại
    if (!cartModal) {
        const modalElement = document.getElementById('cartModal');
        if (modalElement) {
            cartModal = new bootstrap.Modal(modalElement);
            console.log('cartModal initialized in openCart:', cartModal);
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
    
    let orderDetails = '<div style="text-align: left; line-height: 2;">';
    orderDetails += '<strong style="font-size: 1.1rem;">📋 Chi tiết đơn hàng:</strong><br><br>';
    
    let total = 0;
    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        total += itemTotal;
        orderDetails += `<div style="margin-bottom: 8px;">• <strong>${item.name}</strong> x${item.qty} = <span style="color: #ff6b35; font-weight: 600;">${itemTotal.toLocaleString('vi-VN')}đ</span></div>`;
    });
    
    orderDetails += `<br><div style="padding: 15px; background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); border-radius: 10px; border: 2px solid #ff6b35; margin-top: 10px;">`;
    orderDetails += `<strong style="font-size: 1.3rem; color: #ff6b35;">💰 Tổng cộng: ${total.toLocaleString('vi-VN')}đ</strong>`;
    orderDetails += `</div>`;
    orderDetails += `<div style="margin-top: 15px; color: #666; font-size: 0.95rem;">✨ Cảm ơn quý khách đã đặt hàng!</div>`;
    orderDetails += '</div>';
    
    showNotification(orderDetails, 'success');
    
    cartModal.hide();
    
    setTimeout(() => {
        cart = [];
        saveCart();
        updateCartIcon();
        updateCartModal();
    }, 3000);
}

function saveCart() {
    localStorage.setItem('cartData', JSON.stringify(cart));
}

// Export functions để có thể gọi từ HTML
window.addToCart = addToCart;
window.openCart = openCart;
window.checkout = checkout;
window.changeQty = changeQty;
window.removeItem = removeItem;

function closeMenu() {
    document.getElementById('menuCheckbox').checked = false;
}

document.querySelector('.menu-overlay')?.addEventListener('click', closeMenu);