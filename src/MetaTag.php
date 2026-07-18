<?php

namespace denisok94\helper\yii2;

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * MetaTag Class
 * @method void tag(View $view, array $tags) добавить meta теги на страницу
 * @author Denisok94
 * @version 0.1.1
 * @link https://ogp.me/
 * @link https://ruogp.me/
 * @link https://developers.facebook.com/tools/debug/
 */
class MetaTag
{
    private mixed $view;
    public int $maxLength = 150;
    private array $defaultTag = [];
    private ?string $title = null;
    private ?string $name = null;
    private ?string $language = null;
    private ?string $domain = null;
    private $twitterTag = [
        'title',
        'description',
        'url',
        'domain',
        'site',
        'image',
        'image:src',
        'creator',
        'card'
    ];
    private $ogTag = [
        'title',
        'description',
        'url',
        'locale',
        'image',
        'image:src',
        'image:alt',
        'image:secure_url',
        'image:type',
        'image:width',
        'image:height',
        'site_name',
        'locale',
        'type',
    ];

    /**
     *  @param mixed|View $view $this->view
     */
    public function __construct(View $view)
    {
        $this->init($view);
    }

    /**
     * @param mixed|View $view $this->view
     * @param array $tags ['name1' => 'content2', 'name1' => 'content2', ...]
     * 
     * names: 
     * - title - default: `$this->view->title` or `Yii::$app->name`
     * - description
     * - keywords
     * - author/creator
     * - image(image:src, image:width, image:height)
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
     *    MetaTag::tag($this->view, [
     *        'title' => $model->title,
     *        'description' => substr($model->text, 0, 100),
     *        'keywords' => $model->tagsToString,
     *    ]);
     *    return $this->render('view', ['model' => $model]);
     * }}
     * ```
     */
    public static function tag(View $view, array $tags = [])
    {
        $new = new MetaTag($view);
        $new->tags($tags);
    }

    /**
     * @param array $tags ['name1' => 'content2', 'name1' => 'content2', ...]
     * @return self
     * name: title, description, keywords, author/creator, image(image:src, image:width, image:height), card: summary/summary_large_image, type: website/profile
     */
    public function tags(array $tags = []): self
    {
        $newTags = array_merge($this->defaultTag, $tags);

        if ($newTags['description']) {
            $newTags['description'] = self::makeMetaDescription($newTags['description'], $this->maxLength);
        }
        $this->setTeg('description', $newTags['description']);
        //
        if ($newTags['keywords']) {
            if (is_array($newTags['keywords'])) {
                $newTags['keywords'] = implode(',', $newTags['keywords']);
            }
            $this->setTeg('keywords', $newTags['keywords']);
        }
        unset($newTags['keywords']);
        //
        foreach ($newTags as $key => $value) {
            $del = false;
            if ($this->multineedle_stripos($key, $this->twitterTag) !== false) {
                $del = true;
                $this->setTeg("twitter:$key", $value);
            }
            if ($this->multineedle_stripos($key, $this->ogTag) !== false) {
                $del = true;
                $this->setTeg("og:$key", $value);
            }
            if ($del == false) {
                $this->setTeg("$key", $value);
            }
        }
        return $this;
    }

    /**
     * @param string $favicon
     * @return self
     */
    public function setFavicon(string $favicon): self
    {
        $ext = self::ext($favicon);
        $this->view->registerLinkTag([
            'rel' => 'icon',
            'type' => "image/$ext",
            'href' => Url::to($favicon, true)
        ]);
        return $this;
    }
    
    //-----------------------------------------------

    /**
     * @param Yii\web\View $view
     * @return void
     */
    private function init(View $view)
    {
        $this->view = $view;

        $this->title = isset($this->view->title) ? Html::encode($this->view->title) : Yii::$app->name;
        $this->name = Yii::$app->name;
        $this->language = isset(Yii::$app->language) ? Yii::$app->language : 'en-EN';

        if (!isset(Yii::$app->domain)) {
            $urlData = parse_url(Url::home(true));
            $this->domain = $urlData['host'];
        } else {
            $this->domain = Yii::$app->domain;
        }

        $image = $width = $height = null;
        // if (file_exists(Yii::$app->getBasePath() . "/web/favicon.ico")) {
        //     $image = Url::to('favicon.ico', true);
        //     list($width, $height, $type, $attr) = getimagesize(Yii::$app->getBasePath() . "/web/favicon.ico");
        // }

        $this->defaultTag = [
            'title' => $this->title,
            'locale' => $this->language,
            'description' => $this->title,
            'keywords' => null,
            'url' => Url::to('', true), // Url::base(true) ,
            'domain' => $this->domain, // 
            'site' => "@" . ucwords($this->name),
            'image' => $image,
            'image:src' => $image,
            'image:width' => $width,
            'image:height' => $height,
            // 'creator' => '@Denisok1494', // автор статьи
            'site_name' => ucwords($this->name), // 
            'card' => 'summary_large_image', // summary
            'type' => 'website', //website, profile
        ];
    }

    /**
     * 
     */
    private function multineedle_stripos($haystack, $needles, $offset = 0, $flags = false)
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
     * @param string|null $content
     * @return void
     */
    private function setTeg(string $name, ?string $content = null): void
    {
        if ($content) {
            $this->view->registerMetaTag(
                ['property' => $name, 'content' => $content]
            );
        }
    }

    /**
     * @param string $text
     * @param int $maxLength
     * @return string
     */
    private function makeMetaDescription(string $text, int $maxLength = 150): string
    {
        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        $text = mb_substr($text, 0, $maxLength);
        $lastSpace = mb_strrpos($text, ' ');
        if ($lastSpace !== false) {
            $text = mb_substr($text, 0, $lastSpace);
        }
        $text = $text . ' &hellip;';
        return $text;
    }

    /**
     * Получить расширение файла
     * @param string $file файл,
     * @return string
     */
    private static function ext(string $file)
    {
        $extension = pathinfo(basename($file), PATHINFO_EXTENSION);
        return strtolower($extension ?? 'png');
    }
}
