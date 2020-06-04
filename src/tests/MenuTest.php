<?php

use PHPUnit\Framework\TestCase;
use Vladlink\Menu;

class MenuTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testisJsonException()
    {
        $this->expectException(InvalidArgumentException::class);
        $data = 'foo/bar';
        $data = json_encode($data);
        $menu = new Menu();
        $menu->loadMenu($data);
    }
}
?>
