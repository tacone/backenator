<?php

if(!class_exists('Config')){

	class Config {

		public static function get($name){
			
			$name = str_replace('backenator::', '', $name);
			
			$config = array(
				'queryBuilder' => '\EllipseSynergie\Backenator\Query\BaseBuilder',
				'baseUrl' => 'http://api.als11.ellipsesynergie.loc/',
				'autoId' => true,
				'log' => false
			);
			
			if(in_array($name, $config)){
				return $config[$name];
			}
			
			return '';
		}

	}

}