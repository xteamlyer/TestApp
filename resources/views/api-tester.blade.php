<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        h1, h2 { color: #333; }
        .section { margin: 2.5rem 0; padding: 1.5rem; border: 1px solid #ddd; border-radius: 8px; background: #fafafa; }
        form { display: grid; gap: 1rem; }
        label { font-weight: 600; display: block; margin-bottom: 0.4rem; }
        input, textarea, select { width: 100%; padding: 0.6rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; box-sizing: border-box; }
        button { padding: 0.7rem 1.4rem; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #0052a3; }
        button[type="reset"], button.delete { background: #777; }
        button.delete:hover { background: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f4f8; }
        #response, .message { margin-top: 1rem; font-family: monospace; white-space: pre-wrap; }
        .success { color: #006400; }
        .error   { color: #8b0000; }
        .actions button { margin-right: 0.5rem; font-size: 0.9rem; padding: 0.4rem 0.8rem; }
    </style>
</head>
<body>

<h1>Управление задачами</h1>

<div class="section">
    <h2>Список задач</h2>
    <button onclick="loadTasks()">Обновить список</button>
    <table id="tasks-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Статус</th>
                <th>Создано</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody id="tasks-body">
            <tr><td colspan="6">Загрузка...</td></tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h2>Создать новую задачу</h2>
    <form id="create-form">
        <div>
            <label for="title">Название <span style="color:red">*</span></label>
            <input type="text" id="title" name="title" required placeholder="Купить продукты">
        </div>
        <div>
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="3" placeholder="Молоко 3.2%, хлеб ржаной, яйца"></textarea>
        </div>
        <div>
            <label for="status">Статус</label>
            <select id="status" name="status">
                <option value="new">Новая</option>
                <option value="in_progress">В работе</option>
                <option value="done">Выполнена</option>
                <option value="cancelled">Отменена</option>
            </select>
        </div>
        <div>
            <button type="submit">Создать задачу</button>
            <button type="reset">Очистить</button>
        </div>
    </form>
    <div id="create-response" class="message"></div>
</div>

<div class="section">
    <h2>Редактировать задачу</h2>
    <form id="update-form">
        <div>
            <label for="update_id">ID задачи <span style="color:red">*</span></label>
            <input type="number" id="update_id" name="id" required min="1" placeholder="1">
        </div>
        <div>
            <label for="update_title">Название (оставь пустым — не изменится)</label>
            <input type="text" id="update_title" name="title" placeholder="Новое название">
        </div>
        <div>
            <label for="update_description">Описание</label>
            <textarea id="update_description" name="description" rows="3" placeholder="Обновлённое описание"></textarea>
        </div>
        <div>
            <label for="update_status">Статус</label>
            <select id="update_status" name="status">
                <option value="">Не менять</option>
                <option value="new">Новая</option>
                <option value="in_progress">В работе</option>
                <option value="done">Выполнена</option>
                <option value="cancelled">Отменена</option>
            </select>
        </div>
        <div>
            <button type="submit">Обновить</button>
            <button type="reset">Очистить</button>
        </div>
    </form>
    <div id="update-response" class="message"></div>
</div>

<script>
const api = axios.create({
    baseURL: 'http://localhost/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

const tasksBody = document.getElementById('tasks-body');
const createResponse = document.getElementById('create-response');
const updateResponse = document.getElementById('update-response');

async function loadTasks() {
    try {
        const res = await api.get('/tasks');
        const tasks = Array.isArray(res.data) ? res.data : (res.data?.data || []);
        renderTasks(tasks);
    } catch (err) {
        tasksBody.innerHTML = `<tr><td colspan="6" class="error">Ошибка загрузки: ${err.message}</td></tr>`;
    }
}
function renderTasks(tasks) {
    if (!tasks || tasks.length === 0) {
        tasksBody.innerHTML = '<tr><td colspan="6">Нет задач</td></tr>';
        return;
    }

    let html = '';
    tasks.forEach(task => {
        html += `
            <tr>
                <td>${task.id}</td>
                <td>${escapeHtml(task.title)}</td>
                <td>${escapeHtml(task.description || '—')}</td>
                <td>${task.status}</td>
                <td>${new Date(task.created_at).toLocaleString('ru-RU')}</td>
                <td class="actions">
                    <button onclick="fillUpdateForm(${task.id})">Редактировать</button>
                    <button class="delete" onclick="deleteTask(${task.id})">Удалить</button>
                </td>
            </tr>
        `;
    });
    tasksBody.innerHTML = html;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function fillUpdateForm(id) {
    document.getElementById('update_id').value = id;
    updateResponse.innerHTML = `<div class="success">Выбрана задача #${id} для редактирования</div>`;
}

async function deleteTask(id) {
    if (!confirm(`Удалить задачу #${id}?`)) return;

    try {
        await api.delete(`/tasks/${id}`);
        updateResponse.innerHTML = `<div class="success">Задача #${id} удалена</div>`;
        loadTasks();
    } catch (err) {
        updateResponse.innerHTML = `<div class="error">Ошибка удаления: ${err.response?.data?.message || err.message}</div>`;
    }
}

document.getElementById('create-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const res = await api.post('/tasks', data);
        createResponse.innerHTML = `<div class="success">Задача создана (ID: ${res.data.id})</div>`;
        e.target.reset();
        loadTasks();
    } catch (err) {
        createResponse.innerHTML = `<div class="error">Ошибка: ${JSON.stringify(err.response?.data || err.message, null, 2)}</div>`;
    }
});

document.getElementById('update-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {};
    const id = formData.get('id');

    for (const [key, value] of formData.entries()) {
        if (key !== 'id' && value.trim() !== '' && value !== 'Не менять') {
            data[key] = value;
        }
    }

    if (Object.keys(data).length === 0) {
        updateResponse.innerHTML = '<div class="error">Нет данных для обновления</div>';
        return;
    }

    try {
        const res = await api.put(`/tasks/${id}`, data);
        updateResponse.innerHTML = '<div class="success">Задача обновлена</div>';
        e.target.reset();
        loadTasks();
    } catch (err) {
        updateResponse.innerHTML = `<div class="error">Ошибка: ${JSON.stringify(err.response?.data || err.message, null, 2)}</div>`;
    }
});

window.addEventListener('load', loadTasks);
</script>

</body>
</html>