<?php

if(!class_exists('Config')){

	class Config {

		public static function get($name){
			
			$name = str_replace('backenator::', '', $name);
			
			$config = array(
				'queryBuilder' => '\EllipseSynergie\Backenator\Query\BaseBuilder',
				'baseUrl' => 'http://api.example.com/',
				'autoId' => true,
				'log' => false,
				'options' => array()
			);
			
			if(in_array($name, $config)){
				return $config[$name];
			}
			
			return '';
		}

	}

}