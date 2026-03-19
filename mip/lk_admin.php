<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <a href="#" data-section="dashboard">Главная</a>
    <a href="#" data-section="equipment">Оборудование</a>
    <a href="#" data-section="news">Новости</a>
    <a href="#" data-section="users">Пользователи</a>
    <a href="#" data-section="requests">Запросы</a>
    <a href="../logout.php">Выйти</a>
</div>

<!-- Пустой контейнер — содержимое подгрузится автоматически -->
<div class="content" id="content"></div>

<script>
function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "<")
        .replace(/>/g, ">")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function postToPartial(partial, data) {
    return fetch(`partials/${partial}.php`, {
        method: 'POST',
        body: data
    }).then(res => res.json().catch(() => null));
}

function loadSection(section) {
    fetch(`get_section.php?section=${encodeURIComponent(section)}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('content').innerHTML = html;

            // ✅ ВАЖНО: Выполняем скрипты после вставки HTML
            const scripts = document.getElementById('content').getElementsByTagName('script');
            for (let i = 0; i < scripts.length; i++) {
                const script = scripts[i];
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.head.appendChild(newScript);
            }

   

            // Новости 
            document.querySelectorAll('.delete-news').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (confirm('Удалить новость?')) {
                        postToPartial('news', new URLSearchParams({ action: 'delete', id: btn.dataset.id }))
                            .then(() => loadSection('news'));
                    }
                });
            });

            const addNewsForm = document.getElementById('addNewsForm');
            if (addNewsForm) {
                addNewsForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(addNewsForm);
                    formData.append('action', 'add');
                    const res = await postToPartial('news', formData);
                    if (res?.success) {
                        alert('Новость добавлена!');
                        loadSection('news');
                    } else {
                        alert('Ошибка: ' + (res?.error || ''));
                    }
                });
            }

            // Пользователи 
            document.querySelectorAll('.delete-user').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (confirm(`Удалить пользователя #${btn.dataset.id}? Это необратимо!`)) {
                        postToPartial('users', new URLSearchParams({ action: 'delete', id: btn.dataset.id }))
                            .then(() => loadSection('users'));
                    }
                });
            });

            const addUserForm = document.getElementById('addUserForm');
            if (addUserForm) {
                addUserForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(addUserForm);
                    formData.append('action', 'add');
                    const res = await postToPartial('users', formData);
                    if (res && res.success) {
                        alert('Пользователь добавлен!');
                        loadSection('users');
                    } else {
                        const errorMsg = res?.error || 'Сервер вернул некорректный ответ. Проверьте консоль.';
                        alert('Ошибка: ' + errorMsg);
                        console.error('Ответ сервера:', res);
                    }
                });
            }

            document.querySelectorAll('.save-user-role').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    const row = btn.closest('tr');
                    const select = row.querySelector('.role-select');
                    const newRole = select.value;
                    const originalRole = select.dataset.original;

                    if (newRole === originalRole) {
                        alert('Роль не изменилась.');
                        return;
                    }

                    if (!confirm(`Изменить роль пользователя #${id} на "${newRole}"?`)) return;

                    const res = await postToPartial('users', new URLSearchParams({
                        action: 'save',
                        id: id,
                        role: newRole
                    }));

                    if (res?.success) {
                        alert('Роль обновлена!');
                        loadSection('users');
                    } else {
                        alert('Ошибка: ' + (res?.error || ''));
                    }
                });
            });

            // Запросы 
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', () => {
                    postToPartial('requests', new URLSearchParams({
                        action: 'update_status',
                        id: select.dataset.id,
                        status: select.value
                    }));
                });
            });

            document.querySelectorAll('.support-select').forEach(select => {
                select.addEventListener('change', async () => {
                    const requestId = select.dataset.requestId;
                    const supportId = select.value;

                    const res = await postToPartial('requests', new URLSearchParams({
                        action: 'assign_support',
                        request_id: requestId,
                        support_id: supportId
                    }));

                    if (res?.success) {
                        alert('Специалист назначен!');
                    } else {
                        alert('Ошибка: ' + (res?.error || ''));
                        select.value = select.dataset.original || '0';
                    }
                });
            });

           
            
           
            document.querySelectorAll('#equipmentTable .image-cell').forEach(cell => {
                cell.addEventListener('click', () => {
                    const input = cell.querySelector('.image-input');
                    if (input) input.click();
                });
            });
            document.querySelectorAll('#equipmentTable .image-input').forEach(input => {
                input.addEventListener('change', function () {
                    if (!this.files.length) return;
                    const reader = new FileReader();
                    reader.onload = () => {
                        const cell = this.closest('.image-cell');
                        const img = cell.querySelector('img');
                        const placeholder = cell.querySelector('.no-image-placeholder');
                        if (img) {
                            img.src = reader.result;
                        } else if (placeholder) {
                            placeholder.outerHTML = `<img src="${reader.result}" class="admin-img">`;
                        } else {
                            this.insertAdjacentHTML('beforebegin', `<img src="${reader.result}" class="admin-img">`);
                        }
                        cell.dataset.imageChanged = 'true';
                    };
                    reader.readAsDataURL(this.files[0]);
                });
            });


        })
        .catch(err => {
            document.getElementById('content').innerHTML =
                `<p class="error">Ошибка загрузки раздела: ${err.message}</p>`;
        });
}

// Инициализация сайдбара
document.querySelectorAll('.sidebar a[data-section]').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        loadSection(link.dataset.section);
    });
});

// Автозагрузка главной страницы при открытии
document.addEventListener('DOMContentLoaded', () => {
    loadSection('dashboard');
});
</script>

</body>
</html>