<?php
namespace Handmap\Tilemap;

ini_set('memory_limit',"-1");
ini_set('max_execution_time', '0');
error_reporting(E_ALL);

class Img2file{
    private $filename = './handmap1.png';
    private $saveid = '';
    private $tileX = 0;
    private $tileY = 0;
    private $tileW = 0;
    private $tileH = 0;
    private $tileZ = 17;
    private $range = array();
    
    function __construct($file='',$savename=''){
        if(!empty($file)){
            $this->filename = $file;
        }
        if(!empty($savename)){
            $this->saveid = $savename;
        }else{
            exit();
        }
    }
    
    public function setInitPos($tilex , $tiley , $tilew , $tileh , $tilez){
        $tilex ? $this->tileX = intval($tilex):'';
        $tiley ? $this->tileY = intval($tiley):'';
        $tilew ? $this->tileW = intval($tilew):'';
        $tileh ? $this->tileH = intval($tileh):'';
        $tilez ? $this->tileZ = intval($tilez):'';
    }
    
    
    public function setTileRange($range='17,19'){
        $range = explode(',', $range);
        !empty($range[0]) ? $startrange = intval($range[0]) : exit();
        !empty($range[1]) ? $endrange   = intval($range[1]) : exit();
        if($endrange-$startrange<0){
            exit();
        }
        
        for ($range=$startrange;$range<=$endrange;$range++){
            $this->range[$range] = $this->computeRange($range, $this->tileX,  $this->tileY,$this->tileW,$this->tileH);
        }
        
    }
    
    public function tileImg(){
        //header("Content-type: image/png");
        $source=imagecreatefrompng($this->filename);
        foreach ($this->range as $k=>$v){
            $this->ImgMakeDir($k, $this->saveid);
            foreach ($v as $vo){
                $block = imagecreatetruecolor($vo['w'],$vo['w']);
                imagealphablending($block , false);
                $bg = imagecolorallocatealpha($block , 0 , 0 , 0 , 127);
                imagefill($block , 0 , 0 , $bg);
                imagesavealpha($block , true);
                
                imagecopyresampled($block,$source,0,0,$vo['realx'],$vo['realy'],$vo['w'],$vo['w'],$vo['realw'],$vo['realw']);
                
                $this->saveImg($vo, $k, $this->saveid, $block);
                
                imagedestroy($block);
            }
        }
        
    }
    
    private function ImgMakeDir($range,$saveid){
        $dir = public_path('map/'.$this->saveid);
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $dir = $dir.DIRECTORY_SEPARATOR.$range;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
    }
    
    private function saveImg($info,$range,$saveid,$block){
        $file = public_path('map/'.$this->saveid.'/'.$range.'/'.$info['x'].'_'.$info['y'].'.png');
        if(file_exists($file)){
            unlink($file);
        }
        imagepng($block,$file);
    }
    
    private function computeRange($range,$x,$y,$w,$h){
        list($width, $height, $type, $attr) = getimagesize($this->filename);
        $scale = pow(2,($range - $this->tileZ));
        $each = 256/$scale;
        $x = $x*$scale;
        $y = $y*$scale;
        $w = $w*$scale;
        $h = $h*$scale;
        
        $realeach = $width/$w;
        
        for($i=$x; $i<$x+$w; $i++){
            for($j=$y; $j<$y+$h; $j++){
                $swap['x'] = $i;
                $swap['y'] = $j;
                $swap['w'] = 256;
                $swap['realw'] = $realeach;
                $swap['realx'] = ($i-$x)*$realeach;
                $swap['realy'] = ($j-$y)*$realeach;
                $rangeAry[] = $swap;
            }
        }
        return $rangeAry;
    }
}

// $obj = new Img2file('./handmap1.png','8848');
// $obj->setInitPos(106209, 54473, 6, 4, 17);
// $obj->setTileRange('17,19');
// $obj->tileImg();