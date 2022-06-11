Generation of meta tags.

# Установка

Run:

```bash
composer require --prefer-dist denisok94/yii-metatag
# or
php composer.phar require --prefer-dist denisok94/yii-metatag
```

or add to the `require` section of your `composer.json` file:

```json
"denisok94/yii-metatag": "*"
```

```bash
composer update
# or
php composer.phar update
```

In the settings (`config`), where the `web.php` files are located or `config.php ` specify the name of the site and the main language
```php
$config = [
    'name' => 'Site Name',
    'language' => 'en-EN',
    'basePath' => dirname(__DIR__),
    //...
```

# Using

| Method | Description |
|----------------|:----------------|
| setTag | Install MetaTag on the page |

```php
use \denisok94\helper\MetaTag;
```

Указываются в `action` контроллере, перед `render()`.
```php
$meta = new MetaTag($this->view);
$meta->setTag([
    'nameTag1' => 'valueTag1',
    'nameTag2' => 'valueTag2',
    //...
]);

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

```php
namespace app\controllers;
use \denisok94\helper\MetaTag;

class NewsController extends Controller
{
    // ...
    public function actionView($id)
    {
        $model = $this->findModel($id);
        //
        (new MetaTag($this->view))->setTag([
            'title' => $model->title,
            'description' => substr($model->text, 0, 100),
            'keywords' => $model->tags, // string
        ]);
        // or
        $this->view->title = $model->title;
        $meta = new MetaTag($this->view, $model->image->url);
        $meta->setTag([
            'description' => $model->announce,
            'keywords' => implode(', ', $model->tags), // if tags array
        ]);
        //
        return $this->render('view', ['model' => $model]);
    }
}
```