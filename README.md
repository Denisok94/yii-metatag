
Генерация мета тегов.

```php
use \denisok94\helper\MetaTag;
```
> Пока, реализованы простые теги, позже, доработаю остальные.

| Method | Description |
|----------------|:----------------|
| tag | Установить MetaTag на страницу |

В в настройках(`config`), где находятся файлы `web.php` или `config.php` укажите название сайта и основной язык
```php
$config = [
    'name' => 'Site Name',
    'language' => 'en-EN', // ru-RU 
    'basePath' => dirname(__DIR__),
    //...
```

Указываются в `action` контроллере, перед `render()`.
```php
$meta = new MetaTag($this->view);
```
Установить изображение
```php
$meta = new MetaTag($this->view, "/image.jpg");
```
Индивидуальная иконка для страницы
```php
// Before
$this->view->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Url::to("/favicon.png", true)]);
// Since MetaTag
$meta = new MetaTag($this->view, null, "/favicon.png");
```
