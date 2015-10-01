<?php namespace Deploy;

/**
 * Cheating, a sort of "Facade" that
 * also acts as a service locator
 * @package Deploy
 */
class Render
{
    public static $instance;

    public static function render($page)
    {
        if( ! static::$instance instanceof RenderFarm )
        {
            static::$instance = new RenderFarm( dirname(__FILE__).'/../pages' );
        }

        return static::$instance->render($page);
    }
}