<?php

namespace PHPageBuilder;

use PHPageBuilder\Contracts\ThemeContract;

class ThemeBlock
{
    /**
     * @var $config
     */
    protected $config;

    /**
     * @var ThemeContract $theme
     */
    protected $theme;

    /**
     * @var string $blockSlug
     */
    protected $blockSlug;

    /**
     * Theme constructor.
     *
     * @param ThemeContract $theme         the theme this block belongs to
     * @param string $blockSlug
     */
    public function __construct(ThemeContract $theme, string $blockSlug)
    {
        $this->theme = $theme;
        $this->blockSlug = $blockSlug;

        $this->config = [];
        if (file_exists($this->getFolder() . '/config.php')) {
            $this->config = include $this->getFolder() . '/config.php';
        }
    }

    /**
     * Return the absolute folder path of this theme block.
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->theme->getFolder() . '/blocks/' . basename($this->blockSlug);
    }

    /**
     * Return the controller file of this theme block.
     *
     * @return string
     */
    public function getControllerFile()
    {
        if (file_exists($this->getFolder() . '/controller.php')) {
            return $this->getFolder() . '/controller.php';
        }
        return __DIR__ . '/Modules/GrapesJS/Block/BaseController.php';
    }

    /**
     * Return the model file of this theme block.
     *
     * @return string
     */
    public function getModelFile()
    {
        if (file_exists($this->getFolder() . '/model.php')) {
            return $this->getFolder() . '/model.php';
        }
        return __DIR__ . '/Modules/GrapesJS/Block/BaseModel.php';
    }

    /**
     * Return the view file of this theme block.
     *
     * @return string
     */
    public function getViewFile()
    {
        if ($this->isPhpBlock()) {
            return $this->getFolder() . '/view.php';
        }
        return $this->getFolder() . '/view.html';
    }

    /**
     * Return the file path of the thumbnail of this block.
     *
     * @return string
     */
    public function getThumbPath()
    {
        $blockThumbsFolder = $this->theme->getFolder() . '/public/block-thumbs/';
        return $blockThumbsFolder . md5($this->blockSlug) . '/' . md5(file_get_contents($this->getViewFile())) . '.jpg';
    }

    public function getThumbUrl()
    {
        return phpb_theme_asset('block-thumbs/' . md5($this->blockSlug) . '/' . md5(file_get_contents($this->getViewFile())) . '.jpg');
    }

    /**
     * Return the slug identifying this type of block.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->blockSlug;
    }

    /**
     * Return whether this block is a block containing/allowing PHP code.
     *
     * @return bool
     */
    public function isPhpBlock()
    {
        return file_exists($this->getFolder() . '/view.php');
    }

    /**
     * Return whether this block is a plain html block that does not contain/allow PHP code.
     *
     * @return bool
     */
    public function isHtmlBlock()
    {
        return (! $this->isPhpBlock());
    }

    /**
     * Return configuration with the given key (as dot-separated multidimensional array selector).
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        // if no dot notation is used, return first dimension value or empty string
        if (strpos($key, '.') === false) {
            return $this->config[$key] ?? null;
        }

        // if dot notation is used, traverse config string
        $segments = explode('.', $key);
        $subArray = $this->config;
        foreach ($segments as $segment) {
            if (isset($subArray[$segment])) {
                $subArray = &$subArray[$segment];
            } else {
                return null;
            }
        }

        return $subArray;
    }
}
