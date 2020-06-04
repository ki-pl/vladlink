<?php


namespace Vladlink;

use InvalidArgumentException;
use PDO;

class Menu
{
	/**
     *  Data to sanitize
     *  @var json
     */
    protected $data;

    /**
     *  DB
     *  @var DB
     */
    protected $DB;
   
	/**
     *  Create a new menu.
     *
     *  @param  json    $data
     *  @param  array   $DB 
     *  @return Menu
     */
    public function __construct()
    {
    	/**/
         $DB_HOST = 'localhost';
    	 $DB_NAME = 'menudb';
    	 $DB_USER = 'menu';
    	 $DB_PASS = 'menu';
    	 $DB_OPTS = [
				      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
				    ];
 		$this->DB = new PDO(
	    'mysql:host=' . $DB_HOST . ';dbname=' . $DB_NAME,
	    	$DB_USER,
	    	$DB_PASS,
	    	$DB_OPTS
        );
        $this->initDB();
    }

    /**
     *  init  
     *  
     */
    public function initDB() 
    {
    	$query = 			"CREATE TABLE IF NOT EXISTS `menu` (
							`id` int(11) NOT NULL AUTO_INCREMENT,
							`id_category` int(11) NOT NULL,
							`name` text NOT NULL,
							`alias` text NOT NULL,
							`level` int(11) DEFAULT '1',
							`id_parent` int(11) NOT NULL DEFAULT '0',
							PRIMARY KEY (`id`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
							";
    	$this->DB->query($query);
    }

    /**
     *  Load menu  
     *  
     */
    public function loadMenu(string $data) 
    {
        if ($this->isJson($data))
        {
            $this->data = json_decode($data,1);
        }else
        {
            $this->error = 'Invalid JSON format';
            throw new InvalidArgumentException("Invalid JSON format");
        }
        $this->saveMenu();
    }


    /**
     *  Save menu  
     *  
     */
    public function saveMenu(array $data = array() ,int $ID_parent = 0,int $level = 1) 
    {
    	if (empty($data)) $data=$this->data;
    	
    	foreach ($data as $key => $value) 
    	{
    		$check = $this->DB->prepare("SELECT count(*) FROM menu WHERE id_category = :id_category LIMIT 1");
 			$check->bindParam(':id_category', $value['id']);
 			$check->execute();	

   			if ($check->fetchColumn() > 0) 
    		{
		    	if (!empty($value['childrens']))
		    	{
		    		$this->saveMenu($value['childrens'],$value['id'],$level+1);	
		    	}
       		}else
       		{
		    	$stmt = $this->DB->prepare("INSERT INTO menu (id_category, name, alias,level,id_parent) VALUES ( :id_category, :name, :alias,:level,:id_parent)");
		    	$stmt->bindParam(':id_category', $value['id']);
		    	$stmt->bindParam(':name', $value['name']);
				$stmt->bindParam(':alias', $value['alias']);
				$stmt->bindParam(':level', $level);
				$stmt->bindParam(':id_parent', $ID_parent);
	    		$stmt->execute();
	    		if (!empty($value['childrens']))
	    		{
	    			$this->saveMenu($value['childrens'],$value['id'],$level+1);	
	    		}
       		}
       	}
    	
    }

    public function generateType_A() 
    {
    	$filename='type_a.txt';
    	$content = $this->makeType_A();
    	file_put_contents($filename,$content);
    }
    private function makeType_A(int $ID_parent = 0, int $level=1,string $path = '/')
    {
    	$content = '';
    	$stmt = $this->DB->prepare("SELECT * FROM menu WHERE id_parent=:id_parent AND level=:level");
		$stmt->bindParam(':level', $level);
		$stmt->bindParam(':id_parent', $ID_parent);
		$stmt->execute();
		while ($menu = $stmt->fetch()) {
			$content.=str_repeat("\t", $level).$menu['name'].' '.$path.$menu['alias']."\n";
			$content.=$this->makeType_A($menu['id_category'],$menu['level']+1,$path.$menu['alias'].'/');  
		}
    	return $content;
    } 

    public function generateType_B() 
    {
    	$filename='type_b.txt';
    	$content = $this->makeType_B();
    	file_put_contents($filename,$content);
    }

    private function makeType_B(int $ID_parent = 0, int $level=1, int $max_level=2)
    {
    	$content = '';
    	$stmt = $this->DB->prepare("SELECT * FROM menu WHERE id_parent=:id_parent AND level=:level");
		$stmt->bindParam(':level', $level);
		$stmt->bindParam(':id_parent', $ID_parent);
		$stmt->execute();
		while ($menu = $stmt->fetch()) {
			$content.=str_repeat("\t", $level).$menu['name']."\n";
			if ($level != $max_level)
			{ 
				$content.=$this->makeType_B($menu['id_category'],$menu['level']+1,$max_level);  
			}
		}
    	return $content;
    } 

    public function print() 
    {
    	$content = $this->makeType_A();
        print_r('<pre>');
    	print_r($content);
    	print_r('</pre>');
	}

 	/**
     *  Data is Json? 
     *  
     *  @param  json   $data 
     *  @return bool 
     */
	private function isJson( $data = null ) 
	{
		if( ! empty( $data ) ) {
			$tmp = json_decode( $data );
			return (
					json_last_error() === JSON_ERROR_NONE
					&& ( is_object( $tmp ) || is_array( $tmp ) )
			);
		}
		return false;
	}
}
