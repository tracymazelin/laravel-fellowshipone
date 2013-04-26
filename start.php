<?php
/**
 * @author Tracy Mazelin
 * @copyright 2013 Tracy Mazelin
 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
 */

Autoloader::map(array(
	'FellowshipOne' => path('app').'libraries/FellowshipOne.php',
));


Laravel\IoC::singleton('FellowshipOne', function()
{
	$settings = Config::get('fellowshipone');
	return new FellowshipOne($settings);
});