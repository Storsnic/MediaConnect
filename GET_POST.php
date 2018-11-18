<?php

// Для обработки GET/POST по необходимости пользуюсь такими функциями - myPost, myGet


public function myClear($val, $default) {
	switch (gettype($default)) {
		case "integer": return intval($val);
		case "string": return htmlspecialchars($val);
		case "double": return doubleval($val);
		case "float": return floatval($val);
		default: return $val;
	}
}

public function myPost($name, $default, $clear = true) {
	return isset($_POST[$name]) ? (
			($clear) ? $this->myClear($_POST[$name], $default) : $_POST[$name]
			) : $default;
}

public function myGet($name, $default, $clear = true) {
	return isset($_GET[$name]) ? (
			($clear) ? $this->myClear($_GET[$name], $default) : $_GET[$name]
			) : $default;
}




// Пример вызова

$date = $this->myGet('date', '');
$phone = $this->myGet('phone', '');

$mapProvider = $this->myGet('map', '2gis'); //Выбор провайдера карты
if (!in_array($mapProvider, ['yandex', '2gis'])) {
	$mapProvider = '2gis';
}


//--------------------------------------------------


$date = $this->myPost('searchDate', '');
$phone = $this->myPost('searchPhone', '');
//Дата может быть пустой, телефон - нет
if (empty($phone)) {
	throw new CHttpException(400, 'Выберите идентификатор');
}

$timeDot = $this->myPost('timedot', -1);
$distDot = $this->myPost('distdot', -1);


?>