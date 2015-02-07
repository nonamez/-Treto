<?php
/*
 * ToDo:
 *     > resize on width
 *     > text with font
 *     > text position
 */

class ImageManager {
	// Путь для сохранения картинок
	private static $_path;
	// Установка пути для сохранения картинок
	public static function setPath($path) 
	{
		self::$_path = $path;
	}
	// содержание изображения
	private $_image_content;
	
	function __construct($image)
	{
		// Если путь для сохранения картинок не указан - пытаемся его создать сами
		if (is_null(self::$_path)) {
			$path = getcwd() . '/images';
			
			if (is_dir($path) == FALSE)
				mkdir($path);
			
			self::setPath($path);
		}
		
		// Проверяем, передана нам картинка или путь на картинку
		if (is_array(@getimagesize($image)))
			$image = file_get_contents($image);

		$this->_image_content = imagecreatefromstring($image);
	}

	// На всякие пожарные...
	function __destruct()
	{
		if (is_resource($this->_image_content))
			$this->destroy();
	}

	// Тут начинается всякого рода картиночная магия суть которой понятна из названия метода...
	
	public function resize($height, $width = FALSE)
	{
		$image = $this->_image_content;
	
		$width  = imagesx($image);
		$height = imagesy($image);

		$thumb_height = 200;
		$thumb_width = round(($width * $thumb_height) / $height);

		$thumb = imagecreatetruecolor($thumb_width, $thumb_height);

		imagecopyresampled($thumb, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

		$this->_image_content = $thumb;

		imagedestroy($image);
	}

	public function addText($text, array $color = array(255, 255, 255))
	{
		array_unshift($color, $this->_image_content);

		$color = call_user_func_array('imagecolorallocate', $color);

		$width  = imagesx($this->_image_content);
		$height = imagesy($this->_image_content);

		// imagettftext($this->_image_content, 25, 0, 75, 300, $color, $font_path, $text);

		ImageString($this->_image_content, 5, 0, 0, $text, $color);
	}
	
	public function save($name, $strip_space = FALSE)
	{
		$ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		$name = $strip_space ? str_replace(' ', '_', $name) : $name;
		$path = self::$_path . '/' . $name;
		
		if ($ext == 'jpg' || $ext == 'jpeg')
			imagejpeg($this->_image_content, $path);
		elseif ($ext == 'gif')
			imagecreatefromgif($this->_image_content, $path);
		elseif ($ext == 'png')
			imagecreatefrompng($this->_image_content, $path);
		
		return $path;
	}

	public function destroy()
	{
		imagedestroy($this->_image_content);
	}
}

