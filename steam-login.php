<?php
require_once 'openid.php'; // Подключаем LightOpenID
use LightOpenID;

session_start();

// Настройки
$domain = 'http://ваш-сайт.ru'; // Замени на свой домен

try {
    $openid = new LightOpenID($domain);
    
    // Если пользователь только нажал "Войти через Steam"
    if (!$openid->mode) {
        $openid->identity = 'https://steamcommunity.com/openid'; // Steam OpenID URL
        header('Location: ' . $openid->authUrl()); // Перенаправляем на Steam
        exit;
    } 
    
    // Если пользователь вернулся с Steam
    elseif ($openid->validate()) {
        // Получаем SteamID64 (например, "76561197960287930")
        $steamId = str_replace('https://steamcommunity.com/openid/id/', '', $openid->identity);
        
        // Проверяем, первый ли это вход (можно через сессию или БД)
        if (!isset($_SESSION['steam_registered'])) {
            $_SESSION['steam_registered'] = true;
            $_SESSION['steam_id'] = $steamId;
            
            // Начисляем 40 фрагов (можно записать в БД или LocalStorage)
            $_SESSION['frags'] = ($_SESSION['frags'] ?? 0) + 40;
            
            // Перенаправляем с параметром успешной регистрации
            header('Location: /?steam_auth=1&first_login=1');
            exit;
        } else {
            // Обычный вход (без начисления фрагов)
            header('Location: /?steam_auth=1');
            exit;
        }
    } else {
        die("Ошибка: Steam не подтвердил авторизацию.");
    }
} catch (ErrorException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>