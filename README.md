# Yii2 MetaTag Class

Generation of meta tags.

## Installation

Run:

```bash
composer require --prefer-dist denisok94/yii-metatag
# or
php composer.phar require --prefer-dist denisok94/yii-metatag
```

In the settings (`config`), where the `web.php` files are located or `config.php` specify the name of the site and the main language
```php
$config = [
    'name' => 'Site Name',
    'language' => 'en-EN',
    'basePath' => dirname(__DIR__),
    //...
];
```

## Use

| Method | Description |
| ---------------- | :---------------- |
| static::tag() | Install MetaTag on the page |
| tags() | Install MetaTag on the page |
| setFavicon() | change the page icon favicon |

```php
namespace app\controllers;
use \denisok94\helper\yii2\MetaTag;

class NewsController extends Controller
{
    // ...
    public function actionView($id)
    {
        $model = $this->findModel($id);
        //
        MetaTag::tag($this->view, [
            'title' => $model->title,
            'description' => substr($model->text, 0, 100),
            'keywords' => $model->tags, // string
        ]);
        // or
        $this->view->title = $model->title;
        list($width, $height, $type, $attr) = getimagesize(Yii::$app->getBasePath() . $model->image_path);
        MetaTag::tag($this->view, [
            'title' => $model->title,
            'description' => $model->announce,
            'keywords' => implode(', ', $model->tags), // if tags array
            'image' => Url::to($model->image_path, true),
            'image:src' => Url::to($model->image_path, true),
            'image:type' => 'image/jpeg',
            'image:width' => $width,
            'image:height' => $height,
        ]);
        // or
        $tag = new MetaTag($view);
        $tag->tags([
            'title' => $model->title,
            //....
        ]);
        return $this->render('view', ['model' => $model]);
    }
}
```

Specified in `action`, before `render()'.
```php
MetaTag::tag($this->view, [
    'nameTag1' => 'valueTag1',
    'nameTag2' => 'valueTag2',
    //...
]);
```

Individual icon(favicon) for the page
```php
// Before
$this->view->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Url::to("/favicon.png", true)]);
// Since MetaTag
$tag = new MetaTag($view);
$tag->setFavicon("/favicon.png");
```
