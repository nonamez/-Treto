<?php
define('DEBUG', FALSE);

ini_set('max_execution_time', 0);
ini_set('max_input_time', 0);
ini_set('upload_max_filesize', 0);
ini_set('post_max_size', 0);
ini_set('memory_limit', 128*1024*1024);

if (DEBUG == FALSE) {
	error_reporting(0);
	ini_set('display_errors', 0);
}

$image_types = array('jpg', 'jpeg', 'bmp', 'gif');

// Вывод загруженный картинок
if (isset($_GET['get_images'])) {
	$images = glob('thumbs/*.{' . implode(',', $image_types) . '}', GLOB_BRACE);
	
	die(json_encode($images));
}

// Загрузка новых картинок (сразу проверяем есть ли текстовой файл)
if (isset($_FILES['userfile']) && pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION) == 'txt') {
	require_once('php/Helpers.php');
	require_once('php/ImageManager.php');

	// Подгружаем строки из файла
	$images = file($_FILES['userfile']['tmp_name']);

	// Проверяем каждый элемент массива на тип файла и убираем пробелы
	$images = array_map(function ($image) use($image_types) {
		$image = trim($image);

		if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), $image_types) == FALSE)
			return NULL;

		return $image;
	} , $images);

	// Убираем пустые строки и повторы
	$images = array_filter(array_unique($images));

	// Если после уборки мусора нечего не осталось - возвращаем ошибку
	if (count($images) == 0)
		die('<script>parent.ALERT.show()</script>');

	// Подгружаем файл с уже использованными картинками
	$uploaded_images_urls_path = getcwd() . '/links.json';

	// Проверяем его на случай проблем с ним
	if (file_exists($uploaded_images_urls_path))
		$uploaded_images_urls  = @json_decode(file_get_contents($uploaded_images_urls_path));

	$uploaded_images_urls = isset($uploaded_images_urls) ? $uploaded_images_urls : [];

	// Устанавливаем путь для сохранения новых картинок
	ImageManager::setPath(getcwd() . '/thumbs');

	// Проходим через все картинки
	foreach ($images as & $image) {

		// Если картинка уже подгружалась, то пропускаем её.
		if (in_array($image, $uploaded_images_urls)) {
			$image = FALSE;
			
			continue;
		} else {
			// В противном случае проверяем её и добавляем в список
			if (url_exists($image))
				array_push($uploaded_images_urls, $image);
			else {
				// Или же выкидываем
				$image = FALSE;
				
				continue;
			}

		}

		// Тут и так всё понятно
		$name = basename(urldecode($image));

		// Создаём объект для работы с картинкой
		$img_man = new ImageManager($image);
		
		// Изменяем размер до 200 по высоте
		$img_man->resize(200);
		
		// Если есть текст - добавляем его
		if (isset($_POST['watermark']) && strlen($_POST['watermark']) > 0)
			$img_man->addText($_POST['watermark']);
		
		// Сохраняем картинку
		$img_man->save($name);

		// Освобождаем памят
		$img_man->destroy();

		// Ставим новый путь
		$image = 'thumbs/' . $name;
	}

	// Сохраняем уникальные картинки картинки
	file_put_contents(getcwd() . '/links.json', json_encode($uploaded_images_urls));

	// Чистим массив картинок
	$images = array_values(array_filter($images));

	die('<script>parent.generateImageList(' . json_encode($images) . ')</script>');
} else
	die('<script>parent.ALERT.show()</script>');