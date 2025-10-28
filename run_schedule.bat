@echo off
REM === Ir al proyecto Laravel ===
cd /d "C:\laragon\www\api_canchas"

REM === Ejecutar scheduler de Laravel con PHP de Laragon ===
"C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" artisan schedule:run >> "C:\laragon\www\api_canchas\storage\logs\laravel.log" 2>&1

REM === Registrar hora de ejecución ===
echo Tarea ejecutada el %date% a las %time% >> "C:\laragon\www\api_canchas\storage\logs\laravel.log"
exit /b
