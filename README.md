# Тестовое задание

---

### Для реализации был выбран Laravel и MySQL.

#### Было сделано:

1. Создан Laravel проект

   ```powershell
   #Используя Win11, устанавливаем PHP
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.4'))

   #Устанавлиаем Laravel через composer
   composer global require laravel/installer

   #Создаем test-app
   laravel new test-app

   #Инициализируем Node.js и билдим
   cd test-app
   npm install && npm run build
   composer run dev
   ```
2. На удаленном MySQL сервере создана БД
3. Создана модель Task и последующая миграция таблиц

   ```powershell
   php artisan make:model Task -m
   php artisan migrate
   ```
4. Создан ресурс для json ответа

   ```powershell
   php artisan make:resource TaskResource
   ```
5. Создан котроллер api через который будем получать ответы

   ```powershell
   php artisan make:controller Api/TaskController --api --model=Task
   ```
6. Создан роут api.php
7. Создана базовая страница для проверки API
