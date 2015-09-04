<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 18.08.15
 * Time: 11:00
 */

namespace Drupal\grid\Components;


use Drupal\Component\Utility\SafeStringInterface;

class GridSafeString implements SafeStringInterface
{

    private $content="";
    /**
     * GridSafeString constructor.
     */
    public function __construct($content)
    {
        $this->content=$content;
    }


    /**
     * Returns a safe string.
     *
     * @return string
     *   The safe string.
     */
    public function __toString()
    {
        return $this->content;
    }

    public  function jsonSerialize ()
    {
        return $this->content;
    }
}