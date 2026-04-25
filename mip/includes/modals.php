<!-- Модальное окно для авторизации -->
<div id="authModal" class="auth-modal">
    <div class="auth-modal-content">
        <div class="auth-modal-header">
            <h3>Требуется авторизация</h3>
            <button class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
        </div>
        <div class="auth-modal-body">
            <p>Для заказа услуги необходимо войти в личный кабинет.</p>
            <p>У вас уже есть аккаунт или нужно зарегистрироваться?</p>
        </div>
        <div class="auth-modal-footer">
            <a href="../authorization.php" class="btn-auth btn-login-page">Войти</a>
            <a href="../authorization.php#register" class="btn-auth btn-register-page">Зарегистрироваться</a>
            <button class="btn-auth btn-cancel" onclick="closeAuthModal()">Отмена</button>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения заказа услуги -->
<div id="serviceOrderModal" class="service-modal">
    <div class="service-modal-content">
        <div class="service-modal-header">
            <h3>Подтверждение заказа</h3>
            <button class="service-modal-close" onclick="closeServiceModal()">&times;</button>
        </div>
        <div class="service-modal-body">
            <div class="service-info-block">
                <span class="service-info-label">Услуга:</span>
                <span class="service-info-value" id="modalServiceName"></span>
            </div>
            <div class="service-info-block">
                <span class="service-info-label">Стоимость:</span>
                <span class="service-info-value" id="modalServicePrice"></span>
            </div>
            <div class="service-description">
                <p>Услуга будет добавлена в ваш личный кабинет.</p>
                <p>После подтверждения заказа с вами свяжется специалист.</p>
            </div>
        </div>
        <div class="service-modal-footer">
            <button class="btn-service btn-service-confirm" id="confirmOrderBtn">Подтвердить заказ</button>
            <button class="btn-service btn-service-cancel" onclick="closeServiceModal()">Отмена</button>
        </div>
    </div>
</div>

<!-- Модальное окно для ошибки роли -->
<div id="roleErrorModal" class="role-modal">
    <div class="role-modal-content">
        <div class="role-modal-header">
            <h3>Доступ запрещён</h3>
            <button class="role-modal-close" onclick="closeRoleErrorModal()">&times;</button>
        </div>
        <div class="role-modal-body">
            <p>Заказ услуг доступен только клиентам.</p>
            <p>Ваша роль: <strong id="userRole"></strong></p>
            <p>Пожалуйста, войдите под учётной записью клиента.</p>
        </div>
        <div class="role-modal-footer">
            <button class="btn-role btn-role-logout" onclick="logoutAndRedirect()">Выйти и войти как клиент</button>
            <button class="btn-role btn-role-cancel" onclick="closeRoleErrorModal()">Закрыть</button>
        </div>
    </div>
</div>