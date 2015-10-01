<?php namespace Deploy;

use Michelf\MarkdownExtra;

class RenderFarm
{
    protected $base_path;

    /**
     * Create RenderFarm, giving the base path
     * of the markdown files to be rendered
     *
     * @param $file_base_path
     * @throws \Exception
     */
    public function __construct($file_base_path)
    {
        if( ! is_dir($file_base_path) )
        {
            throw new \Exception('Base path does not exist or is not a readable directory');
        }

        $this->base_path = $file_base_path;
    }

    /**
     * Find & render supplied
     * MD content to html
     * @param $page
     * @return mixed
     * @throws \Exception
     */
    public function render($page)
    {
        $file = $this->buildPath($page);

        if( ! file_exists($file) )
        {
            throw new \Exception(sprintf('Pgae %s cannot be found at %s', $page, $file));
        }

        return MarkdownExtra::defaultTransform( file_get_contents($file) );
    }

    /**
     * Build a file path from the
     * base path + page name
     * @param $page
     * @return string
     */
    protected function buildPath($page)
    {
        return sprintf('%s/%s.md', $this->base_path, $page);
    }
}