@echo off
title Laravel Scheduler (No cerrar)

:: --- CONFIGURACIÓN EXACTA ---
:: 1. Ruta directa a tu PHP (La que me pasaste + php.exe)
set PHP_BIN="C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe"

:: 2. Ruta de tu proyecto (Confirma que sea esta carpeta)
set PROYECTO="C:\laragon\www\api_canchas"

echo ---------------------------------------------------
echo MOTOR DE RESERVAS INICIADO
echo Usando PHP en: %PHP_BIN%
echo Proyecto en: %PROYECTO%
echo ---------------------------------------------------

:loop
:: 3. Entrar a la carpeta
cd /d %PROYECTO%

:: 4. Ejecutar el comando
echo [%time%] Buscando tareas pendientes...
%PHP_BIN% artisan schedule:run

:: 5. Esperar 60 segundos antes de volver a preguntar
timeout /t 60 /nobreak > NUL

:: 6. Repetir
goto loop