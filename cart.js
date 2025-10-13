let cart = [];
let cartModal;

document.addEventListener('DOMContentLoaded', function() {
    cartModal = new bootstrap.Modal(document.getElementById('cartModal'));

    // 🔹 Khi load trang, lấy dữ liệu giỏ hàng đã lưu
    const savedCart = localStorage.getItem('cartData');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartIcon();
        updateCartModal();
    }

    // Nút Đặt Ngay → thêm vào giỏ
    document.querySelectorAll('.btn-danger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.card');
            const name = card.querySelector('.card-title').textContent;
            const priceText = card.querySelector('.card-text').textContent;
            const img = card.querySelector('.cover-image').src;
            const price = parseInt(priceText.replace(/\./g, '').replace('đ', ''));

            addToCart(name, price, img);
        });
    });
});

function addToCart(name, price, img) {
    const existing = cart.find(item => item.name === name);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ name, price, img, qty: 1 });
    }
    saveCart(); // 🔹 Lưu lại mỗi lần thêm
    updateCartIcon();
    updateCartModal();
}

function updateCartIcon() {
    document.getElementById('cartCount').textContent = cart.reduce((sum, i) => sum + i.qty, 0);
}

function openCart(event) {
    event.preventDefault();
    updateCartModal();
    cartModal.show();
}

function updateCartModal() {
    const cartContainer = document.getElementById('cartItems');
    cartContainer.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        cartContainer.innerHTML = '<p class="text-center text-muted">Giỏ hàng trống</p>';
    } else {
        cart.forEach((item, index) => {
            const itemTotal = item.qty * item.price;
            total += itemTotal;
            cartContainer.innerHTML += `
                <div class="cart-item d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <img src="${item.img}" class="cart-item-image" alt="${item.name}">
                        <div class="cart-item-details">
                            <h6 class="cart-item-name mb-0">${item.name}</h6>
                            <small class="cart-item-price">${item.price.toLocaleString('vi-VN')}đ</small>
                        </div>
                    </div>
                    <div class="cart-item-quantity">
                        <button class="cart-qty-btn" onclick="changeQty(${index}, -1)">-</button>
                        <span class="cart-qty-value">${item.qty}</span>
                        <button class="cart-qty-btn" onclick="changeQty(${index}, 1)">+</button>
                    </div>
                    <div class="cart-item-total">
                        <b class="cart-item-total-price">${itemTotal.toLocaleString('vi-VN')}đ</b>
                        <button class="cart-item-remove" onclick="changeQty(${index}, -${item.qty})">&times;</button>
                    </div>
                </div>`;
        });
    }

    document.getElementById('cartTotal').textContent = total.toLocaleString('vi-VN') + 'đ';
}

function changeQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) cart.splice(index, 1);
    saveCart(); // 🔹 Lưu lại mỗi lần thay đổi số lượng
    updateCartIcon();
    updateCartModal();
}

function checkout() {
    if (cart.length === 0) {
        alert('Giỏ hàng đang trống!');
        return;
    }
    let orderText = 'Đơn hàng của bạn:\n\n';
    let total = 0;
    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        orderText += `${item.name} x${item.qty} = ${itemTotal.toLocaleString('vi-VN')}đ\n`;
        total += itemTotal;
    });
    orderText += `\nTổng cộng: ${total.toLocaleString('vi-VN')}đ\n\nCảm ơn quý khách!`;
    alert(orderText);

    cart = [];
    saveCart(); // 🔹 Xóa dữ liệu trong LocalStorage khi thanh toán xong
    updateCartIcon();
    updateCartModal();
    cartModal.hide();
}

// 🔹 Hàm lưu dữ liệu giỏ hàng vào LocalStorage
function saveCart() {
    localStorage.setItem('cartData', JSON.stringify(cart));
}
