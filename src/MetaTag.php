<?php

namespace denisok94\helper\yii2;

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Install Meta Tags on the page
 * 
 * @author Denisok94
 * @link https://developers.facebook.com/tools/debug/
 *  
 * @example
 * ```php
 * namespace app\controllers;
 * use \denisok94\helper\yii2\MetaTag;
 * class NewsController extends Controller
 * {
 *      public function actionView($id)
 *      {
 *          $model = $this->findModel($id);
 *          $meta = new MetaTag($this->view, $model->image->url);
 *          $meta->setTags([
 *              'title' => $model->title,
 *              'description' => substr($model->text, 0, 100),
 *              'keywords' => $model->tags, // string
 *          ]);
 *          return $this->render('view', [
 *              'model' => $model,
 *          ]);
 *      }
 * }
 * ```
 */
class MetaTag
{
    /**
     * @var View
     */
    private $view;

    /**
     * @var array
     */
    private $defaultTag = [];

    /**
     * @var string|null
     */
    private $image, $favicon;

    /**
     * @var array
     */
    private $twitterTag = [
        'title', 'description', 'url', 'domain', 'site', 'image', 'image:src', 'creator', 'card'
    ];

    /**
     * @var array
     */
    private $ogTag = [
        'title', 'description', 'url', 'locale', 'type', 'image', 'image:src', 'image:type', 'image:width', 'image:height', 'site_name',
    ];

    /**
     * @param View $view $this->view
     * @param string|null $image ~"/30.jpg"
     * @param string|null $favicon ~"/favicon.png" or Url::to($favicon, true);
     * @return self
     */
    public function __construct(View $view, string $image = null, string $favicon = null)
    {
        $this->image = $image;
        $this->favicon = $favicon;
        $this->view = $view;
        $this->init();
        return $this;
    }

    private function init()
    {
        $title = isset($this->view->title) ? Html::encode($this->view->title) : Yii::$app->name;
        $name = Yii::$app->name;
        $language = Yii::$app->language ?? 'en-EN';

        if (!isset(Yii::$app->domain)) {
            $urlData = parse_url(Url::home(true));
            $domain = $urlData['host'];
        } else {
            $domain = Yii::$app->domain;
        }

        $this->defaultTag = [
            'title' => $title,
            'description' => "",
            'keywords' => "",
            'locale' => $language,
            'url' => Url::to([], true), // Url::base(true) ,
            'domain' => $domain, // 
            'site' => "@" . str_replace(' ', '_', ucwords($name)),
            // 'creator' => '@Denisok1494', // автор статьи
            'site_name' => ucwords($name), // 
            'card' => 'summary_large_image', // summary
            'type' => 'website', //website, profile
        ];
        if ($this->image && file_exists(Yii::$app->getBasePath() . '/web' . $this->image)) {
            list($width, $height, $type, $attr) = getimagesize(Yii::$app->getBasePath() . '/web' . $this->image);
            $this->defaultTag['image'] =  Url::to($this->image, true);
            $this->defaultTag['image:src'] =  Url::to($this->image, true);
            $this->defaultTag['image:width'] = $width;
            $this->defaultTag['image:height'] = $height;
        }
        if ($this->favicon) {
            $this->setFavicon();
        }
    }

    //-----------------------------------------------

    /**
     * @param array $tags ['name1' => 'content2', 'name1' => 'content2', ...]
     * 
     * names: 
     * - title - default: `$this->view->title` or `Yii::$app->name`
     * - description
     * - keywords
     * - author/creator
     * - card - summary or summary_large_image - default: `summary_large_image`
     * - url - default: `Url::to([], true)`
     * - locale - default: `Yii::$app->language` or `'en-EN'`
     * - site - default: `Yii::$app->name`
     * - domain - default: `Yii::$app->domain` or `Url::home(true)`
     * - type - website or profile - default: `website`.
     * @example 1:
     * ```php
     * class NewsController extends Controller {
     * public function actionView($id) {
     *    $model = $this->findModel($id);
     *    $meta = new MetaTag($this->view, $model->image->url);
     *    $meta->setTags([
     *        'title' => $model->title,
     *        'description' => substr($model->text, 0, 100),
     *        'keywords' => $model->tagsToString,
     *    ]);
     *    return $this->render('view', ['model' => $model]);
     * }}
     * ```
     */
    public function setTags(array $tags)
    {
        $newTags = array_merge($this->defaultTag, $tags);

        $this->registerTeg('description', $newTags['description']);
        $this->registerTeg('keywords', $newTags['keywords']);
        unset($newTags['keywords']);

        foreach ($newTags as $key => $value) {
            $del = false;
            if ($this->multiNeedleStripos($key, $this->twitterTag) !== false) {
                $del = true;
                $this->registerTeg("twitter:$key", $value);
            }
            if ($this->multiNeedleStripos($key, $this->ogTag) !== false) {
                $del = true;
                $this->registerTeg("og:$key", $value);
            }
            if ($del == false) {
                $this->registerTeg("$key", $value);
            };
        }
    }

    /**
     * @param array $tags
     * @deprecated Не актуален, используйте: `setTags()`
     */
    public function tag(array $tags)
    {
        $this->setTags($tags);
    }

    //-----------------------------------------------

    /**
     * @param string $haystack
     * @param [type] $needles
     * @param integer $offset
     * @param boolean $flags
     */
    private function multiNeedleStripos(string $haystack, $needles, $offset = 0, $flags = false)
    {
        if (is_array($needles)) {
            foreach ($needles as $needle) {
                // $found[$needle] = stripos($haystack, $needle, $offset);
                if (stripos($haystack, $needle, $offset) !== false) {
                    return $flags ? $needle : true;
                }
            }
        } else {
            if (stripos($haystack, $needles, $offset) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @param string $content
     */
    private function registerTeg(string $name, string $content)
    {
        $this->view->registerMetaTag(
            ['property' => $name, 'content' => $content]
        );
    }

    /**
     * 
     */
    private function setFavicon()
    {
        $ext = self::ext($this->favicon);
        $this->view->registerLinkTag([
            'rel' => 'icon', 'type' => "image/$ext", 'href' => Url::to($this->favicon, true)
        ]);
    }

    /**
     * Получить расширение файла
     * @param string $file файл,
     * @return string
     */
    public static function ext(string $file)
    {
        $extension = pathinfo(basename($file), PATHINFO_EXTENSION);
        return strtolower($extension ?? 'png');
    }
}
