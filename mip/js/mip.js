// ============================================
// СЛАЙДЕР
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slider-slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dotsContainer = document.getElementById('sliderDots');
    
    console.log('Слайдер инициализация, найдено слайдов:', slides.length);
    
    if (slides.length <= 1) {
        if(prevBtn) prevBtn.style.display = 'none';
        if(nextBtn) nextBtn.style.display = 'none';
        if(dotsContainer) dotsContainer.style.display = 'none';
        return;
    }
    
    let currentSlide = 0;
    let slideInterval;
    
    slides.forEach((_, index) => {
        const dot = document.createElement('span');
        dot.className = 'slider-dot' + (index === 0 ? ' active' : '');
        dot.setAttribute('role', 'button');
        dot.setAttribute('aria-label', 'Перейти к слайду ' + (index + 1));
        dot.addEventListener('click', () => { goToSlide(index); resetInterval(); });
        if (dotsContainer) dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.slider-dot');
    
    function goToSlide(index) {
        slides[currentSlide]?.classList.remove('active');
        dots[currentSlide]?.classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide]?.classList.add('active');
        dots[currentSlide]?.classList.add('active');
    }
    
    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }
    
    if(nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetInterval(); });
    if(prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetInterval(); });
    
    function startInterval() { slideInterval = setInterval(nextSlide, 5000); }
    function resetInterval() { clearInterval(slideInterval); startInterval(); }
    
    const sliderWrapper = document.querySelector('.slider-wrapper');
    if(sliderWrapper) {
        sliderWrapper.addEventListener('mouseenter', () => clearInterval(slideInterval));
        sliderWrapper.addEventListener('mouseleave', startInterval);
    }
    
    startInterval();
});


// ============================================
// МОДАЛЬНОЕ ОКНО АВТОРИЗАЦИИ
// ============================================
function showAuthModal() {
    console.log('showAuthModal вызвана');
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        console.log('authModal показан');
    } else {
        console.log('authModal не найден!');
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
    console.log('showRoleErrorModal вызвана, роль:', role);
    const roleSpan = document.getElementById('userRole');
    if (roleSpan) {
        roleSpan.textContent = role || 'неизвестна';
        console.log('roleSpan обновлён:', roleSpan.textContent);
    } else {
        console.log('roleSpan не найден!');
    }
    const modal = document.getElementById('roleErrorModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        console.log('roleErrorModal показан');
    } else {
        console.log('roleErrorModal не найден!');
    }
}

function closeRoleErrorModal() {
    const modal = document.getElementById('roleErrorModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function logoutAndRedirect() {
    window.location.href = '../logout.php';
}


// ============================================
// МОДАЛЬНОЕ ОКНО ПОДТВЕРЖДЕНИЯ УСЛУГИ
// ============================================
let currentServiceId = null;
let currentServiceName = null;
let currentServicePrice = null;
let currentServiceBtn = null;

function showServiceModal(serviceId, serviceName, servicePrice, btnElement) {
    console.log('showServiceModal вызвана');
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
        console.log('serviceOrderModal показан');
    } else {
        console.log('serviceOrderModal не найден!');
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
    console.log('confirmServiceOrder вызвана');
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
// КНОПКИ ЗАКАЗА УСЛУГ
// ============================================
function initServiceButtons() {
    const buttons = document.querySelectorAll('.service-link');
    console.log('Инициализация кнопок услуг, найдено:', buttons.length);
    
    buttons.forEach(btn => {
        btn.removeEventListener('click', btn._clickHandler);
        
        const handler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const serviceId = this.dataset.serviceId;
            const serviceName = this.dataset.serviceName;
            const servicePrice = this.dataset.servicePrice;
            
            console.log('1. Клик по кнопке, serviceId:', serviceId);
            
            fetch('check_auth_status.php')
                .then(res => res.json())
                .then(authData => {
                    console.log('2. Ответ от check_auth_status.php:', authData);
                    
                    if (!authData.is_logged_in) {
                        console.log('3. Пользователь не авторизован');
                        showAuthModal();
                        return;
                    }
                    
                    if (authData.role !== 'client') {
                        console.log('3. Роль не client, роль:', authData.role);
                        showRoleErrorModal(authData.role);
                        return;
                    }
                    
                    console.log('3. Всё хорошо, показываем модалку услуги');
                    showServiceModal(serviceId, serviceName, servicePrice, this);
                })
                .catch(err => {
                    console.error('Ошибка fetch:', err);
                    alert('Ошибка проверки авторизации');
                });
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

// Инициализация кнопок услуг при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализация...');
    initServiceButtons();
    
    // Дополнительная проверка для динамически загружаемых элементов
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                const newButtons = document.querySelectorAll('.service-link');
                if (newButtons.length > 0) {
                    initServiceButtons();
                }
            }
        });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});


// ========== СЛАЙДЕР С АДАПТИВНОСТЬЮ ==========
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('productsSlider');
    const slides = document.querySelectorAll('.slider-slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const dotsContainer = document.getElementById('sliderDots');
    
    let currentIndex = 0;
    let autoSlideInterval;
    let isPlaying = true;
    
    if (!slides.length) return;
    
    // Функция обновления активного слайда
    function updateSlider(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
        
        // Обновляем активную точку
        const dots = document.querySelectorAll('.slider-dot');
        dots.forEach((dot, i) => {
            dot.classList.remove('active');
            if (i === index) {
                dot.classList.add('active');
            }
        });
        
        currentIndex = index;
    }
    
    // Следующий слайд
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlider(currentIndex);
    }
    
    // Предыдущий слайд
    function prevSlide() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateSlider(currentIndex);
    }
    
    // Создание точек навигации
    function createDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('slider-dot');
            if (index === currentIndex) dot.classList.add('active');
            dot.addEventListener('click', () => {
                updateSlider(index);
                resetAutoSlide();
            });
            dotsContainer.appendChild(dot);
        });
    }
    
    // Автоматическое переключение
    function startAutoSlide() {
        if (autoSlideInterval) clearInterval(autoSlideInterval);
        if (!isPlaying) return;
        autoSlideInterval = setInterval(() => {
            nextSlide();
        }, 5000);
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
            autoSlideInterval = null;
        }
    }
    
    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }
    
    // Обработчики кнопок
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            resetAutoSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            resetAutoSlide();
        });
    }
    
    // Пауза при наведении на слайдер
    const sliderWrapper = document.querySelector('.slider-wrapper');
    if (sliderWrapper) {
        sliderWrapper.addEventListener('mouseenter', () => {
            stopAutoSlide();
        });
        sliderWrapper.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
        
        // Для touch-устройств
        sliderWrapper.addEventListener('touchstart', () => {
            stopAutoSlide();
        });
        sliderWrapper.addEventListener('touchend', () => {
            startAutoSlide();
        });
    }
    
    // Свайпы для мобильных устройств
    let touchStartX = 0;
    let touchEndX = 0;
    
    if (sliderWrapper) {
        sliderWrapper.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        sliderWrapper.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    prevSlide(); // свайп вправо - предыдущий
                } else {
                    nextSlide(); // свайп влево - следующий
                }
                resetAutoSlide();
            }
        });
    }
    
    // Клавиатура для десктопа
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            resetAutoSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            resetAutoSlide();
        }
    });
    
    // Инициализация
    createDots();
    startAutoSlide();
    
    // Перезапуск слайдера при изменении видимости страницы
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoSlide();
        } else {
            startAutoSlide();
        }
    });
});