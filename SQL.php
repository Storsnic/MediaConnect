<?php

$sql = "SELECT 
			AppointmentBase.Id,
			ApptRef.ReferencedType,
			ApptRef.ReferencedObjectId
		FROM 
			Activity.AppointmentBase AppointmentBase
			JOIN Security.Users Users ON Users.Id = AppointmentBase.OwnerCode
			JOIN Security.UserOrganizationUnits UserOrganizationUnits 
				ON UserOrganizationUnits.UserId = Users.Id
			JOIN Activity.AppointmentReferences ApptRef
			ON ApptRef.AppointmentId = AppointmentBase.Id
		WHERE
			AppointmentBase.ScheduledStart LIKE :date AND 
			AppointmentBase.Status IN (1,2) AND 
			AppointmentBase.OwnerCode = :OwnerCode AND 
			UserOrganizationUnits.OrganizationUnitId IN (16,17,20,33)
			AND ReferencedType in (146, 200)
		";
$meetings = Yii::app()->erm->createCommand($sql)->bindValues([
	':date' => $date.'%',
	':OwnerCode' => $user['OwnerCode'],
])->queryAll();

$appts = ArrayHelper::index($meetings, 'ReferencedObjectId', 'ReferencedType');
$apptsIdIndex = ArrayHelper::index($meetings, 'ReferencedType', ['Id']);




//--------------------------------------------------

$res_sql = Yii::app()->asu->createCommand()
				->select("IF( ph.begin_time > dt.dtfirst, UNIX_TIMESTAMP(ph.begin_time), UNIX_TIMESTAMP(dt.dtfirst) ) AS tf, " .
						"IF( ph.end_time < dt.dtlast, UNIX_TIMESTAMP(ph.end_time), UNIX_TIMESTAMP(dt.dtlast) ) AS tl")
				->from('tracker_dates dt, tracker_phone_history ph')
				->where(
						array('and',
							'dt.phone = ph.phone',
							array('or',
								'ph.ldap_id <> \'\' AND md5(ph.ldap_id) = "' . $hash . '"',
								'ph.ldap_id = \'\' AND md5(ph.phone) = "' . $hash . '"'
							),
							'dt.dtfirst <= ph.end_time',
							'dt.dtlast >= ph.begin_time',
						)
				)
				->order('dt.dtfirst')->text;

$res = yii::app()->cache->get($res_sql);
if ($res === false) {
	$res = Yii::app()->asu->createCommand($res_sql)->queryAll();
	yii::app()->cache->set($res_sql, $res, 100000);
}




//--------------------------------------------------

$sql = Yii::app()->asu->createCommand()
			->select('ind.phone, UNIX_TIMESTAMP(ind.time_first) AS timef, UNIX_TIMESTAMP(ind.time_last) AS timel, ' .
					'ind.lat, ind.lng, ind.speed, ind.accuracy, ind.battery_first, ind.battery_last, ind.provider')
			->from('tracker_data ind, tracker_phone_history ph')
			->where(
					array('and',
				'ind.phone = ph.phone',
				'ind.time_first BETWEEN ph.begin_time AND ph.end_time',
				array('or',
					'ind.time_first BETWEEN FROM_UNIXTIME("' . $this->_fromTimestamp . '") AND FROM_UNIXTIME("' . $this->_toTimestamp . '")',
					array('and', 'not ind.time_last is null', 'ind.time_first <= FROM_UNIXTIME("' . $this->_toTimestamp . '")', 'ind.time_last >= FROM_UNIXTIME("' . $this->_fromTimestamp . '")')
				),
				array('and', 'ph.`ldap_id`<>\'\'', '(ph.`phone`) = "' . $this->getRealUser()->phone . '"'),
					), array(':phone' => $this->getRealUser()->phone, ':dateMin' => $this->_fromTimestamp, ':dateMax' => $this->_toTimestamp)
			)
			->order('ind.time_first ASC')
	->text;

$data = Yii::app()->asu->createCommand($sql)->queryAll();




//--------------------------------------------------

//Получим список пользователей // -- у которых есть координаты
// + сразу фильтруем по id как детей

$model = new TrackerData();
$model->ownerId = yii::app()->user->id;
$ldaps = CHtml::listData(User::model()->ByActive()->by('id', $model->getChilds())->findAll(), 'ldap_id', 'ldap_id');

$sql = Yii::app()->asu->createCommand()
	->select('t1.et, t1.ldap_id, ph.huser_id, ph.phone, ph.begin_time, ph.end_time, ph.battery, ph.signal, ph.imei, ph.version, ph.local_datetime, ph.local_timezone, ph.battery_capacity, ph.android, ph.model_phone, ph.gpservices, ph.lat, ph.lng, ph.latlng_time, ph.latlng_battery, ph.ldap_id')
	->from('(SELECT max(end_time) as et, ldap_id from asu.tracker_phone_history WHERE ldap_id <> "" group by ldap_id ' .
		'UNION SELECT max(end_time) as et, ldap_id from asu.tracker_phone_history WHERE ldap_id = "" group by phone) as t1')
	->leftjoin('tracker_phone_history ph', 't1.et = ph.end_time and t1.ldap_id = ph.ldap_id')
	->order('ph.end_time DESC');
// если НЕ сисады то  добавляем критерий с лдапами иначе выведем всех
if (!in_array(yii::app()->user->name, Yii::app()->params['systemAdmin'])) {//['i.gonnyh', 'sav2k', 's.alex', 'a.nehaev']
	$sql->where(array('in', 'ph.ldap_id', $ldaps));
}
?>