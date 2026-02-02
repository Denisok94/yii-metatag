<?php

namespace denisok94\helper\yii2;

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * MetaTag Class
 * @method tag tag добавить meta теги на страницу
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
    private $init = false;
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
    public function __construct($view)
    {
        $this->init($view);
    }

    private function init($view)
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
        $this->init = true;

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
     * @param array $tags [name => content]
     * name: title, description, keywords, author/creator, image(image:src, image:width, image:height), card: summary/summary_large_image, type: website/profile
     */
    public function tags($tags = []): void
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
     * @param mixed|View $view $this->view
     * @param array $tags [name => content]
     */
    public static function tag($view, $tags = [])
    {
        $new = new MetaTag($view);
        $new->tags($tags);
    }
}
