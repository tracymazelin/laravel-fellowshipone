# Fellowship One API

## Installation

### Aritsan

	php artisan bundle:install fellowshipone

### Bundle Registration

Add the following to your **application/bundles.php** file:

	'fellowshipone' => array('auto' => true),

## Configuration


Copy the sample config file to **application/config/fellowshipone.php** and enter your configuration information.

	
## Usage

Place this in your Base_Controller to have access throughout all your controllers:

	public static $f1;

	public function __construct(){
		self::$f1 = IoC::resolve('FellowshipOne');
	}

Make an F1 API call:

	$statuses = self::$f1->get('/v1/people/statuses');	
		
Questions: @tracymazelin