document.addEventListener('DOMContentLoaded', function() {
    // Валидация поиска — обычный alert
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const query = searchInput.value.trim();
            if (query.length > 0 && query.length < 2) {
                e.preventDefault();
                alert('Введите минимум 2 символа для поиска');
                searchInput.focus();
            }
        });
    }
    
    // Инициализация кнопок заказа (с модальными окнами)
    initServiceOrderButtons();
});


// ============================================
// МОДАЛЬНОЕ ОКНО АВТОРИЗАЦИИ
// ============================================
function showAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}


// ============================================
// МОДАЛЬНОЕ ОКНО ОШИБКИ РОЛИ
// ============================================
function showRoleErrorModal(role) {
    const roleSpan = document.getElementById('userRole');
    if (roleSpan) roleSpan.textContent = role || 'неизвестна';
    const modal = document.getElementById('roleErrorModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeRoleErrorModal() {
    const modal = document.getElementById('roleErrorModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}


// ============================================
// МОДАЛЬНОЕ ОКНО ПОДТВЕРЖДЕНИЯ УСЛУГИ
// ============================================
let currentServiceId = null;
let currentServiceName = null;
let currentServicePrice = null;
let currentServiceBtn = null;

function showServiceModal(serviceId, serviceName, servicePrice, btnElement) {
    currentServiceId = serviceId;
    currentServiceName = serviceName;
    currentServicePrice = servicePrice;
    currentServiceBtn = btnElement;
    
    const nameSpan = document.getElementById('modalServiceName');
    const priceSpan = document.getElementById('modalServicePrice');
    
    if (nameSpan) nameSpan.textContent = serviceName;
    if (priceSpan) priceSpan.textContent = 'от ' + Number(servicePrice).toLocaleString('ru-RU') + ' ₽';
    
    const modal = document.getElementById('serviceOrderModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeServiceModal() {
    const modal = document.getElementById('serviceOrderModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    currentServiceId = null;
    currentServiceName = null;
    currentServicePrice = null;
    currentServiceBtn = null;
}

function confirmServiceOrder() {
    if (!currentServiceId || !currentServiceBtn) {
        closeServiceModal();
        return;
    }
    
    const btn = currentServiceBtn;
    const originalText = btn.textContent;
    btn.textContent = 'Добавление...';
    btn.disabled = true;
    
    fetch('add_service.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `service_id=${currentServiceId}&service_name=${encodeURIComponent(currentServiceName)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✓ Добавлено!';
            setTimeout(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            }, 2000);
            closeServiceModal();
        } else if (data.error === 'Необходимо авторизоваться') {
            btn.textContent = originalText;
            btn.disabled = false;
            closeServiceModal();
            showAuthModal();
        } else if (data.error === 'Только клиенты могут заказывать услуги') {
            btn.textContent = originalText;
            btn.disabled = false;
            closeServiceModal();
            fetch('check_auth_status.php')
                .then(res => res.json())
                .then(authData => {
                    showRoleErrorModal(authData.role);
                })
                .catch(() => {
                    showRoleErrorModal('неизвестна');
                });
        } else {
            throw new Error(data.error || 'Ошибка');
        }
    })
    .catch(err => {
        console.error(err);
        btn.textContent = originalText;
        btn.disabled = false;
        closeServiceModal();
        alert('Ошибка: ' + err.message);
    });
}


// ============================================
// ИНИЦИАЛИЗАЦИЯ КНОПОК ЗАКАЗА УСЛУГ
// ============================================
function initServiceOrderButtons() {
    const buttons = document.querySelectorAll('.service-order-btn');
    console.log('Найдено кнопок заказа услуг:', buttons.length);
    
    buttons.forEach(btn => {
        btn.removeEventListener('click', btn._clickHandler);
        
        const handler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const serviceId = this.dataset.serviceId;
            const serviceName = this.dataset.serviceName;
            const servicePrice = this.dataset.servicePrice;
            
            fetch('check_auth_status.php')
                .then(res => res.json())
                .then(authData => {
                    if (!authData.is_logged_in) {
                        showAuthModal();
                        return;
                    }
                    if (authData.role !== 'client') {
                        showRoleErrorModal(authData.role);
                        return;
                    }
                    showServiceModal(serviceId, serviceName, servicePrice, this);
                })
                .catch(err => console.error(err));
        };
        
        btn._clickHandler = handler;
        btn.addEventListener('click', handler);
    });
}


// ============================================
// ЗАКРЫТИЕ МОДАЛЬНЫХ ОКОН ПО ESCAPE И КЛИКУ НА ФОН
// ============================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAuthModal();
        closeServiceModal();
        closeRoleErrorModal();
    }
});

// Закрытие модалки авторизации по клику на фон
const authModal = document.getElementById('authModal');
if (authModal) {
    authModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAuthModal();
        }
    });
}

// Закрытие модалки услуги по клику на фон
const serviceModal = document.getElementById('serviceOrderModal');
if (serviceModal) {
    serviceModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeServiceModal();
        }
    });
}

// Закрытие модалки ошибки роли по клику на фон
const roleModal = document.getElementById('roleErrorModal');
if (roleModal) {
    roleModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeRoleErrorModal();
        }
    });
}

// Кнопка подтверждения заказа
const confirmBtn = document.getElementById('confirmOrderBtn');
if (confirmBtn) {
    confirmBtn.removeEventListener('click', confirmBtn._clickHandler);
    const confirmHandler = function() {
        confirmServiceOrder();
    };
    confirmBtn._clickHandler = confirmHandler;
    confirmBtn.addEventListener('click', confirmHandler);
}

function logoutAndRedirect() {
    window.location.href = '../logout.php';
}