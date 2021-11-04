<?php

namespace vale\tasks;



use vale\Hub;
use vale\tasks\database\MyDatabaseTask;
use vale\tasks\player\PlayerSaveDataTask;

class TaskHandler{

	public static function init(): void{
		$instance = Hub::getInstance();
		$instance->getScheduler()->scheduleRepeatingTask(new PlayerSaveDataTask($instance), 300);
	}
}