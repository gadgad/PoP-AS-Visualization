<?php

class Color
{
	public $trans;
	public $red;
	public $green;
	public $blue;
	public $red_hex;
	public $green_hex;
	public $blue_hex;
	
	public function __construct()
	{
		$this->randColor();
		if(func_num_args()==3)
		{
			$r = func_get_arg(0);
			$g = func_get_arg(1);
			$b = func_get_arg(2);
			$this->setColor($r, $g, $b);
		}
		$this->Dec2Hex();
	}
	
	function randColor()
	{
		$this->red = rand(0,255);
		$this->green = rand(0,255);
		$this->blue = rand(0,255);
		$this->trans = 150;
	}
	
	function setColor($r,$g,$b)
	{
		$this->red = $r;
		$this->green = $g;
		$this->blue = $b;
	}
	
	function Dec2Hex()
	{
		$this->red_hex = ($this->red<16)? '0'.dechex($this->red):dechex($this->red);
		$this->green_hex = ($this->green<16)? '0'.dechex($this->green):dechex($this->green);
		$this->blue_hex = ($this->blue<16)? '0'.dechex($this->blue):dechex($this->blue);
	}
	
	public function gm_format()
	{
		return dechex($this->trans).($this->blue_hex).($this->green_hex).($this->red_hex);
	}
	
	public function web_format()
	{
		return ($this->red_hex).($this->green_hex).($this->blue_hex);
	}
	
	public function calc_dist()
	{
		$r2 = $g2 = $b2 = 0;
		if(func_num_args()==3) {
			$r2 = func_get_arg(0);
			$g2 = func_get_arg(1);
			$b2 = func_get_arg(2);
		} else if(func_num_args() == 1) {
			$r2 = func_get_arg(0)->red;
			$g2 = func_get_arg(0)->green;
			$b2 = func_get_arg(0)->blue;
		}
		$dr = $this->red - $r2;
		$dg = $this->green - $g2;
		$db = $this->blue - $b2;	
		return sqrt(pow($dr,2)+pow($dg,2)+pow($db,2));
	}
}

class ColorPicker
{
	public $safe_colors;
	public $color_list;
	private static $counter = 0;
	
	public function __construct($num)
	{
		$this->init_safe_colors();
		$this->init_color_list($num);
	}
	
	function init_safe_colors()
	{
		$this->safe_colors = array();
		$c=array('00','33','66','99','CC','FF');
		foreach ($c as $x) {
			foreach ($c as $y) {
				foreach ($c as $z) {
					$r = hexdec($x);
					$g = hexdec($y);
					$b = hexdec($z);
					$color = new Color($r,$g,$b);
					$this->safe_colors[] = $color;
				}
			}
		}
	}
	
	function init_color_list($num)
	{
		$range = 216;
		$offset = rand(0,$range-1);
		$delta = floor($range/$num);
		$this->color_list=array_fill(0, $num, NULL);
		for($i=0;$i<$num;$i++)
		{
			$rand_steps = rand(0,$num-$i-1);
			$j = 0;
			$index = 0;
			while($this->color_list[$index]!= NULL){
					$index++;
			}
			while($j<$rand_steps)
			{
				$index++;
				$j++;
				if($this->color_list[$index]!= NULL)
					$index++;	
			}
			$this->color_list[$index] = $this->safe_colors[($offset+$i*$delta)%$range];
		}	
	}
	
	function getColor()
	{
		return $this->color_list[self::$counter++];
	}
}

?>